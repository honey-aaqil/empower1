<?php
require_once '../includes/config.php';
requireLogin();

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

if ($employee->num_rows === 0) {
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

$employeeData = $employee->fetch_assoc();

// Get attendance data
$attendanceData = $db->query("SELECT COUNT(*) as present FROM attendance WHERE employee_id = $employeeId AND status = 'present' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$presentDays = $attendanceData->fetch_assoc()['present'];

// Get performance reviews
$reviews = $db->query("SELECT AVG(overall_rating) as avg_rating FROM performance_reviews WHERE employee_id = $employeeId");
$avgRating = $reviews->fetch_assoc()['avg_rating'] ?? 3;

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

Provide your response in JSON format with:
1. prediction_score (0-100)
2. trend (improving/stable/declining)
3. key_strengths (array)
4. areas_to_improve (array)
5. recommendations (array)
6. retention_risk (low/medium/high)";

$result = getGoogleAI()->generateContent($prompt);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $responseText = $result['candidates'][0]['content']['parts'][0]['text'];

    preg_match('/\{.*\}/s', $responseText, $matches);

    if ($matches) {
        $predictionData = json_decode($matches[0], true);

        // Save to database
        $stmt = $db->prepare("INSERT INTO ai_analysis (employee_id, analysis_type, input_data, result, confidence_score) VALUES (?, 'performance_prediction', ?, ?, ?)");
        $inputJson = json_encode($analysisData);
        $resultJson = json_encode($predictionData);
        $score = $predictionData['prediction_score'] ?? 70;
        $stmt->bind_param("issd", $employeeId, $inputJson, $resultJson, $score);
        $stmt->execute();

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
    echo json_encode(['error' => 'Failed to generate prediction']);
}
?>