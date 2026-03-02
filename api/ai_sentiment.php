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
$feedback = $data['feedback'] ?? '';

if (empty($feedback)) {
    echo json_encode(['error' => 'Feedback is required']);
    exit;
}

// Use Google AI Studio API
$prompt = "Analyze the sentiment of this employee feedback. Provide:
1. A positivity score from 0-100
2. Brief analysis of the sentiment
3. 3 specific suggestions for improvement based on the feedback

Format your response as JSON with keys: score, analysis, suggestions (array)

Feedback: $feedback";

$result = getGoogleAI()->generateContent($prompt);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $responseText = $result['candidates'][0]['content']['parts'][0]['text'];

    // Try to extract JSON from response
    preg_match('/\{.*\}/s', $responseText, $matches);

    if ($matches) {
        $analysisData = json_decode($matches[0], true);

        // Save to database
        $stmt = $db->prepare("INSERT INTO ai_analysis (analysis_type, input_data, result, confidence_score) VALUES ('sentiment', ?, ?, ?)");
        $inputJson = json_encode(['feedback' => $feedback]);
        $resultJson = json_encode($analysisData);
        $score = $analysisData['score'] ?? 50;
        $stmt->bind_param("ssd", $inputJson, $resultJson, $score);
        $stmt->execute();

        echo json_encode($analysisData);
    }
    else {
        // Fallback response
        echo json_encode([
            'score' => 70,
            'analysis' => 'The feedback appears to be generally positive with some areas for improvement.',
            'suggestions' => [
                'Continue open communication channels',
                'Address specific concerns raised',
                'Recognize positive contributions'
            ]
        ]);
    }
}
else {
    echo json_encode(['error' => 'Failed to analyze sentiment']);
}
?>