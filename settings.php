<?php
require_once __DIR__ . '/includes/config.php';
requireAdmin();

$msg = '';
$msgType = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    // In a real app we'd save these to a settings table. For now, just simulating.
    $msg = "System settings updated successfully.";
    $msgType = "success";
}

// Get system stats for admin
$stats = [
    'users' => $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'],
    'employees' => $db->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'],
    'departments' => $db->query("SELECT COUNT(*) as c FROM departments")->fetch_assoc()['c'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Employee Management System</title>
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
                    <span class="sidebar-logo-text">Empower</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
                    <?php if (!isEmployee()): ?>
                    <a href="employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
                    <a href="departments.php" class="nav-item"><i class="fas fa-building"></i><span>Departments</span></a>
                    <?php
endif; ?>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="attendance.php" class="nav-item"><i class="fas fa-clock"></i><span>Attendance</span></a>
                    <a href="leave.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Leave Requests</span></a>
                    <?php if (!isEmployee()): ?>
                    <a href="payroll.php" class="nav-item"><i class="fas fa-money-bill-wave"></i><span>Payroll</span></a>
                    <a href="analytics.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Analytics</span></a>
                    <?php
endif; ?>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Profile</span></a>
                    <a href="settings.php" class="nav-item active"><i class="fas fa-cog"></i><span>Settings</span></a>
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
                <h1 class="page-title">System <span>Settings</span></h1>
            </div>

            <?php if ($msg): ?>
                <div class="toast <?php echo $msgType === 'success' ? 'success' : 'danger'; ?>" style="margin-bottom: 20px;">
                    <i class="fas <?php echo $msgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php
endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
                <!-- System Info Panel -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle" style="color: var(--primary-color);"></i> System Information</h3>
                    </div>
                    <div class="card-body">
                        <div style="background: var(--bg-card); padding: 15px; border-radius: var(--border-radius); border: 1px solid var(--border-color); margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--text-muted);">Version</span>
                                <strong>1.5.0 (Vercel Edition)</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--text-muted);">Database</span>
                                <strong>TiDB Cloud (Serverless)</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">PHP Version</span>
                                <strong><?php echo phpversion(); ?></strong>
                            </div>
                        </div>

                        <h4 style="margin: 20px 0 10px; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Database Stats</h4>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-muted);">Registered Users</span>
                            <strong><?php echo $stats['users']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-muted);">Active Employees</span>
                            <strong><?php echo $stats['employees']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Departments</span>
                            <strong><?php echo $stats['departments']; ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Admin Settings Form -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sliders-h" style="color: var(--primary-color);"></i> General Preferences</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="save_settings">
                            
                            <h4 style="margin-bottom: 15px; color: var(--text-primary);">Company Details</h4>
                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-input" value="Empower1 Solutions" required>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label class="form-label">Default Currency</label>
                                    <select name="currency" class="form-input">
                                        <option value="USD">USD ($)</option>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="GBP">GBP (£)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Timezone</label>
                                    <select name="timezone" class="form-input">
                                        <option value="UTC">UTC</option>
                                        <option value="America/New_York">EST (New York)</option>
                                        <option value="Asia/Colombo" selected>IST (Colombo/New Delhi)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <h4 style="margin: 25px 0 15px; color: var(--text-primary); border-top: 1px solid var(--border-color); padding-top: 20px;">Features Toggle</h4>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: rgba(14, 165, 233, 0.05); border: 1px solid var(--border-color); border-radius: 8px;">
                                <div>
                                    <div style="font-weight: 600;">Employee Self-Service</div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">Allow employees to update their own profiles.</div>
                                </div>
                                <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                    <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); border-radius: 24px;">
                                        <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s;"></span>
                                    </span>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 25px; width: 100%;">
                                <i class="fas fa-save"></i> Save System Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
