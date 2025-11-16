<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

require_once __DIR__ . '/db_connect.php'; // <-- new: use existing DB connection

// Ensure demo data exists and load tutors + boards for display
try {
	// Insert sample tutors if fewer than 3 exist
	$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM tutors");
	$cnt = (int) ($stmt->fetch()['cnt'] ?? 0);
	if ($cnt < 3) {
		$sampleTutors = [
			['Alice Thompson','alice.tutor@example.com','password123','Mathematics, Calculus'],
			['Brian Lee','brian.tutor@example.com','password123','Physics, Mechanics'],
			['Carla Gomez','carla.tutor@example.com','password123','Chemistry, Organic Chemistry'],
		];
		$ins = $pdo->prepare("INSERT IGNORE INTO tutors (name,email,password,subjects,role,settings) VALUES (?,?,?,?,?,?)");
		foreach ($sampleTutors as $t) {
			$ins->execute([
				$t[0],
				$t[1],
				password_hash($t[2], PASSWORD_DEFAULT),
				$t[3],
				'tutor',
				json_encode(new stdClass())
			]);
		}
	}

	// Insert sample boards/courses if fewer than 3 exist
	$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM boards");
	$bCnt = (int) ($stmt->fetch()['cnt'] ?? 0);
	if ($bCnt < 3) {
		// get first three tutor ids
		$tstmt = $pdo->query("SELECT id FROM tutors ORDER BY id ASC LIMIT 3");
		$tutors = $tstmt->fetchAll();
		$courses = [
			['Calculus 101','Intro to derivatives and integrals'],
			['Physics Mechanics','Newtonian mechanics basics'],
			['Organic Chemistry Basics','Structure and reactions overview'],
		];
		$insB = $pdo->prepare("INSERT IGNORE INTO boards (title,content,user_id,user_role) VALUES (?,?,?,?)");
		foreach ($courses as $i => $c) {
			$tutorId = $tutors[$i]['id'] ?? ($tutors[0]['id'] ?? 1);
			$insB->execute([$c[0], $c[1], $tutorId, 'tutor']);
		}
	}

	// Load tutors and boards for display
	$tutorsList = $pdo->query("SELECT id,name,subjects,email FROM tutors ORDER BY id ASC LIMIT 10")->fetchAll();
	$boardsList = $pdo->query("SELECT boards.id,boards.title,boards.content,boards.user_id, tutors.name AS tutor_name FROM boards LEFT JOIN tutors ON boards.user_id = tutors.id ORDER BY boards.created_at DESC LIMIT 10")->fetchAll();

} catch (Exception $e) {
	// silent fail for demo; in production log the error
	$tutorsList = [];
	$boardsList = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ZU Tutors</title>
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
                    <?php if ($user_role === 'student'): ?>
                        <li><a href="dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="find_tutors.php" class="nav-link"><i class="fas fa-search"></i> Find Tutors</a></li>
                        <li><a href="my_boards.php" class="nav-link"><i class="fas fa-comments"></i> My Messages</a></li>
                        <li><a href="request_help.php" class="nav-link"><i class="fas fa-hand-paper"></i> Request Help</a></li>
                    <?php elseif ($user_role === 'tutor'): ?>
                        <li><a href="dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="my_boards.php" class="nav-link"><i class="fas fa-comments"></i> My Messages</a></li>
                        <li><a href="help_requests.php" class="nav-link"><i class="fas fa-inbox"></i> Help Requests</a></li>
                    <?php endif; ?>
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
                <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Here's your personalized dashboard</p>
            </header>
            
            <div class="content-body">
                <!-- Role-specific Content -->
                <?php if ($user_role === 'student'): ?>
                    <div class="role-specific-content">
                        <h2>Student Dashboard</h2>

                        <!-- Available Tutors -->
                        <div class="section-card">
                            <h3><i class="fas fa-chalkboard-teacher"></i> Available Tutors</h3>
                            <div class="tutors-grid">
                                <?php if (!empty($tutorsList)): ?>
                                    <?php foreach ($tutorsList as $t): ?>
                                        <div class="tutor-card">
                                            <div class="tutor-name"><?php echo htmlspecialchars($t['name']); ?></div>
                                            <div class="tutor-subjects"><?php echo htmlspecialchars($t['subjects']); ?></div>
                                            <a href="request_help.php" class="view-profile-btn">Request Help</a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No tutors available right now.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="student-sections">
                            <div class="section-card">
                                <h3><i class="fas fa-comments"></i> My Messages</h3>
                                <div class="board-list">
                                    <div class="empty-state">
                                        <i class="fas fa-comments"></i>
                                        <p>No active conversations yet. Request help from tutors to start messaging!</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="section-card">
                                <h3><i class="fas fa-paper-plane"></i> My Requests</h3>
                                <div class="request-list">
                                    <div class="empty-state">
                                        <i class="fas fa-paper-plane"></i>
                                        <p>No pending requests. Create a new help request!</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-actions">
                                <a href="find_tutors.php" class="quick-action-btn">
                                    <i class="fas fa-search"></i>
                                    <span>Find Tutors</span>
                                </a>
                                <a href="request_help.php" class="quick-action-btn">
                                    <i class="fas fa-hand-paper"></i>
                                    <span>Request Help</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($user_role === 'tutor'): ?>
                    <div class="role-specific-content">
                        <h2>Tutor Dashboard</h2>
                        
                        <div class="tutor-sections">
                            <div class="section-card">
                                <h3><i class="fas fa-comments"></i> My Messages</h3>
                                <div class="board-list">
                                    <div class="empty-state">
                                        <i class="fas fa-comments"></i>
                                        <p>No active conversations yet. Accept help requests to start tutoring!</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="section-card">
                                <h3><i class="fas fa-inbox"></i> Help Requests</h3>
                                <div class="request-list">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>No pending requests. Students will find you when they need help!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
