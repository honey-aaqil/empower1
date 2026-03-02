<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if (empty($id)) {
    echo json_encode(['error' => 'Employee ID is required']);
    exit;
}

$stmt = $db->prepare("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

$employee = $result->fetch_assoc();

// Remove sensitive data
unset($employee['password']);

echo json_encode($employee);
?>