<?php
/**
 * ETHCO CODERS - Helper Functions
 * Reusable functions for common operations
 */

require_once __DIR__ . '/config.php';

/**
 * Get base URL for assets
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    
    // If we're in dashboard, return dashboard path
    if (strpos($path, '/dashboard') !== false || strpos($script, '/dashboard') !== false) {
        return $protocol . '://' . $host . '/dashboard';
    }
    
    return $protocol . '://' . $host;
}

/**
 * Get asset path
 */
function getAssetPath($relativePath) {
    $baseUrl = getBaseUrl();
    if (strpos($baseUrl, '/dashboard') !== false) {
        return $baseUrl . '/' . $relativePath;
    }
    return $relativePath;
}

/**
 * Sanitize input data to prevent XSS attacks
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Check if user is team member
 */
function isTeamMember() {
    return hasRole(ROLE_TEAM_MEMBER) || isAdmin();
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? ROLE_USER;
}

/**
 * Redirect user with optional message
 */
function redirect($url, $message = null, $messageType = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $messageType;
    }
    header("Location: " . $url);
    exit();
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return '';
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Get relative time (e.g., "2 hours ago")
 */
function getRelativeTime($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime, 'M d, Y');
}

/**
 * Log activity to database
 */
function logActivity($userId, $action, $details = null) {
    try {
        $db = getDBConnection();
        
        // Check if activity_logs table exists
        $stmt = $db->query("SHOW TABLES LIKE 'activity_logs'");
        if ($stmt->rowCount() == 0) {
            // Table doesn't exist, skip logging
            return;
        }
        
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, details, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $action, $details]);
    } catch (PDOException $e) {
        // Don't break the application if logging fails
        error_log("Activity Log Error: " . $e->getMessage());
    }
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    $role = getCurrentUserRole();
    
    $permissions = [
        ROLE_ADMIN => ['all'],
        ROLE_TEAM_MEMBER => ['view_projects', 'manage_tasks', 'view_chat'],
        ROLE_USER => ['view_projects', 'submit_projects', 'view_chat']
    ];
    
    if (!isset($permissions[$role])) {
        return false;
    }
    
    return in_array('all', $permissions[$role]) || in_array($permission, $permissions[$role]);
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php', 'Please login to access this page', 'danger');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect('dashboard/index.php', 'You do not have permission to access this page', 'danger');
    }
}

/**
 * Sanitize filename for safe upload
 */
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return $filename;
}

/**
 * Validate file upload
 */
function validateFileUpload($file) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error occurred';
        return $errors;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds maximum allowed size';
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_FILE_TYPES)) {
        $errors[] = 'File type not allowed';
    }
    
    return $errors;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get User Error: " . $e->getMessage());
        return null;
    }
}
