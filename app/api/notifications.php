<?php
/**
 * ETHCO CODERS - Notifications API
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$userId = getCurrentUserId();
$action = $_GET['action'] ?? 'list';

try {
    $db = getDBConnection();
    
    if ($action === 'list') {
        $limit = $_GET['limit'] ?? 10;
        $stmt = $db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        $notifications = $stmt->fetchAll();
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $unreadCount = $stmt->fetch()['count'];
        
        jsonResponse([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    } elseif ($action === 'mark_read') {
        $notificationId = $_POST['notification_id'] ?? 0;
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notificationId, $userId]);
        
        jsonResponse(['success' => true]);
    }
} catch (PDOException $e) {
    error_log("Notifications API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}

