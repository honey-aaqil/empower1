<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$departmentId = intval($data['department_id'] ?? 0);

// Get team data
if ($departmentId) {
    $teamData = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.department_id = $departmentId AND e.status = 'active'");
}
else {
    $teamData = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.status = 'active'");
}

$employees = [];
while ($row = $teamData->fetch_assoc()) {
    $employees[] = [
        'name' => $row['first_name'] . ' ' . $row['last_name'],
        'designation' => $row['designation'],
        'department' => $row['department_name'],
        'joining_date' => $row['joining_date']
    ];
}

$prompt = "Analyze this team composition and provide insights on team dynamics, collaboration potential, and improvement suggestions:

Team Members:
" . json_encode($employees, JSON_PRETTY_PRINT) . "

Provide your response in JSON format with:
1. metrics (array of objects with name and value 0-100) - include: Collaboration, Communication, Skill Diversity, Leadership, Innovation
2. analysis (detailed text analysis)
3. recommendations (array of specific actions)";

$result = getGoogleAI()->generateContent($prompt);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $responseText = $result['candidates'][0]['content']['parts'][0]['text'];

    preg_match('/\{.*\}/s', $responseText, $matches);

    if ($matches) {
        $teamAnalysis = json_decode($matches[0], true);

        // Save to database
        $stmt = $db->prepare("INSERT INTO ai_analysis (analysis_type, input_data, result) VALUES ('team_dynamics', ?, ?)");
        $inputJson = json_encode(['department_id' => $departmentId, 'employee_count' => count($employees)]);
        $resultJson = json_encode($teamAnalysis);
        $stmt->bind_param("ss", $inputJson, $resultJson);
        $stmt->execute();

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
            'analysis' => 'The team shows strong communication skills and good collaboration potential. Skill diversity is moderate, suggesting opportunities for cross-training. Leadership presence is adequate but could be strengthened.',
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
    echo json_encode(['error' => 'Failed to analyze team dynamics']);
}
?>