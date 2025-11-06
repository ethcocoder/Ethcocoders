<?php
/**
 * ETHCO CODERS - Dashboard Home Page
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';

requireLogin();

$pageTitle = 'Dashboard';

// Get statistics
$db = getDBConnection();
$userId = getCurrentUserId();
$role = getCurrentUserRole();

// Get user stats
$stats = [];

// Projects count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
$stmt->execute([$userId]);
$stats['my_projects'] = $stmt->fetch()['count'];

// Tasks count
if (isAdmin() || isTeamMember()) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?");
    $stmt->execute([$userId]);
    $stats['my_tasks'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'to_do'");
    $stats['pending_tasks'] = $stmt->fetch()['count'];
}

// Unread messages
$stmt = $db->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$stats['unread_messages'] = $stmt->fetch()['count'];

// Unread notifications
$stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$stats['unread_notifications'] = $stmt->fetch()['count'];

// Recent activity
$stmt = $db->prepare("
    SELECT * FROM projects 
    WHERE user_id = ? 
    ORDER BY submitted_at DESC 
    LIMIT 5
");
$stmt->execute([$userId]);
$recentProjects = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p class="text-muted">Here's what's happening with your account today.</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['my_projects']; ?></h3>
                        <p>My Projects</p>
                    </div>
                </div>
            </div>
            
            <?php if (isAdmin() || isTeamMember()): ?>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['my_tasks'] ?? 0; ?></h3>
                        <p>My Tasks</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_tasks'] ?? 0; ?></h3>
                        <p>Pending Tasks</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['unread_messages']; ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Projects -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Recent Projects</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentProjects)): ?>
                    <p class="text-muted text-center py-4">No projects yet. <a href="projects.php">Submit your first project</a></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentProjects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $project['status'] == 'approved' ? 'success' : 
                                                ($project['status'] == 'rejected' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo getRelativeTime($project['submitted_at']); ?></td>
                                    <td>
                                        <a href="projects.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="projects.php" class="btn btn-primary">View All Projects</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="projects.php?action=submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Submit New Project
                            </a>
                            <a href="message.php" class="btn btn-outline-primary">
                                <i class="fas fa-comments me-2"></i>Open Chat
                            </a>
                            <?php if (isAdmin() || isTeamMember()): ?>
                            <a href="tasks.php?action=create" class="btn btn-outline-success">
                                <i class="fas fa-tasks me-2"></i>Create Task
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Role:</strong> <?php echo ucfirst(str_replace('_', ' ', $role)); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                        <p><strong>Last Login:</strong> <?php 
                            $stmt = $db->prepare("SELECT last_login FROM users WHERE id = ?");
                            $stmt->execute([$userId]);
                            $lastLogin = $stmt->fetch()['last_login'];
                            echo $lastLogin ? getRelativeTime($lastLogin) : 'Never';
                        ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

