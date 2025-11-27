<?php
/**
 * User Class
 * Handles user authentication and management
 */

require_once __DIR__ . '/../config/config.php';

class User {
    private $conn;
    private $table_name = "users";
    
    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $first_name;
    public $last_name;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Register a new user
     * @return bool
     */
    public function register() {
        // Check if username or email already exists
        if ($this->userExists()) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password_hash, first_name, last_name) 
                  VALUES (:username, :email, :password_hash, :first_name, :last_name)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash the password
        $hashed_password = password_hash($this->password_hash, HASH_ALGO);
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $hashed_password);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Login user
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username, $password) {
        $query = "SELECT id, username, email, password_hash, first_name, last_name 
                  FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password_hash'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                
                // Set session variables
                $_SESSION['user_id'] = $this->id;
                $_SESSION['username'] = $this->username;
                $_SESSION['first_name'] = $this->first_name;
                $_SESSION['last_name'] = $this->last_name;
                $_SESSION['login_time'] = time();
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Check if user exists by username or email
     * @return bool
     */
    private function userExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get user by ID
     * @param int $id
     * @return bool
     */
    public function getUserById($id) {
        $query = "SELECT id, username, email, first_name, last_name, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     * @return bool
     */
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = :first_name, last_name = :last_name, email = :email 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Change password
     * @param string $current_password
     * @param string $new_password
     * @return bool
     */
    public function changePassword($current_password, $new_password) {
        // First verify current password
        $query = "SELECT password_hash FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($current_password, $row['password_hash'])) {
                // Update password
                $update_query = "UPDATE " . $this->table_name . " 
                                SET password_hash = :password_hash 
                                WHERE id = :id";
                
                $update_stmt = $this->conn->prepare($update_query);
                $hashed_password = password_hash($new_password, HASH_ALGO);
                
                $update_stmt->bindParam(':password_hash', $hashed_password);
                $update_stmt->bindParam(':id', $this->id);
                
                return $update_stmt->execute();
            }
        }
        
        return false;
    }
    
    /**
     * Validate user input
     * @param array $data
     * @return array
     */
    public static function validateRegistration($data) {
        $errors = [];
        
        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!validate_email($data['email'])) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters long';
        }
        
        // Confirm password validation
        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        // First name validation
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        // Last name validation
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        return $errors;
    }
}
?>

