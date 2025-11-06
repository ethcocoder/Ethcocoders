<?php
/**
 * ETHCO CODERS - Authentication Controller
 * Handles user registration, login, logout, and password management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Register a new user
     */
    public function registerUser($username, $email, $password, $confirmPassword) {
        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }
        
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Check if username or email already exists
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Create user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if (!$passwordHash) {
                return ['success' => false, 'message' => 'Password hashing failed. Please try again.'];
            }
            
            // Check which columns exist
            $hasRole = false;
            $hasStatus = false;
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'role'");
                $hasRole = $stmt->rowCount() > 0;
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'status'");
                $hasStatus = $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                // Error checking columns
            }
            
            // Build INSERT query based on available columns
            if ($hasRole && $hasStatus) {
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password_hash, role, status) 
                    VALUES (?, ?, ?, ?, 'active')
                ");
                $stmt->execute([$username, $email, $passwordHash, ROLE_USER]);
            } elseif ($hasRole) {
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password_hash, role) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$username, $email, $passwordHash, ROLE_USER]);
            } else {
                // Basic table structure
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password_hash) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$username, $email, $passwordHash]);
            }
            
            // Log activity (non-blocking)
            try {
                logActivity(null, 'user_registered', "New user: $username");
            } catch (Exception $e) {
                // Ignore logging errors
                error_log("Activity log error: " . $e->getMessage());
            }
            
            error_log("Registration successful for user: $username");
            
            return [
                'success' => true, 
                'message' => 'Registration successful! Please login.'
            ];
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            $errorCode = $e->getCode();
            
            error_log("Registration PDO Error: Code=$errorCode, Message=$errorMsg");
            
            // Provide more specific error messages
            if ($errorCode == 23000 || strpos($errorMsg, 'Duplicate entry') !== false) { 
                return ['success' => false, 'message' => 'Username or email already exists.'];
            } elseif ($errorCode == '42S02' || strpos($errorMsg, "doesn't exist") !== false) { 
                return ['success' => false, 'message' => 'Database table not found. Please run database schema.'];
            } elseif (strpos($errorMsg, 'Unknown column') !== false) {
                return ['success' => false, 'message' => 'Database table structure is incorrect. Please run database/add_missing_columns.sql to fix it.'];
            } else {
                return ['success' => false, 'message' => 'Registration failed. Error: ' . $errorMsg];
            }
        } catch (Exception $e) {
            error_log("Registration Exception: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     */
    public function loginUser($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }
        
        try {
            // Check if status column exists
            $checkStatus = false;
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'status'");
                $checkStatus = $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                // Status column doesn't exist
            }
            
            // Check if role column exists
            $checkRole = false;
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'role'");
                $checkRole = $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                // Role column doesn't exist
            }
            
            // Build query based on available columns
            $selectCols = ['id', 'username', 'email', 'password_hash'];
            if ($checkRole) $selectCols[] = 'role';
            if ($checkStatus) $selectCols[] = 'status';
            
            $stmt = $this->db->prepare("
                SELECT " . implode(', ', $selectCols) . " 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Check status only if column exists
            if ($checkStatus && isset($user['status']) && $user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Your account is not active. Please contact administrator.'];
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Set session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? ROLE_USER; // Default to user if role column doesn't exist
            
            // Update last login (if column exists)
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'last_login'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                }
            } catch (PDOException $e) {
                // last_login column doesn't exist, skip
            }
            
            logActivity($user['id'], 'user_login', "User logged in: {$user['username']}");
            
            // Get redirect path based on role
            $redirect = $this->getRedirectPath($user['role']);
            
            return [
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => $redirect
            ];
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Logout user
     */
    public function logoutUser() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'user_logout', "User logged out");
        }
        
        session_unset();
        session_destroy();
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Get redirect path based on user role
     */
    public function getRedirectPath($role) {
        // Always redirect to dashboard for all roles
        $redirect = 'dashboard/index.php';
        
        // Log redirect for debugging
        error_log("Redirect path for role '$role': $redirect");
        
        return $redirect;
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        if (empty($email) || !isValidEmail($email)) {
            return ['success' => false, 'message' => 'Valid email is required'];
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Don't reveal if email exists for security
                return ['success' => true, 'message' => 'If email exists, password reset link has been sent'];
            }
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete old tokens
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            // Insert new token
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, token, expires_at) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user['id'], $token, $expiresAt]);
            
            // In production, send email here with reset link
            // For now, we'll return the token (remove in production)
            $resetLink = APP_URL . "/reset_password.php?token=" . $token;
            
            logActivity($user['id'], 'password_reset_requested', "Password reset requested");
            
            return [
                'success' => true, 
                'message' => 'Password reset link has been sent to your email',
                'token' => $token // Remove this in production
            ];
        } catch (PDOException $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process password reset request'];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword, $confirmPassword) {
        if (empty($token) || empty($newPassword)) {
            return ['success' => false, 'message' => 'Token and new password are required'];
        }
        
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }
        
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }
        
        try {
            // Find valid token
            $stmt = $this->db->prepare("
                SELECT pr.user_id, u.email 
                FROM password_resets pr
                JOIN users u ON pr.user_id = u.id
                WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
            ");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();
            
            if (!$reset) {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }
            
            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$passwordHash, $reset['user_id']]);
            
            // Mark token as used
            $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            logActivity($reset['user_id'], 'password_reset_completed', "Password reset completed");
            
            return ['success' => true, 'message' => 'Password reset successful. Please login.'];
        } catch (PDOException $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to reset password'];
        }
    }
}

