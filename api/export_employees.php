<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="employees_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['Employee Code', 'First Name', 'Last Name', 'Email', 'Phone', 'Department', 'Designation', 'Joining Date', 'Employment Type', 'Status', 'Salary']);

// Get employees
$employees = $db->query("SELECT e.employee_code, e.first_name, e.last_name, e.email, e.phone, d.name as department, e.designation, e.joining_date, e.employment_type, e.status, e.salary FROM employees e LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.first_name");

while ($employee = $employees->fetch_assoc()) {
    fputcsv($output, [
        $employee['employee_code'],
        $employee['first_name'],
        $employee['last_name'],
        $employee['email'],
        $employee['phone'],
        $employee['department'],
        $employee['designation'],
        $employee['joining_date'],
        $employee['employment_type'],
        $employee['status'],
        $employee['salary']
    ]);
}

fclose($output);
exit;
?>