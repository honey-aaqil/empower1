<?php
require_once 'includes/config.php';
requireLogin();

// Handle leave request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_leave'])) {
    $employeeId = intval($_POST['employee_id']);
    $leaveType = sanitize($_POST['leave_type']);
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    $reason = sanitize($_POST['reason']);
    
    $stmt = $db->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $employeeId, $leaveType, $startDate, $endDate, $reason);
    $stmt->execute();
    
    redirect('leave.php?msg=applied');
}

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['id']) && (isAdmin() || $_SESSION['role'] === 'hr')) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] === 'approve' ? 'approved' : 'rejected';
    $userId = $_SESSION['user_id'];
    
    $db->query("UPDATE leave_requests SET status = '$action', approved_by = $userId, approved_at = NOW() WHERE id = $id");
    redirect('leave.php?msg=' . $action);
}

// Get leave requests
$leaves = $db->query("SELECT l.*, e.first_name, e.last_name, e.employee_code, a.username as approved_by_name FROM leave_requests l JOIN employees e ON l.employee_id = e.id LEFT JOIN users a ON l.approved_by = a.id ORDER BY l.created_at DESC");

// Get employees for dropdown
$employees = $db->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - Employee Management System</title>
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
                    <a href="attendance.php" class="nav-item">
                        <i class="fas fa-clock"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="leave.php" class="nav-item active">
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
                <h1 class="page-title">Leave <span>Requests</span></h1>
                <div class="header-actions">
                    <button class="btn btn-primary" data-modal="applyLeaveModal">
                        <i class="fas fa-plus"></i>
                        Apply Leave
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="toast success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span>Leave request <?php echo $_GET['msg']; ?> successfully</span>
                </div>
            <?php endif; ?>

            <!-- Leave Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">
                                <?php echo $db->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'")->fetch_assoc()['count']; ?>
                            </div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">
                                <?php echo $db->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'approved'")->fetch_assoc()['count']; ?>
                            </div>
                            <div class="stat-label">Approved</div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">
                                <?php echo $db->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'rejected'")->fetch_assoc()['count']; ?>
                            </div>
                            <div class="stat-label">Rejected</div>
                        </div>
                        <div class="stat-icon danger">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Requests Table -->
            <div class="content-card" style="margin-top: 25px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list" style="color: var(--primary-color);"></i> All Leave Requests</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = $leaves->fetch_assoc()): 
                                $start = new DateTime($leave['start_date']);
                                $end = new DateTime($leave['end_date']);
                                $days = $start->diff($end)->days + 1;
                            ?>
                            <tr>
                                <td>
                                    <div class="employee-cell">
                                        <div class="employee-avatar">
                                            <?php echo strtoupper(substr($leave['first_name'], 0, 1) . substr($leave['last_name'], 0, 1)); ?>
                                        </div>
                                        <div class="employee-info">
                                            <span class="employee-name"><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></span>
                                            <span class="employee-email"><?php echo htmlspecialchars($leave['employee_code']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $leave['leave_type'])); ?></td>
                                <td><?php echo date('M d', strtotime($leave['start_date'])) . ' - ' . date('M d, Y', strtotime($leave['end_date'])); ?></td>
                                <td><?php echo $days; ?> days</td>
                                <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($leave['status']) {
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-danger',
                                        'pending' => 'badge-warning',
                                        default => 'badge-info'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($leave['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($leave['status'] === 'pending' && (isAdmin() || $_SESSION['role'] === 'hr')): ?>
                                    <div class="action-btns">
                                        <a href="?action=approve&id=<?php echo $leave['id']; ?>" class="action-btn view" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?action=reject&id=<?php echo $leave['id']; ?>" class="action-btn delete" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">
                                        <?php echo $leave['approved_by_name'] ? 'By ' . htmlspecialchars($leave['approved_by_name']) : '-'; ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Apply Leave Modal -->
    <div class="modal-overlay" id="applyLeaveModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Apply for Leave</h3>
                <button class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <form id="applyLeaveForm" method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-input" required>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                            <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type" class="form-input" required>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-input" rows="3" required></textarea>
                    </div>
                    
                    <input type="hidden" name="apply_leave" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" form="applyLeaveForm" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Submit Request
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
