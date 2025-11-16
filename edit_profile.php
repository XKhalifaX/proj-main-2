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

// Handle profile update
if ($_POST) {
    $success_message = "Profile updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ZU Tutors</title>
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
                    <li><a href="user_settings.php" class="nav-link"><i class="fas fa-cog"></i> User Settings</a></li>
                    <li><a href="edit_profile.php" class="nav-link active"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
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
                <h1>Edit Profile</h1>
                <p>Update your profile information and preferences</p>
            </header>
            
            <div class="content-body">
                <?php if (isset($success_message)): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-avatar-section">
                            <div class="profile-avatar-large">
                                <i class="fas fa-user"></i>
                            </div>
                            <button class="change-avatar-btn">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                        <div class="profile-basic-info">
                            <h2><?php echo htmlspecialchars($user_name); ?></h2>
                            <p class="profile-role"><?php echo ucfirst($user_role); ?></p>
                            <p class="profile-email"><?php echo htmlspecialchars($user_email); ?></p>
                        </div>
                    </div>
                    
                    <form action="" method="POST" class="profile-form">
                        <div class="profile-sections">
                            <div class="profile-section">
                                <h3><i class="fas fa-user"></i> Personal Information</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" value="John" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" value="Doe" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="+1234567890">
                                    </div>
                                    <div class="form-group">
                                        <label for="birth_date">Date of Birth</label>
                                        <input type="date" id="birth_date" name="birth_date" value="1995-06-15">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="profile-section">
                                <h3><i class="fas fa-map-marker-alt"></i> Location Information</h3>
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="address">Address</label>
                                        <input type="text" id="address" name="address" value="123 University Street">
                                    </div>
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" id="city" name="city" value="Cairo">
                                    </div>
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <select id="country" name="country">
                                            <option value="egypt" selected>Egypt</option>
                                            <option value="usa">United States</option>
                                            <option value="uk">United Kingdom</option>
                                            <option value="canada">Canada</option>
                                            <option value="australia">Australia</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="profile-section">
                                <h3><i class="fas fa-info-circle"></i> Bio & Description</h3>
                                <div class="form-group full-width">
                                    <label for="bio">About Me</label>
                                    <textarea id="bio" name="bio" rows="4" placeholder="Tell others about yourself..."><?php if ($user_role === 'student'): ?>I'm a dedicated student at Zewail University, passionate about learning and always eager to improve my academic performance. I believe in the power of collaborative learning and seeking help when needed.<?php elseif ($user_role === 'tutor'): ?>Experienced educator with a passion for helping students achieve their academic goals. I specialize in making complex concepts easy to understand and creating a supportive learning environment.<?php else: ?>Platform administrator dedicated to creating the best possible experience for both students and tutors. I ensure the platform runs smoothly and all users have access to quality educational resources.<?php endif; ?></textarea>
                                </div>
                            </div>
                            
                            <?php if ($user_role === 'tutor'): ?>
                            <div class="profile-section">
                                <h3><i class="fas fa-graduation-cap"></i> Teaching Information</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="subjects">Subjects I Teach</label>
                                        <input type="text" id="subjects" name="subjects" value="Mathematics, Physics" placeholder="e.g., Mathematics, Physics, Chemistry">
                                    </div>
                                    <div class="form-group">
                                        <label for="experience_years">Years of Experience</label>
                                        <select id="experience_years" name="experience_years">
                                            <option value="1-2">1-2 years</option>
                                            <option value="3-5" selected>3-5 years</option>
                                            <option value="5-10">5-10 years</option>
                                            <option value="10+">10+ years</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="hourly_rate">Hourly Rate ($)</label>
                                        <input type="number" id="hourly_rate" name="hourly_rate" value="25" min="5" max="200">
                                    </div>
                                    <div class="form-group">
                                        <label for="education_level">Education Level</label>
                                        <select id="education_level" name="education_level">
                                            <option value="bachelors">Bachelor's Degree</option>
                                            <option value="masters" selected>Master's Degree</option>
                                            <option value="phd">PhD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label for="teaching_style">Teaching Style & Approach</label>
                                    <textarea id="teaching_style" name="teaching_style" rows="3" placeholder="Describe your teaching methodology...">I focus on interactive learning and real-world applications. My approach is to break down complex problems into manageable steps and encourage students to think critically about solutions.</textarea>
                                </div>
                            </div>
                            <?php elseif ($user_role === 'student'): ?>
                            <div class="profile-section">
                                <h3><i class="fas fa-book"></i> Academic Information</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="university">University</label>
                                        <input type="text" id="university" name="university" value="Zewail University">
                                    </div>
                                    <div class="form-group">
                                        <label for="major">Major/Field of Study</label>
                                        <input type="text" id="major" name="major" value="Computer Science">
                                    </div>
                                    <div class="form-group">
                                        <label for="year_of_study">Year of Study</label>
                                        <select id="year_of_study" name="year_of_study">
                                            <option value="1">1st Year</option>
                                            <option value="2" selected>2nd Year</option>
                                            <option value="3">3rd Year</option>
                                            <option value="4">4th Year</option>
                                            <option value="graduate">Graduate</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="gpa">Current GPA</label>
                                        <input type="number" id="gpa" name="gpa" value="3.7" min="0" max="4" step="0.1">
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label for="subjects_interested">Subjects I Need Help With</label>
                                    <input type="text" id="subjects_interested" name="subjects_interested" value="Mathematics, Physics, Programming" placeholder="e.g., Mathematics, Physics, Chemistry">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="profile-section">
                                <h3><i class="fas fa-link"></i> Social Links</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="linkedin">LinkedIn Profile</label>
                                        <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/yourprofile">
                                    </div>
                                    <div class="form-group">
                                        <label for="github">GitHub Profile</label>
                                        <input type="url" id="github" name="github" placeholder="https://github.com/yourusername">
                                    </div>
                                    <div class="form-group">
                                        <label for="website">Personal Website</label>
                                        <input type="url" id="website" name="website" placeholder="https://yourwebsite.com">
                                    </div>
                                    <div class="form-group">
                                        <label for="twitter">Twitter Profile</label>
                                        <input type="url" id="twitter" name="twitter" placeholder="https://twitter.com/yourusername">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <button type="submit" class="save-profile-btn">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="cancel-btn">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>