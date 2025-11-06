<?php
/**
 * ETHCO CODERS - Tasks Page
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/controllers/TaskController.php';

requireLogin();

if (!isAdmin() && !isTeamMember()) {
    redirect('index.php', 'You do not have permission to access this page', 'danger');
}

$pageTitle = 'Tasks';
$userId = getCurrentUserId();
$taskController = new TaskController();
$message = '';
$message_type = '';

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task']) && isAdmin()) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $assignedTo = $_POST['assigned_to'] ?? 0;
    $priority = $_POST['priority'] ?? 'medium';
    $dueDate = $_POST['due_date'] ?? null;
    
    $result = $taskController->createTask($title, $description, $assignedTo, $userId, $priority, $dueDate);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Handle task status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $taskId = $_POST['task_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    $result = $taskController->updateTaskStatus($taskId, $status, $userId);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Handle task edit (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task']) && isAdmin()) {
    $taskId = $_POST['task_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $assignedTo = $_POST['assigned_to'] ?? 0;
    $priority = $_POST['priority'] ?? 'medium';
    $dueDate = $_POST['due_date'] ?? null;
    $status = $_POST['status'] ?? 'to_do';
    
    $result = $taskController->updateTask($taskId, $title, $description, $assignedTo, $priority, $dueDate, $status, $userId);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Handle task delete (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task']) && isAdmin()) {
    $taskId = $_POST['task_id'] ?? 0;
    
    $result = $taskController->deleteTask($taskId, $userId);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        redirect('tasks.php', $message, 'success');
    }
}

// Get tasks
if (isAdmin()) {
    $tasks = $taskController->getAllTasks();
} else {
    $tasks = $taskController->getUserTasks($userId);
}

// Get team members for assignment
$teamMembers = [];
if (isAdmin()) {
    $teamMembers = $taskController->getTeamMembers();
}

include __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Tasks</h1>
            <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0"><?php echo isAdmin() ? 'Manage all tasks' : 'View and update your assigned tasks'; ?></p>
                <?php if (isAdmin()): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="fas fa-plus me-2"></i>Create Task
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($tasks)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No tasks yet.</p>
                    <?php if (isAdmin()): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                            Create Your First Task
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php
                $statuses = ['to_do' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done', 'blocked' => 'Blocked'];
                foreach ($statuses as $statusKey => $statusLabel):
                    $statusTasks = array_filter($tasks, function($task) use ($statusKey) {
                        return $task['status'] == $statusKey;
                    });
                ?>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><?php echo $statusLabel; ?> (<?php echo count($statusTasks); ?>)</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($statusTasks as $task): ?>
                                    <div class="task-item mb-3 p-3" style="background: rgba(17, 34, 64, 0.6); border-radius: 8px; border-left: 3px solid <?php 
                                        echo $task['priority'] == 'high' ? '#da121a' : ($task['priority'] == 'medium' ? '#fcdd09' : '#078930'); 
                                    ?>;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 flex-grow-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                            <?php if (isAdmin()): ?>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editTaskModal"
                                                            onclick="loadTaskForEdit(<?php echo htmlspecialchars(json_encode($task)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmDeleteTask(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars(addslashes($task['title'])); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="small text-muted mb-2"><?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?><?php echo strlen($task['description']) > 100 ? '...' : ''; ?></p>
                                        <p class="small mb-2">
                                            <strong>Assigned to:</strong> <?php echo htmlspecialchars($task['assigned_to_name']); ?><br>
                                            <?php if ($task['due_date']): ?>
                                                <strong>Due:</strong> <?php echo formatDate($task['due_date'], 'M d, Y'); ?>
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($task['assigned_to'] == $userId || isAdmin()): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <select name="status" class="form-control form-control-sm mb-2" onchange="this.form.submit()">
                                                    <?php foreach ($statuses as $key => $label): ?>
                                                        <option value="<?php echo $key; ?>" <?php echo $task['status'] == $key ? 'selected' : ''; ?>>
                                                            <?php echo $label; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Create Task Modal -->
<?php if (isAdmin()): ?>
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid rgba(100, 255, 218, 0.2);">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select class="form-control" name="assigned_to" required>
                            <?php foreach ($teamMembers as $member): ?>
                                <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['username']); ?> (<?php echo ucfirst($member['role']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-control" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_task" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Task Modal (Admin Only) -->
<?php if (isAdmin()): ?>
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid rgba(100, 255, 218, 0.2);">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTaskForm">
                <input type="hidden" name="task_id" id="edit_task_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="edit_task_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_task_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select class="form-control" name="assigned_to" id="edit_task_assigned_to" required>
                            <?php foreach ($teamMembers as $member): ?>
                                <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['username']); ?> (<?php echo ucfirst($member['role']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-control" name="priority" id="edit_task_priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" id="edit_task_status">
                            <option value="to_do">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" id="edit_task_due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_task" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadTaskForEdit(task) {
    document.getElementById('edit_task_id').value = task.id;
    document.getElementById('edit_task_title').value = task.title;
    document.getElementById('edit_task_description').value = task.description || '';
    document.getElementById('edit_task_assigned_to').value = task.assigned_to;
    document.getElementById('edit_task_priority').value = task.priority;
    document.getElementById('edit_task_status').value = task.status;
    if (task.due_date) {
        const dueDate = new Date(task.due_date);
        document.getElementById('edit_task_due_date').value = dueDate.toISOString().split('T')[0];
    }
}

function confirmDeleteTask(taskId, taskTitle) {
    if (confirm('Are you sure you want to delete the task "' + taskTitle + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="task_id" value="' + taskId + '">' +
                         '<input type="hidden" name="delete_task" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>

