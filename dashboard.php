<?php
/**
 * Dashboard Page
 * Sleep Tracker CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';

// Require login
require_login();

// Get user information
$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
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
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Sleep Tracking Dashboard</h2>
                <p>Track and monitor your sleep patterns for better health</p>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">🛏️</div>
                    <div class="stat-content">
                        <h3>Total Records</h3>
                        <p class="stat-number" id="total-records">-</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-content">
                        <h3>Average Sleep</h3>
                        <p class="stat-number" id="avg-sleep">-</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>This Week</h3>
                        <p class="stat-number" id="week-avg">-</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <h3>Sleep Quality</h3>
                        <p class="stat-number" id="avg-quality">-</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <div class="action-card">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="add-sleep.php" class="btn btn-primary">
                            <span class="btn-icon">➕</span>
                            Add Sleep Record
                        </a>
                        <a href="sleep-log.php" class="btn btn-secondary">
                            <span class="btn-icon">📋</span>
                            View Sleep Log
                        </a>
                        <a href="summary.php" class="btn btn-secondary">
                            <span class="btn-icon">📈</span>
                            View Summary
                        </a>
                    </div>
                </div>
                
                <div class="recent-sleep">
                    <h3>Recent Sleep Records</h3>
                    <div id="recent-records">
                        <p>Loading recent records...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    
    <!-- Copyright Footer -->
    <div class="text-center my-2" style="text-align: center; margin: 40px 0 20px 0; padding: 20px; border-top: 1px solid #e1e5e9; color: #666; font-size: 14px;">
        <div>
            <span>© 2025 .  </span>
            <span class="text- ">Developed by </span>
            <a href="https://rivertheme.com" class="fw-bold text-decoration-none" target="_blank" rel="noopener" style="color: #667eea; text-decoration: none; font-weight: bold;">RiverTheme</a>
        </div>
    </div>
</body>
</html>

