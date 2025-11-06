<?php
/**
 * ETHCO CODERS - Project Controller
 * Handles project submissions and management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

class ProjectController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Submit a new project
     */
    public function submitProject($userId, $title, $description, $file) {
        // Validation
        if (empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Title and description are required'];
        }
        
        // Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }
        
        $fileErrors = validateFileUpload($file);
        if (!empty($fileErrors)) {
            return ['success' => false, 'message' => implode(', ', $fileErrors)];
        }
        
        // Sanitize inputs
        $title = sanitizeInput($title);
        $description = sanitizeInput($description);
        
        // Handle file upload
        $uploadResult = $this->handleFileUpload($file, $userId);
        if (!$uploadResult['success']) {
            return $uploadResult;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO projects (user_id, title, description, file_path, file_name, file_size, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $userId,
                $title,
                $description,
                $uploadResult['file_path'],
                $uploadResult['file_name'],
                $uploadResult['file_size']
            ]);
            
            $projectId = $this->db->lastInsertId();
            
            // Notify admins
            $this->notifyAdmins($projectId, $title);
            
            logActivity($userId, 'project_submitted', "Project submitted: $title");
            
            return [
                'success' => true,
                'message' => 'Project submitted successfully and is pending review',
                'project_id' => $projectId
            ];
        } catch (PDOException $e) {
            error_log("Submit Project Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit project'];
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file, $userId) {
        $uploadDir = UPLOAD_DIR . 'projects/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = sanitizeFilename($file['name']);
        $uniqueFilename = $userId . '_' . time() . '_' . $filename;
        $filePath = $uploadDir . $uniqueFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }
        
        return [
            'success' => true,
            'file_path' => 'projects/' . $uniqueFilename,
            'file_name' => $filename,
            'file_size' => $file['size']
        ];
    }
    
    /**
     * Get user's projects
     */
    public function getUserProjects($userId, $status = null) {
        try {
            $sql = "SELECT * FROM projects WHERE user_id = ?";
            $params = [$userId];
            
            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY submitted_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get User Projects Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all projects (admin)
     */
    public function getAllProjects($status = null, $limit = 50) {
        try {
            $sql = "SELECT p.*, u.username, u.email 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.id";
            $params = [];
            
            if ($status) {
                $sql .= " WHERE p.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY p.submitted_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get All Projects Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get project by ID
     */
    public function getProjectById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.username, u.email 
                FROM projects p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Project Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update project status (admin)
     */
    public function updateProjectStatus($projectId, $status, $adminNotes, $reviewedBy) {
        try {
            $stmt = $this->db->prepare("
                UPDATE projects 
                SET status = ?, admin_notes = ?, reviewed_at = NOW(), reviewed_by = ? 
                WHERE id = ?
            ");
            $stmt->execute([$status, $adminNotes, $reviewedBy, $projectId]);
            
            // Notify project owner
            $project = $this->getProjectById($projectId);
            $this->notifyProjectOwner($project['user_id'], $projectId, $status);
            
            logActivity($reviewedBy, 'project_reviewed', "Project #$projectId status changed to $status");
            
            return ['success' => true, 'message' => 'Project status updated'];
        } catch (PDOException $e) {
            error_log("Update Project Status Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update project status'];
        }
    }
    
    /**
     * Notify admins about new project
     */
    private function notifyAdmins($projectId, $title) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE role = ?");
            $stmt->execute([ROLE_ADMIN]);
            $admins = $stmt->fetchAll();
            
            foreach ($admins as $admin) {
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link) 
                    VALUES (?, 'project', ?, ?, ?)
                ");
                $message = "New project submission: $title";
                $link = "dashboard/projects.php?id=$projectId";
                $stmt->execute([$admin['id'], "New Project Submission", $message, $link]);
            }
        } catch (PDOException $e) {
            error_log("Notify Admins Error: " . $e->getMessage());
        }
    }
    
    /**
     * Notify project owner about status change
     */
    private function notifyProjectOwner($userId, $projectId, $status) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, 'project', ?, ?, ?)
            ");
            $title = "Project Status Updated";
            $message = "Your project has been " . ucfirst($status);
            $link = "dashboard/projects.php?id=$projectId";
            $stmt->execute([$userId, $title, $message, $link]);
        } catch (PDOException $e) {
            error_log("Notify Project Owner Error: " . $e->getMessage());
        }
    }
    
    /**
     * Update project (edit)
     */
    public function updateProject($projectId, $userId, $title, $description, $file = null) {
        // Verify ownership
        $project = $this->getProjectById($projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found'];
        }
        
        if ($project['user_id'] != $userId && !isAdmin()) {
            return ['success' => false, 'message' => 'You do not have permission to edit this project'];
        }
        
        // Validate
        if (empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Title and description are required'];
        }
        
        $title = sanitizeInput($title);
        $description = sanitizeInput($description);
        
        try {
            $updateData = [];
            $updateParams = [];
            
            // Handle file update if provided
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $fileErrors = validateFileUpload($file);
                if (!empty($fileErrors)) {
                    return ['success' => false, 'message' => implode(', ', $fileErrors)];
                }
                
                $uploadResult = $this->handleFileUpload($file, $userId);
                if (!$uploadResult['success']) {
                    return $uploadResult;
                }
                
                // Delete old file
                if ($project['file_path']) {
                    $oldFilePath = UPLOAD_DIR . $project['file_path'];
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                
                $updateData[] = "file_path = ?";
                $updateData[] = "file_name = ?";
                $updateData[] = "file_size = ?";
                $updateParams[] = $uploadResult['file_path'];
                $updateParams[] = $uploadResult['file_name'];
                $updateParams[] = $uploadResult['file_size'];
            }
            
            $updateData[] = "title = ?";
            $updateData[] = "description = ?";
            $updateParams[] = $title;
            $updateParams[] = $description;
            $updateParams[] = $projectId;
            
            $sql = "UPDATE projects SET " . implode(", ", $updateData) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($updateParams);
            
            logActivity($userId, 'project_updated', "Project #$projectId updated");
            
            return ['success' => true, 'message' => 'Project updated successfully'];
        } catch (PDOException $e) {
            error_log("Update Project Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update project'];
        }
    }
    
    /**
     * Delete project
     */
    public function deleteProject($projectId, $userId) {
        // Verify ownership
        $project = $this->getProjectById($projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found'];
        }
        
        if ($project['user_id'] != $userId && !isAdmin()) {
            return ['success' => false, 'message' => 'You do not have permission to delete this project'];
        }
        
        try {
            // Delete file if exists
            if ($project['file_path']) {
                $filePath = UPLOAD_DIR . $project['file_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            // Delete project
            $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            
            logActivity($userId, 'project_deleted', "Project #$projectId deleted");
            
            return ['success' => true, 'message' => 'Project deleted successfully'];
        } catch (PDOException $e) {
            error_log("Delete Project Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete project'];
        }
    }
}

