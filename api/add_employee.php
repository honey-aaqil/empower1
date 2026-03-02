<?php
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Generate employee code
$year = date('Y');
$lastEmployee = $db->query("SELECT employee_code FROM employees WHERE employee_code LIKE 'EMP{$year}%' ORDER BY id DESC LIMIT 1");

if ($lastEmployee && $lastEmployee->num_rows > 0) {
    $lastCode = $lastEmployee->fetch_assoc()['employee_code'];
    $lastNum = intval(substr($lastCode, -3));
    $newNum = $lastNum + 1;
} else {
    $newNum = 1;
}

$employeeCode = 'EMP' . $year . str_pad($newNum, 3, '0', STR_PAD_LEFT);

// Get form data
$firstName = sanitize($_POST['first_name']);
$lastName = sanitize($_POST['last_name']);
$email = sanitize($_POST['email']);
$phone = sanitize($_POST['phone'] ?? '');
$departmentId = intval($_POST['department_id']);
$designation = sanitize($_POST['designation']);
$joiningDate = sanitize($_POST['joining_date']);
$employmentType = sanitize($_POST['employment_type'] ?? 'full_time');
$salary = floatval($_POST['salary'] ?? 0);
$address = sanitize($_POST['address'] ?? '');

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($departmentId) || empty($designation) || empty($joiningDate)) {
    $_SESSION['error'] = 'Please fill in all required fields';
    redirect('../employees.php');
}

// Check if email already exists
$checkEmail = $db->prepare("SELECT id FROM employees WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$result = $checkEmail->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'Email already exists';
    redirect('../employees.php');
}

// Insert employee
$stmt = $db->prepare("INSERT INTO employees (employee_code, first_name, last_name, email, phone, department_id, designation, joining_date, employment_type, salary, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
$stmt->bind_param("sssssisssds", $employeeCode, $firstName, $lastName, $email, $phone, $departmentId, $designation, $joiningDate, $employmentType, $salary, $address);

if ($stmt->execute()) {
    $employeeId = $db->lastInsertId();
    
    // Log activity
    $db->query("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES ({$_SESSION['user_id']}, 'add_employee', 'Added employee: $employeeCode', '{$_SERVER['REMOTE_ADDR']}')");
    
    // Create user account for employee
    $username = strtolower($firstName . '.' . $lastName);
    $password = password_hash('employee123', PASSWORD_DEFAULT);
    
    $userStmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'employee')");
    $userStmt->bind_param("sss", $username, $email, $password);
    $userStmt->execute();
    
    $userId = $db->lastInsertId();
    $db->query("UPDATE employees SET user_id = $userId WHERE id = $employeeId");
    
    $_SESSION['success'] = 'Employee added successfully';
} else {
    $_SESSION['error'] = 'Error adding employee: ' . $stmt->error;
}

redirect('../employees.php');
?>