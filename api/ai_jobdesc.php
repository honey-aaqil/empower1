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
$role = sanitize($data['role'] ?? '');
$requirements = sanitize($data['requirements'] ?? '');

if (empty($role) || empty($requirements)) {
    echo json_encode(['error' => 'Role and requirements are required']);
    exit;
}

$prompt = "Generate a professional job description for the following position:

Role: $role
Requirements: $requirements

Please include:
1. Job Title
2. Company Overview
3. Job Responsibilities (5-7 bullet points)
4. Required Qualifications
5. Preferred Qualifications
6. Benefits
7. How to Apply

Format it professionally with clear sections.";

$result = $googleAI->generateContent($prompt);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $jobDescription = $result['candidates'][0]['content']['parts'][0]['text'];
    
    // Save to database
    $stmt = $db->prepare("INSERT INTO ai_analysis (analysis_type, input_data, result) VALUES ('custom', ?, ?)");
    $inputJson = json_encode(['role' => $role, 'requirements' => $requirements]);
    $stmt->bind_param("ss", $inputJson, $jobDescription);
    $stmt->execute();
    
    echo json_encode([
        'job_description' => nl2br(htmlspecialchars($jobDescription)),
        'raw_description' => $jobDescription
    ]);
} else {
    echo json_encode(['error' => 'Failed to generate job description']);
}
?>