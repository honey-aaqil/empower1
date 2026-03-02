<?php
ini_set('display_errors', '0');
error_reporting(0);
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$diagnostics = [
    'php_version' => phpversion(),
    'curl_available' => function_exists('curl_init'),
    'json_available' => function_exists('json_encode'),
    'openssl_available' => function_exists('openssl_encrypt'),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s'),
];

// Test config loading
try {
    require_once __DIR__ . '/../includes/config.php';
    $diagnostics['config_loaded'] = true;
    $diagnostics['db_connected'] = isset($db) ? true : false;
    $diagnostics['google_ai_url'] = GOOGLE_AI_API_URL ?? 'not set';
    $diagnostics['session_started'] = session_status() === PHP_SESSION_ACTIVE;
    $diagnostics['session_id'] = session_id() ?: 'none';
    $diagnostics['logged_in'] = isLoggedIn();
}
catch (Exception $e) {
    $diagnostics['config_loaded'] = false;
    $diagnostics['config_error'] = $e->getMessage();
}

// Check for any buffered output (warnings/errors from config)
$buffered = ob_get_clean();
if (!empty($buffered)) {
    $diagnostics['buffered_output'] = substr($buffered, 0, 500);
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
