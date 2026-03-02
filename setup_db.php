<?php
require_once __DIR__ . '/includes/config.php';

$sqlFile = __DIR__ . '/includes/database.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found.");
}

$sqlContent = file_get_contents($sqlFile);

// Replace the hardcoded database name with the configured one
$sqlContent = str_replace('employee_management', DB_NAME, $sqlContent);

// Remove comments
$sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
$sqlContent = preg_replace('/^\s*$/m', '', $sqlContent);

// Split into individual queries
$queries = array_filter(array_map('trim', explode(';', $sqlContent)));

$globalDb = new Database();
$conn = $globalDb->getConnection();

$success = 0;
$failed = 0;

foreach ($queries as $query) {
    if (empty($query))
        continue;

    // Using mysqli_query directly to see errors easily
    if (mysqli_query($conn, $query)) {
        $success++;
        echo "Success: " . substr($query, 0, 50) . "...\n";
    }
    else {
        $failed++;
        echo "Error: " . mysqli_error($conn) . " Query: " . substr($query, 0, 50) . "...\n";
    }
}

echo "\nCompleted. Success: $success, Failed: $failed\n";
