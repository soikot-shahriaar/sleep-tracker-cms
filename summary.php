<?php
/**
 * Sleep Summary Page
 * Sleep Tracker CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/SleepRecord.php';

// Require login
require_login();

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get sleep record object
$sleep_record = new SleepRecord($db);
$sleep_record->user_id = $_SESSION['user_id'];

// Get overall statistics
$overall_stats = $sleep_record->getStatistics();

// Get weekly statistics
$weekly_stats = $sleep_record->getWeeklyStatistics();

// Get monthly statistics (last 30 days)
$monthly_query = "SELECT 
                    COUNT(*) as total_records,
                    AVG(sleep_duration) as avg_duration,
                    AVG(CASE 
                        WHEN sleep_quality = 'poor' THEN 1
                        WHEN sleep_quality = 'fair' THEN 2
                        WHEN sleep_quality = 'good' THEN 3
                        WHEN sleep_quality = 'excellent' THEN 4
                    END) as avg_quality_score
                  FROM sleep_records 
                  WHERE user_id = :user_id 
                  AND sleep_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

$monthly_stmt = $db->prepare($monthly_query);
$monthly_stmt->bindParam(':user_id', $_SESSION['user_id']);
$monthly_stmt->execute();
$monthly_stats = $monthly_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent 30 days data for chart
$chart_query = "SELECT 
                    sleep_date,
                    sleep_duration,
                    CASE 
                        WHEN sleep_quality = 'poor' THEN 1
                        WHEN sleep_quality = 'fair' THEN 2
                        WHEN sleep_quality = 'good' THEN 3
                        WHEN sleep_quality = 'excellent' THEN 4
                    END as quality_score
                FROM sleep_records 
                WHERE user_id = :user_id 
                AND sleep_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ORDER BY sleep_date ASC";

$chart_stmt = $db->prepare($chart_query);
$chart_stmt->bindParam(':user_id', $_SESSION['user_id']);
$chart_stmt->execute();
$chart_data = $chart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sleep quality distribution
$quality_query = "SELECT 
                    sleep_quality,
                    COUNT(*) as count
                  FROM sleep_records 
                  WHERE user_id = :user_id 
                  GROUP BY sleep_quality
                  ORDER BY FIELD(sleep_quality, 'poor', 'fair', 'good', 'excellent')";

$quality_stmt = $db->prepare($quality_query);
$quality_stmt->bindParam(':user_id', $_SESSION['user_id']);
$quality_stmt->execute();
$quality_distribution = $quality_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sleep pattern analysis (by day of week)
$pattern_query = "SELECT 
                    DAYNAME(sleep_date) as day_name,
                    DAYOFWEEK(sleep_date) as day_num,
                    AVG(sleep_duration) as avg_duration,
                    COUNT(*) as count
                  FROM sleep_records 
                  WHERE user_id = :user_id 
                  GROUP BY DAYOFWEEK(sleep_date), DAYNAME(sleep_date)
                  ORDER BY DAYOFWEEK(sleep_date)";

$pattern_stmt = $db->prepare($pattern_query);
$pattern_stmt->bindParam(':user_id', $_SESSION['user_id']);
$pattern_stmt->execute();
$sleep_patterns = $pattern_stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to get quality label
function getQualityLabel($score) {
    if ($score >= 3.5) return 'Excellent';
    if ($score >= 2.5) return 'Good';
    if ($score >= 1.5) return 'Fair';
    return 'Poor';
}

// Helper function to get quality color
function getQualityColor($score) {
    if ($score >= 3.5) return '#4CAF50';
    if ($score >= 2.5) return '#8BC34A';
    if ($score >= 1.5) return '#FF9800';
    return '#F44336';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleep Summary - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="summary.php" class="nav-link active">Summary</a>
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
            <h2>Sleep Summary & Analytics</h2>
            <p>Insights into your sleep patterns and trends</p>
        </div>
        
        <?php if ($overall_stats['total_records'] == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <h3>No Data Available</h3>
                <p>Start tracking your sleep to see detailed analytics and insights.</p>
                <a href="add-sleep.php" class="btn btn-primary">Add Sleep Record</a>
            </div>
        <?php else: ?>
            <!-- Summary Statistics -->
            <div class="summary-stats">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Overall Statistics</h3>
                        <div class="stat-item">
                            <span class="stat-label">Total Records:</span>
                            <span class="stat-value"><?php echo $overall_stats['total_records']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Sleep:</span>
                            <span class="stat-value"><?php echo format_duration($overall_stats['avg_duration']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Quality:</span>
                            <span class="stat-value" style="color: <?php echo getQualityColor($overall_stats['avg_quality_score']); ?>">
                                <?php echo getQualityLabel($overall_stats['avg_quality_score']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>This Week</h3>
                        <div class="stat-item">
                            <span class="stat-label">Records:</span>
                            <span class="stat-value"><?php echo $weekly_stats['total_records']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Sleep:</span>
                            <span class="stat-value">
                                <?php echo $weekly_stats['avg_duration'] ? format_duration($weekly_stats['avg_duration']) : 'N/A'; ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Quality:</span>
                            <span class="stat-value" style="color: <?php echo getQualityColor($weekly_stats['avg_quality_score'] ?? 0); ?>">
                                <?php echo $weekly_stats['avg_quality_score'] ? getQualityLabel($weekly_stats['avg_quality_score']) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Last 30 Days</h3>
                        <div class="stat-item">
                            <span class="stat-label">Records:</span>
                            <span class="stat-value"><?php echo $monthly_stats['total_records']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Sleep:</span>
                            <span class="stat-value">
                                <?php echo $monthly_stats['avg_duration'] ? format_duration($monthly_stats['avg_duration']) : 'N/A'; ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Quality:</span>
                            <span class="stat-value" style="color: <?php echo getQualityColor($monthly_stats['avg_quality_score'] ?? 0); ?>">
                                <?php echo $monthly_stats['avg_quality_score'] ? getQualityLabel($monthly_stats['avg_quality_score']) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-container">
                    <h3>Sleep Duration Trend (Last 30 Days)</h3>
                    <canvas id="sleepTrendChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3>Sleep Quality Distribution</h3>
                    <canvas id="qualityChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3>Sleep Patterns by Day of Week</h3>
                    <canvas id="patternChart"></canvas>
                </div>
            </div>
            
            <!-- Sleep Insights -->
            <div class="insights-section">
                <h3>Sleep Insights</h3>
                <div class="insights-grid">
                    <?php
                    $avg_duration = $overall_stats['avg_duration'];
                    $avg_quality = $overall_stats['avg_quality_score'];
                    ?>
                    
                    <div class="insight-card">
                        <div class="insight-icon">💤</div>
                        <div class="insight-content">
                            <h4>Sleep Duration</h4>
                            <?php if ($avg_duration >= 7 && $avg_duration <= 9): ?>
                                <p class="insight-good">Great! You're getting the recommended 7-9 hours of sleep.</p>
                            <?php elseif ($avg_duration < 7): ?>
                                <p class="insight-warning">You're averaging less than 7 hours. Consider going to bed earlier.</p>
                            <?php else: ?>
                                <p class="insight-info">You're sleeping more than 9 hours on average. This might be fine, but consider your sleep quality.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-icon">⭐</div>
                        <div class="insight-content">
                            <h4>Sleep Quality</h4>
                            <?php if ($avg_quality >= 3): ?>
                                <p class="insight-good">Excellent sleep quality! Keep up the good habits.</p>
                            <?php elseif ($avg_quality >= 2): ?>
                                <p class="insight-warning">Your sleep quality could be improved. Consider your sleep environment and routine.</p>
                            <?php else: ?>
                                <p class="insight-error">Poor sleep quality detected. Consider consulting a healthcare provider.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Consistency</h4>
                            <?php
                            $consistency_score = $overall_stats['total_records'] / max(1, (strtotime('now') - strtotime('-30 days')) / (24 * 60 * 60));
                            ?>
                            <?php if ($consistency_score >= 0.8): ?>
                                <p class="insight-good">Great consistency in tracking your sleep!</p>
                            <?php elseif ($consistency_score >= 0.5): ?>
                                <p class="insight-warning">Good tracking habits. Try to log your sleep more regularly.</p>
                            <?php else: ?>
                                <p class="insight-info">Consider tracking your sleep more consistently for better insights.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Chart data from PHP
        const chartData = <?php echo json_encode($chart_data); ?>;
        const qualityData = <?php echo json_encode($quality_distribution); ?>;
        const patternData = <?php echo json_encode($sleep_patterns); ?>;
        
        // Sleep Trend Chart
        if (chartData.length > 0) {
            const ctx1 = document.getElementById('sleepTrendChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: chartData.map(item => new Date(item.sleep_date).toLocaleDateString()),
                    datasets: [{
                        label: 'Sleep Duration (hours)',
                        data: chartData.map(item => parseFloat(item.sleep_duration)),
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 12,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        }
        
        // Quality Distribution Chart
        if (qualityData.length > 0) {
            const ctx2 = document.getElementById('qualityChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: qualityData.map(item => item.sleep_quality.charAt(0).toUpperCase() + item.sleep_quality.slice(1)),
                    datasets: [{
                        data: qualityData.map(item => parseInt(item.count)),
                        backgroundColor: ['#F44336', '#FF9800', '#8BC34A', '#4CAF50']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        // Sleep Pattern Chart
        if (patternData.length > 0) {
            const ctx3 = document.getElementById('patternChart').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: patternData.map(item => item.day_name),
                    datasets: [{
                        label: 'Average Sleep Duration (hours)',
                        data: patternData.map(item => parseFloat(item.avg_duration)),
                        backgroundColor: '#2196F3',
                        borderColor: '#1976D2',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 12,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>

