<?php
/**
 * Sleep Log Page
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

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $record_id = (int)$_GET['id'];
    
    $sleep_record = new SleepRecord($db);
    $sleep_record->user_id = $_SESSION['user_id'];
    $sleep_record->id = $record_id;
    
    if ($sleep_record->delete()) {
        $success_message = 'Sleep record deleted successfully.';
    } else {
        $error_message = 'Failed to delete sleep record.';
    }
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Get sleep records
$sleep_record = new SleepRecord($db);
$sleep_record->user_id = $_SESSION['user_id'];
$records = $sleep_record->getAllByUser($records_per_page, $offset);

// Get total count for pagination
$total_query = "SELECT COUNT(*) as total FROM sleep_records WHERE user_id = :user_id";
$total_stmt = $db->prepare($total_query);
$total_stmt->bindParam(':user_id', $_SESSION['user_id']);
$total_stmt->execute();
$total_records = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleep Log - <?php echo APP_NAME; ?></title>
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
                <a href="sleep-log.php" class="nav-link active">Sleep Log</a>
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
            <h2>Sleep Log</h2>
            <p>View and manage your sleep records</p>
            <a href="add-sleep.php" class="btn btn-primary">Add New Record</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($records)): ?>
            <div class="empty-state">
                <div class="empty-icon">😴</div>
                <h3>No Sleep Records Yet</h3>
                <p>Start tracking your sleep by adding your first sleep record.</p>
                <a href="add-sleep.php" class="btn btn-primary">Add Sleep Record</a>
            </div>
        <?php else: ?>
            <div class="sleep-log">
                <div class="log-stats">
                    <p>Showing <?php echo count($records); ?> of <?php echo $total_records; ?> records</p>
                </div>
                
                <div class="records-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Bedtime</th>
                                <th>Wake Time</th>
                                <th>Duration</th>
                                <th>Quality</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td class="date-cell">
                                        <?php echo date('M j, Y', strtotime($record['sleep_date'])); ?>
                                        <small><?php echo date('D', strtotime($record['sleep_date'])); ?></small>
                                    </td>
                                    <td><?php echo date('g:i A', strtotime($record['bedtime'])); ?></td>
                                    <td><?php echo date('g:i A', strtotime($record['wake_time'])); ?></td>
                                    <td class="duration-cell">
                                        <?php echo format_duration($record['sleep_duration']); ?>
                                    </td>
                                    <td>
                                        <span class="quality-badge quality-<?php echo $record['sleep_quality']; ?>">
                                            <?php echo ucfirst($record['sleep_quality']); ?>
                                        </span>
                                    </td>
                                    <td class="notes-cell">
                                        <?php if (!empty($record['notes'])): ?>
                                            <span class="notes-preview" title="<?php echo htmlspecialchars($record['notes']); ?>">
                                                <?php echo htmlspecialchars(substr($record['notes'], 0, 50)); ?>
                                                <?php if (strlen($record['notes']) > 50): ?>...<?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-notes">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="edit-sleep.php?id=<?php echo $record['id']; ?>" 
                                           class="btn btn-small btn-secondary" title="Edit">
                                            ✏️
                                        </a>
                                        <a href="sleep-log.php?action=delete&id=<?php echo $record['id']; ?>" 
                                           class="btn btn-small btn-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this sleep record?')">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="sleep-log.php?page=<?php echo $page - 1; ?>" class="btn btn-secondary">Previous</a>
                        <?php endif; ?>
                        
                        <span class="page-info">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="sleep-log.php?page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

