<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

redirectIfLoggedIn();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Check user in database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Set success message
                $_SESSION['success'] = 'Login successful! Welcome back, ' . $user['username'] . '!';
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}

// Display logout message if redirected from logout
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = 'You have been successfully logged out.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Performance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <!-- Decorative elements -->
        <div class="auth-decoration"></div>
        <div class="auth-decoration"></div>
        <div class="auth-decoration"></div>
        
        <div class="auth-box">
            <div class="auth-logo">
                <h1>Student Performance</h1>
                <p>Academic Performance Prediction System</p>
            </div>
            
            <form method="POST" action="" class="auth-form">
                <h2>Login to Your Account</h2>
                
                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                    <button type="button" class="password-toggle" style="
                        position: absolute;
                        right: 15px;
                        top: 40px;
                        background: none;
                        border: none;
                        color: #95a5a6;
                        cursor: pointer;
                        font-size: 1.2rem;
                        padding: 5px;
                    ">👁️</button>
                </div>
                
                <button type="submit" class="btn btn-full">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div class="auth-links">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>