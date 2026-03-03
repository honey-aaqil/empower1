<?php
require_once __DIR__ . '/../includes/config.php';
requireManagement();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="analytics_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Overall Stats Header
fputcsv($output, ['EMPLOYEE MANAGEMENT SYSTEM - ANALYTICS REPORT']);
fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
fputcsv($output, []);

// 1. Department Statistics
fputcsv($output, ['--- DEPARTMENT STATISTICS ---']);
fputcsv($output, ['Department Name', 'Employee Count', 'Average Salary ($)']);

$deptStats = $db->query("SELECT d.name, COUNT(e.id) as count, AVG(e.salary) as avg_salary FROM departments d LEFT JOIN employees e ON d.id = e.department_id GROUP BY d.id, d.name ORDER BY count DESC");
$totalEmployees = 0;
while ($row = $deptStats->fetch_assoc()) {
    fputcsv($output, [
        $row['name'],
        $row['count'],
        number_format($row['avg_salary'] ?? 0, 2, '.', '')
    ]);
    $totalEmployees += $row['count'];
}
fputcsv($output, ['TOTAL EMPLOYEES', $totalEmployees, '']);
fputcsv($output, []);

// 2. Attendance Trends (Last 30 Days)
fputcsv($output, ['--- ATTENDANCE TRENDS (LAST 30 DAYS) ---']);
fputcsv($output, ['Date', 'Employees Present']);

$attendanceTrend = $db->query("SELECT DATE_FORMAT(date, '%Y-%m-%d') as day, COUNT(*) as present FROM attendance WHERE status = 'present' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY day ORDER BY day DESC");
while ($row = $attendanceTrend->fetch_assoc()) {
    fputcsv($output, [
        $row['day'],
        $row['present']
    ]);
}
fputcsv($output, []);

// 3. Recent Leaves
fputcsv($output, ['--- RECENT LEAVE REQUESTS ---']);
fputcsv($output, ['Employee', 'Leave Type', 'Start Date', 'End Date', 'Status']);

$recentLeaves = $db->query("SELECT e.first_name, e.last_name, l.leave_type, l.start_date, l.end_date, l.status FROM leaves l JOIN employees e ON l.employee_id = e.id ORDER BY l.created_at DESC LIMIT 20");
while ($row = $recentLeaves->fetch_assoc()) {
    fputcsv($output, [
        $row['first_name'] . ' ' . $row['last_name'],
        ucfirst($row['leave_type']),
        $row['start_date'],
        $row['end_date'],
        ucfirst($row['status'])
    ]);
}

fclose($output);
exit;
?>
