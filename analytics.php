<?php
require_once 'includes/config.php';
requireLogin();

// Get analytics data
$employeeGrowth = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM employees GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 12");
$attendanceTrend = $db->query("SELECT DATE_FORMAT(date, '%Y-%m-%d') as day, COUNT(*) as present FROM attendance WHERE status = 'present' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY day ORDER BY day");
$departmentStats = $db->query("SELECT d.name, COUNT(e.id) as count, AVG(e.salary) as avg_salary FROM departments d LEFT JOIN employees e ON d.id = e.department_id GROUP BY d.id, d.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Employee Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- 3D Background -->
    <div class="bg-3d-container">
        <div class="bg-3d-cube"></div>
        <div class="bg-3d-cube"></div>
        <div class="bg-3d-cube"></div>
        <div class="bg-3d-cube"></div>
        <div class="bg-3d-cube"></div>
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <span class="sidebar-logo-text">EMS</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="employees.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Employees</span>
                    </a>
                    <a href="departments.php" class="nav-item">
                        <i class="fas fa-building"></i>
                        <span>Departments</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="attendance.php" class="nav-item">
                        <i class="fas fa-clock"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="leave.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Leave Requests</span>
                    </a>
                    <a href="payroll.php" class="nav-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payroll</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">AI Features</div>
                    <a href="ai-features.php" class="nav-item">
                        <i class="fas fa-robot"></i>
                        <span>AI Insights</span>
                    </a>
                    <a href="analytics.php" class="nav-item active">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-item">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role"><?php echo $_SESSION['role']; ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Analytics <span>& Reports</span></h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="exportReport()">
                        <i class="fas fa-download"></i>
                        Export Report
                    </button>
                </div>
            </div>

            <!-- Charts Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px;">
                <!-- Employee Growth Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line" style="color: var(--primary-color);"></i> Employee Growth</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-3d-container">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Attendance Trend Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clock" style="color: #10b981;"></i> Attendance Trend (30 Days)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-3d-container">
                            <canvas id="attendanceTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Department Distribution -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-building" style="color: #8b5cf6;"></i> Department Distribution</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-3d-container">
                            <canvas id="deptChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Salary Analysis -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-dollar-sign" style="color: #f59e0b;"></i> Average Salary by Department</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-3d-container">
                            <canvas id="salaryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="content-card" style="margin-top: 25px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table" style="color: var(--primary-color);"></i> Department Summary</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Employee Count</th>
                                <th>Average Salary</th>
                                <th>% of Workforce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$totalEmployees = $db->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];
while ($dept = $departmentStats->fetch_assoc()):
    $percentage = $totalEmployees > 0 ? round(($dept['count'] / $totalEmployees) * 100, 1) : 0;
?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                <td><?php echo $dept['count']; ?></td>
                                <td>$<?php echo number_format($dept['avg_salary'] ?? 0, 2); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="progress-bar" style="width: 100px;">
                                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <?php echo $percentage; ?>%
                                    </div>
                                </td>
                            </tr>
                            <?php
endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Employee Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: <?php
$growthLabels = [];
$growthData = [];
while ($row = $employeeGrowth->fetch_assoc()) {
    $growthLabels[] = date('M Y', strtotime($row['month'] . '-01'));
    $growthData[] = $row['count'];
}
echo json_encode(array_reverse($growthLabels));
?>,
                datasets: [{
                    label: 'New Employees',
                    data: <?php echo json_encode(array_reverse($growthData)); ?>,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(14, 165, 233, 0.1)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Attendance Trend Chart
        const attendanceCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: <?php
$attLabels = [];
$attData = [];
while ($row = $attendanceTrend->fetch_assoc()) {
    $attLabels[] = date('M d', strtotime($row['day']));
    $attData[] = $row['present'];
}
echo json_encode($attLabels);
?>,
                datasets: [{
                    label: 'Present',
                    data: <?php echo json_encode($attData); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(14, 165, 233, 0.1)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Department Distribution Chart
        const deptCtx = document.getElementById('deptChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: <?php
$deptLabels = [];
$deptData = [];
$departmentStats->data_seek(0);
while ($row = $departmentStats->fetch_assoc()) {
    $deptLabels[] = $row['name'];
    $deptData[] = $row['count'];
}
echo json_encode($deptLabels);
?>,
                datasets: [{
                    data: <?php echo json_encode($deptData); ?>,
                    backgroundColor: ['#0ea5e9', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#22d3ee']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%'
            }
        });

        // Salary Chart
        const salaryCtx = document.getElementById('salaryChart').getContext('2d');
        new Chart(salaryCtx, {
            type: 'bar',
            data: {
                labels: <?php
$salaryLabels = [];
$salaryData = [];
$departmentStats->data_seek(0);
while ($row = $departmentStats->fetch_assoc()) {
    $salaryLabels[] = $row['name'];
    $salaryData[] = $row['avg_salary'];
}
echo json_encode($salaryLabels);
?>,
                datasets: [{
                    label: 'Average Salary',
                    data: <?php echo json_encode($salaryData); ?>,
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(14, 165, 233, 0.1)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        function exportReport() {
            showToast('Generating report...', 'info');
            setTimeout(() => {
                window.open('api/export_analytics.php', '_blank');
            }, 1000);
        }
    </script>
</body>
</html>
