<?php
/**
 * ETHCO CODERS - Forgot Password Page
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/functions.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// If user is already logged in, redirect
if (isLoggedIn()) {
    redirect('dashboard/index.php');
}

$authController = new AuthController();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    $result = $authController->requestPasswordReset($email);
    
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ethco Coders | Forgot Password</title>
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
            --dark-gray: #112240;
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
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 20%, rgba(100, 255, 218, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(7, 137, 48, 0.05) 0%, transparent 20%);
            z-index: -1;
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
        
        .auth-container h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(to right, var(--off-white), var(--light-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-control {
            background: rgba(17, 34, 64, 0.6);
            border: 1px solid rgba(100, 255, 218, 0.3);
            color: var(--off-white);
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.05rem;
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
            transition: all 0.4s ease;
            box-shadow: 0 8px 25px rgba(100, 255, 218, 0.4);
            font-size: 1.1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 35px rgba(100, 255, 218, 0.6);
        }
        
        .navbar-brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 1.9rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
            margin-bottom: 2rem;
        }
        
        .alert {
            text-align: left;
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
        }
        
        .alert-danger {
            background-color: rgba(218, 18, 26, 0.2);
            color: var(--ethiopian-red);
            border: 1px solid var(--ethiopian-red);
        }
        
        .alert-success {
            background-color: rgba(7, 137, 48, 0.2);
            color: var(--ethiopian-green);
            border: 1px solid var(--ethiopian-green);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <a class="navbar-brand" href="index.html">Ethco<span>Coders</span></a>
        <h2>Reset Password</h2>
        
        <p class="mb-4">Enter your email address and we'll send you a link to reset your password.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>
        
        <div class="mt-4">
            <a href="login.php" style="color: var(--light-blue); text-decoration: none;">Back to Login</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

