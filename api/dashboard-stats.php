<?php
/**
 * Dashboard Statistics API
 * Sleep Tracker CMS
 */

require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/SleepRecord.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
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
    
    // Get recent records (last 5)
    $recent_records = $sleep_record->getRecentRecords(30);
    $recent_records = array_slice($recent_records, 0, 5);
    
    // Format recent records
    $formatted_recent = [];
    foreach ($recent_records as $record) {
        $formatted_recent[] = [
            'id' => $record['id'],
            'sleep_date' => date('M j, Y', strtotime($record['sleep_date'])),
            'duration' => format_duration($record['sleep_duration']),
            'quality' => ucfirst($record['sleep_quality']),
            'quality_class' => 'quality-' . $record['sleep_quality']
        ];
    }
    
    // Helper function to get quality label
    function getQualityLabel($score) {
        if ($score >= 3.5) return 'Excellent';
        if ($score >= 2.5) return 'Good';
        if ($score >= 1.5) return 'Fair';
        return 'Poor';
    }
    
    // Prepare response data
    $response = [
        'success' => true,
        'data' => [
            'total_records' => (int)$overall_stats['total_records'],
            'avg_sleep' => $overall_stats['avg_duration'] ? format_duration($overall_stats['avg_duration']) : 'N/A',
            'week_avg' => $weekly_stats['avg_duration'] ? format_duration($weekly_stats['avg_duration']) : 'N/A',
            'avg_quality' => $overall_stats['avg_quality_score'] ? getQualityLabel($overall_stats['avg_quality_score']) : 'N/A',
            'recent_records' => $formatted_recent
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>

