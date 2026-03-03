<?php
require_once __DIR__ . '/includes/config.php';

// Log the logout activity
if (isset($_SESSION['user_id'])) {
    $db->query("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES ({$_SESSION['user_id']}, 'logout', 'User logged out', '{$_SERVER['REMOTE_ADDR']}')");
}

// Clear session
session_unset();
session_destroy();

// Redirect to login
redirect('login.php');
?>
