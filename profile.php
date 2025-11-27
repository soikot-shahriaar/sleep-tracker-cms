<?php
/**
 * User Profile Page
 * Sleep Tracker CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';

// Require login
require_login();

$errors = [];
$success_message = '';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get user information
$user = new User($db);
$user->getUserById($_SESSION['user_id']);

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        
        // Validate input
        if (empty($first_name)) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($last_name)) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!validate_email($email)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        if (empty($errors)) {
            // Update user profile
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->email = $email;
            
            if ($user->updateProfile()) {
                // Update session variables
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                
                $success_message = 'Profile updated successfully!';
            } else {
                $errors['general'] = 'Failed to update profile. Email might already be in use.';
            }
        }
    }
}

// Process password change form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['password_general'] = 'Invalid request. Please try again.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required';
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = 'New password must be at least 6 characters long';
        }
        
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your new password';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            if ($user->changePassword($current_password, $new_password)) {
                $success_message = 'Password changed successfully!';
            } else {
                $errors['password_general'] = 'Current password is incorrect.';
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="sleep-log.php" class="nav-link">Sleep Log</a>
                <a href="add-sleep.php" class="nav-link">Add Sleep</a>
                <a href="summary.php" class="nav-link">Summary</a>
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
            <div class="nav-user">
                Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h2>User Profile</h2>
            <p>Manage your account information and settings</p>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-container">
            <!-- Profile Information -->
            <div class="profile-section">
                <h3>Profile Information</h3>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="profile.php" class="form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($user->first_name); ?>" 
                                   required>
                            <?php if (!empty($errors['first_name'])): ?>
                                <span class="error"><?php echo $errors['first_name']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($user->last_name); ?>" 
                                   required>
                            <?php if (!empty($errors['last_name'])): ?>
                                <span class="error"><?php echo $errors['last_name']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user->username); ?>" 
                               disabled>
                        <small>Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user->email); ?>" 
                               required>
                        <?php if (!empty($errors['email'])): ?>
                            <span class="error"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="profile-section">
                <h3>Change Password</h3>
                
                <?php if (!empty($errors['password_general'])): ?>
                    <div class="alert alert-error">
                        <?php echo $errors['password_general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="profile.php" class="form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                        <?php if (!empty($errors['current_password'])): ?>
                            <span class="error"><?php echo $errors['current_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <?php if (!empty($errors['new_password'])): ?>
                            <span class="error"><?php echo $errors['new_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <span class="error"><?php echo $errors['confirm_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
            
            <!-- Account Information -->
            <div class="profile-section">
                <h3>Account Information</h3>
                <div class="account-info">
                    <div class="info-item">
                        <span class="info-label">Member Since:</span>
                        <span class="info-value"><?php echo date('M j, Y', strtotime($user->created_at)); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value"><?php echo date('M j, Y g:i A', strtotime($user->updated_at)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

