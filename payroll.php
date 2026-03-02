<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Handle Payroll Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate') {
    $employees = $db->query("SELECT id, salary FROM employees WHERE status = 'active'");

    while ($emp = $employees->fetch_assoc()) {
        $empId = $emp['id'];
        $baseSalary = $emp['salary'];

        // Simple default calculations (can be complex in a real app)
        $allowances = $baseSalary * 0.10; // 10% allowance as default
        $tax = $baseSalary * 0.15; // 15% flat tax default
        $deductions = $tax; // Total deductions just tax for now
        $netSalary = $baseSalary + $allowances - $deductions;

        // Use REPLACE OR INSERT depending on DB, but 'ON DUPLICATE KEY UPDATE' is safer
        $stmt = $db->prepare("INSERT INTO payroll (employee_id, month, year, basic_salary, allowances, deductions, tax, net_salary, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending') 
                              ON DUPLICATE KEY UPDATE 
                              basic_salary = VALUES(basic_salary),
                              allowances = VALUES(allowances),
                              deductions = VALUES(deductions),
                              tax = VALUES(tax),
                              net_salary = VALUES(net_salary)");

        $stmt->bind_param("iiiddddd", $empId, $selectedMonth, $selectedYear, $baseSalary, $allowances, $deductions, $tax, $netSalary);
        $stmt->execute();
    }
    redirect("payroll.php?month=$selectedMonth&year=$selectedYear&msg=generated");
}

// Handle Status Change
if (isset($_GET['mark_paid']) && isset($_GET['id'])) {
    $payId = intval($_GET['id']);
    $today = date('Y-m-d');
    $db->query("UPDATE payroll SET status = 'paid', payment_date = '$today' WHERE id = $payId");
    redirect("payroll.php?month=$selectedMonth&year=$selectedYear&msg=paid");
}

// Get Payroll Records
$payrollRecords = $db->query("
    SELECT p.*, e.first_name, e.last_name, e.employee_code, d.name as department_name 
    FROM payroll p 
    JOIN employees e ON p.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE p.month = $selectedMonth AND p.year = $selectedYear
    ORDER BY e.first_name
");

// Stats for current month
$statsQuery = $db->query("
    SELECT 
        COUNT(id) as total_slips,
        SUM(net_salary) as total_payout,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count
    FROM payroll 
    WHERE month = $selectedMonth AND year = $selectedYear
");
$stats = $statsQuery->fetch_assoc();

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$years = range(date('Y') - 5, date('Y') + 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll - Employee Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="bg-3d-container">
        <div class="bg-3d-cube"></div>
        <div class="bg-3d-cube"></div>
        <div class="bg-3d-cube"></div>
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
    </div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon"><i class="fas fa-users-cog"></i></div>
                    <span class="sidebar-logo-text">EMS</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
                    <a href="employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
                    <a href="departments.php" class="nav-item"><i class="fas fa-building"></i><span>Departments</span></a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="attendance.php" class="nav-item"><i class="fas fa-clock"></i><span>Attendance</span></a>
                    <a href="leave.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Leave Requests</span></a>
                    <a href="payroll.php" class="nav-item active"><i class="fas fa-money-bill-wave"></i><span>Payroll</span></a>
                    <a href="analytics.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Analytics</span></a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Profile</span></a>
                    <?php if (isAdmin()): ?>
                    <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
                    <?php
endif; ?>
                    <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role"><?php echo $_SESSION['role']; ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Payroll <span>Management</span></h1>
                <div class="header-actions">
                    <form method="POST" action="" style="display:inline-block;">
                        <input type="hidden" name="action" value="generate">
                        <button type="submit" class="btn btn-primary" <?php echo !isAdmin() ? 'disabled' : ''; ?>>
                            <i class="fas fa-cog"></i> Generate Payroll
                        </button>
                    </form>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'generated'): ?>
                    <div class="toast success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <span>Payroll generated successfully for <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?></span>
                    </div>
                <?php
    elseif ($_GET['msg'] === 'paid'): ?>
                    <div class="toast success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <span>Salary marked as paid successfully</span>
                    </div>
                <?php
    endif; ?>
            <?php
endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$<?php echo number_format($stats['total_payout'] ?? 0, 2); ?></div>
                            <div class="stat-label">Total Payout (<?php echo $months[$selectedMonth]; ?>)</div>
                        </div>
                        <div class="stat-icon blue"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $stats['paid_count'] ?? 0; ?> / <?php echo $stats['total_slips'] ?? 0; ?></div>
                            <div class="stat-label">Employees Paid</div>
                        </div>
                        <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
                    </div>
                </div>
            </div>

            <div class="content-card" style="margin-top: 25px;">
                <div class="card-header">
                    <h3 class="card-title">Salary Records</h3>
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <select name="month" class="form-input" style="width: auto;">
                            <?php foreach ($months as $m => $name): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m === $selectedMonth ? 'selected' : ''; ?>><?php echo $name; ?></option>
                            <?php
endforeach; ?>
                        </select>
                        <select name="year" class="form-input" style="width: auto;">
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y === $selectedYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php
endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                    </form>
                </div>
                
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Basic Salary</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payrollRecords && $payrollRecords->num_rows > 0): ?>
                                <?php while ($record = $payrollRecords->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="employee-cell">
                                            <div class="employee-avatar">
                                                <?php echo strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)); ?>
                                            </div>
                                            <div class="employee-info">
                                                <span class="employee-name"><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></span>
                                                <span class="employee-email"><?php echo htmlspecialchars($record['employee_code']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['department_name'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo number_format($record['basic_salary'], 2); ?></td>
                                    <td><strong>$<?php echo number_format($record['net_salary'], 2); ?></strong></td>
                                    <td>
                                        <?php if ($record['status'] === 'paid'): ?>
                                            <span class="badge badge-success">Paid</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:3px;"><?php echo date('M d, Y', strtotime($record['payment_date'])); ?></div>
                                        <?php
        else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php
        endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['status'] !== 'paid' && isAdmin()): ?>
                                            <a href="payroll.php?mark_paid=1&id=<?php echo $record['id']; ?>&month=<?php echo $selectedMonth; ?>&year=<?php echo $selectedYear; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Mark as paid?');">
                                                Mark Paid
                                            </a>
                                        <?php
        endif; ?>
                                    </td>
                                </tr>
                                <?php
    endwhile; ?>
                            <?php
else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        <i class="fas fa-file-invoice-dollar" style="font-size: 2.5rem; margin-bottom: 15px; display: block; filter: grayscale(1); opacity: 0.5;"></i>
                                        No payroll records found for <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?>.<br>
                                        <?php if (isAdmin()): ?>Click "Generate Payroll" to calculate salaries for this month.<?php
    endif; ?>
                                    </td>
                                </tr>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
