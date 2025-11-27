<?php
/**
 * SleepRecord Class
 * Handles sleep record management
 */

require_once __DIR__ . '/../config/config.php';

class SleepRecord {
    private $conn;
    private $table_name = "sleep_records";
    
    public $id;
    public $user_id;
    public $sleep_date;
    public $bedtime;
    public $wake_time;
    public $sleep_duration;
    public $sleep_quality;
    public $notes;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Add a new sleep record
     * @return bool
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, sleep_date, bedtime, wake_time, sleep_duration, sleep_quality, notes) 
                  VALUES (:user_id, :sleep_date, :bedtime, :wake_time, :sleep_duration, :sleep_quality, :notes)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':sleep_date', $this->sleep_date);
        $stmt->bindParam(':bedtime', $this->bedtime);
        $stmt->bindParam(':wake_time', $this->wake_time);
        $stmt->bindParam(':sleep_duration', $this->sleep_duration);
        $stmt->bindParam(':sleep_quality', $this->sleep_quality);
        $stmt->bindParam(':notes', $this->notes);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update an existing sleep record
     * @return bool
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET sleep_date = :sleep_date, bedtime = :bedtime, wake_time = :wake_time, 
                      sleep_duration = :sleep_duration, sleep_quality = :sleep_quality, notes = :notes 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':sleep_date', $this->sleep_date);
        $stmt->bindParam(':bedtime', $this->bedtime);
        $stmt->bindParam(':wake_time', $this->wake_time);
        $stmt->bindParam(':sleep_duration', $this->sleep_duration);
        $stmt->bindParam(':sleep_quality', $this->sleep_quality);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a sleep record
     * @return bool
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get a sleep record by ID
     * @param int $id
     * @return bool
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->sleep_date = $row['sleep_date'];
            $this->bedtime = $row['bedtime'];
            $this->wake_time = $row['wake_time'];
            $this->sleep_duration = $row['sleep_duration'];
            $this->sleep_quality = $row['sleep_quality'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all sleep records for a user
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllByUser($limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY sleep_date DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent sleep records for a user
     * @param int $days
     * @return array
     */
    public function getRecentRecords($days = 7) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND sleep_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                  ORDER BY sleep_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get sleep records for a date range
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND sleep_date BETWEEN :start_date AND :end_date
                  ORDER BY sleep_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get sleep statistics for a user
     * @return array
     */
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_records,
                    AVG(sleep_duration) as avg_duration,
                    MIN(sleep_duration) as min_duration,
                    MAX(sleep_duration) as max_duration,
                    AVG(CASE 
                        WHEN sleep_quality = 'poor' THEN 1
                        WHEN sleep_quality = 'fair' THEN 2
                        WHEN sleep_quality = 'good' THEN 3
                        WHEN sleep_quality = 'excellent' THEN 4
                    END) as avg_quality_score
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get weekly statistics
     * @return array
     */
    public function getWeeklyStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_records,
                    AVG(sleep_duration) as avg_duration,
                    AVG(CASE 
                        WHEN sleep_quality = 'poor' THEN 1
                        WHEN sleep_quality = 'fair' THEN 2
                        WHEN sleep_quality = 'good' THEN 3
                        WHEN sleep_quality = 'excellent' THEN 4
                    END) as avg_quality_score
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND sleep_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if a record exists for a specific date
     * @param string $date
     * @return bool
     */
    public function existsForDate($date) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND sleep_date = :sleep_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':sleep_date', $date);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Validate sleep record data
     * @param array $data
     * @return array
     */
    public static function validateData($data) {
        $errors = [];
        
        // Sleep date validation
        if (empty($data['sleep_date'])) {
            $errors['sleep_date'] = 'Sleep date is required';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['sleep_date'])) {
            $errors['sleep_date'] = 'Invalid date format';
        } elseif (strtotime($data['sleep_date']) > time()) {
            $errors['sleep_date'] = 'Sleep date cannot be in the future';
        }
        
        // Bedtime validation
        if (empty($data['bedtime'])) {
            $errors['bedtime'] = 'Bedtime is required';
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $data['bedtime'])) {
            $errors['bedtime'] = 'Invalid time format (use HH:MM)';
        }
        
        // Wake time validation
        if (empty($data['wake_time'])) {
            $errors['wake_time'] = 'Wake time is required';
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $data['wake_time'])) {
            $errors['wake_time'] = 'Invalid time format (use HH:MM)';
        }
        
        // Sleep quality validation
        if (empty($data['sleep_quality'])) {
            $errors['sleep_quality'] = 'Sleep quality is required';
        } elseif (!in_array($data['sleep_quality'], ['poor', 'fair', 'good', 'excellent'])) {
            $errors['sleep_quality'] = 'Invalid sleep quality value';
        }
        
        // Calculate and validate duration if times are provided
        if (empty($errors['bedtime']) && empty($errors['wake_time'])) {
            $duration = calculate_sleep_duration($data['bedtime'], $data['wake_time']);
            if ($duration < 0.5 || $duration > 24) {
                $errors['duration'] = 'Sleep duration must be between 0.5 and 24 hours';
            }
        }
        
        return $errors;
    }
}
?>

