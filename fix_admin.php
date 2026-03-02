<?php
require_once __DIR__ . '/includes/config.php';

$globalDb = new Database();
$conn = $globalDb->getConnection();

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$query = "UPDATE users SET password = '$hash' WHERE username = 'admin'";

if (mysqli_query($conn, $query)) {
    echo "Admin password successfully reset to 'admin123'.";
}
else {
    echo "Error updating password: " . mysqli_error($conn);
}
?>
