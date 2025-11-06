<?php
/**
 * ETHCO CODERS - Task Controller
 * Handles task creation, assignment, and management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

class TaskController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Create a new task
     */
    public function createTask($title, $description, $assignedTo, $assignedBy, $priority = 'medium', $dueDate = null) {
        if (empty($title) || empty($assignedTo)) {
            return ['success' => false, 'message' => 'Title and assignee are required'];
        }
        
        $title = sanitizeInput($title);
        $description = sanitizeInput($description);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO tasks (title, description, assigned_to, assigned_by, priority, due_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'to_do')
            ");
            $stmt->execute([$title, $description, $assignedTo, $assignedBy, $priority, $dueDate]);
            
            $taskId = $this->db->lastInsertId();
            
            // Notify assigned user
            $this->notifyTaskAssignment($assignedTo, $taskId, $title);
            
            logActivity($assignedBy, 'task_created', "Task created: $title");
            
            return [
                'success' => true,
                'message' => 'Task created successfully',
                'task_id' => $taskId
            ];
        } catch (PDOException $e) {
            error_log("Create Task Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create task'];
        }
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status, $userId) {
        try {
            // Verify user has permission (assigned to or admin)
            $stmt = $this->db->prepare("SELECT assigned_to, assigned_by FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task) {
                return ['success' => false, 'message' => 'Task not found'];
            }
            
            if ($task['assigned_to'] != $userId && !isAdmin()) {
                return ['success' => false, 'message' => 'You do not have permission to update this task'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET status = ?, updated_at = NOW(), completed_at = ? 
                WHERE id = ?
            ");
            
            $completedAt = ($status === 'done') ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$status, $completedAt, $taskId]);
            
            logActivity($userId, 'task_updated', "Task #$taskId status changed to $status");
            
            return ['success' => true, 'message' => 'Task status updated'];
        } catch (PDOException $e) {
            error_log("Update Task Status Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update task'];
        }
    }
    
    /**
     * Get user's tasks
     */
    public function getUserTasks($userId, $status = null) {
        try {
            $sql = "SELECT t.*, 
                           u1.username as assigned_to_name,
                           u2.username as assigned_by_name
                    FROM tasks t
                    JOIN users u1 ON t.assigned_to = u1.id
                    JOIN users u2 ON t.assigned_by = u2.id
                    WHERE t.assigned_to = ?";
            $params = [$userId];
            
            if ($status) {
                $sql .= " AND t.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY t.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get User Tasks Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all tasks (admin)
     */
    public function getAllTasks($status = null, $priority = null) {
        try {
            $sql = "SELECT t.*, 
                           u1.username as assigned_to_name,
                           u2.username as assigned_by_name
                    FROM tasks t
                    JOIN users u1 ON t.assigned_to = u1.id
                    JOIN users u2 ON t.assigned_by = u2.id
                    WHERE 1=1";
            $params = [];
            
            if ($status) {
                $sql .= " AND t.status = ?";
                $params[] = $status;
            }
            
            if ($priority) {
                $sql .= " AND t.priority = ?";
                $params[] = $priority;
            }
            
            $sql .= " ORDER BY t.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get All Tasks Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get task by ID
     */
    public function getTaskById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, 
                       u1.username as assigned_to_name,
                       u2.username as assigned_by_name
                FROM tasks t
                JOIN users u1 ON t.assigned_to = u1.id
                JOIN users u2 ON t.assigned_by = u2.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Task Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get team members for task assignment
     */
    public function getTeamMembers() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, role 
                FROM users 
                WHERE role IN (?, ?) AND status = 'active'
                ORDER BY username
            ");
            $stmt->execute([ROLE_ADMIN, ROLE_TEAM_MEMBER]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Team Members Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Notify user about task assignment
     */
    private function notifyTaskAssignment($userId, $taskId, $taskTitle) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, 'task', ?, ?, ?)
            ");
            $title = "New Task Assigned";
            $message = "You have been assigned a new task: $taskTitle";
            $link = "dashboard/tasks.php?id=$taskId";
            $stmt->execute([$userId, $title, $message, $link]);
        } catch (PDOException $e) {
            error_log("Notify Task Assignment Error: " . $e->getMessage());
        }
    }
    
    /**
     * Update task (edit) - Admin only
     */
    public function updateTask($taskId, $title, $description, $assignedTo, $priority, $dueDate, $status, $userId) {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'Only admins can edit tasks'];
        }
        
        if (empty($title) || empty($assignedTo)) {
            return ['success' => false, 'message' => 'Title and assignee are required'];
        }
        
        $title = sanitizeInput($title);
        $description = sanitizeInput($description);
        
        try {
            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET title = ?, description = ?, assigned_to = ?, priority = ?, 
                    due_date = ?, status = ?, updated_at = NOW(),
                    completed_at = ?
                WHERE id = ?
            ");
            
            $completedAt = ($status === 'done') ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$title, $description, $assignedTo, $priority, $dueDate, $status, $completedAt, $taskId]);
            
            logActivity($userId, 'task_updated', "Task #$taskId updated by admin");
            
            return ['success' => true, 'message' => 'Task updated successfully'];
        } catch (PDOException $e) {
            error_log("Update Task Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update task'];
        }
    }
    
    /**
     * Delete task - Admin only
     */
    public function deleteTask($taskId, $userId) {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'Only admins can delete tasks'];
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            
            logActivity($userId, 'task_deleted', "Task #$taskId deleted");
            
            return ['success' => true, 'message' => 'Task deleted successfully'];
        } catch (PDOException $e) {
            error_log("Delete Task Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete task'];
        }
    }
}

