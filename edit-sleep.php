<?php
/**
 * Edit Sleep Record Page
 * Sleep Tracker CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/SleepRecord.php';

// Require login
require_login();

// Check if record ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('sleep-log.php');
}

$record_id = (int)$_GET['id'];
$errors = [];
$success_message = '';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get the sleep record
$sleep_record = new SleepRecord($db);
$sleep_record->user_id = $_SESSION['user_id'];

if (!$sleep_record->getById($record_id)) {
    redirect('sleep-log.php');
}

// Populate data array with existing record
$data = [
    'sleep_date' => $sleep_record->sleep_date,
    'bedtime' => substr($sleep_record->bedtime, 0, 5), // Remove seconds
    'wake_time' => substr($sleep_record->wake_time, 0, 5), // Remove seconds
    'sleep_quality' => $sleep_record->sleep_quality,
    'notes' => $sleep_record->notes
];

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
        
        // Check if date changed and if new date already has a record
        if (empty($errors['sleep_date']) && $data['sleep_date'] != $sleep_record->sleep_date) {
            if ($sleep_record->existsForDate($data['sleep_date'])) {
                $errors['sleep_date'] = 'A sleep record already exists for this date. Please choose a different date.';
            }
        }
        
        if (empty($errors)) {
            // Calculate sleep duration
            $sleep_duration = calculate_sleep_duration($data['bedtime'], $data['wake_time']);
            
            // Update sleep record
            $sleep_record->sleep_date = $data['sleep_date'];
            $sleep_record->bedtime = $data['bedtime'];
            $sleep_record->wake_time = $data['wake_time'];
            $sleep_record->sleep_duration = $sleep_duration;
            $sleep_record->sleep_quality = $data['sleep_quality'];
            $sleep_record->notes = $data['notes'];
            
            if ($sleep_record->update()) {
                $success_message = 'Sleep record updated successfully!';
            } else {
                $errors['general'] = 'Failed to update sleep record. Please try again.';
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
    <title>Edit Sleep Record - <?php echo APP_NAME; ?></title>
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
            <h2>Edit Sleep Record</h2>
            <p>Update your sleep data</p>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
                <p><a href="sleep-log.php">Back to Sleep Log</a></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="edit-sleep.php?id=<?php echo $record_id; ?>" class="form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="sleep_date">Sleep Date</label>
                    <input type="date" id="sleep_date" name="sleep_date" 
                           value="<?php echo htmlspecialchars($data['sleep_date']); ?>" 
                           max="<?php echo date('Y-m-d'); ?>" required>
                    <?php if (!empty($errors['sleep_date'])): ?>
                        <span class="error"><?php echo $errors['sleep_date']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bedtime">Bedtime</label>
                        <input type="time" id="bedtime" name="bedtime" 
                               value="<?php echo htmlspecialchars($data['bedtime']); ?>" 
                               required>
                        <?php if (!empty($errors['bedtime'])): ?>
                            <span class="error"><?php echo $errors['bedtime']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="wake_time">Wake Time</label>
                        <input type="time" id="wake_time" name="wake_time" 
                               value="<?php echo htmlspecialchars($data['wake_time']); ?>" 
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
                        <option value="poor" <?php echo ($data['sleep_quality'] == 'poor') ? 'selected' : ''; ?>>Poor</option>
                        <option value="fair" <?php echo ($data['sleep_quality'] == 'fair') ? 'selected' : ''; ?>>Fair</option>
                        <option value="good" <?php echo ($data['sleep_quality'] == 'good') ? 'selected' : ''; ?>>Good</option>
                        <option value="excellent" <?php echo ($data['sleep_quality'] == 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                    </select>
                    <?php if (!empty($errors['sleep_quality'])): ?>
                        <span class="error"><?php echo $errors['sleep_quality']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4" 
                              placeholder="Any additional notes about your sleep..."><?php echo htmlspecialchars($data['notes']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Sleep Record</button>
                    <a href="sleep-log.php" class="btn btn-secondary">Cancel</a>
                    <a href="sleep-log.php?action=delete&id=<?php echo $record_id; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this sleep record?')">
                        Delete Record
                    </a>
                </div>
            </form>
        </div>
        
        <div class="record-info">
            <h3>Record Information</h3>
            <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($sleep_record->created_at)); ?></p>
            <?php if ($sleep_record->updated_at != $sleep_record->created_at): ?>
                <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($sleep_record->updated_at)); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/sleep-form.js"></script>
</body>
</html>

