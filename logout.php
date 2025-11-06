<?php
/**
 * ETHCO CODERS - Logout Handler
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/functions.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

$authController = new AuthController();
$authController->logoutUser();

redirect('login.php', 'You have been successfully logged out', 'success');

