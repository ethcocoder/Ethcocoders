<?php
/**
 * ETHCO CODERS - Message Debug Utility
 * Quick checks for DB connectivity and chat endpoints
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/controllers/MessageController.php';

requireLogin();

$dbOk = false;
$dbError = null;
$now = null;

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT NOW() as now');
    $row = $stmt->fetch();
    $now = $row['now'] ?? null;
    $dbOk = true;
} catch (Throwable $e) {
    $dbOk = false;
    $dbError = $e->getMessage();
}

$controller = new MessageController();

$currentUserId = getCurrentUserId();
$otherUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$users = [];
$conversations = [];
$conversation = [];
$apiErrors = [];

try {
    $users = $controller->getChatUsers($currentUserId, getCurrentUserRole());
} catch (Throwable $e) {
    $apiErrors[] = 'getChatUsers error: ' . $e->getMessage();
}

try {
    $conversations = $controller->getConversations($currentUserId);
} catch (Throwable $e) {
    $apiErrors[] = 'getConversations error: ' . $e->getMessage();
}

if ($otherUserId) {
    try {
        $conversation = $controller->getConversation($currentUserId, $otherUserId, 100, 0);
    } catch (Throwable $e) {
        $apiErrors[] = 'getConversation error: ' . $e->getMessage();
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0a192f; color: #e6f1ff; }
        .card { background: rgba(10,25,47,0.7); border: 1px solid rgba(100,255,218,0.25); }
        pre { white-space: pre-wrap; word-break: break-word; color: #a7fff3; }
        a { color: #64ffda; }
        a:hover { color: #a7fff3; }
        .ok { color: #22c55e; }
        .bad { color: #ef4444; }
    </style>
<?php /* No header partials to keep output minimal */ ?>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Message Debug</h2>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card p-3">
                <h5>Environment</h5>
                <ul class="mb-0">
                    <li>Current user ID: <strong><?php echo (int)$currentUserId; ?></strong></li>
                    <li>Other user ID (GET user_id): <strong><?php echo (int)$otherUserId; ?></strong></li>
                    <li>DB connection: <?php echo $dbOk ? '<span class="ok">OK</span>' : '<span class="bad">FAILED</span>'; ?></li>
                    <?php if ($now): ?><li>DB NOW(): <code><?php echo htmlspecialchars($now); ?></code></li><?php endif; ?>
                    <?php if ($dbError): ?><li class="bad">DB Error: <?php echo htmlspecialchars($dbError); ?></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card p-3">
                <h5>Quick Links</h5>
                <ul class="mb-0">
                    <li><a target="_blank" href="messages_api.php?action=conversations">GET conversations (JSON)</a></li>
                    <li><a target="_blank" href="messages_api.php?action=users">GET users (JSON)</a></li>
                    <li><a target="_blank" href="messages_api.php?action=conversation&user_id=<?php echo (int)($otherUserId ?: $currentUserId); ?>">GET conversation with user_id (JSON)</a></li>
                </ul>
                <form class="mt-3" method="get">
                    <div class="input-group">
                        <input type="number" class="form-control" name="user_id" placeholder="Test with other user_id" value="<?php echo (int)$otherUserId; ?>">
                        <button class="btn btn-primary" type="submit">Load</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3">
                <h5>Users available for chat</h5>
                <pre><?php echo htmlspecialchars(var_export($users, true)); ?></pre>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3">
                <h5>Your conversations (summary)</h5>
                <pre><?php echo htmlspecialchars(var_export($conversations, true)); ?></pre>
            </div>
        </div>

        <?php if ($otherUserId): ?>
        <div class="col-12">
            <div class="card p-3">
                <h5>Conversation with user #<?php echo (int)$otherUserId; ?></h5>
                <pre><?php echo htmlspecialchars(var_export($conversation, true)); ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($apiErrors)): ?>
        <div class="col-12">
            <div class="card p-3">
                <h5>Errors</h5>
                <pre class="bad"><?php echo htmlspecialchars(var_export($apiErrors, true)); ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>


