<?php
/**
 * Authentication Controller
 * Handles login, logout, and session management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    /**
     * Handle login
     */
    public function login($username, $password) {
        // First check if user exists and password is correct (regardless of status)
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password_hash, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Check if account is pending approval
                if ($user['status'] === 'pending') {
                    $conn->close();
                    return [
                        'success' => false,
                        'message' => 'Your account is pending approval. Please wait for an administrator to approve your account before logging in.',
                        'pending_approval' => true
                    ];
                }
                
                // Check if account is inactive
                if ($user['status'] === 'inactive') {
                    $conn->close();
                    return [
                        'success' => false,
                        'message' => 'Your account has been deactivated. Please contact an administrator.'
                    ];
                }
                
                // Account is active, proceed with login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                $conn->close();
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'role' => $user['role']
                ];
            }
        }
        
        $conn->close();
        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        session_unset();
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
    
    /**
     * Handle signup
     */
    public function signup($username, $password, $confirmPassword, $role = 'Pipe Fitter/Helper') {
        // Validation
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Username and password are required'
            ];
        }
        
        if (strlen($username) < 3) {
            return [
                'success' => false,
                'message' => 'Username must be at least 3 characters long'
            ];
        }
        
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters long'
            ];
        }
        
        if ($password !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Passwords do not match'
            ];
        }
        
        // Check if username already exists
        if ($this->userModel->usernameExists($username)) {
            return [
                'success' => false,
                'message' => 'Username already exists. Please choose a different username.'
            ];
        }
        
        // Create user with 'pending' status (requires approval)
        $userId = $this->userModel->createUser($username, $password, $role, 'pending');
        
        if ($userId) {
            return [
                'success' => true,
                'message' => 'Account created successfully! Your account is pending approval. You will be able to login once an administrator approves your account.',
                'user_id' => $userId,
                'pending_approval' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to create account. Please try again.'
        ];
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        
        return null;
    }
}
?>

