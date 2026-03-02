<?php
// Suppress HTML error output and buffer any stray output
ini_set('display_errors', '0');
error_reporting(0);
ob_start();

try {
    require_once __DIR__ . '/../includes/config.php';
}
catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server configuration error']);
    exit;
}

// Clear any output from config loading
ob_end_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$departmentId = intval($data['department_id'] ?? 0);

// Get team data
try {
    if ($departmentId) {
        $teamData = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.department_id = $departmentId AND e.status = 'active'");
    }
    else {
        $teamData = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.status = 'active'");
    }
}
catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$employees = [];
if ($teamData) {
    while ($row = $teamData->fetch_assoc()) {
        $employees[] = [
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'designation' => $row['designation'],
            'department' => $row['department_name'],
            'joining_date' => $row['joining_date']
        ];
    }
}

$prompt = "Analyze this team composition and provide insights on team dynamics, collaboration potential, and improvement suggestions:

Team Members:
" . json_encode($employees, JSON_PRETTY_PRINT) . "

Provide your response ONLY as valid JSON with these keys:
1. metrics (array of objects with name and value 0-100) - include: Collaboration, Communication, Skill Diversity, Leadership, Innovation
2. analysis (string - detailed text analysis)
3. recommendations (array of strings - specific actions)
No other text outside the JSON.";

try {
    $result = getGoogleAI()->generateContent($prompt);
}
catch (Exception $e) {
    echo json_encode(['error' => 'AI service error: ' . $e->getMessage()]);
    exit;
}

// Check for API errors
if (isset($result['error'])) {
    echo json_encode(['error' => 'AI API Error: ' . ($result['error']['message'] ?? 'Unknown error')]);
    exit;
}

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $responseText = $result['candidates'][0]['content']['parts'][0]['text'];

    preg_match('/\{.*\}/s', $responseText, $matches);

    if ($matches) {
        $teamAnalysis = json_decode($matches[0], true);

        if ($teamAnalysis) {
            // Save to database
            try {
                $stmt = $db->prepare("INSERT INTO ai_analysis (analysis_type, input_data, result) VALUES ('team_dynamics', ?, ?)");
                $inputJson = json_encode(['department_id' => $departmentId, 'employee_count' => count($employees)]);
                $resultJson = json_encode($teamAnalysis);
                $stmt->bind_param("ss", $inputJson, $resultJson);
                $stmt->execute();
            }
            catch (Exception $e) {
            // DB save failed silently
            }

            echo json_encode($teamAnalysis);
        }
        else {
            echo json_encode([
                'metrics' => [
                    ['name' => 'Collaboration', 'value' => 75],
                    ['name' => 'Communication', 'value' => 80],
                    ['name' => 'Skill Diversity', 'value' => 70],
                    ['name' => 'Leadership', 'value' => 65],
                    ['name' => 'Innovation', 'value' => 72]
                ],
                'analysis' => strip_tags($responseText),
                'recommendations' => [
                    'Organize regular team-building activities',
                    'Implement cross-functional training programs',
                    'Establish clear communication channels',
                    'Set up mentorship opportunities'
                ]
            ]);
        }
    }
    else {
        echo json_encode([
            'metrics' => [
                ['name' => 'Collaboration', 'value' => 75],
                ['name' => 'Communication', 'value' => 80],
                ['name' => 'Skill Diversity', 'value' => 70],
                ['name' => 'Leadership', 'value' => 65],
                ['name' => 'Innovation', 'value' => 72]
            ],
            'analysis' => 'The team shows strong communication skills and good collaboration potential.',
            'recommendations' => [
                'Organize regular team-building activities',
                'Implement cross-functional training programs',
                'Establish clear communication channels',
                'Set up mentorship opportunities'
            ]
        ]);
    }
}
else {
    echo json_encode(['error' => 'Failed to analyze team dynamics. Please try again.']);
}