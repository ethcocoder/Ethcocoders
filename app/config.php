<?php
/**
 * ETHCO CODERS - Configuration File
 * Secure database credentials and environment variables
 * 
 * IMPORTANT: This file should be excluded from version control
 * Add to .gitignore for production security
 */

// Database Configuration
define('DB_HOST', 'sql211.infinityfree.com');
define('DB_NAME', 'if0_40051151_ethco_db');
define('DB_USER', 'if0_40051151');
define('DB_PASS', 'changedpass1221');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Ethco Coders');
define('APP_URL', 'http://ethcocoders.gt.tc');
define('APP_TIMEZONE', 'Africa/Addis_Ababa');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'ETHCO_SESSION');

// Security Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10 MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'zip', 'rar', 'jpg', 'jpeg', 'png']);

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEAM_MEMBER', 'team_member');
define('ROLE_USER', 'user');

// Database Connection Function
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please contact the administrator.");
        }
    }
    
    return $conn;
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

