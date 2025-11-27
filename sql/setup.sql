-- Sleep Tracker CMS Database Setup
-- This file creates the necessary database and tables for the Sleep Tracker CMS

-- Create database
CREATE DATABASE IF NOT EXISTS sleep_tracker_cms;
USE sleep_tracker_cms;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create sleep_records table
CREATE TABLE IF NOT EXISTS sleep_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sleep_date DATE NOT NULL,
    bedtime TIME NOT NULL,
    wake_time TIME NOT NULL,
    sleep_duration DECIMAL(4,2) NOT NULL, -- in hours (e.g., 7.5 for 7 hours 30 minutes)
    sleep_quality ENUM('poor', 'fair', 'good', 'excellent') DEFAULT 'good',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, sleep_date)
);

-- Create indexes for better performance
CREATE INDEX idx_sleep_records_user_date ON sleep_records(user_id, sleep_date);
CREATE INDEX idx_sleep_records_date ON sleep_records(sleep_date);

-- Insert sample data for testing
-- Demo user password: demo123
INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES
('demo_user', 'demo@example.com', '$2y$10$UqUHqY1d0TozJP3Z7y5kdOsGR71bE53CmM3sbxdf2n95DKINxp6Ve', 'Demo', 'User');

-- Get the user ID for sample sleep records
SET @demo_user_id = LAST_INSERT_ID();

-- Insert sample sleep records
INSERT INTO sleep_records (user_id, sleep_date, bedtime, wake_time, sleep_duration, sleep_quality, notes) VALUES
(@demo_user_id, '2024-08-01', '23:00:00', '07:00:00', 8.00, 'good', 'Felt well rested'),
(@demo_user_id, '2024-08-02', '23:30:00', '06:45:00', 7.25, 'fair', 'Woke up a few times during the night'),
(@demo_user_id, '2024-08-03', '22:45:00', '07:15:00', 8.50, 'excellent', 'Perfect sleep, very refreshed'),
(@demo_user_id, '2024-08-04', '00:15:00', '07:30:00', 7.25, 'good', 'Stayed up late watching a movie'),
(@demo_user_id, '2024-08-05', '23:15:00', '06:30:00', 7.25, 'poor', 'Had trouble falling asleep'),
(@demo_user_id, '2024-08-06', '22:30:00', '07:00:00', 8.50, 'excellent', 'Early bedtime paid off'),
(@demo_user_id, '2024-08-07', '23:45:00', '07:45:00', 8.00, 'good', 'Weekend sleep-in');

-- Create a view for sleep statistics
CREATE VIEW sleep_statistics AS
SELECT 
    u.id as user_id,
    u.username,
    COUNT(sr.id) as total_records,
    AVG(sr.sleep_duration) as avg_sleep_duration,
    MIN(sr.sleep_duration) as min_sleep_duration,
    MAX(sr.sleep_duration) as max_sleep_duration,
    AVG(CASE 
        WHEN sr.sleep_quality = 'poor' THEN 1
        WHEN sr.sleep_quality = 'fair' THEN 2
        WHEN sr.sleep_quality = 'good' THEN 3
        WHEN sr.sleep_quality = 'excellent' THEN 4
    END) as avg_quality_score
FROM users u
LEFT JOIN sleep_records sr ON u.id = sr.user_id
GROUP BY u.id, u.username;

