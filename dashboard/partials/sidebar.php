<?php
/**
 * ETHCO CODERS - Dashboard Sidebar Partial
 */

$currentRole = getCurrentUserRole();
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h5>Navigation</h5>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <?php if (isAdmin() || isTeamMember()): ?>
        <li>
            <a href="tasks.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tasks.php') ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i>
                <span>Tasks</span>
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="projects.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'projects.php') ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i>
                <span>Projects</span>
            </a>
        </li>
        
        <li>
            <a href="message.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'message.php') ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                <span>Chat</span>
                <span class="badge bg-danger ms-2" id="chatBadge" style="display: none;">0</span>
            </a>
        </li>
        
        <?php if (isAdmin()): ?>
        <li>
            <a href="contacts.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contacts.php') ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Contact Messages</span>
                <span class="badge bg-danger ms-2" id="contactsBadge" style="display: none;">0</span>
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="profile.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="../index.html" class="text-decoration-none">
            <i class="fas fa-globe me-2"></i>
            <span>Visit Website</span>
        </a>
    </div>
</div>

