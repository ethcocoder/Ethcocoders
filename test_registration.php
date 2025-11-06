<?php
/**
 * ETHCO CODERS - Registration Test Script
 * Use this to test if registration is working properly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Registration System Test</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files = [
    __DIR__ . '/app/config.php',
    __DIR__ . '/app/functions.php',
    __DIR__ . '/app/controllers/AuthController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} exists<br>";
    } else {
        echo "✗ {$file} NOT FOUND<br>";
    }
}

// Test 2: Check database connection
echo "<h3>2. Database Connection Test</h3>";
try {
    require_once __DIR__ . '/app/config.php';
    $db = getDBConnection();
    echo "✓ Database connection successful<br>";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists<br>";
    } else {
        echo "✗ Users table NOT FOUND. Please run the database schema first.<br>";
    }
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: Check AuthController
echo "<h3>3. AuthController Test</h3>";
try {
    require_once __DIR__ . '/app/functions.php';
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $authController = new AuthController();
    echo "✓ AuthController loaded successfully<br>";
} catch (Exception $e) {
    echo "✗ AuthController failed: " . $e->getMessage() . "<br>";
}

// Test 4: Check PHP functions
echo "<h3>4. PHP Functions Check</h3>";
if (function_exists('password_hash')) {
    echo "✓ password_hash() function available<br>";
} else {
    echo "✗ password_hash() function NOT available<br>";
}

if (class_exists('PDO')) {
    echo "✓ PDO class available<br>";
} else {
    echo "✗ PDO class NOT available<br>";
}

// Test 5: Check permissions
echo "<h3>5. Directory Permissions</h3>";
$dirs = [
    __DIR__ . '/logs',
    __DIR__ . '/uploads'
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✓ {$dir} is writable<br>";
        } else {
            echo "✗ {$dir} is NOT writable<br>";
        }
    } else {
        echo "⚠ {$dir} does not exist<br>";
    }
}

echo "<hr>";
echo "<p><strong>If all tests pass, registration should work. If not, fix the issues above.</strong></p>";

