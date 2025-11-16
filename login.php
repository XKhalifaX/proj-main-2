<?php
session_start();

// Include existing DB connection (db_connect.php) at the top of login.php.
require_once __DIR__ . '/db_connect.php';

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Try students table
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $source = 'student';

        // If not found, try tutors table (tutors table doesn't have role column)
        if (!$user) {
            $stmt = $pdo->prepare("SELECT id, name, email, password FROM tutors WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $user['role'] = 'tutor'; // Add role manually since tutors table doesn't have it
            }
            $source = 'tutor';
        }

        if ($user) {
            $stored = $user['password'];

            // Accept either hashed passwords or plain text (for simple local setups)
            $valid = false;
            if (!empty($stored) && password_verify($password, $stored)) {
                $valid = true;
            } elseif ($password === $stored) {
                $valid = true;
            }

            if ($valid) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                // Use role from DB if present, otherwise infer from source table
                $_SESSION['user_role'] = !empty($user['role']) ? $user['role'] : ($source === 'tutor' ? 'tutor' : 'student');
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    } catch (Exception $e) {
        $error = 'Login error: please try again';
    }
}

// show optional created message (keep near top of HTML output)
$created = isset($_GET['created']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZU Tutors</title>
    <link rel="stylesheet" href="styling.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <h1 class="logo">ZU Tutors</h1>
                <p class="tagline">Connect. Learn. Succeed.</p>
            </div>
            
            <div class="login-form-section">
                <h2 class="form-title">Welcome Back</h2>
                <p class="form-subtitle">Sign in to your account</p>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($created): ?>
                    <div class="success-message">
                        Account created successfully — please sign in.
                    </div>
                <?php endif; ?>
                
                <div class="demo-credentials">
                    <h4>Demo Credentials:</h4>
                    <p><strong>Student:</strong> student@example.com / password123</p>
                    <p><strong>Tutor:</strong> tutor@example.com / password123</p>
                    <p><strong>Admin:</strong> admin@example.com / password123</p>
                </div>
                
                <form class="login-form" action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="login-btn">Log In</button>
                </form>
                
                <div class="signup-link">
                    <p>Don't have an account? <a href="signup.php" class="signup-btn">Sign up here</a></p>
                </div>
                
                <div class="user-type-info">
                    <div class="info-card">
                        <h4>For Students</h4>
                        <p>Find expert tutors and boost your academic performance</p>
                    </div>
                    <div class="info-card">
                        <h4>For Tutors</h4>
                        <p>Share your knowledge and earn while teaching</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>