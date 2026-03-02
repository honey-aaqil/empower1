<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Get dashboard statistics
$totalEmployees = $db->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];
$totalDepartments = $db->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];
$todayAttendance = $db->query("SELECT COUNT(*) as count FROM attendance WHERE date = CURDATE() AND status = 'present'")->fetch_assoc()['count'];
$pendingLeaves = $db->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'")->fetch_assoc()['count'];

// Get recent employees
$recentEmployees = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.created_at DESC LIMIT 5");

// Get attendance data for chart
$attQuery = $db->query("
    SELECT DATE_FORMAT(date, '%a') as day, 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
    FROM attendance 
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY date, day ORDER BY date ASC LIMIT 5
");
$attLabels = [];
$attPresent = [];
$attAbsent = [];
while ($row = $attQuery->fetch_assoc()) {
    $attLabels[] = $row['day'];
    $attPresent[] = $row['present_count'];
    $attAbsent[] = $row['absent_count'];
}

// Get department distribution
$deptDistribution = $db->query("SELECT d.name, COUNT(e.id) as count FROM departments d LEFT JOIN employees e ON d.id = e.department_id GROUP BY d.id, d.name");
$deptLabels = [];
$deptData = [];
while ($row = $deptDistribution->fetch_assoc()) {
    $deptLabels[] = $row['name'];
    $deptData[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Employee Management System</title>
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
                    <a href="dashboard.php" class="nav-item active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <?php if (!isEmployee()): ?>
                    <a href="employees.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Employees</span>
                    </a>
                    <a href="departments.php" class="nav-item">
                        <i class="fas fa-building"></i>
                        <span>Departments</span>
                    </a>
                    <?php
endif; ?>
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
                    <?php if (!isEmployee()): ?>
                    <a href="payroll.php" class="nav-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payroll</span>
                    </a>
                    <a href="analytics.php" class="nav-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                    <?php
endif; ?>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="profile.php" class="nav-item">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="settings.php" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <?php
endif; ?>
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
                <h1 class="page-title">Dashboard <span>Overview</span></h1>
                <div class="header-actions">
                    <div class="live-clock" style="font-size: 1.2rem; color: var(--primary-color); font-weight: 600;"></div>
                    <?php if (!isEmployee()): ?>
                    <button class="btn btn-primary" data-modal="addEmployeeModal">
                        <i class="fas fa-plus"></i>
                        Add Employee
                    </button>
                    <?php
endif; ?>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $totalEmployees; ?></div>
                            <div class="stat-label">Total Employees</div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $totalDepartments; ?></div>
                            <div class="stat-label">Departments</div>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-check"></i>
                        <span>All active</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $todayAttendance; ?></div>
                            <div class="stat-label">Today's Attendance</div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>95% present</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $pendingLeaves; ?></div>
                            <div class="stat-label">Pending Leaves</div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-change warning">
                        <i class="fas fa-exclamation"></i>
                        <span>Needs attention</span>
                    </div>
                </div>
            </div>


            <!-- Charts Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; margin-bottom: 25px;">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Attendance Overview</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-3d-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Department Distribution</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-3d-container">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Employees & Performance -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px;">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Employees</h3>
                        <a href="employees.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">
                            View All
                        </a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($employee = $recentEmployees->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="employee-cell">
                                            <div class="employee-avatar">
                                                <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                                            </div>
                                            <div class="employee-info">
                                                <span class="employee-name"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></span>
                                                <span class="employee-email"><?php echo htmlspecialchars($employee['email']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                                            <?php echo ucfirst($employee['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal-overlay" id="addEmployeeModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Employee</h3>
                <button class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="api/add_employee.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-input" required>
                            <option value="">Select Department</option>
                            <?php
$depts = $db->query("SELECT id, name FROM departments");
while ($dept = $depts->fetch_assoc()):
?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php
endwhile; ?>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-input" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" form="addEmployeeForm" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Employee
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
    window.dashboardData = {
        attendance: {
            labels: <?php echo json_encode($attLabels ?: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']); ?>,
            present: <?php echo json_encode($attPresent ?: [0, 0, 0, 0, 0]); ?>,
            absent: <?php echo json_encode($attAbsent ?: [0, 0, 0, 0, 0]); ?>
        },
        departments: {
            labels: <?php echo json_encode($deptLabels ?: ['IT', 'HR', 'Finance']); ?>,
            data: <?php echo json_encode($deptData ?: [1, 1, 1]); ?>
        }
    };
    </script>
    <script>
    // === Professional Dashboard Animations ===
    document.addEventListener('DOMContentLoaded', () => {

        // 1. Staggered card entrance with cascading delay
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card, i) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(40px) scale(0.95)';
            card.style.transition = 'none';
            setTimeout(() => {
                card.style.transition = 'opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
            }, 150 * i + 200);
        });

        // 2. Animated number counters
        const statValues = document.querySelectorAll('.stat-value');
        statValues.forEach(el => {
            const target = parseInt(el.textContent) || 0;
            if (target === 0) return;
            el.textContent = '0';
            const duration = 1500;
            const startTime = performance.now();
            function countUp(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 4); // easeOutQuart
                el.textContent = Math.round(target * eased);
                if (progress < 1) requestAnimationFrame(countUp);
            }
            setTimeout(() => requestAnimationFrame(countUp), 600);
        });

        // 3. Premium 3D tilt effect on stat cards
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(800px) rotateY(${x * 8}deg) rotateX(${-y * 8}deg) translateY(-5px)`;
                card.style.transition = 'transform 0.1s ease';
                // Dynamic light reflection
                card.style.background = `linear-gradient(${135 + x * 30}deg, var(--bg-card) 0%, rgba(14,165,233,${0.03 + Math.abs(x) * 0.04}) 100%)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(800px) rotateY(0) rotateX(0) translateY(0)';
                card.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), background 0.5s ease';
                card.style.background = '';
            });
        });

        // 4. 3D tilt on content cards & AI feature cards
        document.querySelectorAll('.content-card, .ai-feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(1000px) rotateY(${x * 5}deg) rotateX(${-y * 5}deg) translateY(-3px)`;
                card.style.transition = 'transform 0.1s ease';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
            });
        });

        // 5. Stat icon shimmer animation
        document.querySelectorAll('.stat-icon').forEach((icon, i) => {
            icon.style.animation = `iconFloat 3s ease-in-out ${i * 0.3}s infinite`;
        });

        // 6. AI section gradient border glow
        const aiSection = document.querySelector('.ai-section');
        if (aiSection) {
            aiSection.style.position = 'relative';
            aiSection.style.overflow = 'hidden';
            const glowBar = document.createElement('div');
            glowBar.className = 'ai-glow-bar';
            aiSection.appendChild(glowBar);
        }

        // 7. Page title slide-in
        const title = document.querySelector('.page-title');
        if (title) {
            title.style.opacity = '0';
            title.style.transform = 'translateX(-30px)';
            title.style.transition = 'none';
            setTimeout(() => {
                title.style.transition = 'opacity 0.8s ease, transform 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
                title.style.opacity = '1';
                title.style.transform = 'translateX(0)';
            }, 100);
        }
    });
    </script>
</body>
</html>
