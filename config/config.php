<?php
/**
 * Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Base URL (adjust if your project is in a subdirectory)
// Auto-detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$path = str_replace('index.php', '', $script);
define('BASE_URL', $protocol . '://' . $host . $path);

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Maximum file upload size (in bytes) - 10MB
define('MAX_UPLOAD_SIZE', 10485760);

// Allowed file types for uploads
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// Timezone
date_default_timezone_set('Asia/Manila');

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user has required role
 */
function hasRole($requiredRoles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'];
    
    if (is_array($requiredRoles)) {
        return in_array($userRole, $requiredRoles);
    }
    
    return $userRole === $requiredRoles;
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

/**
 * Require role - redirect to dashboard if role doesn't match
 */
function requireRole($requiredRoles) {
    requireLogin();
    
    if (!hasRole($requiredRoles)) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}
?>

