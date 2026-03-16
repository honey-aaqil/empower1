<?php
require_once __DIR__ . '/includes/config.php';
requireManagement();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('employees.php');
}

// Get employee data
$stmt = $db->prepare("
    SELECT e.*, u.username, u.role as user_role, u.id as user_account_id 
    FROM employees e 
    LEFT JOIN users u ON e.user_id = u.id 
    WHERE e.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    $_SESSION['error'] = 'Employee not found';
    redirect('employees.php');
}

// Manager Data Isolation Check
if (isManager() && isset($_SESSION['department_id'])) {
    if ($employee['department_id'] != $_SESSION['department_id']) {
        $_SESSION['error'] = 'Access Denied: You can only edit employees in your own department.';
        redirect('employees.php');
    }
}

// Get departments
$departments = $db->query("SELECT id, name FROM departments ORDER BY name");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Prevent managers from changing an employee's department
    $departmentId = (isManager() && isset($_SESSION['department_id'])) 
        ? intval($_SESSION['department_id']) 
        : intval($_POST['department_id']);
        
    $designation = sanitize($_POST['designation']);
    $joiningDate = sanitize($_POST['joining_date']);
    $employmentType = sanitize($_POST['employment_type'] ?? 'full_time');
    $salary = floatval($_POST['salary'] ?? 0);
    $address = sanitize($_POST['address'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    $username = '';
    $userRole = '';
    $password = '';
    $accountError = '';

    if (isAdmin()) {
        $username = sanitize($_POST['username'] ?? '');
        $userRole = sanitize($_POST['user_role'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($userRole)) {
            $accountError = 'Username and role are required for the account.';
        } else {
            $checkUser = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $checkUserId = $employee['user_account_id'] ?? 0;
            $checkUser->bind_param("si", $username, $checkUserId);
            $checkUser->execute();
            if ($checkUser->get_result()->num_rows > 0) {
                $accountError = 'Username already taken by another account.';
            }
        }
    }

    // Check if email exists for another employee
    $checkEmail = $db->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Email already used by another employee';
    } else if ($accountError) {
        $_SESSION['error'] = $accountError;
    }
    else {
        $updateStmt = $db->prepare("UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, department_id=?, designation=?, joining_date=?, employment_type=?, salary=?, address=?, status=? WHERE id=?");
        $updateStmt->bind_param("ssssisssdssi", $firstName, $lastName, $email, $phone, $departmentId, $designation, $joiningDate, $employmentType, $salary, $address, $status, $id);

        if ($updateStmt->execute()) {
            // Update User Account if Admin
            if (isAdmin()) {
                if (!empty($employee['user_account_id'])) {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $uStmt = $db->prepare("UPDATE users SET username=?, role=?, password=? WHERE id=?");
                        $uStmt->bind_param("sssi", $username, $userRole, $hash, $employee['user_account_id']);
                    } else {
                        $uStmt = $db->prepare("UPDATE users SET username=?, role=? WHERE id=?");
                        $uStmt->bind_param("ssi", $username, $userRole, $employee['user_account_id']);
                    }
                    $uStmt->execute();
                } else {
                    $hash = password_hash(empty($password) ? 'employee123' : $password, PASSWORD_DEFAULT);
                    $uStmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $uStmt->bind_param("ssss", $username, $email, $hash, $userRole);
                    if ($uStmt->execute()) {
                        $newUserId = $uStmt->insert_id;
                        $db->query("UPDATE employees SET user_id = $newUserId WHERE id = $id");
                    }
                }
            }

            // Log activity
            $db->query("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES ({$_SESSION['user_id']}, 'edit_employee', 'Updated employee ID: $id', '{$_SERVER['REMOTE_ADDR']}')");
            $_SESSION['success'] = 'Employee updated successfully';
            redirect('employees.php');
        }
        else {
            $_SESSION['error'] = 'Error updating employee';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Employee Management System</title>
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
                    <a href="employees.php" class="nav-item active">
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
                <h1 class="page-title">Edit <span>Employee</span></h1>
                <div class="header-actions">
                    <a href="employees.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Employees
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="toast error" style="margin-bottom: 20px;">
                    <i class="fas fa-times-circle"></i>
                    <span><?php echo $_SESSION['error'];
    unset($_SESSION['error']); ?></span>
                </div>
            <?php
endif; ?>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit" style="color: var(--primary-color);"></i>
                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                        <span class="badge badge-info" style="margin-left: 10px; font-size: 0.75rem;"><?php echo htmlspecialchars($employee['employee_code']); ?></span>
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Department *</label>
                                <?php if (isManager()): ?>
                                    <!-- Managers cannot change an employee's department -->
                                    <select name="department_id" class="form-input" style="background-color: rgba(0,0,0,0.2); cursor: not-allowed;" disabled>
                                        <?php while ($dept = $departments->fetch_assoc()): ?>
                                            <?php if ($dept['id'] == $employee['department_id']): ?>
                                            <option value="<?php echo $dept['id']; ?>" selected>
                                                <?php echo htmlspecialchars($dept['name']); ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="hidden" name="department_id" value="<?php echo $employee['department_id']; ?>">
                                <?php else: ?>
                                    <select name="department_id" class="form-input" required>
                                        <option value="">Select Department</option>
                                        <?php while ($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo $dept['id'] == $employee['department_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Designation *</label>
                                <input type="text" name="designation" class="form-input" value="<?php echo htmlspecialchars($employee['designation']); ?>" required>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Joining Date *</label>
                                <input type="date" name="joining_date" class="form-input" value="<?php echo htmlspecialchars($employee['joining_date']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-input">
                                    <option value="full_time" <?php echo $employee['employment_type'] === 'full_time' ? 'selected' : ''; ?>>Full Time</option>
                                    <option value="part_time" <?php echo $employee['employment_type'] === 'part_time' ? 'selected' : ''; ?>>Part Time</option>
                                    <option value="contract" <?php echo $employee['employment_type'] === 'contract' ? 'selected' : ''; ?>>Contract</option>
                                    <option value="intern" <?php echo $employee['employment_type'] === 'intern' ? 'selected' : ''; ?>>Intern</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Salary</label>
                                <input type="number" name="salary" class="form-input" step="0.01" min="0" value="<?php echo $employee['salary'] ?? 0; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-input">
                                    <option value="active" <?php echo $employee['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $employee['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="on_leave" <?php echo $employee['status'] === 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                                    <option value="terminated" <?php echo $employee['status'] === 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-input" rows="3"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <?php if (isAdmin()): ?>
                        <hr style="border:0; border-top: 1px solid var(--border-color); margin: 30px 0;">
                        <h4 style="margin-bottom: 20px; color: var(--primary-color); font-size: 1.2rem;"><i class="fas fa-key"></i> Account &amp; Authentication</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($employee['username'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">System Role</label>
                                <select name="user_role" class="form-input" required>
                                    <option value="employee" <?php echo ($employee['user_role'] ?? '') === 'employee' ? 'selected' : ''; ?>>Employee (Self-Service)</option>
                                    <option value="manager" <?php echo ($employee['user_role'] ?? '') === 'manager' ? 'selected' : ''; ?>>Department Manager</option>
                                    <option value="hr" <?php echo ($employee['user_role'] ?? '') === 'hr' ? 'selected' : ''; ?>>HR Manager</option>
                                    <option value="admin" <?php echo ($employee['user_role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-input" placeholder="Leave blank to keep existing password" autocomplete="new-password">
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 15px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <a href="employees.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
