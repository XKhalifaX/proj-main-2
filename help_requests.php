<?php
session_start();

// Check if user is logged in and is a tutor
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

require_once __DIR__ . '/db_connect.php';

// Get tutor ID from database
$tutor_id = null;
try {
    $stmt = $pdo->prepare("SELECT id FROM tutors WHERE email = ? LIMIT 1");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch();
    $tutor_id = $user['id'] ?? null;
} catch (Exception $e) {
    $tutor_id = null;
}

// Get pending help requests from database
$help_requests = [];
try {
    $stmt = $pdo->prepare("SELECT b.id, b.title as subject, b.content as description, b.created_at, s.name as student_name, s.id as student_id 
                          FROM boards b 
                          LEFT JOIN students s ON b.user_id = s.id 
                          WHERE b.user_role = 'request' 
                          ORDER BY b.created_at DESC");
    $stmt->execute();
    $requests = $stmt->fetchAll();
    
    foreach ($requests as $req) {
        $help_requests[] = [
            'id' => $req['id'],
            'student_id' => $req['student_id'],
            'student_name' => $req['student_name'] ?? 'Student',
            'subject' => $req['subject'],
            'description' => $req['description'],
            'created_at' => date('M j, Y g:i A', strtotime($req['created_at'])),
            'status' => 'pending'
        ];
    }
} catch (Exception $e) {
    $help_requests = [];
}

// Handle request actions
if ($_POST && $tutor_id) {
    $action = $_POST['action'] ?? '';
    $request_id = (int)($_POST['request_id'] ?? 0);
    
    if ($action === 'accept' && $request_id > 0) {
        try {
            // Update the request to become an active conversation and link tutor
            $stmt = $pdo->prepare("UPDATE boards SET user_role = 'conversation', tutor_id = ? WHERE id = ? AND user_role = 'request'");
            $stmt->execute([$tutor_id, $request_id]);
            
            if ($stmt->rowCount() > 0) {
                // Redirect to avoid showing the accepted request again
                header('Location: help_requests.php?accepted=1');
                exit;
            } else {
                $error_message = "Failed to accept request. It may have already been processed.";
            }
        } catch (Exception $e) {
            $error_message = "Database error. Please try again.";
        }
    } elseif ($action === 'decline' && $request_id > 0) {
        try {
            // Delete the declined request
            $stmt = $pdo->prepare("DELETE FROM boards WHERE id = ? AND user_role = 'request'");
            $stmt->execute([$request_id]);
            
            if ($stmt->rowCount() > 0) {
                // Redirect to avoid confusion
                header('Location: help_requests.php?declined=1');
                exit;
            } else {
                $error_message = "Failed to decline request. It may have already been processed.";
            }
        } catch (Exception $e) {
            $error_message = "Database error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Requests - ZU Tutors</title>
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
                    <li><a href="my_boards.php" class="nav-link"><i class="fas fa-comments"></i> My Messages</a></li>
                    <li><a href="help_requests.php" class="nav-link active"><i class="fas fa-inbox"></i> Help Requests</a></li>
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
                <h1>Help Requests</h1>
                <p>Review and respond to student help requests</p>
            </header>
            
            <div class="content-body">
                <?php if (isset($_GET['accepted'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> Request accepted! You can now message with the student in 'My Messages'.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['declined'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> Request declined successfully.
                    </div>
                <?php endif; ?>
                
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

                
                <!-- Help Requests List -->
                <div class="help-requests-list">
                    <?php if (empty($help_requests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No help requests available</h3>
                            <p>When students submit help requests that match your expertise, they will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($help_requests as $request): ?>
                        <div class="help-request-card">
                            <div class="request-header">
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="student-details">
                                        <h4><?php echo htmlspecialchars($request['student_name']); ?></h4>
                                        <span class="request-time"><?php echo $request['created_at']; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="request-content">
                                <div class="request-title">
                                    <span class="subject-tag"><?php echo htmlspecialchars($request['subject']); ?></span>
                                </div>
                                
                                <div class="request-description">
                                    <p><?php echo htmlspecialchars($request['description']); ?></p>
                                </div>
                            </div>
                            
                            <div class="request-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="action" value="decline">
                                    <button type="submit" class="decline-request-btn">
                                        <i class="fas fa-times"></i> Decline
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="accept-request-btn">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Confirm actions
        document.querySelectorAll('.accept-request-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to accept this request?')) {
                    e.preventDefault();
                }
            });
        });
        
        document.querySelectorAll('.decline-request-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to decline this request?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>