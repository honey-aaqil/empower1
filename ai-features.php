<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Get all employees for dropdowns
$employees = $db->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
$departments = $db->query("SELECT id, name FROM departments ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Features - Employee Management System</title>
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
                    <a href="ai-features.php" class="nav-item active">
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
                <h1 class="page-title">AI <span>Features</span></h1>
                <div class="header-actions">
                    <div class="live-clock" style="font-size: 1.2rem; color: var(--primary-color); font-weight: 600;"></div>
                </div>
            </div>

            <!-- AI Hero Section -->
            <div class="ai-section" style="text-align: center; padding: 40px;">
                <div class="ai-icon" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 2.5rem;">
                    <i class="fas fa-brain"></i>
                </div>
                <h2 style="color: var(--text-primary); margin-bottom: 10px;">Powered by Google AI Studio</h2>
                <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">
                    Leverage advanced artificial intelligence to gain insights, predict trends, and make data-driven decisions for your workforce.
                </p>
            </div>

            <!-- AI Features Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-top: 30px;">
                
                <!-- Sentiment Analysis -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-smile" style="color: var(--primary-color);"></i> Sentiment Analysis</h3>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Analyze employee feedback to understand satisfaction levels and identify improvement areas.</p>
                        
                        <div class="form-group">
                            <label class="form-label">Employee Feedback</label>
                            <textarea id="feedbackText" class="form-input" rows="4" placeholder="Enter employee feedback here..."></textarea>
                        </div>
                        
                        <button class="btn btn-primary btn-block" id="analyzeSentiment">
                            <i class="fas fa-magic"></i>
                            Analyze Sentiment
                        </button>
                        
                        <div id="sentimentResult" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Performance Prediction -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line" style="color: #8b5cf6;"></i> Performance Prediction</h3>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Predict future performance trends based on historical data and current metrics.</p>
                        
                        <div class="form-group">
                            <label class="form-label">Select Employee</label>
                            <select id="predictEmployeeId" class="form-input">
                                <option value="">Choose an employee</option>
                                <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></option>
                                <?php
endwhile; ?>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary btn-block" id="predictPerformance" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                            <i class="fas fa-brain"></i>
                            Predict Performance
                        </button>
                        
                        <div id="predictionResult" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Job Description Generator -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-alt" style="color: #10b981;"></i> Job Description Generator</h3>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Generate professional job descriptions with AI assistance.</p>
                        
                        <div class="form-group">
                            <label class="form-label">Job Role</label>
                            <input type="text" id="jobRole" class="form-input" placeholder="e.g., Senior Software Engineer">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Key Requirements</label>
                            <textarea id="jobRequirements" class="form-input" rows="3" placeholder="Enter key requirements..."></textarea>
                        </div>
                        
                        <button class="btn btn-primary btn-block" id="generateJobDesc" style="background: linear-gradient(135deg, #10b981, #34d399);">
                            <i class="fas fa-magic"></i>
                            Generate Description
                        </button>
                        
                        <div id="jobDescResult" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Team Dynamics -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users" style="color: #f59e0b;"></i> Team Dynamics Analysis</h3>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Analyze team composition and collaboration patterns.</p>
                        
                        <div class="form-group">
                            <label class="form-label">Select Department</label>
                            <select id="teamDepartment" class="form-input">
                                <option value="">All Departments</option>
                                <?php while ($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php
endwhile; ?>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary btn-block" id="analyzeTeam" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                            <i class="fas fa-project-diagram"></i>
                            Analyze Team
                        </button>
                        
                        <div id="teamResult" style="margin-top: 20px;"></div>
                    </div>
                </div>
            </div>

            <!-- AI Analysis History -->
            <div class="content-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history" style="color: var(--primary-color);"></i> Recent AI Analysis</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Employee/Context</th>
                                <th>Confidence Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$analysisHistory = $db->query("SELECT a.*, e.first_name, e.last_name FROM ai_analysis a LEFT JOIN employees e ON a.employee_id = e.id ORDER BY a.created_at DESC LIMIT 10");
while ($analysis = $analysisHistory->fetch_assoc()):
?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($analysis['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo ucfirst(str_replace('_', ' ', $analysis['analysis_type'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
    if ($analysis['first_name']) {
        echo htmlspecialchars($analysis['first_name'] . ' ' . $analysis['last_name']);
    }
    else {
        echo 'General Analysis';
    }
?>
                                </td>
                                <td>
                                    <div class="progress-bar" style="width: 100px; display: inline-block; vertical-align: middle; margin-right: 10px;">
                                        <div class="progress-fill" style="width: <?php echo $analysis['confidence_score'] ?? 70; ?>%"></div>
                                    </div>
                                    <?php echo $analysis['confidence_score'] ?? 70; ?>%
                                </td>
                                <td>
                                    <button class="action-btn view" onclick="viewAnalysis(<?php echo $analysis['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
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
        // Sentiment Analysis
        document.getElementById('analyzeSentiment').addEventListener('click', async function() {
            const feedback = document.getElementById('feedbackText').value;
            if (!feedback) {
                showToast('Please enter feedback text', 'warning');
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
            
            try {
                const response = await fetch('api/ai_sentiment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feedback })
                });
                
                const result = await response.json();
                
                if (result.error) {
                    showToast(result.error, 'error');
                } else {
                    document.getElementById('sentimentResult').innerHTML = `
                        <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--border-radius); margin-top: 15px;">
                            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: conic-gradient(var(--primary-color) ${result.score}%, var(--border-color) ${result.score}%); display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary-color);">
                                        ${result.score}%
                                    </div>
                                </div>
                                <div>
                                    <h4 style="margin-bottom: 5px;">Positivity Score</h4>
                                    <p style="color: var(--text-muted); font-size: 0.9rem;">${result.analysis}</p>
                                </div>
                            </div>
                            <div>
                                <h5 style="margin-bottom: 10px;">Suggestions:</h5>
                                <ul style="padding-left: 20px; color: var(--text-secondary);">
                                    ${result.suggestions.map(s => `<li style="margin-bottom: 5px;">${s}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                showToast('Error analyzing sentiment: ' + error.message, 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-magic"></i> Analyze Sentiment';
            }
        });

        // Performance Prediction
        document.getElementById('predictPerformance').addEventListener('click', async function() {
            const employeeId = document.getElementById('predictEmployeeId').value;
            if (!employeeId) {
                showToast('Please select an employee', 'warning');
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Predicting...';
            
            try {
                const response = await fetch('api/ai_predict.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employee_id: employeeId })
                });
                
                const result = await response.json();
                
                if (result.error) {
                    showToast(result.error, 'error');
                } else {
                    document.getElementById('predictionResult').innerHTML = `
                        <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--border-radius); margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <div>
                                    <h4>Prediction Score</h4>
                                    <div style="font-size: 2rem; font-weight: 700; color: #8b5cf6;">${result.prediction_score}%</div>
                                </div>
                                <span class="badge ${result.trend === 'improving' ? 'badge-success' : result.trend === 'declining' ? 'badge-danger' : 'badge-warning'}">
                                    ${result.trend}
                                </span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div>
                                    <h5 style="margin-bottom: 10px; color: var(--success-color);">Key Strengths</h5>
                                    <ul style="padding-left: 20px; font-size: 0.9rem;">
                                        ${result.key_strengths.map(s => `<li>${s}</li>`).join('')}
                                    </ul>
                                </div>
                                <div>
                                    <h5 style="margin-bottom: 10px; color: var(--warning-color);">Areas to Improve</h5>
                                    <ul style="padding-left: 20px; font-size: 0.9rem;">
                                        ${result.areas_to_improve.map(a => `<li>${a}</li>`).join('')}
                                    </ul>
                                </div>
                            </div>
                            
                            <div>
                                <h5 style="margin-bottom: 10px;">Recommendations</h5>
                                <ul style="padding-left: 20px; font-size: 0.9rem;">
                                    ${result.recommendations.map(r => `<li>${r}</li>`).join('')}
                                </ul>
                            </div>
                            
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                                <span style="color: var(--text-muted);">Retention Risk: </span>
                                <span class="badge ${result.retention_risk === 'low' ? 'badge-success' : result.retention_risk === 'high' ? 'badge-danger' : 'badge-warning'}">
                                    ${result.retention_risk}
                                </span>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                showToast('Error predicting performance', 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-brain"></i> Predict Performance';
            }
        });

        // Job Description Generator
        document.getElementById('generateJobDesc').addEventListener('click', async function() {
            const role = document.getElementById('jobRole').value;
            const requirements = document.getElementById('jobRequirements').value;
            
            if (!role || !requirements) {
                showToast('Please fill in all fields', 'warning');
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            
            try {
                const response = await fetch('api/ai_jobdesc.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ role, requirements })
                });
                
                const result = await response.json();
                
                if (result.error) {
                    showToast(result.error, 'error');
                } else {
                    document.getElementById('jobDescResult').innerHTML = `
                        <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--border-radius); margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4>Generated Job Description</h4>
                                <button class="btn btn-secondary" onclick="copyToClipboard('${encodeURIComponent(result.raw_description)}')" style="padding: 8px 16px; font-size: 0.85rem;">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <div style="background: white; padding: 20px; border-radius: var(--border-radius); font-size: 0.95rem; line-height: 1.6;">
                                ${result.job_description}
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                showToast('Error generating job description', 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-magic"></i> Generate Description';
            }
        });

        // Team Dynamics Analysis
        document.getElementById('analyzeTeam').addEventListener('click', async function() {
            const departmentId = document.getElementById('teamDepartment').value;
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
            
            try {
                const response = await fetch('api/ai_team.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ department_id: departmentId })
                });
                
                const result = await response.json();
                
                if (result.error) {
                    showToast(result.error, 'error');
                } else {
                    document.getElementById('teamResult').innerHTML = `
                        <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--border-radius); margin-top: 15px;">
                            <h4 style="margin-bottom: 20px;">Team Dynamics Analysis</h4>
                            
                            <div style="margin-bottom: 20px;">
                                ${result.metrics.map(m => `
                                    <div style="margin-bottom: 15px;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                            <span>${m.name}</span>
                                            <span style="font-weight: 600;">${m.value}%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: ${m.value}%; background: ${m.value > 70 ? 'var(--success-color)' : m.value > 40 ? 'var(--warning-color)' : 'var(--danger-color)'}"></div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <div style="background: white; padding: 15px; border-radius: var(--border-radius);">
                                <h5 style="margin-bottom: 10px;">Analysis & Recommendations</h5>
                                <p style="color: var(--text-secondary); line-height: 1.6;">${result.analysis}</p>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                showToast('Error analyzing team dynamics', 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-project-diagram"></i> Analyze Team';
            }
        });

        function copyToClipboard(text) {
            const decoded = decodeURIComponent(text);
            navigator.clipboard.writeText(decoded).then(() => {
                showToast('Copied to clipboard!', 'success');
            });
        }

        function viewAnalysis(id) {
            // Load and display analysis details
            showToast('Loading analysis details...', 'info');
        }
    </script>
</body>
</html>
