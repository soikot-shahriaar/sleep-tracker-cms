<?php
/**
 * Main Configuration File
 * Sleep Tracker CMS
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'Sleep Tracker CMS');
define('APP_VERSION', '1.0.0');
// Set APP_URL to your base URL (without port number)
// Examples: 'http://localhost' for root, or 'http://localhost/project_name' for subdirectory
define('APP_URL', 'http://localhost');

// Security settings
define('HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Date and time settings
date_default_timezone_set('UTC');
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Include database configuration
require_once __DIR__ . '/database.php';

/**
 * Utility Functions
 */

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 * @return string
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require login - redirect to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirect function
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Format time duration in hours and minutes
 * @param float $hours
 * @return string
 */
function format_duration($hours) {
    $h = floor($hours);
    $m = round(($hours - $h) * 60);
    return sprintf('%dh %02dm', $h, $m);
}

/**
 * Calculate sleep duration between two times
 * @param string $bedtime
 * @param string $wake_time
 * @return float
 */
function calculate_sleep_duration($bedtime, $wake_time) {
    $bedtime_timestamp = strtotime($bedtime);
    $wake_time_timestamp = strtotime($wake_time);
    
    // If wake time is earlier than bedtime, assume it's the next day
    if ($wake_time_timestamp < $bedtime_timestamp) {
        $wake_time_timestamp += 24 * 60 * 60; // Add 24 hours
    }
    
    $duration_seconds = $wake_time_timestamp - $bedtime_timestamp;
    return round($duration_seconds / 3600, 2); // Convert to hours with 2 decimal places
}
?>

