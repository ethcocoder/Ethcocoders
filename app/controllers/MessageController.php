<?php
/**
 * ETHCO CODERS - Message Controller
 * Handles chat messages and conversations
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

class MessageController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Send a message
     */
    public function sendMessage($senderId, $receiverId, $message) {
        if (empty($message)) {
            return ['success' => false, 'message' => 'Message cannot be empty'];
        }
        
        // Sanitize message
        $message = sanitizeInput($message);
        
        try {
            $stmt = $this->db->prepare("\n                INSERT INTO messages (sender_id, receiver_id, message) \n                VALUES (?, ?, ?)\n            ");
            $stmt->execute([$senderId, $receiverId, $message]);
            
            $messageId = $this->db->lastInsertId();
            
            // Create notification for receiver
            $this->createMessageNotification($receiverId, $senderId);
            
            logActivity($senderId, 'message_sent', "Message sent to user #$receiverId");
            
            // Fetch full message data for immediate client update
            $fetchStmt = $this->db->prepare("\n                SELECT id, sender_id, receiver_id, message, is_read, created_at\n                FROM messages\n                WHERE id = ?\n            ");
            $fetchStmt->execute([$messageId]);
            $messageData = $fetchStmt->fetch();

            return [
                'success' => true,
                'message' => 'Message sent successfully',
                'message_id' => $messageId,
                'message_data' => $messageData
            ];
        } catch (PDOException $e) {
            error_log("Send Message Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send message'];
        }
    }
    
    /**
     * Get conversation between two users
     */
    public function getConversation($userId1, $userId2, $limit = 50, $offset = 0, $sinceId = null) {
        try {
            // Inline LIMIT/OFFSET to avoid PDO binding issues on some MySQL configs
            $limit = (int)$limit;
            $offset = (int)$offset;
            $extraWhere = '';
            $params = [$userId1, $userId2, $userId2, $userId1];
            if ($sinceId !== null) {
                $sinceId = (int)$sinceId;
                $extraWhere = " AND m.id > $sinceId";
            }
            $sql = "\n                SELECT m.*, \n                       u1.username as sender_name,\n                       u2.username as receiver_name\n                FROM messages m\n                JOIN users u1 ON m.sender_id = u1.id\n                JOIN users u2 ON m.receiver_id = u2.id\n                WHERE ((m.sender_id = ? AND m.receiver_id = ?) \n                   OR (m.sender_id = ? AND m.receiver_id = ?))\n                   $extraWhere\n                ORDER BY m.created_at DESC\n                LIMIT $limit OFFSET $offset\n            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $messages = $stmt->fetchAll();
            
            // Mark messages as read
            $this->markMessagesAsRead($userId1, $userId2);
            
            return array_reverse($messages); // Return in chronological order
        } catch (PDOException $e) {
            error_log("Get Conversation Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all conversations for a user
     */
    public function getConversations($userId) {
        try {
            $stmt = $this->db->prepare("\n                SELECT DISTINCT\n                    CASE \n                        WHEN m.sender_id = ? THEN m.receiver_id\n                        ELSE m.sender_id\n                    END as other_user_id,\n                    u.username as other_username,\n                    u.email as other_email,\n                    (SELECT message FROM messages \n                     WHERE (sender_id = ? AND receiver_id = other_user_id) \n                        OR (sender_id = other_user_id AND receiver_id = ?)\n                     ORDER BY created_at DESC LIMIT 1) as last_message,\n                    (SELECT created_at FROM messages \n                     WHERE (sender_id = ? AND receiver_id = other_user_id) \n                        OR (sender_id = other_user_id AND receiver_id = ?)\n                     ORDER BY created_at DESC LIMIT 1) as last_message_time,\n                    (SELECT COUNT(*) FROM messages \n                     WHERE receiver_id = ? AND sender_id = other_user_id AND is_read = 0) as unread_count\n                FROM messages m\n                JOIN users u ON (CASE \n                    WHEN m.sender_id = ? THEN m.receiver_id\n                    ELSE m.sender_id\n                END = u.id)\n                WHERE m.sender_id = ? OR m.receiver_id = ?\n                ORDER BY last_message_time DESC\n            ");
            $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Conversations Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread message count
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Get Unread Count Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark messages as read
     */
    private function markMessagesAsRead($userId, $otherUserId) {
        try {
            $stmt = $this->db->prepare("\n                UPDATE messages \n                SET is_read = 1 \n                WHERE receiver_id = ? AND sender_id = ? AND is_read = 0\n            ");
            $stmt->execute([$userId, $otherUserId]);
        } catch (PDOException $e) {
            error_log("Mark Messages Read Error: " . $e->getMessage());
        }
    }
    
    /**
     * Create notification for new message
     */
    private function createMessageNotification($receiverId, $senderId) {
        try {
            $sender = getUserById($senderId);
            $stmt = $this->db->prepare("\n                INSERT INTO notifications (user_id, type, title, message, link) \n                VALUES (?, 'message', ?, ?, ?)\n            ");
            $title = "New message from {$sender['username']}";
            $message = "You have a new message";
            $link = "message.php?user_id=$senderId";
            $stmt->execute([$receiverId, $title, $message, $link]);
        } catch (PDOException $e) {
            error_log("Create Notification Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all users for chat (filtered by role)
     */
    public function getChatUsers($currentUserId, $currentRole) {
        try {
            // Admin can chat with everyone
            // Team members can chat with admins and other team members
            // Users can chat with admins and other users
            
            if ($currentRole === ROLE_ADMIN) {
                $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE id != ? ORDER BY username");
                $stmt->execute([$currentUserId]);
            } elseif ($currentRole === ROLE_TEAM_MEMBER) {
                $stmt = $this->db->prepare("\n                    SELECT id, username, email, role \n                    FROM users \n                    WHERE id != ? AND (role = ? OR role = ?)\n                    ORDER BY username\n                ");
                $stmt->execute([$currentUserId, ROLE_ADMIN, ROLE_TEAM_MEMBER]);
            } else {
                $stmt = $this->db->prepare("\n                    SELECT id, username, email, role \n                    FROM users \n                    WHERE id != ? AND (role = ? OR role = ?)\n                    ORDER BY username\n                ");
                $stmt->execute([$currentUserId, ROLE_ADMIN, ROLE_USER]);
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Chat Users Error: " . $e->getMessage());
            return [];
        }
    }
}


