<?php
/**
 * ETHCO CODERS - Projects Page
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/controllers/ProjectController.php';

requireLogin();

$pageTitle = 'Projects';
$userId = getCurrentUserId();
$projectController = new ProjectController();
$message = '';
$message_type = '';

// Handle project submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_project'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $file = $_FILES['project_file'] ?? null;
    
    $result = $projectController->submitProject($userId, $title, $description, $file);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Handle project review (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_project']) && isAdmin()) {
    $projectId = $_POST['project_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $adminNotes = $_POST['admin_notes'] ?? '';
    
    $result = $projectController->updateProjectStatus($projectId, $status, $adminNotes, $userId);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Handle project edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_project'])) {
    $projectId = $_POST['project_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $file = $_FILES['project_file'] ?? null;
    
    if ($file && $file['error'] === UPLOAD_ERR_NO_FILE) {
        $file = null; // No file uploaded, keep existing
    }
    
    $result = $projectController->updateProject($projectId, $userId, $title, $description, $file);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Handle project delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $projectId = $_POST['project_id'] ?? 0;
    
    $result = $projectController->deleteProject($projectId, $userId);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        redirect('projects.php', $message, 'success');
    }
}

// Get projects
if (isAdmin()) {
    $projects = $projectController->getAllProjects();
} else {
    $projects = $projectController->getUserProjects($userId);
}

// Get single project if viewing
$viewProject = null;
if (isset($_GET['id'])) {
    $viewProject = $projectController->getProjectById($_GET['id']);
    if ($viewProject && !isAdmin() && $viewProject['user_id'] != $userId) {
        redirect('projects.php', 'You do not have permission to view this project', 'danger');
    }
}

include __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Projects</h1>
            <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0"><?php echo isAdmin() ? 'Manage all project submissions' : 'View and submit your projects'; ?></p>
                <?php if (!isAdmin()): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitProjectModal">
                        <i class="fas fa-plus me-2"></i>Submit New Project
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
        
        <?php if ($viewProject): ?>
            <!-- Project Detail View -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($viewProject['title']); ?></h5>
                </div>
                <div class="card-body">
                    <p><strong>Submitted by:</strong> <?php echo htmlspecialchars($viewProject['username']); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-<?php 
                            echo $viewProject['status'] == 'approved' ? 'success' : 
                                ($viewProject['status'] == 'rejected' ? 'danger' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($viewProject['status']); ?>
                        </span>
                    </p>
                    <p><strong>Submitted:</strong> <?php echo formatDate($viewProject['submitted_at']); ?></p>
                    <hr>
                    <h6>Description</h6>
                    <p><?php echo nl2br(htmlspecialchars($viewProject['description'])); ?></p>
                    
                    <?php if ($viewProject['file_name']): ?>
                        <p><strong>File:</strong> <a href="../uploads/<?php echo htmlspecialchars($viewProject['file_path']); ?>" download><?php echo htmlspecialchars($viewProject['file_name']); ?></a></p>
                    <?php endif; ?>
                    
                    <?php if ($viewProject['admin_notes']): ?>
                        <hr>
                        <h6>Admin Notes</h6>
                        <p><?php echo nl2br(htmlspecialchars($viewProject['admin_notes'])); ?></p>
                    <?php endif; ?>
                    
                    <?php if (isAdmin() && $viewProject['status'] == 'pending'): ?>
                        <hr>
                        <form method="POST">
                            <input type="hidden" name="project_id" value="<?php echo $viewProject['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea class="form-control" name="admin_notes" rows="3"></textarea>
                            </div>
                            <button type="submit" name="review_project" class="btn btn-primary">Submit Review</button>
                        </form>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 mt-3">
                        <a href="projects.php" class="btn btn-outline-secondary">Back to Projects</a>
                        <?php if ($viewProject['user_id'] == $userId || isAdmin()): ?>
                            <button type="button" class="btn btn-outline-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProjectModal"
                                    onclick="loadProjectForEdit(<?php echo htmlspecialchars(json_encode($viewProject)); ?>)">
                                <i class="fas fa-edit me-2"></i>Edit Project
                            </button>
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="confirmDeleteProject(<?php echo $viewProject['id']; ?>, '<?php echo htmlspecialchars(addslashes($viewProject['title'])); ?>')">
                                <i class="fas fa-trash me-2"></i>Delete Project
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Projects List -->
            <?php if (empty($projects)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No projects yet.</p>
                        <?php if (!isAdmin()): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitProjectModal">
                                Submit Your First Project
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <?php if (isAdmin()): ?>
                                    <th>Submitted By</th>
                                <?php endif; ?>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <?php if (isAdmin()): ?>
                                        <td><?php echo htmlspecialchars($project['username'] ?? 'N/A'); ?></td>
                                    <?php endif; ?>
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
                                        <div class="btn-group" role="group">
                                            <a href="projects.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php if ($project['user_id'] == $userId || isAdmin()): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editProjectModal"
                                                        onclick="loadProjectForEdit(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDeleteProject(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars(addslashes($project['title'])); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<!-- Submit Project Modal -->
<?php if (!isAdmin()): ?>
<div class="modal fade" id="submitProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid rgba(100, 255, 218, 0.2);">
            <div class="modal-header">
                <h5 class="modal-title">Submit New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project File</label>
                        <input type="file" class="form-control" name="project_file" required>
                        <small class="text-muted">Max size: <?php echo round(MAX_FILE_SIZE / 1048576); ?>MB. Allowed types: <?php echo implode(', ', ALLOWED_FILE_TYPES); ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_project" class="btn btn-primary">Submit Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid rgba(100, 255, 218, 0.2);">
            <div class="modal-header">
                <h5 class="modal-title">Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editProjectForm">
                <input type="hidden" name="project_id" id="edit_project_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project Title</label>
                        <input type="text" class="form-control" name="title" id="edit_project_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_project_description" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project File (Optional - leave empty to keep current file)</label>
                        <input type="file" class="form-control" name="project_file">
                        <small class="text-muted">Max size: <?php echo round(MAX_FILE_SIZE / 1048576); ?>MB. Allowed types: <?php echo implode(', ', ALLOWED_FILE_TYPES); ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_project" class="btn btn-primary">Update Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadProjectForEdit(project) {
    document.getElementById('edit_project_id').value = project.id;
    document.getElementById('edit_project_title').value = project.title;
    document.getElementById('edit_project_description').value = project.description;
}

function confirmDeleteProject(projectId, projectTitle) {
    if (confirm('Are you sure you want to delete the project "' + projectTitle + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="project_id" value="' + projectId + '">' +
                         '<input type="hidden" name="delete_project" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

