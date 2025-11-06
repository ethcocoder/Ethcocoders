<?php
/**
 * ETHCO CODERS - Contact Controller
 * Handles contact form submissions from landing page
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

class ContactController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Submit contact form
     */
    public function submitContact($name, $email, $subject, $message) {
        // Validation
        if (empty($name) || empty($email) || empty($message)) {
            return ['success' => false, 'message' => 'Name, email, and message are required'];
        }
        
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Sanitize inputs
        $name = sanitizeInput($name);
        $email = sanitizeInput($email);
        $subject = sanitizeInput($subject);
        $message = sanitizeInput($message);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO contacts (name, email, subject, message, status) 
                VALUES (?, ?, ?, ?, 'new')
            ");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $contactId = $this->db->lastInsertId();
            
            // Create notification for admins
            $this->notifyAdmins($contactId, $name, $subject);
            
            // Optional: Send confirmation email to user
            // $this->sendConfirmationEmail($email, $name);
            
            logActivity(null, 'contact_submitted', "New contact from: $email");
            
            return [
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.',
                'contact_id' => $contactId
            ];
        } catch (PDOException $e) {
            error_log("Contact Submission Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit message. Please try again.'];
        }
    }
    
    /**
     * Get all contact messages (for admin)
     */
    public function getAllContacts($status = null, $limit = 50, $offset = 0) {
        try {
            $sql = "SELECT * FROM contacts";
            $params = [];
            
            if ($status) {
                $sql .= " WHERE status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Contacts Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get contact by ID
     */
    public function getContactById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Contact Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update contact status
     */
    public function updateContactStatus($id, $status, $adminNotes = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE contacts 
                SET status = ?, admin_notes = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $adminNotes, $id]);
            
            logActivity(getCurrentUserId(), 'contact_updated', "Contact #$id status changed to $status");
            
            return ['success' => true, 'message' => 'Contact status updated'];
        } catch (PDOException $e) {
            error_log("Update Contact Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update contact'];
        }
    }
    
    /**
     * Get contact statistics
     */
    public function getContactStats() {
        try {
            $stats = [];
            
            // Total contacts
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM contacts");
            $stats['total'] = $stmt->fetch()['total'];
            
            // New contacts
            $stmt = $this->db->query("SELECT COUNT(*) as new FROM contacts WHERE status = 'new'");
            $stats['new'] = $stmt->fetch()['new'];
            
            // Read contacts
            $stmt = $this->db->query("SELECT COUNT(*) as read FROM contacts WHERE status = 'read'");
            $stats['read'] = $stmt->fetch()['read'];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Get Contact Stats Error: " . $e->getMessage());
            return ['total' => 0, 'new' => 0, 'read' => 0];
        }
    }
    
    /**
     * Notify admins about new contact
     */
    private function notifyAdmins($contactId, $name, $subject) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE role = ?");
            $stmt->execute([ROLE_ADMIN]);
            $admins = $stmt->fetchAll();
            
            foreach ($admins as $admin) {
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link) 
                    VALUES (?, 'contact', ?, ?, ?)
                ");
                $title = "New Contact Message from $name";
                $message = "Subject: $subject";
                $link = "dashboard/contacts.php?id=$contactId";
                $stmt->execute([$admin['id'], $title, $message, $link]);
            }
        } catch (PDOException $e) {
            error_log("Notify Admins Error: " . $e->getMessage());
        }
    }
    
    /**
     * Search contacts
     */
    public function searchContacts($query) {
        try {
            $searchTerm = "%$query%";
            $stmt = $this->db->prepare("
                SELECT * FROM contacts 
                WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search Contacts Error: " . $e->getMessage());
            return [];
        }
    }
}

