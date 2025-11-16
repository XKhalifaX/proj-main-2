<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

require_once __DIR__ . '/db_connect.php';

// Get student ID from database
$student_id = null;
try {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch();
    $student_id = $user['id'] ?? null;
} catch (Exception $e) {
    $student_id = null;
}

// Handle form submission
if ($_POST && $student_id) {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($subject) && !empty($description)) {
        try {
            // Create help request as inactive board entry
            $stmt = $pdo->prepare("INSERT INTO boards (title, content, user_id, user_role, tutor_id, parent_id, created_at) VALUES (?, ?, ?, 'request', NULL, NULL, NOW())");
            $stmt->execute([$subject, $description, $student_id]);
            $success_message = "Help request submitted successfully! Tutors will be notified and can accept your request.";
        } catch (Exception $e) {
            $error_message = "Failed to submit request. Please try again.";
        }
    } else {
        $error_message = "Please fill in both subject and description.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Help - ZU Tutors</title>
    <link rel="stylesheet" href="styling.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-logo">ZU Tutors</h2>
                <p class="sidebar-tagline">Connect. Learn. Succeed.</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="find_tutors.php" class="nav-link"><i class="fas fa-search"></i> Find Tutors</a></li>
                    <li><a href="my_boards.php" class="nav-link"><i class="fas fa-comments"></i> My Messages</a></li>
                    <li><a href="request_help.php" class="nav-link active"><i class="fas fa-hand-paper"></i> Request Help</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="user-role"><?php echo ucfirst($user_role); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="content-header">
                <h1>Request Help</h1>
                <p>Submit a general help request and let qualified tutors come to you</p>
            </header>
            
            <div class="content-body">
                <?php if (isset($success_message)): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="request-help-container">
                    <div class="help-info-section">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="info-content">
                                <h3>How it works</h3>
                                <ol>
                                    <li>Submit your help request</li>
                                    <li>Tutors will see your request</li>
                                    <li>When a tutor accepts, you can start messaging</li>
                                    <li>Get the help you need!</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div class="request-form-section">
                        <form action="" method="POST" class="help-request-form">
                            <div class="form-section">
                                <h3><i class="fas fa-info-circle"></i> Request Details</h3>
                                
                                <div class="form-group">
                                    <label for="subject">Subject</label>
                                    <input type="text" id="subject" name="subject" placeholder="e.g., Mathematics, Physics, Chemistry" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">What do you need help with?</label>
                                    <textarea id="description" name="description" rows="5" placeholder="Describe what you need help with..." required></textarea>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="submit-request-btn">
                                    <i class="fas fa-paper-plane"></i> Submit Help Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>