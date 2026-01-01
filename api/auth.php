<?php
/**
 * Authentication API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$authController = new AuthController();

switch ($action) {
    case 'login':
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit();
        }
        
        $result = $authController->login($username, $password);
        echo json_encode($result);
        break;
        
    case 'signup':
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = sanitizeInput($_POST['role'] ?? 'Pipe Fitter/Helper');
        
        $result = $authController->signup($username, $password, $confirmPassword, $role);
        echo json_encode($result);
        break;
        
    case 'logout':
        $result = $authController->logout();
        echo json_encode($result);
        break;
        
    case 'check':
        $user = $authController->getCurrentUser();
        echo json_encode(['logged_in' => isLoggedIn(), 'user' => $user]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

