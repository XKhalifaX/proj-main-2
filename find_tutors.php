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

// Load tutors from database
$tutors = [];
try {
    $stmt = $pdo->query("SELECT id, name, email, subjects, bio, settings FROM tutors ORDER BY id ASC");
    $dbTutors = $stmt->fetchAll();
    foreach ($dbTutors as $row) {
        $subjects = [];
        if (!empty($row['subjects'])) {
            $subjects = array_map('trim', explode(',', $row['subjects']));
        }
        $tutors[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'subjects' => $subjects,
            'bio' => $row['bio'] ?? 'Available to help with various topics.'
        ];
    }
} catch (Exception $e) {
    // On error, show no tutors (or log $e->getMessage() in real app)
    $tutors = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Tutors - ZU Tutors</title>
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
                    <li><a href="find_tutors.php" class="nav-link active"><i class="fas fa-search"></i> Find Tutors</a></li>
                    <li><a href="my_boards.php" class="nav-link"><i class="fas fa-comments"></i> My Messages</a></li>
                    <li><a href="request_help.php" class="nav-link"><i class="fas fa-hand-paper"></i> Request Help</a></li>
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
                <h1>Find Tutors</h1>
                <p>Browse and connect with qualified tutors</p>
            </header>
            
            <div class="content-body">
                <!-- Search -->
                <div class="search-section">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search by subject or tutor name..." />
                    </div>
                </div>
                
                <!-- Tutors Grid -->
                <div class="tutors-grid">
                    <?php foreach ($tutors as $tutor): ?>
                    <div class="tutor-card">
                        <div class="tutor-header">
                            <div class="tutor-avatar-large">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="tutor-basic-info">
                                <h3><?php echo htmlspecialchars($tutor['name']); ?></h3>
                            </div>
                        </div>
                        
                        <div class="tutor-subjects">
                            <?php foreach ($tutor['subjects'] as $subject): ?>
                                <span class="subject-tag"><?php echo htmlspecialchars($subject); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="tutor-bio">
                            <p><?php echo htmlspecialchars($tutor['bio']); ?></p>
                        </div>
                        
                        <div class="tutor-footer">
                            <div class="tutor-actions">
                                <button class="request-tutor-btn" data-tutor-id="<?php echo $tutor['id']; ?>" data-tutor-name="<?php echo htmlspecialchars($tutor['name']); ?>">
                                    <i class="fas fa-paper-plane"></i> Request Help
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Modal -->
    <div class="modal-overlay" id="requestModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Request Help from <span id="tutorName"></span></h3>
                <button class="close-modal" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="requestForm">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="e.g., Calculus, Organic Chemistry" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe what you need help with..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                <button type="submit" form="requestForm" class="submit-request-btn">Send Request</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(tutorId, tutorName) {
            document.getElementById('tutorName').textContent = tutorName;
            document.getElementById('requestModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        // Add event listeners to request buttons
        document.querySelectorAll('.request-tutor-btn:not([disabled])').forEach(button => {
            button.addEventListener('click', function() {
                const tutorId = this.dataset.tutorId;
                const tutorName = this.dataset.tutorName;
                openModal(tutorId, tutorName);
            });
        });

        // Handle form submission
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Here you would normally send the request to the server
            alert('Help request sent successfully!');
            closeModal();
        });

        // Close modal when clicking outside
        document.getElementById('requestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>