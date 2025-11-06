<?php
/**
 * ETHCO CODERS - Contact Messages Page (Admin only)
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/controllers/ContactController.php';

requireLogin();
requireRole(ROLE_ADMIN);

$pageTitle = 'Contact Messages';
$contactController = new ContactController();
$message = '';
$message_type = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $contactId = $_POST['contact_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $adminNotes = $_POST['admin_notes'] ?? '';
    
    $result = $contactController->updateContactStatus($contactId, $status, $adminNotes);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Get contacts
$status = $_GET['status'] ?? null;
$contacts = $contactController->getAllContacts($status);
$stats = $contactController->getContactStats();

// Get single contact if viewing
$viewContact = null;
if (isset($_GET['id'])) {
    $viewContact = $contactController->getContactById($_GET['id']);
}

include __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Contact Messages</h1>
            <p class="text-muted">Manage contact form submissions from the landing page</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Messages</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['new']; ?></h3>
                        <p>New Messages</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['read']; ?></h3>
                        <p>Read Messages</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="contacts.php" class="btn btn-outline-primary <?php echo !$status ? 'active' : ''; ?>">All</a>
                    <a href="contacts.php?status=new" class="btn btn-outline-primary <?php echo $status == 'new' ? 'active' : ''; ?>">New</a>
                    <a href="contacts.php?status=read" class="btn btn-outline-primary <?php echo $status == 'read' ? 'active' : ''; ?>">Read</a>
                    <a href="contacts.php?status=replied" class="btn btn-outline-primary <?php echo $status == 'replied' ? 'active' : ''; ?>">Replied</a>
                </div>
            </div>
        </div>
        
        <?php if ($viewContact): ?>
            <!-- Contact Detail View -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Message from <?php echo htmlspecialchars($viewContact['name']); ?></h5>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($viewContact['email']); ?></p>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($viewContact['subject']); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-<?php 
                            echo $viewContact['status'] == 'new' ? 'danger' : 
                                ($viewContact['status'] == 'replied' ? 'success' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($viewContact['status']); ?>
                        </span>
                    </p>
                    <p><strong>Received:</strong> <?php echo formatDate($viewContact['created_at']); ?></p>
                    <hr>
                    <h6>Message</h6>
                    <p><?php echo nl2br(htmlspecialchars($viewContact['message'])); ?></p>
                    
                    <?php if ($viewContact['admin_notes']): ?>
                        <hr>
                        <h6>Admin Notes</h6>
                        <p><?php echo nl2br(htmlspecialchars($viewContact['admin_notes'])); ?></p>
                    <?php endif; ?>
                    
                    <hr>
                    <form method="POST">
                        <input type="hidden" name="contact_id" value="<?php echo $viewContact['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="new" <?php echo $viewContact['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo $viewContact['status'] == 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $viewContact['status'] == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="archived" <?php echo $viewContact['status'] == 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea class="form-control" name="admin_notes" rows="3"><?php echo htmlspecialchars($viewContact['admin_notes']); ?></textarea>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                    
                    <a href="contacts.php" class="btn btn-outline-secondary mt-3">Back to Messages</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Contacts List -->
            <?php if (empty($contacts)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No contact messages yet.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Received</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['subject'] ?: 'No subject'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $contact['status'] == 'new' ? 'danger' : 
                                                ($contact['status'] == 'replied' ? 'success' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($contact['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo getRelativeTime($contact['created_at']); ?></td>
                                    <td>
                                        <a href="contacts.php?id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
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

<?php include __DIR__ . '/partials/footer.php'; ?>

