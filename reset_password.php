<?php
/**
 * ETHCO CODERS - Reset Password Page
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/functions.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// If user is already logged in, redirect
if (isLoggedIn()) {
    redirect('dashboard/index.php');
}

$token = $_GET['token'] ?? '';
$authController = new AuthController();
$message = '';
$message_type = '';
$validToken = false;

// Validate token
if ($token) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT id FROM password_resets 
            WHERE token = ? AND expires_at > NOW() AND used = 0
        ");
        $stmt->execute([$token]);
        $validToken = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Token Validation Error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $result = $authController->resetPassword($token, $newPassword, $confirmPassword);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
        $validToken = false; // Token is now used
    } else {
        $message = $result['message'];
        $message_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ethco Coders | Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ethiopian-green: #078930;
            --ethiopian-yellow: #fcdd09;
            --ethiopian-red: #da121a;
            --dark-blue: #0a192f;
            --light-blue: #64ffda;
            --off-white: #ccd6f6;
            --card-bg: rgba(10, 25, 47, 0.7);
            --gradient-primary: linear-gradient(135deg, var(--ethiopian-green), var(--ethiopian-yellow), var(--ethiopian-red));
        }
        
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--dark-blue);
            color: var(--off-white);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .auth-container {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 3rem;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(100, 255, 218, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .form-control {
            background: rgba(17, 34, 64, 0.6);
            border: 1px solid rgba(100, 255, 218, 0.3);
            color: var(--off-white);
            padding: 1rem;
            border-radius: 8px;
        }
        
        .form-control:focus {
            background: rgba(17, 34, 64, 0.8);
            border-color: var(--light-blue);
            color: var(--off-white);
            box-shadow: 0 0 0 0.25rem rgba(100, 255, 218, 0.3);
            outline: none;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white !important;
            border: none;
            padding: 1rem 2.5rem;
            font-weight: 600;
            border-radius: 6px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <a class="navbar-brand" href="index.html" style="font-family: 'Orbitron', sans-serif; font-weight: 700; font-size: 1.9rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: block; margin-bottom: 2rem;">Ethco<span>Coders</span></a>
        <h2 style="font-family: 'Orbitron', sans-serif; background: linear-gradient(to right, var(--off-white), var(--light-blue)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Reset Password</h2>
        
        <?php if (!$validToken && empty($token)): ?>
            <div class="alert alert-danger">Invalid or missing reset token.</div>
            <a href="forgot_password.php" style="color: var(--light-blue);">Request new reset link</a>
        <?php elseif (!$validToken && !empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <a href="login.php" style="color: var(--light-blue);">Go to Login</a>
        <?php else: ?>
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-4">
                    <input type="password" class="form-control" name="password" placeholder="New Password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                </div>
                <div class="mb-4">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

