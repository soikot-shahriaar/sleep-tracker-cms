<?php
/**
 * Main Index Page
 * Sleep Tracker CMS
 */

// Ensure no output before headers
ob_start();

require_once 'config/config.php';

// Redirect based on login status
if (is_logged_in()) {
    ob_end_clean();
    redirect('dashboard.php');
} else {
    ob_end_clean();
    redirect('login.php');
}
?>

