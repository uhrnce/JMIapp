<?php
/**
 * User Model
 * Handles all user-related database operations
 */

require_once __DIR__ . '/../config/database.php';

class UserModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password_hash, role, status FROM users WHERE username = ? AND status = 'active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                return $user;
            }
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT id, username, role, status, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get all users
     */
    public function getAllUsers() {
        $result = $this->conn->query("SELECT id, username, role, status, created_at FROM users ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get pending users (awaiting approval)
     * Includes users with status 'pending', NULL, or empty string
     */
    public function getPendingUsers() {
        $result = $this->conn->query("SELECT id, username, role, status, created_at FROM users WHERE status = 'pending' OR status IS NULL OR status = '' ORDER BY created_at ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Approve user (change status from pending to active)
     * Handles pending, NULL, or empty status
     */
    public function approveUser($id) {
        $stmt = $this->conn->prepare("UPDATE users SET status = 'active' WHERE id = ? AND (status = 'pending' OR status IS NULL OR status = '')");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Reject user (change status from pending to inactive)
     * Handles pending, NULL, or empty status
     */
    public function rejectUser($id) {
        $stmt = $this->conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ? AND (status = 'pending' OR status IS NULL OR status = '')");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Create new user
     */
    public function createUser($username, $password, $role, $status = 'active') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $passwordHash, $role, $status);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $username, $role, $status) {
        $stmt = $this->conn->prepare("UPDATE users SET username = ?, role = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $role, $status, $id);
        return $stmt->execute();
    }
    
    /**
     * Change password
     */
    public function changePassword($id, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $passwordHash, $id);
        return $stmt->execute();
    }
    
    /**
     * Delete user
     * Note: This permanently deletes the user. Consider deactivating instead.
     */
    public function deleteUser($id) {
        // Prevent deleting the current user (safety check)
        if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
            return false;
        }
        
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Close connection
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

