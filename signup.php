<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$errors = [];
$old = ['name'=>'','email'=>'','role'=>'student','subjects'=>'','bio'=>''];

if ($_POST) {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$confirm = $_POST['confirm_password'] ?? '';
	$role = ($_POST['role'] ?? 'student') === 'tutor' ? 'tutor' : 'student';
	$subjects = trim($_POST['subjects'] ?? '');
	$bio = trim($_POST['bio'] ?? '');

	$old = ['name'=>$name,'email'=>$email,'role'=>$role,'subjects'=>$subjects,'bio'=>$bio];

	// Basic validation
	if ($name === '') $errors[] = 'Name is required.';
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
	if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
	if ($password !== $confirm) $errors[] = 'Passwords do not match.';

	// Check existing email in students or tutors
	if (empty($errors)) {
		$stmt = $pdo->prepare("SELECT 1 FROM students WHERE email = ? LIMIT 1");
		$stmt->execute([$email]);
		if ($stmt->fetch()) $errors[] = 'Email already registered as a student.';

		$stmt = $pdo->prepare("SELECT 1 FROM tutors WHERE email = ? LIMIT 1");
		$stmt->execute([$email]);
		if ($stmt->fetch()) $errors[] = 'Email already registered as a tutor.';
	}

	if (empty($errors)) {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$settings = json_encode(new stdClass());

		try {
			if ($role === 'tutor') {
				$ins = $pdo->prepare("INSERT INTO tutors (name,email,password,subjects,bio,settings) VALUES (?,?,?,?,?,?)");
				$ins->execute([$name, $email, $hash, $subjects, $bio, $settings]);
			} else {
				// students table expects role column per schema
				$ins = $pdo->prepare("INSERT INTO students (name,email,password,role,settings) VALUES (?,?,?,?,?)");
				$ins->execute([$name, $email, $hash, 'student', $settings]);
			}
			// on success redirect to login with created flag
			header('Location: login.php?created=1');
			exit;
		} catch (Exception $e) {
			$errors[] = 'Database error: could not create account.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Sign Up - ZU Tutors</title>
	<link rel="stylesheet" href="styling.css">
	<style>
		/* Slightly reduced sizes for a more compact form */
		.login-card {
			padding: 18px;
			max-width: 700px; /* reduced from 760px */
			margin: 0 auto;
			box-sizing: border-box;
		}

		.login-card .login-form {
			width: 100%;
			margin: 0;
			box-sizing: border-box;
		}
		.login-card .form-group { margin-bottom: 12px; }

		.login-card label {
			display: block;
			text-align: left;
			margin-bottom: 6px;
			font-weight: 600;
			font-size: 13px; /* slightly smaller */
			color: #222;
		}

		/* Inputs: slightly smaller padding and font-size */
		.login-card input[type="text"],
		.login-card input[type="email"],
		.login-card input[type="password"],
		.login-card textarea,
		.login-card select {
			width: 100%;
			margin: 0;
			padding: 10px 12px; /* reduced padding */
			box-sizing: border-box;
			border: 1px solid #ccc;
			border-radius: 8px;
			text-align: left;
			font-size: 15px; /* slightly smaller */
			line-height: 1.3;
			height: auto;
			max-width: none;
		}

		/* Single-line inputs consistent, slightly reduced height */
		.login-card input[type="text"],
		.login-card input[type="email"],
		.login-card input[type="password"],
		.login-card select {
			height: 44px; /* slightly lower */
		}

		.login-card textarea {
			resize: vertical;
			min-height: 95px; /* slightly reduced */
			font-size: 14.5px;
			padding-top: 8px;
			padding-bottom: 8px;
		}

		/* Button sizing slightly reduced */
		.login-btn {
			display: block;
			width: 100%;
			margin: 12px 0 0;
			padding: 11px 14px; /* reduced padding */
			font-size: 15px;
			border-radius: 8px;
		}

		.login-card .error-message,
		.login-card .success-message {
			width: 100%;
			margin: 8px 0;
			padding: 10px 12px;
			box-sizing: border-box;
		}

		@media (max-width: 640px) {
			.login-card { margin: 12px; padding: 14px; }
			.login-card textarea { min-height: 80px; }
			.login-btn { padding: 10px 12px; }
		}
	</style>
</head>
<body>
	<div class="login-container">
		<div class="login-card">
			<div class="logo-section">
				<h1 class="logo">ZU Tutors</h1>
				<p class="tagline">Create your account</p>
			</div>

			<?php if (!empty($errors)): ?>
				<div class="error-message">
					<ul>
					<?php foreach ($errors as $err): ?>
						<li><?php echo htmlspecialchars($err); ?></li>
					<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<form action="" method="POST" class="login-form">
				<div class="form-group">
					<label for="name">Full name</label>
					<input id="name" name="name" value="<?php echo htmlspecialchars($old['name']); ?>" required />
				</div>
				<div class="form-group">
					<label for="email">Email</label>
					<input id="email" type="email" name="email" value="<?php echo htmlspecialchars($old['email']); ?>" required />
				</div>
				<div class="form-group">
					<label for="password">Password</label>
					<input id="password" type="password" name="password" required />
				</div>
				<div class="form-group">
					<label for="confirm_password">Confirm Password</label>
					<input id="confirm_password" type="password" name="confirm_password" required />
				</div>

				<div class="form-group">
					<label for="role">Account type</label>
					<select id="role" name="role">
						<option value="student" <?php echo $old['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
						<option value="tutor" <?php echo $old['role'] === 'tutor' ? 'selected' : ''; ?>>Tutor</option>
					</select>
				</div>

				<div class="form-group">
					<label for="subjects">Subjects (for tutors only)</label>
					<input id="subjects" name="subjects" value="<?php echo htmlspecialchars($old['subjects']); ?>" placeholder="e.g. Mathematics, Physics" />
				</div>

				<div class="form-group">
					<label for="bio">Bio (for tutors only)</label>
					<textarea id="bio" name="bio" rows="2" placeholder="Brief description about yourself"><?php echo htmlspecialchars($old['bio']); ?></textarea>
				</div>

				<button type="submit" class="login-btn">Create Account</button>
			</form>

			<div class="signup-link">
				<p>Already have an account? <a href="login.php">Sign in</a></p>
			</div>
		</div>
	</div>
</body>
</html>
