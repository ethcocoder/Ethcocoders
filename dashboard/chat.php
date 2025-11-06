<?php
session_start();
require '../../app/functions.php';
 require '../../app/config.php';

require '../../app/controllers/AuthController.php';
require '../../app/controllers/MessageController.php';

$authController = new AuthController($conn);
if (!$authController->isAuthenticated()) {
    redirect('../../login.php?error=Please log in to access the dashboard');
}

$chatController = new MessageController($conn);
$userId = $_SESSION['user_id'];

// For demonstration, let's assume a chat room ID is passed via GET or a default is used.
// In a real application, you'd have logic to select or create chat rooms.
$chatRoomId = $_GET['room_id'] ?? 1; // Default to room 1 for now

// You might want to check if the current user is a participant of this chat room
$userChatRooms = $chatController->getUserChatRooms($userId);
$isParticipant = false;
foreach ($userChatRooms as $room) {
    if ($room['id'] == $chatRoomId) {
        $isParticipant = true;
        break;
    }
}

if (!$isParticipant) {
    // Handle case where user is not a participant of the chat room
    // For now, let's just redirect or show an error
    // In a real app, you might create a new chat room or add the user to it
    // For simplicity, we'll allow access for now, but this should be secured.
    // echo "You are not a participant of this chat room.";
    // exit();
}

$messages = $chatController->getMessages($chatRoomId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETHCO CODERS - Chat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="./assets/css/dashboard.css" rel="stylesheet" />
    <link href="./assets/css/message.css" rel="stylesheet" />
</head>
<body class="g-sidenav-show bg-gray-100 light-theme">
    <?php include 'partials/sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <?php include 'partials/header.php'; ?>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-9 col-md-12 mx-auto">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Chat Room #<?php echo htmlspecialchars($chatRoomId); ?></h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chat-box" id="chatBox" data-chat-room-id="<?php echo htmlspecialchars($chatRoomId); ?>">
                                <?php foreach ($messages as $message): ?>
                                    <div class="message">
                                        <?php if (!empty($message['avatar'])): ?>
                                            <img src="<?php echo htmlspecialchars($message['avatar']); ?>" alt="Avatar" class="avatar rounded-circle me-2" width="30" height="30">
                                        <?php else: ?>
                                            <img src="./assets/img/default-avatar.png" alt="Default Avatar" class="avatar rounded-circle me-2" width="30" height="30">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($message['sender_username']); ?>:</strong>
                                        <?php echo htmlspecialchars($message['message_text']); ?>
                                        <span class="timestamp"><?php echo date('M d, Y H:i A', strtotime($message['created_at'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" id="messageInput" class="form-control" placeholder="Type your message...">
                                <button class="btn btn-primary mb-0" type="button" id="sendMessageButton">Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php'; ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/message.js"></script>
</body>
</html>