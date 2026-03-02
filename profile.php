<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$msg = '';
$msgType = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $username = $db->escape($_POST['username']);
        $email = $db->escape($_POST['email']);

        $stmt = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $msg = "Profile updated successfully.";
            $msgType = "success";
        }
        else {
            $msg = "Error updating profile. Username or email might already exist.";
            $msgType = "danger";
        }
    }
    elseif ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            $msg = "New passwords do not match.";
            $msgType = "danger";
        }
        else {
            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (password_verify($currentPassword, $user['password'])) {
                $hashedNew = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedNew, $userId);
                $updateStmt->execute();

                $msg = "Password changed successfully.";
                $msgType = "success";
            }
            else {
                $msg = "Incorrect current password.";
                $msgType = "danger";
            }
        }
    }
}

// Get User Data
$stmt = $db->prepare("SELECT username, email, role, created_at, last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Get Employee Link if exists
$empStmt = $db->prepare("SELECT first_name, last_name, designation FROM employees WHERE user_id = ?");
$empStmt->bind_param("i", $userId);
$empStmt->execute();
$empResult = $empStmt->get_result();
$employeeData = $empResult->num_rows > 0 ? $empResult->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Employee Management System</title>
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
                    <a href="payroll.php" class="nav-item"><i class="fas fa-money-bill-wave"></i><span>Payroll</span></a>
                    <a href="analytics.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Analytics</span></a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-item active"><i class="fas fa-user"></i><span>Profile</span></a>
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
                <h1 class="page-title">My <span>Profile</span></h1>
            </div>

            <?php if ($msg): ?>
                <div class="toast <?php echo $msgType === 'success' ? 'success' : 'danger'; ?>" style="margin-bottom: 20px;">
                    <i class="fas <?php echo $msgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php
endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
                <!-- Profile Card -->
                <div class="content-card">
                    <div class="card-body" style="text-align: center; padding: 40px 20px;">
                        <div class="employee-avatar" style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);">
                            <?php echo strtoupper(substr($userData['username'], 0, 1)); ?>
                        </div>
                        <h2 style="color: var(--text-primary); margin-bottom: 5px;"><?php echo htmlspecialchars($userData['username']); ?></h2>
                        <div style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.95rem;"><?php echo htmlspecialchars($userData['email']); ?></div>
                        
                        <span class="badge badge-primary" style="font-size: 0.9rem; padding: 8px 15px;">
                            <i class="fas fa-shield-alt"></i> <?php echo ucfirst($userData['role']); ?> User
                        </span>
                        
                        <div style="margin-top: 30px; text-align: left; background: var(--bg-card); padding: 20px; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <span style="color: var(--text-muted);"><i class="fas fa-calendar-alt"></i> Member Since</span>
                                <strong><?php echo date('M d, Y', strtotime($userData['created_at'])); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);"><i class="fas fa-sign-in-alt"></i> Last Login</span>
                                <strong><?php echo $userData['last_login'] ? date('M d, Y', strtotime($userData['last_login'])) : 'Unknown'; ?></strong>
                            </div>
                        </div>

                        <?php if ($employeeData): ?>
                        <div style="margin-top: 15px; text-align: left; background: var(--bg-card); padding: 20px; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                            <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Linked Employee Record</h4>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--text-muted);">Name</span>
                                <strong><?php echo htmlspecialchars($employeeData['first_name'] . ' ' . $employeeData['last_name']); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Position</span>
                                <strong><?php echo htmlspecialchars($employeeData['designation']); ?></strong>
                            </div>
                        </div>
                        <?php
endif; ?>
                    </div>
                </div>

                <!-- Update Forms -->
                <div>
                    <!-- Account Settings -->
                    <div class="content-card" style="margin-bottom: 25px;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-edit" style="color: var(--primary-color);"></i> Account Settings</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-lock" style="color: var(--primary-color);"></i> Security</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-input" required>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                    <div class="form-group">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-input" required minlength="6">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-input" required minlength="6">
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-secondary" style="margin-top: 10px;">
                                    <i class="fas fa-key"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
