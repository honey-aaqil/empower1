<?php
require_once __DIR__ . '/includes/config.php';
requireManagement();

// Get department ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('departments.php');
}
$deptId = intval($_GET['id']);

// Handle update form submission
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_department'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $managerId = !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
    
    if (empty($name)) {
        $error_msg = "Department Name is required.";
    } else {
        // Prepare the base query depending on whether the manager exists
        if ($managerId !== null) {
            $stmt = $db->prepare("UPDATE departments SET name = ?, description = ?, manager_id = ? WHERE id = ?");
            $stmt->bind_param("ssii", $name, $description, $managerId, $deptId);
        } else {
            $stmt = $db->prepare("UPDATE departments SET name = ?, description = ?, manager_id = NULL WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $deptId);
        }
        
        if ($stmt->execute()) {
            redirect("departments.php?msg=updated");
        } else {
            $error_msg = "Database Error: " . $stmt->error;
        }
    }
}

// Fetch existing department data
$deptQuery = $db->query("SELECT * FROM departments WHERE id = $deptId");
if ($deptQuery->num_rows === 0) {
    redirect('departments.php');
}
$department = $deptQuery->fetch_assoc();

// Get managers for the dropdown (only active employees from this department)
$managers = $db->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status = 'active' AND department_id = $deptId ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department - Employee Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
                    <span class="sidebar-logo-text">Empower</span>
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
                    <a href="departments.php" class="nav-item active">
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

        <main class="main-content">
            <div class="page-header" style="display: flex; align-items: center; gap: 15px;">
                <a href="departments.php" class="btn btn-secondary" style="padding: 10px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-arrow-left" style="margin: 0;"></i>
                </a>
                <h1 class="page-title" style="margin: 0;">Edit <span>Department</span></h1>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="toast danger" style="margin-bottom: 20px; background-color: var(--danger-color); color: white;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            <?php endif; ?>

            <div class="content-card" style="max-width: 800px; margin: 0 auto; margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">Department Details</h3>
                </div>
                <div class="card-body">
                    <form id="editDepartmentForm" method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Department Name *</label>
                            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($department['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-input" rows="4"><?php echo htmlspecialchars($department['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group" style="margin-top: 25px; padding-top: 25px; border-top: 1px solid var(--border-color);">
                            <label class="form-label" style="font-size: 1.1rem; margin-bottom: 15px;">
                                <i class="fas fa-user-tie" style="color: var(--primary-color); margin-right: 8px;"></i>
                                Department Manager Assignment
                            </label>
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                                Only active employees belonging to this department can be assigned as a manager.
                            </p>
                            <select name="manager_id" class="form-input">
                                <option value="">-- No Manager Assigned --</option>
                                <?php while ($mgr = $managers->fetch_assoc()): ?>
                                    <option value="<?php echo $mgr['id']; ?>" <?php echo ($department['manager_id'] == $mgr['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mgr['first_name'] . ' ' . $mgr['last_name'] . ' (' . $mgr['employee_code'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <input type="hidden" name="update_department" value="1">
                        
                        <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;">
                            <a href="departments.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
