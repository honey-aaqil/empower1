<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Handle add department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $managerId = intval($_POST['manager_id'] ?? 0);

    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO departments (name, description, manager_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $managerId);
        $stmt->execute();
        redirect('departments.php?msg=added');
    }
}

// Handle delete department
if (isset($_GET['delete']) && isAdmin()) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM departments WHERE id = $id");
    redirect('departments.php?msg=deleted');
}

// Get all departments with employee count
$departments = $db->query("SELECT d.*, COUNT(e.id) as employee_count, MAX(CONCAT(m.first_name, ' ', m.last_name)) as manager_name FROM departments d LEFT JOIN employees e ON d.id = e.department_id LEFT JOIN employees m ON d.manager_id = m.id GROUP BY d.id ORDER BY d.name");

// Get managers for dropdown
$managers = $db->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Employee Management System</title>
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Departments <span>Management</span></h1>
                <div class="header-actions">
                    <button class="btn btn-primary" data-modal="addDepartmentModal">
                        <i class="fas fa-plus"></i>
                        Add Department
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="toast <?php echo $_GET['msg'] === 'deleted' ? 'error' : 'success'; ?>" style="margin-bottom: 20px;">
                    <i class="fas <?php echo $_GET['msg'] === 'deleted' ? 'fa-times-circle' : 'fa-check-circle'; ?>"></i>
                    <span>Department <?php echo $_GET['msg']; ?> successfully</span>
                </div>
            <?php
endif; ?>

            <!-- Departments Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
                <?php while ($dept = $departments->fetch_assoc()): ?>
                <div class="employee-card-3d">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div class="stat-icon blue" style="width: 60px; height: 60px;">
                            <i class="fas fa-building" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="action-btns">
                            <button class="action-btn edit" onclick="editDepartment(<?php echo $dept['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if (isAdmin()): ?>
                            <button class="action-btn delete" onclick="deleteDepartment(<?php echo $dept['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php
    endif; ?>
                        </div>
                    </div>
                    
                    <h3 style="color: var(--text-primary); margin-bottom: 10px;"><?php echo htmlspecialchars($dept['name']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
                        <?php echo htmlspecialchars($dept['description'] ?: 'No description available'); ?>
                    </p>
                    
                    <div style="display: flex; justify-content: space-between; padding: 15px 0; border-top: 1px solid var(--border-color);">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);"><?php echo $dept['employee_count']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Employees</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                                <?php echo htmlspecialchars($dept['manager_name'] ?: 'Not Assigned'); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Manager</div>
                        </div>
                    </div>
                    
                    <button class="btn btn-secondary btn-block" style="margin-top: 15px;" onclick="viewDepartmentEmployees(<?php echo $dept['id']; ?>)">
                        <i class="fas fa-users"></i>
                        View Employees
                    </button>
                </div>
                <?php
endwhile; ?>
            </div>

            <!-- Department Stats -->
            <div class="content-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie" style="color: var(--primary-color);"></i> Department Distribution</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <?php
$departments->data_seek(0);
$totalEmployees = $db->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];

while ($dept = $departments->fetch_assoc()):
    $percentage = $totalEmployees > 0 ? round(($dept['employee_count'] / $totalEmployees) * 100, 1) : 0;
?>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($dept['name']); ?></span>
                                <span style="color: var(--text-muted);"><?php echo $percentage; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php
endwhile; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Department Modal -->
    <div class="modal-overlay" id="addDepartmentModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Department</h3>
                <button class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <form id="addDepartmentForm" method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Department Name *</label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Department Manager</label>
                        <select name="manager_id" class="form-input">
                            <option value="">Select Manager</option>
                            <?php
$managers->data_seek(0);
while ($mgr = $managers->fetch_assoc()):
?>
                            <option value="<?php echo $mgr['id']; ?>"><?php echo htmlspecialchars($mgr['first_name'] . ' ' . $mgr['last_name']); ?></option>
                            <?php
endwhile; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" name="add_department" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" form="addDepartmentForm" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Department
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function editDepartment(id) {
            window.location.href = `edit-department.php?id=${id}`;
        }

        function deleteDepartment(id) {
            if (confirm('Are you sure you want to delete this department?')) {
                window.location.href = `departments.php?delete=${id}`;
            }
        }

        function viewDepartmentEmployees(id) {
            window.location.href = `employees.php?department=${id}`;
        }
    </script>
</body>
</html>
