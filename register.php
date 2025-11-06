<?php
/**
 * ETHCO CODERS - Registration Page
 * User registration interface
 */

// Start output buffering to catch any errors
ob_start();

// Error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/app/config.php';
    require_once __DIR__ . '/app/functions.php';
    require_once __DIR__ . '/app/controllers/AuthController.php';
} catch (Exception $e) {
    error_log("Registration Page Error - File includes: " . $e->getMessage());
    die("Error loading registration page. Please contact administrator.");
}

$authController = null;
$message = '';
$message_type = '';

try {
    $authController = new AuthController();
} catch (Exception $e) {
    error_log("Registration Page Error - AuthController: " . $e->getMessage());
    $message = 'System error. Please try again later or contact administrator.';
    $message_type = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $authController) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Debug logging
    error_log("Registration attempt - Username: $username, Email: $email");

    try {
        $result = $authController->registerUser($username, $email, $password, $confirm_password);

        if ($result['success']) {
            error_log("Registration successful - Username: $username");
            // Clear any output before redirect
            if (ob_get_level()) {
                ob_clean();
            }
            // Redirect to login page after successful registration
            header("Location: login.php?success=" . urlencode($result['message']));
            exit();
        } else {
            error_log("Registration failed - " . ($result['message'] ?? 'Unknown error'));
            $message = $result['message'];
            $message_type = 'danger';
        }
    } catch (Exception $e) {
        error_log("Registration Exception: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
        $message = 'Registration failed. Please try again. Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Clear any unexpected output
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ethco Coders | Register</title>
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
        .password-strength {
            height: 4px;
            margin-top: 0.5rem;
            border-radius: 2px;
            background: rgba(17, 34, 64, 0.6);
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0%;
        }
        .password-strength.weak .password-strength-bar {
            width: 33%;
            background: var(--ethiopian-red);
        }
        .password-strength.medium .password-strength-bar {
            width: 66%;
            background: var(--ethiopian-yellow);
        }
        .password-strength.strong .password-strength-bar {
            width: 100%;
            background: var(--ethiopian-green);
        }
        .password-requirements {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            color: rgba(204, 214, 246, 0.7);
        }
        .password-requirements li {
            list-style: none;
            padding: 0.25rem 0;
        }
        .password-requirements li.valid {
            color: var(--ethiopian-green);
        }
        .password-requirements li.valid::before {
            content: '✓ ';
            color: var(--ethiopian-green);
        }
        .password-requirements li.invalid::before {
            content: '✗ ';
            color: var(--ethiopian-red);
        }
        .input-group-text {
            border-right: none;
        }
        .form-control + .input-group-text {
            border-left: none;
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
        <h2>Create Your Account</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php 
        // Display success message from login.php redirect
        if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm" novalidate>
            <div class="mb-4">
                <label for="registerUsername" class="form-label visually-hidden">Username</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(17, 34, 64, 0.6); border: 1px solid rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control" name="username" id="registerUsername" placeholder="Username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" autocomplete="username">
                </div>
                <div class="invalid-feedback">Username must be 3-50 characters (letters, numbers, underscore only).</div>
                <small class="text-muted">3-50 characters, letters, numbers, and underscore only</small>
            </div>
            
            <div class="mb-4">
                <label for="registerEmail" class="form-label visually-hidden">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(17, 34, 64, 0.6); border: 1px solid rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" name="email" id="registerEmail" placeholder="Email Address" required maxlength="255" autocomplete="email">
                </div>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
            
            <div class="mb-4">
                <label for="registerPassword" class="form-label visually-hidden">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(17, 34, 64, 0.6); border: 1px solid rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="password" id="registerPassword" placeholder="Password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="btn btn-outline-secondary" id="toggleRegisterPassword" style="background: rgba(17, 34, 64, 0.6); border-color: rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-eye" id="toggleRegisterPasswordIcon"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="password-strength-bar"></div>
                </div>
                <div class="password-requirements" id="passwordRequirements">
                    <ul class="mb-0">
                        <li id="req-length" class="invalid">At least 8 characters</li>
                        <li id="req-uppercase" class="invalid">One uppercase letter</li>
                        <li id="req-lowercase" class="invalid">One lowercase letter</li>
                        <li id="req-number" class="invalid">One number</li>
                    </ul>
                </div>
                <div class="invalid-feedback">Password must meet all requirements.</div>
            </div>
            
            <div class="mb-4">
                <label for="registerConfirmPassword" class="form-label visually-hidden">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(17, 34, 64, 0.6); border: 1px solid rgba(100, 255, 218, 0.3); color: var(--off-white);">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="confirm_password" id="registerConfirmPassword" placeholder="Confirm Password" required autocomplete="new-password">
                </div>
                <div class="invalid-feedback" id="confirmPasswordFeedback">Passwords do not match.</div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100" id="registerSubmitBtn">
                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                <span class="btn-text">Register</span>
            </button>
        </form>
        <div class="auth-links">
            <a href="login.php" class="d-block mt-3">Already have an account? Login</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and password strength checker
        (function() {
            'use strict';
            
            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('registerPassword');
            const confirmPasswordInput = document.getElementById('registerConfirmPassword');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordRequirements = document.getElementById('passwordRequirements');
            
            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                
                // Length check
                if (password.length >= 8) {
                    strength++;
                    document.getElementById('req-length').classList.add('valid');
                    document.getElementById('req-length').classList.remove('invalid');
                } else {
                    document.getElementById('req-length').classList.remove('valid');
                    document.getElementById('req-length').classList.add('invalid');
                }
                
                // Uppercase check
                if (/[A-Z]/.test(password)) {
                    strength++;
                    document.getElementById('req-uppercase').classList.add('valid');
                    document.getElementById('req-uppercase').classList.remove('invalid');
                } else {
                    document.getElementById('req-uppercase').classList.remove('valid');
                    document.getElementById('req-uppercase').classList.add('invalid');
                }
                
                // Lowercase check
                if (/[a-z]/.test(password)) {
                    strength++;
                    document.getElementById('req-lowercase').classList.add('valid');
                    document.getElementById('req-lowercase').classList.remove('invalid');
                } else {
                    document.getElementById('req-lowercase').classList.remove('valid');
                    document.getElementById('req-lowercase').classList.add('invalid');
                }
                
                // Number check
                if (/[0-9]/.test(password)) {
                    strength++;
                    document.getElementById('req-number').classList.add('valid');
                    document.getElementById('req-number').classList.remove('invalid');
                } else {
                    document.getElementById('req-number').classList.remove('valid');
                    document.getElementById('req-number').classList.add('invalid');
                }
                
                // Update strength bar
                passwordStrength.className = 'password-strength';
                if (strength <= 2) {
                    passwordStrength.classList.add('weak');
                } else if (strength === 3) {
                    passwordStrength.classList.add('medium');
                } else {
                    passwordStrength.classList.add('strong');
                }
            }
            
            // Password match checker
            function checkPasswordMatch() {
                const feedback = document.getElementById('confirmPasswordFeedback');
                if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                    feedback.textContent = 'Passwords do not match.';
                } else {
                    confirmPasswordInput.setCustomValidity('');
                    feedback.textContent = '';
                }
            }
            
            // Event listeners
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                    if (confirmPasswordInput.value) {
                        checkPasswordMatch();
                    }
                });
            }
            
            if (confirmPasswordInput && passwordInput) {
                confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            }
            
            // Form submission - Use normal form submission to avoid extension conflicts
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Always check password match first
                    checkPasswordMatch();
                    
                    // Validate form
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        form.classList.add('was-validated');
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = document.getElementById('registerSubmitBtn');
                    const spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;
                    const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
                    
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        if (spinner) spinner.classList.remove('d-none');
                        if (btnText) btnText.textContent = 'Registering...';
                    }
                    
                    // Mark form as validated
                    form.classList.add('was-validated');
                    
                    // Allow form to submit normally - don't prevent default
                    // The form will POST to register.php normally
                    return true;
                }, false);
            }
            
            // Password visibility toggle
            const togglePassword = document.getElementById('toggleRegisterPassword');
            const toggleIcon = document.getElementById('toggleRegisterPasswordIcon');
            
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