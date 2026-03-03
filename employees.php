<?php
require_once __DIR__ . '/includes/config.php';
requireManagement();

// Handle delete action
if (isset($_GET['delete']) && isAdmin()) {
    $id = intval($_GET['delete']);
    // Delete related records first (foreign key constraints)
    $db->query("DELETE FROM attendance WHERE employee_id = $id");
    $db->query("DELETE FROM leave_requests WHERE employee_id = $id");
    $db->query("DELETE FROM performance_reviews WHERE employee_id = $id");
    $db->query("DELETE FROM payroll WHERE employee_id = $id");
    $db->query("DELETE FROM employee_projects WHERE employee_id = $id");
    $db->query("DELETE FROM ai_analysis WHERE employee_id = $id");
    // Permanently delete the employee
    $db->query("DELETE FROM employees WHERE id = $id");
    redirect('employees.php?msg=deleted');
}

// Department filtering
$filterDepartment = isset($_GET['department']) ? intval($_GET['department']) : 0;
$filterDeptName = '';
$whereClause = "";

if ($filterDepartment > 0) {
    $deptQuery = $db->query("SELECT name FROM departments WHERE id = $filterDepartment");
    if ($deptQuery && $deptQuery->num_rows > 0) {
        $filterDeptName = $deptQuery->fetch_assoc()['name'];
        $whereClause = " WHERE e.department_id = $filterDepartment";
    } else {
        $filterDepartment = 0;
    }
}

// Get all employees with department info (filtered if department is selected)
$employees = $db->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id" . $whereClause . " ORDER BY e.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Employee Management System</title>
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
                    <?php if (isAdmin()): ?>
                    <a href="settings.php" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <?php endif; ?>
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
                <h1 class="page-title">Employees <span>Management</span></h1>
                <div class="header-actions">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Search employees...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <button class="btn btn-secondary" onclick="exportEmployees()">
                        <i class="fas fa-download"></i>
                        Export
                    </button>
                    <button class="btn btn-primary" data-modal="addEmployeeModal">
                        <i class="fas fa-plus"></i>
                        Add Employee
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="toast success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span>Employee deleted successfully</span>
                </div>
            <?php endif; ?>

            <?php if ($filterDepartment > 0 && !empty($filterDeptName)): ?>
                <div class="toast info" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-filter"></i> Showing employees from <strong><?php echo htmlspecialchars($filterDeptName); ?></strong> department</span>
                    <a href="employees.php" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.85rem;">
                        <i class="fas fa-times"></i> Clear Filter
                    </a>
                </div>
            <?php endif; ?>

            <!-- Employees Table -->
            <div class="content-card">
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Employee ID</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Joining Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($employee = $employees->fetch_assoc()): ?>
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
                                <td><?php echo htmlspecialchars($employee['employee_code']); ?></td>
                                <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($employee['designation']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($employee['joining_date'])); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($employee['status']) {
                                        'active' => 'badge-success',
                                        'inactive' => 'badge-warning',
                                        'on_leave' => 'badge-info',
                                        'terminated' => 'badge-danger',
                                        default => 'badge-info'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <i class="fas fa-circle" style="font-size: 8px;"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $employee['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn view" onclick="viewEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit" onclick="editEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if (isAdmin()): ?>
                                        <button class="action-btn delete" onclick="deleteEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Employee Cards Grid (Alternative View) -->
            <div style="margin-top: 30px;">
                <h3 style="margin-bottom: 20px; color: var(--text-primary);">Team Overview</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                    <?php
                    $employees->data_seek(0);
                    for ($count = 0; $count < 6 && ($employee = $employees->fetch_assoc()) !== null; $count++):
                    ?>
                    <div class="employee-card-3d">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div class="employee-avatar" style="width: 80px; height: 80px; font-size: 1.8rem; margin: 0 auto;">
                                <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                            </div>
                        </div>
                        <h4 style="text-align: center; margin-bottom: 5px; color: var(--text-primary);">
                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                        </h4>
                        <p style="text-align: center; color: var(--text-muted); margin-bottom: 15px;">
                            <?php echo htmlspecialchars($employee['designation']); ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; padding: 15px 0; border-top: 1px solid var(--border-color);">
                            <div style="text-align: center;">
                                <div style="font-size: 0.8rem; color: var(--text-muted);">Department</div>
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 0.8rem; color: var(--text-muted);">Status</div>
                                <?php
                                $cardStatus = match($employee['status']) {
                                    'active' => 'badge-success',
                                    'inactive' => 'badge-warning',
                                    'on_leave' => 'badge-info',
                                    'terminated' => 'badge-danger',
                                    default => 'badge-info'
                                };
                                ?>
                                <span class="badge <?php echo $cardStatus; ?>" style="font-size: 0.75rem;">
                                    <?php echo ucfirst(str_replace('_', ' ', $employee['status'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal-overlay" id="addEmployeeModal">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h3 class="modal-title">Add New Employee</h3>
                <button class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="api/add_employee.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-input">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Department *</label>
                            <select name="department_id" class="form-input" required>
                                <option value="">Select Department</option>
                                <?php
                                $depts = $db->query("SELECT id, name FROM departments");
                                while ($dept = $depts->fetch_assoc()):
                                ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Designation *</label>
                            <input type="text" name="designation" class="form-input" required>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Joining Date *</label>
                            <input type="date" name="joining_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" class="form-input">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-input" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-input" rows="2"></textarea>
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

    <!-- View Employee Modal -->
    <div class="modal-overlay" id="viewEmployeeModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Employee Details</h3>
                <button class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body" id="viewEmployeeContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function viewEmployee(id) {
            fetch(`api/get_employee.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('viewEmployeeContent');
                    content.innerHTML = `
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div class="employee-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto;">
                                ${data.first_name[0]}${data.last_name[0]}
                            </div>
                            <h3 style="margin-top: 15px; color: var(--text-primary);">${data.first_name} ${data.last_name}</h3>
                            <p style="color: var(--text-muted);">${data.designation}</p>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Employee ID</p>
                                <p style="font-weight: 600;">${data.employee_code}</p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Department</p>
                                <p style="font-weight: 600;">${data.department_name || 'N/A'}</p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Email</p>
                                <p style="font-weight: 600;">${data.email}</p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Phone</p>
                                <p style="font-weight: 600;">${data.phone || 'N/A'}</p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Joining Date</p>
                                <p style="font-weight: 600;">${data.joining_date}</p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Employment Type</p>
                                <p style="font-weight: 600;">${data.employment_type}</p>
                            </div>
                        </div>
                    `;
                    openModal('viewEmployeeModal');
                });
        }

        function editEmployee(id) {
            window.location.href = `edit-employee.php?id=${id}`;
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure you want to delete this employee?')) {
                window.location.href = `employees.php?delete=${id}`;
            }
        }

        function exportEmployees() {
            showToast('Exporting employees...', 'info');
            setTimeout(() => {
                window.location.href = 'api/export_employees.php';
            }, 1000);
        }
    </script>
</body>
</html>
