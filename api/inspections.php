<?php
/**
 * Inspections API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/InspectionModel.php';
require_once __DIR__ . '/../models/ComponentModel.php';
require_once __DIR__ . '/../models/UserModel.php';

requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$inspectionModel = new InspectionModel();
$componentModel = new ComponentModel();
$userModel = new UserModel();

switch ($action) {
    case 'list':
        $filters = [
            'date' => $_GET['date'] ?? '',
            'status' => $_GET['status'] ?? '',
            'component_id' => $_GET['component_id'] ?? '',
            'batch_id' => $_GET['batch_id'] ?? ''
        ];
        
        $data = $inspectionModel->getInspections($filters);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $data = $inspectionModel->getInspectionById($id);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        break;
        
    case 'create':
        if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $data = [
            'batch_id' => sanitizeInput($_POST['batch_id'] ?? ''),
            'component_id' => $_POST['component_id'] ?? 0,
            'inspector_id' => $_POST['inspector_id'] ?? $_SESSION['user_id'],
            'inspection_date' => $_POST['inspection_date'] ?? date('Y-m-d'),
            'thread_quality' => $_POST['thread_quality'] ?? 'Pending',
            'pressure_test' => $_POST['pressure_test'] ?? 'Pending',
            'dimensions' => $_POST['dimensions'] ?? 'Pending',
            'notes' => sanitizeInput($_POST['notes'] ?? '')
        ];
        
        $id = $inspectionModel->createInspection($data);
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Inspection created successfully', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create inspection']);
        }
        break;
        
    case 'update':
        if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $id = $_POST['id'] ?? 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit();
        }
        
        $data = [
            'batch_id' => sanitizeInput($_POST['batch_id'] ?? ''),
            'component_id' => $_POST['component_id'] ?? 0,
            'inspector_id' => $_POST['inspector_id'] ?? $_SESSION['user_id'],
            'inspection_date' => $_POST['inspection_date'] ?? date('Y-m-d'),
            'thread_quality' => $_POST['thread_quality'] ?? 'Pending',
            'pressure_test' => $_POST['pressure_test'] ?? 'Pending',
            'dimensions' => $_POST['dimensions'] ?? 'Pending',
            'notes' => sanitizeInput($_POST['notes'] ?? '')
        ];
        
        if ($inspectionModel->updateInspection($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Inspection updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update inspection']);
        }
        break;
        
    case 'delete':
        if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $id = $_POST['id'] ?? 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit();
        }
        
        if ($inspectionModel->deleteInspection($id)) {
            echo json_encode(['success' => true, 'message' => 'Inspection deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete inspection']);
        }
        break;
        
    case 'summary':
        $data = $inspectionModel->getInspectionSummary();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'components':
        $data = $componentModel->getAllComponents();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'inspectors':
        $users = $userModel->getAllUsers();
        $inspectors = array_filter($users, function($user) {
            return in_array($user['role'], ['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin']);
        });
        echo json_encode(['success' => true, 'data' => array_values($inspectors)]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

