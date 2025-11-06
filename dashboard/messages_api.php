<?php
/**
 * ETHCO CODERS - Messages API
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/controllers/MessageController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$chatController = new MessageController();
// Check both GET and POST for action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$userId = getCurrentUserId();

try {
    switch ($action) {
        case 'send':
            $receiverId = $_POST['receiver_id'] ?? 0;
            $message = $_POST['message'] ?? '';
            
            if (empty($receiverId) || empty($message)) {
                jsonResponse(['success' => false, 'message' => 'Receiver ID and message are required'], 400);
            }
            
            $result = $chatController->sendMessage($userId, $receiverId, $message);
            jsonResponse($result, $result['success'] ? 200 : 400);
            break;
            
        case 'conversation':
            $otherUserId = $_GET['user_id'] ?? $_POST['user_id'] ?? 0;
            $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : (isset($_POST['since_id']) ? (int)$_POST['since_id'] : null);
            if (empty($otherUserId)) {
                jsonResponse(['success' => false, 'message' => 'User ID is required'], 400);
            }
            
            $messages = $chatController->getConversation($userId, $otherUserId, 100, 0, $sinceId);
            jsonResponse(['success' => true, 'messages' => $messages]);
            break;
            
        case 'conversations':
            $conversations = $chatController->getConversations($userId);
            jsonResponse(['success' => true, 'conversations' => $conversations]);
            break;
            
        case 'users':
            $role = getCurrentUserRole();
            $users = $chatController->getChatUsers($userId, $role);
            jsonResponse(['success' => true, 'users' => $users]);
            break;
            
        case 'unread_count':
            $count = $chatController->getUnreadCount($userId);
            jsonResponse(['success' => true, 'count' => $count]);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Messages API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}


