<?php
/**
 * Add Sleep Record Page
 * Sleep Tracker CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/SleepRecord.php';

// Require login
require_login();

$errors = [];
$success_message = '';
$data = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $data = [
            'sleep_date' => sanitize_input($_POST['sleep_date'] ?? ''),
            'bedtime' => sanitize_input($_POST['bedtime'] ?? ''),
            'wake_time' => sanitize_input($_POST['wake_time'] ?? ''),
            'sleep_quality' => sanitize_input($_POST['sleep_quality'] ?? ''),
            'notes' => sanitize_input($_POST['notes'] ?? '')
        ];
        
        // Validate input
        $errors = SleepRecord::validateData($data);
        
        if (empty($errors)) {
            // Create database connection
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if record already exists for this date
            $sleep_record = new SleepRecord($db);
            $sleep_record->user_id = $_SESSION['user_id'];
            
            if ($sleep_record->existsForDate($data['sleep_date'])) {
                $errors['sleep_date'] = 'A sleep record already exists for this date. Please edit the existing record or choose a different date.';
            } else {
                // Calculate sleep duration
                $sleep_duration = calculate_sleep_duration($data['bedtime'], $data['wake_time']);
                
                // Create sleep record
                $sleep_record->sleep_date = $data['sleep_date'];
                $sleep_record->bedtime = $data['bedtime'];
                $sleep_record->wake_time = $data['wake_time'];
                $sleep_record->sleep_duration = $sleep_duration;
                $sleep_record->sleep_quality = $data['sleep_quality'];
                $sleep_record->notes = $data['notes'];
                
                if ($sleep_record->create()) {
                    $success_message = 'Sleep record added successfully!';
                    // Clear form data
                    $data = [];
                } else {
                    $errors['general'] = 'Failed to add sleep record. Please try again.';
                }
            }
        }
    }
}

// Set default date to today if not provided
if (empty($data['sleep_date'])) {
    $data['sleep_date'] = date('Y-m-d');
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sleep Record - <?php echo APP_NAME; ?></title>
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
                <a href="add-sleep.php" class="nav-link active">Add Sleep</a>
                <a href="summary.php" class="nav-link">Summary</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
            <div class="nav-user">
                Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h2>Add Sleep Record</h2>
            <p>Log your sleep data to track your sleep patterns</p>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
                <p><a href="sleep-log.php">View all sleep records</a> | <a href="add-sleep.php">Add another record</a></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="add-sleep.php" class="form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="sleep_date">Sleep Date</label>
                    <input type="date" id="sleep_date" name="sleep_date" 
                           value="<?php echo htmlspecialchars($data['sleep_date'] ?? ''); ?>" 
                           max="<?php echo date('Y-m-d'); ?>" required>
                    <?php if (!empty($errors['sleep_date'])): ?>
                        <span class="error"><?php echo $errors['sleep_date']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bedtime">Bedtime</label>
                        <input type="time" id="bedtime" name="bedtime" 
                               value="<?php echo htmlspecialchars($data['bedtime'] ?? ''); ?>" 
                               required>
                        <?php if (!empty($errors['bedtime'])): ?>
                            <span class="error"><?php echo $errors['bedtime']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="wake_time">Wake Time</label>
                        <input type="time" id="wake_time" name="wake_time" 
                               value="<?php echo htmlspecialchars($data['wake_time'] ?? ''); ?>" 
                               required>
                        <?php if (!empty($errors['wake_time'])): ?>
                            <span class="error"><?php echo $errors['wake_time']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($errors['duration'])): ?>
                    <div class="alert alert-error">
                        <?php echo $errors['duration']; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="sleep_quality">Sleep Quality</label>
                    <select id="sleep_quality" name="sleep_quality" required>
                        <option value="">Select sleep quality</option>
                        <option value="poor" <?php echo (($data['sleep_quality'] ?? '') == 'poor') ? 'selected' : ''; ?>>Poor</option>
                        <option value="fair" <?php echo (($data['sleep_quality'] ?? '') == 'fair') ? 'selected' : ''; ?>>Fair</option>
                        <option value="good" <?php echo (($data['sleep_quality'] ?? '') == 'good') ? 'selected' : ''; ?>>Good</option>
                        <option value="excellent" <?php echo (($data['sleep_quality'] ?? '') == 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                    </select>
                    <?php if (!empty($errors['sleep_quality'])): ?>
                        <span class="error"><?php echo $errors['sleep_quality']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4" 
                              placeholder="Any additional notes about your sleep..."><?php echo htmlspecialchars($data['notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Sleep Record</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        
        <div class="sleep-tips">
            <h3>Sleep Tips</h3>
            <ul>
                <li>Aim for 7-9 hours of sleep per night</li>
                <li>Try to maintain a consistent sleep schedule</li>
                <li>Create a relaxing bedtime routine</li>
                <li>Keep your bedroom cool, dark, and quiet</li>
                <li>Avoid caffeine and screens before bedtime</li>
            </ul>
        </div>
    </div>
    
    <script src="assets/js/sleep-form.js"></script>
</body>
</html>

