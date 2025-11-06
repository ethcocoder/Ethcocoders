<?php
/**
 * ETHCO CODERS - Login Page
 * User authentication and login interface
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/functions.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// If user is already logged in, redirect them
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $authController = new AuthController(); // Need an instance to call getRedirectPath
    $redirect_path = $authController->getRedirectPath($_SESSION['role']);
    header("Location: " . $redirect_path);
    exit();
}

$authController = new AuthController();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Debug logging
    error_log("Login attempt - Email: $email");

    try {
        $result = $authController->loginUser($email, $password);

        if ($result['success']) {
            error_log("Login successful - Email: $email, Redirect: " . ($result['redirect'] ?? 'N/A'));
            // Clear any output before redirect
            if (ob_get_level()) {
                ob_clean();
            }
            // Ensure redirect URL is correct
            $redirectUrl = $result['redirect'] ?? 'dashboard/index.php';
            header("Location: " . $redirectUrl);
            exit();
        } else {
            error_log("Login failed - " . ($result['message'] ?? 'Unknown error'));
            $message = $result['message'];
            $message_type = 'danger';
        }
    } catch (Exception $e) {
        error_log("Login Exception: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
        $message = 'Login failed. Please try again.';
        $message_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ethco Coders | Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        /* CSS relevant to body, auth-container, form-control, btn-primary, alerts */
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
            --gradient-secondary: linear-gradient(90deg, var(--ethiopian-green), var(--ethiopian-yellow));
        }
        
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--dark-blue);
            color: var(--off-white);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        body::before { /* Background dots */
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
        h1, h2, h3, h4, h5 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--off-white);
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
            transition: all 0.3s ease;
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
        }
        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 35px rgba(100, 255, 218, 0.6);
            background: linear-gradient(135deg, var(--ethiopian-red), var(--ethiopian-yellow), var(--ethiopian-green));
        }
        .auth-links { margin-top: 1.5rem; }
        .auth-links a {
            color: var(--light-blue);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .auth-links a:hover {
            color: var(--ethiopian-yellow);
            text-decoration: underline;
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
            font-size: 1rem;
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
        @media (max-width: 576px) {
            .auth-container { padding: 2rem; }
            .auth-container h2 { font-size: 2rem; }
            .btn-primary { padding: 0.8rem 2rem; font-size: 1rem; }
            .navbar-brand { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <a class="navbar-brand" href="index.html">Ethco<span>Coders</span></a>
        <h2>Login to Your Account</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php 
        // Display success message from register.php redirect
        if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php 
        // Display logout message
        if (isset($_GET['logged_out'])): ?>
            <div class="alert alert-success">You have been successfully logged out.</div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="loginForm" novalidate>
            <div class="mb-4">
                <label for="loginEmail" class="form-label visually-hidden">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(17, 34, 64, 0.6); border: 1px solid rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" name="email" id="loginEmail" placeholder="Email Address" required autocomplete="email" maxlength="255">
                </div>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
            <div class="mb-4">
                <label for="loginPassword" class="form-label visually-hidden">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(17, 34, 64, 0.6); border: 1px solid rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="password" id="loginPassword" placeholder="Password" required autocomplete="current-password" minlength="8">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" style="background: rgba(17, 34, 64, 0.6); border-color: rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback">Password is required (minimum 8 characters).</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="loginSubmitBtn">
                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                <span class="btn-text">Login</span>
            </button>
        </form>
        <div class="auth-links">
            <a href="register.php" class="d-block mt-3">Don't have an account? Register</a>
            <a href="forgot_password.php" class="d-block mt-2">Forgot Password?</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        // Show loading state
                        const submitBtn = document.getElementById('loginSubmitBtn');
                        const spinner = submitBtn.querySelector('.spinner-border');
                        const btnText = submitBtn.querySelector('.btn-text');
                        submitBtn.disabled = true;
                        spinner.classList.remove('d-none');
                        btnText.textContent = 'Logging in...';
                    }
                    form.classList.add('was-validated');
                }, false);
            }
            
            // Password visibility toggle
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('loginPassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    toggleIcon.classList.toggle('fa-eye');
                    toggleIcon.classList.toggle('fa-eye-slash');
                });
            }
        })();
    </script>
</body>
</html>