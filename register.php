<?php
require_once __DIR__ . '/includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if username exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'employee')");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Employee Management System</title>
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

    <div class="login-container">
        <div class="login-card" style="max-width: 500px;">
            <div class="login-logo">
                <h1><i class="fas fa-users-cog"></i> Empower</h1>
                <p>Create Your Account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="toast error" style="margin-bottom: 20px;">
                    <i class="fas fa-times-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="toast success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Create a password" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Already have an account? 
                    <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
