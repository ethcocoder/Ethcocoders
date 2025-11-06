<?php
/**
 * ETHCO CODERS - Contacts API (Admin only)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../controllers/ContactController.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
}

$contactController = new ContactController();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'unread_count') {
        $stats = $contactController->getContactStats();
        jsonResponse(['success' => true, 'count' => $stats['new']]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Contacts API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}

