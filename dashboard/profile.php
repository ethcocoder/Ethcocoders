<?php
/**
 * ETHCO CODERS - User Profile Page
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';

requireLogin();

$pageTitle = 'My Profile';
$userId = getCurrentUserId();
$db = getDBConnection();
$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    try {
        // Get current user
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        // Update username and email
        if (!empty($username) && $username !== $user['username']) {
            $stmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$username, $userId]);
            $_SESSION['username'] = $username;
        }
        
        if (!empty($email) && $email !== $user['email']) {
            if (!isValidEmail($email)) {
                $message = 'Invalid email address';
                $message_type = 'danger';
            } else {
                $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$email, $userId]);
                $_SESSION['email'] = $email;
            }
        }
        
        // Update password if provided
        if (!empty($newPassword)) {
            if (empty($currentPassword) || !password_verify($currentPassword, $user['password_hash'])) {
                $message = 'Current password is incorrect';
                $message_type = 'danger';
            } else {
                if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                    $message = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
                    $message_type = 'danger';
                } else {
                    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$passwordHash, $userId]);
                    $message = 'Profile updated successfully';
                    $message_type = 'success';
                }
            }
        } else {
            if (empty($message)) {
                $message = 'Profile updated successfully';
                $message_type = 'success';
            }
        }
        
        logActivity($userId, 'profile_updated', 'Profile information updated');
    } catch (PDOException $e) {
        error_log("Profile Update Error: " . $e->getMessage());
        $message = 'Failed to update profile';
        $message_type = 'danger';
    }
}

// Get current user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

include __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>My Profile</h1>
            <p class="text-muted">Manage your account information and preferences.</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" data-validate>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>" disabled>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-3">Change Password</h6>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" placeholder="Leave empty to keep current password">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Leave empty to keep current password" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                <small class="text-muted">Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Member Since:</strong><br><?php echo formatDate($user['created_at'], 'F Y'); ?></p>
                        <p><strong>Last Login:</strong><br><?php echo $user['last_login'] ? getRelativeTime($user['last_login']) : 'Never'; ?></p>
                        <p><strong>Account Status:</strong><br>
                            <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

