<?php
/**
 * User Logout
 * Sleep Tracker CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';

// Create user object and logout
$user = new User(null);
$user->logout();

// Redirect to login page
redirect('login.php');
?>

