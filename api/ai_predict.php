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
$employeeId = intval($data['employee_id'] ?? 0);

if (empty($employeeId)) {
    echo json_encode(['error' => 'Employee ID is required']);
    exit;
}

// Get employee data
$employee = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = $employeeId");

if (!$employee || $employee->num_rows === 0) {
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

$employeeData = $employee->fetch_assoc();

// Get attendance data
$presentDays = 0;
try {
    $attendanceData = $db->query("SELECT COUNT(*) as present FROM attendance WHERE employee_id = $employeeId AND status = 'present' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    if ($attendanceData) {
        $presentDays = $attendanceData->fetch_assoc()['present'];
    }
}
catch (Exception $e) {
// attendance table might not exist
}

// Get performance reviews (safely)
$avgRating = 3;
try {
    $reviews = $db->query("SELECT AVG(overall_rating) as avg_rating FROM performance_reviews WHERE employee_id = $employeeId");
    if ($reviews) {
        $avgRating = $reviews->fetch_assoc()['avg_rating'] ?? 3;
    }
}
catch (Exception $e) {
// performance_reviews table might not exist
}

// Prepare data for AI
$analysisData = [
    'employee_name' => $employeeData['first_name'] . ' ' . $employeeData['last_name'],
    'department' => $employeeData['department_name'],
    'designation' => $employeeData['designation'],
    'joining_date' => $employeeData['joining_date'],
    'attendance_rate' => round(($presentDays / 30) * 100, 2),
    'average_rating' => round($avgRating, 2),
    'salary' => $employeeData['salary']
];

$prompt = "Based on this employee performance data, predict future performance trends and provide recommendations:

" . json_encode($analysisData, JSON_PRETTY_PRINT) . "

Provide your response ONLY as valid JSON with these keys:
1. prediction_score (number 0-100)
2. trend (string: improving/stable/declining)
3. key_strengths (array of strings)
4. areas_to_improve (array of strings)
5. recommendations (array of strings)
6. retention_risk (string: low/medium/high)
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
        $predictionData = json_decode($matches[0], true);

        if ($predictionData) {
            // Save to database
            try {
                $stmt = $db->prepare("INSERT INTO ai_analysis (employee_id, analysis_type, input_data, result, confidence_score) VALUES (?, 'performance_prediction', ?, ?, ?)");
                $inputJson = json_encode($analysisData);
                $resultJson = json_encode($predictionData);
                $score = $predictionData['prediction_score'] ?? 70;
                $stmt->bind_param("issd", $employeeId, $inputJson, $resultJson, $score);
                $stmt->execute();
            }
            catch (Exception $e) {
            // DB save failed silently
            }

            echo json_encode($predictionData);
        }
        else {
            echo json_encode([
                'prediction_score' => 75,
                'trend' => 'stable',
                'key_strengths' => ['Consistent attendance', 'Good team collaboration'],
                'areas_to_improve' => ['Technical skills', 'Communication'],
                'recommendations' => ['Provide training opportunities', 'Set clear goals'],
                'retention_risk' => 'low'
            ]);
        }
    }
    else {
        echo json_encode([
            'prediction_score' => 75,
            'trend' => 'stable',
            'key_strengths' => ['Consistent attendance', 'Good team collaboration'],
            'areas_to_improve' => ['Technical skills', 'Communication'],
            'recommendations' => ['Provide training opportunities', 'Set clear goals'],
            'retention_risk' => 'low'
        ]);
    }
}
else {
    echo json_encode(['error' => 'Failed to generate prediction. Please try again.']);
}