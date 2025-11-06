<?php
/**
 * ETHCO CODERS - Contact Form Handler
 * Handles AJAX contact form submissions
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/functions.php';
require_once __DIR__ . '/app/controllers/ContactController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

// Get POST data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Process contact submission
$contactController = new ContactController();
$result = $contactController->submitContact($name, $email, $subject, $message);

jsonResponse($result, $result['success'] ? 200 : 400);

