<?php
/**
 * Installation Script
 * Sleep Tracker CMS
 */

// Prevent running if already installed
if (file_exists('config/installed.lock')) {
    die('Application is already installed. Delete config/installed.lock to reinstall.');
}

$errors = [];
$success_messages = [];
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Step 1: System Requirements Check
if ($step == 1) {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'Session Support' => function_exists('session_start'),
        'JSON Support' => function_exists('json_encode'),
        'Config Directory Writable' => is_writable('config/'),
    ];
    
    $all_requirements_met = true;
    foreach ($requirements as $requirement => $met) {
        if (!$met) {
            $all_requirements_met = false;
            $errors[] = $requirement . ' - FAILED';
        }
    }
}

// Step 2: Database Configuration
if ($step == 2 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'sleep_tracker_cms';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    
    if (empty($db_user)) {
        $errors[] = 'Database username is required';
    }
    
    if (empty($errors)) {
        // Test database connection
        try {
            $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Update database configuration
            $config_content = file_get_contents('config/database.php');
            $config_content = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$db_host');", $config_content);
            $config_content = str_replace("define('DB_NAME', 'sleep_tracker_cms');", "define('DB_NAME', '$db_name');", $config_content);
            $config_content = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$db_user');", $config_content);
            $config_content = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$db_pass');", $config_content);
            
            file_put_contents('config/database.php', $config_content);
            
            $success_messages[] = 'Database connection successful!';
            $step = 3;
            
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }
    }
}

// Step 3: Database Setup
if ($step == 3 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        // Read and execute SQL file
        $sql_content = file_get_contents('sql/setup.sql');
        $statements = explode(';', $sql_content);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $db->exec($statement);
            }
        }
        
        $success_messages[] = 'Database tables created successfully!';
        $step = 4;
        
    } catch (Exception $e) {
        $errors[] = 'Database setup failed: ' . $e->getMessage();
    }
}

// Step 4: Admin User Creation
if ($step == 4 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $errors[] = 'All fields are required';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($errors)) {
        try {
            require_once 'config/database.php';
            require_once 'includes/User.php';
            
            $database = new Database();
            $db = $database->getConnection();
            
            $user = new User($db);
            $user->username = $username;
            $user->email = $email;
            $user->password_hash = $password;
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            
            if ($user->register()) {
                $success_messages[] = 'Admin user created successfully!';
                $step = 5;
            } else {
                $errors[] = 'Failed to create admin user. Username or email might already exist.';
            }
            
        } catch (Exception $e) {
            $errors[] = 'User creation failed: ' . $e->getMessage();
        }
    }
}

// Step 5: Finalization
if ($step == 5 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create installation lock file
    file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
    
    // Redirect to login page
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Sleep Tracker CMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4A90E2, #7B68EE);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .install-header h1 {
            color: #4A90E2;
            margin-bottom: 10px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            color: #666;
        }
        
        .step.active {
            background: #4A90E2;
            color: white;
        }
        
        .step.completed {
            background: #4CAF50;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4A90E2;
        }
        
        .btn {
            background: #4A90E2;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #357ABD;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .requirements-list {
            list-style: none;
            padding: 0;
        }
        
        .requirements-list li {
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .requirements-list li.pass {
            background: #d4edda;
            color: #155724;
        }
        
        .requirements-list li.fail {
            background: #f8d7da;
            color: #721c24;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>Sleep Tracker CMS</h1>
            <p>Installation Wizard</p>
        </div>
        
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? ($step == 1 ? 'active' : 'completed') : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? ($step == 2 ? 'active' : 'completed') : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? ($step == 3 ? 'active' : 'completed') : ''; ?>">3</div>
            <div class="step <?php echo $step >= 4 ? ($step == 4 ? 'active' : 'completed') : ''; ?>">4</div>
            <div class="step <?php echo $step >= 5 ? ($step == 5 ? 'active' : 'completed') : ''; ?>">5</div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_messages)): ?>
            <div class="alert alert-success">
                <?php foreach ($success_messages as $message): ?>
                    <div><?php echo htmlspecialchars($message); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <h2>Step 1: System Requirements</h2>
            <p>Checking if your system meets the requirements...</p>
            
            <ul class="requirements-list">
                <?php foreach ($requirements as $requirement => $met): ?>
                    <li class="<?php echo $met ? 'pass' : 'fail'; ?>">
                        <span><?php echo $requirement; ?></span>
                        <span><?php echo $met ? '✓ PASS' : '✗ FAIL'; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="text-center mt-3">
                <?php if ($all_requirements_met): ?>
                    <a href="install.php?step=2" class="btn">Continue</a>
                <?php else: ?>
                    <p>Please fix the failed requirements before continuing.</p>
                    <a href="install.php?step=1" class="btn btn-secondary">Recheck</a>
                <?php endif; ?>
            </div>
            
        <?php elseif ($step == 2): ?>
            <h2>Step 2: Database Configuration</h2>
            <p>Enter your database connection details:</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'sleep_tracker_cms'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Database Username</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($_POST['db_pass'] ?? ''); ?>">
                </div>
                
                <div class="text-center mt-3">
                    <button type="submit" class="btn">Test Connection</button>
                </div>
            </form>
            
        <?php elseif ($step == 3): ?>
            <h2>Step 3: Database Setup</h2>
            <p>Create the database tables and insert sample data:</p>
            
            <form method="POST">
                <div class="text-center mt-3">
                    <button type="submit" class="btn">Create Tables</button>
                </div>
            </form>
            
        <?php elseif ($step == 4): ?>
            <h2>Step 4: Create Admin User</h2>
            <p>Create your administrator account:</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="text-center mt-3">
                    <button type="submit" class="btn">Create Admin User</button>
                </div>
            </form>
            
        <?php elseif ($step == 5): ?>
            <h2>Step 5: Installation Complete!</h2>
            <p>Congratulations! Sleep Tracker CMS has been successfully installed.</p>
            
            <div class="alert alert-success">
                <strong>Installation Summary:</strong><br>
                ✓ System requirements checked<br>
                ✓ Database configured<br>
                ✓ Tables created<br>
                ✓ Admin user created<br>
                ✓ Installation completed
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Delete or rename the <code>install.php</code> file for security</li>
                <li>Configure your web server (Apache/Nginx)</li>
                <li>Set up SSL certificate for HTTPS</li>
                <li>Review security settings in <code>.htaccess</code></li>
            </ul>
            
            <form method="POST">
                <div class="text-center mt-3">
                    <button type="submit" class="btn">Go to Login Page</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

