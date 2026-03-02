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

Format your response ONLY as valid JSON with keys: score (number), analysis (string), suggestions (array of strings). No other text.

Feedback: $feedback";

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

    // Try to extract JSON from response
    preg_match('/\{.*\}/s', $responseText, $matches);

    if ($matches) {
        $analysisData = json_decode($matches[0], true);

        if ($analysisData && isset($analysisData['score'])) {
            // Save to database
            try {
                $stmt = $db->prepare("INSERT INTO ai_analysis (analysis_type, input_data, result, confidence_score) VALUES ('sentiment', ?, ?, ?)");
                $inputJson = json_encode(['feedback' => $feedback]);
                $resultJson = json_encode($analysisData);
                $score = $analysisData['score'] ?? 50;
                $stmt->bind_param("ssd", $inputJson, $resultJson, $score);
                $stmt->execute();
            }
            catch (Exception $e) {
            // DB save failed silently
            }

            echo json_encode($analysisData);
        }
        else {
            echo json_encode([
                'score' => 70,
                'analysis' => strip_tags($responseText),
                'suggestions' => [
                    'Continue open communication channels',
                    'Address specific concerns raised',
                    'Recognize positive contributions'
                ]
            ]);
        }
    }
    else {
        echo json_encode([
            'score' => 70,
            'analysis' => strip_tags($responseText),
            'suggestions' => [
                'Continue open communication channels',
                'Address specific concerns raised',
                'Recognize positive contributions'
            ]
        ]);
    }
}
else {
    echo json_encode(['error' => 'Failed to analyze sentiment. Please try again.']);
}