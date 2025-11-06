<?php
/**
 * ETHCO CODERS - Authentication Debug Script
 * Test registration and login functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Authentication System Debug</h2>";
echo "<style>body { font-family: Arial; padding: 20px; background: #0a192f; color: #ccd6f6; } .success { color: #078930; } .error { color: #da121a; } .info { color: #64ffda; } pre { background: #112240; padding: 15px; border-radius: 8px; }</style>";

// Test 1: Check file includes
echo "<h3 class='info'>1. Testing File Includes</h3>";
try {
    require_once __DIR__ . '/app/config.php';
    echo "<p class='success'>✓ config.php loaded</p>";
    
    require_once __DIR__ . '/app/functions.php';
    echo "<p class='success'>✓ functions.php loaded</p>";
    
    require_once __DIR__ . '/app/controllers/AuthController.php';
    echo "<p class='success'>✓ AuthController.php loaded</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Database Connection
echo "<h3 class='info'>2. Testing Database Connection</h3>";
try {
    $db = getDBConnection();
    echo "<p class='success'>✓ Database connected</p>";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Users table exists</p>";
    } else {
        echo "<p class='error'>✗ Users table NOT FOUND</p>";
        echo "<p>Please run: database/ethco_schema.sql</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database Error: " . $e->getMessage() . "</p>";
}

// Test 3: Test Registration Function
echo "<h3 class='info'>3. Testing Registration Function</h3>";
try {
    $authController = new AuthController();
    
    // Test with dummy data
    $testUsername = 'test_' . time();
    $testEmail = 'test_' . time() . '@example.com';
    $testPassword = 'Test123!';
    
    echo "<p>Attempting registration with:</p>";
    echo "<pre>Username: $testUsername\nEmail: $testEmail\nPassword: $testPassword</pre>";
    
    $result = $authController->registerUser($testUsername, $testEmail, $testPassword, $testPassword);
    
    if ($result['success']) {
        echo "<p class='success'>✓ Registration function works!</p>";
        echo "<p>Message: " . htmlspecialchars($result['message']) . "</p>";
        
        // Clean up test user
        try {
            $db = getDBConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE email = ?");
            $stmt->execute([$testEmail]);
            echo "<p class='info'>Test user cleaned up</p>";
        } catch (Exception $e) {
            echo "<p class='error'>Could not clean up test user: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Registration failed</p>";
        echo "<p>Error: " . htmlspecialchars($result['message']) . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Registration Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 4: Session Test
echo "<h3 class='info'>4. Testing Session</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>✓ Session is active</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} else {
    echo "<p class='error'>✗ Session is not active</p>";
}

// Test 5: Check if users exist
echo "<h3 class='info'>5. Existing Users</h3>";
try {
    $db = getDBConnection();
    $stmt = $db->query("SELECT id, username, email, role, status FROM users LIMIT 10");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<p class='success'>✓ Found " . count($users) . " user(s) in database:</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No users found in database</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking users: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3 class='info'>Next Steps</h3>";
echo "<ol>";
echo "<li>If registration test passed, try registering a new user</li>";
echo "<li>Check logs/error.log for any errors</li>";
echo "<li>If login redirect fails, check dashboard/index.php exists</li>";
echo "<li>Disable browser extensions that might interfere</li>";
echo "</ol>";

