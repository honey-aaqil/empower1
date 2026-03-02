<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Handle check-in/check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $employeeId = intval($_POST['employee_id']);
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    if ($_POST['action'] === 'checkin') {
        $stmt = $db->prepare("INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, 'present') ON DUPLICATE KEY UPDATE check_in = ?");
        $stmt->bind_param("isss", $employeeId, $today, $now, $now);
        $stmt->execute();
    } elseif ($_POST['action'] === 'checkout') {
        $db->query("UPDATE attendance SET check_out = '$now' WHERE employee_id = $employeeId AND date = '$today'");
    }
    
    redirect('attendance.php');
}

// Get selected date (default to today)
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    $selectedDate = date('Y-m-d');
}
$isToday = ($selectedDate === date('Y-m-d'));

// Get attendance records for the selected date
$attendance = $db->query("
    SELECT 
        a.id, a.employee_id, a.date, a.check_in, a.check_out, a.status, a.notes,
        e.first_name, e.last_name, e.employee_code 
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    WHERE a.date = '$selectedDate'
    
    UNION
    
    SELECT 
        NULL as id, l.employee_id, '$selectedDate' as date, NULL as check_in, NULL as check_out, 'on_leave' as status, l.reason as notes,
        e.first_name, e.last_name, e.employee_code 
    FROM leave_requests l 
    JOIN employees e ON l.employee_id = e.id 
    WHERE l.status = 'approved' 
      AND '$selectedDate' BETWEEN l.start_date AND l.end_date 
      AND l.employee_id NOT IN (
          SELECT employee_id FROM attendance WHERE date = '$selectedDate'
      )
    ORDER BY check_in DESC, first_name ASC
");

// Get employee list for check-in
$employees = $db->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status = 'active' ORDER BY first_name");

// Get monthly statistics
$monthStats = $db->query("SELECT status, COUNT(*) as count FROM attendance WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE()) GROUP BY status");

// Get recent attendance history (last 30 days, grouped by date)
$attendanceHistory = $db->query("SELECT a.date, COUNT(*) as total_records, SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count, SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count, SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count, SUM(CASE WHEN a.status = 'on_leave' THEN 1 ELSE 0 END) as leave_count FROM attendance a WHERE a.date <= CURDATE() AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY a.date ORDER BY a.date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Employee Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="attendance.php" class="nav-item active">
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
                    <a href="analytics.php" class="nav-item">
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
                <h1 class="page-title">Attendance <span>Management</span></h1>
                <div class="header-actions">
                    <div class="live-clock" style="font-size: 1.2rem; color: var(--primary-color); font-weight: 600;"></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="presentCount">0</div>
                            <div class="stat-label">Present Today</div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="absentCount">0</div>
                            <div class="stat-label">Absent</div>
                        </div>
                        <div class="stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="lateCount">0</div>
                            <div class="stat-label">Late</div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="onLeaveCount">0</div>
                            <div class="stat-label">On Leave</div>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in Section -->
            <div class="content-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-fingerprint" style="color: var(--primary-color);"></i> Quick Check-in / Check-out</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="" style="display: flex; gap: 15px; align-items: flex-end;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label">Select Employee</label>
                            <select name="employee_id" class="form-input" required>
                                <option value="">Choose employee</option>
                                <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_code'] . ')'); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="action" value="checkin" class="btn btn-success" style="padding: 14px 30px;">
                            <i class="fas fa-sign-in-alt"></i>
                            Check In
                        </button>
                        <button type="submit" name="action" value="checkout" class="btn btn-danger" style="padding: 14px 30px;">
                            <i class="fas fa-sign-out-alt"></i>
                            Check Out
                        </button>
                    </form>
                </div>
            </div>

            <!-- Attendance for Selected Date -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list" style="color: var(--primary-color);"></i> <?php echo $isToday ? "Today's" : date('M d, Y', strtotime($selectedDate)); ?> Attendance</h3>
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($selectedDate); ?>" style="width: 170px;">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-filter"></i>
                            Filter
                        </button>
                        <?php if (!$isToday): ?>
                        <a href="attendance.php" class="btn btn-primary" style="padding: 10px 15px;">
                            <i class="fas fa-calendar-day"></i> Today
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Employee ID</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $present = $absent = $late = $leave = 0;
                            while ($record = $attendance->fetch_assoc()): 
                                switch($record['status']) {
                                    case 'present': $present++; break;
                                    case 'absent': $absent++; break;
                                    case 'late': $late++; break;
                                    case 'on_leave': $leave++; break;
                                }
                                
                                $duration = '';
                                if ($record['check_in'] && $record['check_out']) {
                                    $checkIn = strtotime($record['check_in']);
                                    $checkOut = strtotime($record['check_out']);
                                    $diff = $checkOut - $checkIn;
                                    $hours = floor($diff / 3600);
                                    $mins = floor(($diff % 3600) / 60);
                                    $duration = "{$hours}h {$mins}m";
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="employee-cell">
                                        <div class="employee-avatar">
                                            <?php echo strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)); ?>
                                        </div>
                                        <div class="employee-info">
                                            <span class="employee-name"><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($record['employee_code']); ?></td>
                                <td><?php echo $record['check_in'] ? date('h:i A', strtotime($record['check_in'])) : '-'; ?></td>
                                <td><?php echo $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '-'; ?></td>
                                <td><?php echo $duration ?: '-'; ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($record['status']) {
                                        'present' => 'badge-success',
                                        'absent' => 'badge-danger',
                                        'late' => 'badge-warning',
                                        'on_leave' => 'badge-info',
                                        default => 'badge-info'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Calendar View -->
            <div class="content-card" style="margin-top: 25px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt" style="color: var(--primary-color);"></i> Monthly Overview</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; text-align: center;">
                        <?php
                        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        foreach ($days as $day) {
                            echo "<div style=\"font-weight: 600; color: var(--text-secondary); padding: 10px;\">$day</div>";
                        }
                        
                        $firstDay = strtotime(date('Y-m-01'));
                        $startDayOfWeek = date('w', $firstDay);
                        $daysInMonth = date('t', $firstDay);
                        
                        // Empty cells for days before month starts
                        for ($i = 0; $i < $startDayOfWeek; $i++) {
                            echo '<div></div>';
                        }
                        
                        // Days of the month
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $date = date('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $isToday = $date === date('Y-m-d');
                            
                            $attendanceCount = $db->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$date' AND status = 'present'")->fetch_assoc()['count'];
                            
                            $bgColor = $attendanceCount > 0 ? 'rgba(14, 165, 233, 0.1)' : 'transparent';
                            $borderColor = $isToday ? 'var(--primary-color)' : 'transparent';
                            
                            echo "<div style=\"aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; background: $bgColor; border: 2px solid $borderColor; border-radius: var(--border-radius); cursor: pointer; transition: var(--transition);\" class=\"calendar-day\">";
                            echo "<span style=\"font-weight: 600;\">$day</span>";
                            if ($attendanceCount > 0) {
                                echo "<span style=\"font-size: 0.75rem; color: var(--primary-color);\">$attendanceCount</span>";
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="content-card" style="margin-top: 25px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history" style="color: var(--primary-color);"></i> Attendance History (Last 30 Days)</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>On Leave</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($attendanceHistory && $attendanceHistory->num_rows > 0): ?>
                            <?php while ($histRow = $attendanceHistory->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M d, Y', strtotime($histRow['date'])); ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('l', strtotime($histRow['date'])); ?></div>
                                </td>
                                <td><span class="badge badge-success"><?php echo $histRow['present_count']; ?></span></td>
                                <td><span class="badge badge-danger"><?php echo $histRow['absent_count']; ?></span></td>
                                <td><span class="badge badge-warning"><?php echo $histRow['late_count']; ?></span></td>
                                <td><span class="badge badge-info"><?php echo $histRow['leave_count']; ?></span></td>
                                <td><strong><?php echo $histRow['total_records']; ?></strong></td>
                                <td>
                                    <a href="attendance.php?date=<?php echo $histRow['date']; ?>" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                    No attendance history found
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Update attendance counts
        document.getElementById('presentCount').textContent = '<?php echo $present; ?>';
        document.getElementById('absentCount').textContent = '<?php echo $absent; ?>';
        document.getElementById('lateCount').textContent = '<?php echo $late; ?>';
        document.getElementById('onLeaveCount').textContent = '<?php echo $leave; ?>';
    </script>
</body>
</html>
