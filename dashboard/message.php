<?php
/**
 * ETHCO CODERS - Chat Page
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/controllers/MessageController.php';

requireLogin();

$pageTitle = 'Chat';
$userId = getCurrentUserId();
$role = getCurrentUserRole();
$chatController = new MessageController();

// Get chat users
$chatUsers = $chatController->getChatUsers($userId, $role);

// Get selected conversation
$selectedUserId = $_GET['user_id'] ?? null;
$conversation = [];
if ($selectedUserId) {
    $conversation = $chatController->getConversation($userId, $selectedUserId);
}

include __DIR__ . '/partials/header.php';
?>
<?php $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); ?>
<style>
<?php
// Inline message CSS (renamed)
@readfile(__DIR__ . '/assets/css/message.css');
?>
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="main-content chat-container">
        <div class="content-header">
            <h1>Chat</h1>
            <p class="text-muted">Communicate with your team members</p>
        </div>
        
        <div class="row g-0">
            <!-- Users List -->
            <div class="col-md-4 chat-users-panel">
                <div class="card h-100" style="border-radius: 0; border-right: 1px solid rgba(100, 255, 218, 0.2);">
                    <div class="card-header">
                        <h6 class="mb-0">Conversations</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($chatUsers as $user): ?>
                                <a href="message.php?user_id=<?php echo $user['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $selectedUserId == $user['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?php 
                                            $initials = strtoupper(substr($user['username'], 0, 1));
                                            echo $initials;
                                            ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h6>
                                            <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="col-md-8 chat-area-panel">
                <?php if ($selectedUserId): ?>
                    <?php
                    $selectedUser = array_filter($chatUsers, function($u) use ($selectedUserId) {
                        return $u['id'] == $selectedUserId;
                    });
                    $selectedUser = reset($selectedUser);
                    ?>
                    <div class="card h-100" style="border-radius: 0;">
                        <div class="card-header">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <?php echo htmlspecialchars($selectedUser['username']); ?>
                                        <span class="status-indicator"></span>
                                    </h6>
                                    <small><?php echo ucfirst(str_replace('_', ' ', $selectedUser['role'])); ?></small>
                                </div>
                            </div>
                            <div>
                                <button type="button" id="reloadMessagesBtn" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-rotate"></i> Load New
                                </button>
                            </div>
                        </div>
                        <div class="card-body chat-messages" id="chatMessages">
                            <!-- Messages will be loaded dynamically by JavaScript -->
                        </div>
                        <div class="card-footer">
                            <form id="chatForm" method="POST" action="messages_api.php" onsubmit="return false;">
                                <input type="hidden" name="action" value="send">
                                <input type="hidden" name="receiver_id" value="<?php echo $selectedUserId; ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="message" id="messageInput" placeholder="Type a message..." required autocomplete="off">
                                    <button type="button" id="sendMessageBtn" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                        <span class="d-none d-md-inline">Send</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card h-100" style="border-radius: 0;">
                        <div class="chat-empty-state">
                            <i class="fas fa-comments"></i>
                            <p>Select a user to start chatting</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
<?php
// Inline message JS (renamed)
@readfile(__DIR__ . '/assets/js/message.js');
?>
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>


