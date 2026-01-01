<?php
/**
 * Production API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ProductionModel.php';
require_once __DIR__ . '/../models/EmployeeModel.php';
require_once __DIR__ . '/../models/ComponentModel.php';

requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$productionModel = new ProductionModel();
$employeeModel = new EmployeeModel();
$componentModel = new ComponentModel();

switch ($action) {
    case 'list':
        $filters = [
            'date' => $_GET['date'] ?? '',
            'status' => $_GET['status'] ?? '',
            'component_id' => $_GET['component_id'] ?? '',
            'employee_id' => $_GET['employee_id'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $data = $productionModel->getProductionLogs($filters);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $data = $productionModel->getProductionLogById($id);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        break;
        
    case 'create':
        // Allow all authenticated users to create production logs
        // But Pipe Fitters/Helpers can only create logs for themselves
        $employeeId = intval($_POST['employee_id'] ?? 0);
        
        if ($employeeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
            exit();
        }
        
        // If user is Pipe Fitter/Helper, ensure they can only create logs for their own employee record
        if (hasRole(['Pipe Fitter/Helper'])) {
            $userId = $_SESSION['user_id'];
            
            // Check if user_id column exists
            $conn = getDBConnection();
            $checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
            if ($checkColumn->num_rows == 0) {
                $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL AFTER id");
                $conn->query("ALTER TABLE employees ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
            }
            
            // Get current user's employee record by user_id
            $stmt = $conn->prepare("SELECT id FROM employees WHERE user_id = ? AND status = 'active' LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentEmployee = $result->fetch_assoc();
            $conn->close();
            
            // If employee found, verify they're creating log for themselves
            if ($currentEmployee && $currentEmployee['id'] != $employeeId) {
                echo json_encode(['success' => false, 'message' => 'You can only create production logs for yourself']);
                exit();
            }
            
            // If no employee linked, try to auto-link by username (fallback)
            if (!$currentEmployee) {
                $username = $_SESSION['username'];
                $allEmployees = $employeeModel->getAllEmployees();
                
                foreach ($allEmployees as $emp) {
                    if (stripos($emp['employee_code'], $username) !== false || 
                        stripos($emp['full_name'], $username) !== false) {
                        // Auto-link this employee to user
                        $conn = getDBConnection();
                        $checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
                        if ($checkColumn->num_rows == 0) {
                            $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL AFTER id");
                            $conn->query("ALTER TABLE employees ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
                        }
                        $stmt = $conn->prepare("UPDATE employees SET user_id = ? WHERE id = ?");
                        $stmt->bind_param("ii", $userId, $emp['id']);
                        $stmt->execute();
                        $conn->close();
                        
                        // Verify the employee ID matches
                        if ($emp['id'] != $employeeId) {
                            echo json_encode(['success' => false, 'message' => 'You can only create production logs for yourself']);
                            exit();
                        }
                        break;
                    }
                }
            }
        }
        
        $data = [
            'employee_id' => $employeeId,
            'component_id' => intval($_POST['component_id'] ?? 0),
            'production_date' => $_POST['production_date'] ?? date('Y-m-d'),
            'target_quantity' => intval($_POST['target_quantity'] ?? 0),
            'actual_quantity' => intval($_POST['actual_quantity'] ?? 0),
            'status' => $_POST['status'] ?? 'In-progress',
            'qc_status' => $_POST['qc_status'] ?? 'Pending',
            'notes' => sanitizeInput($_POST['notes'] ?? ''),
            'created_by' => $_SESSION['user_id']
        ];
        
        // Validate required fields
        if ($data['component_id'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Component is required']);
            exit();
        }
        
        if ($data['target_quantity'] < 0 || $data['actual_quantity'] < 0) {
            echo json_encode(['success' => false, 'message' => 'Quantities must be non-negative']);
            exit();
        }
        
        $id = $productionModel->createProductionLog($data);
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Production log created successfully', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create production log. Please check your input.']);
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
            'employee_id' => $_POST['employee_id'] ?? 0,
            'component_id' => $_POST['component_id'] ?? 0,
            'production_date' => $_POST['production_date'] ?? date('Y-m-d'),
            'target_quantity' => $_POST['target_quantity'] ?? 0,
            'actual_quantity' => $_POST['actual_quantity'] ?? 0,
            'status' => $_POST['status'] ?? 'In-progress',
            'qc_status' => $_POST['qc_status'] ?? 'Pending',
            'notes' => sanitizeInput($_POST['notes'] ?? '')
        ];
        
        if ($productionModel->updateProductionLog($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Production log updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update production log']);
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
        
        if ($productionModel->deleteProductionLog($id)) {
            echo json_encode(['success' => true, 'message' => 'Production log deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete production log']);
        }
        break;
        
    case 'employees':
        $data = $employeeModel->getAllEmployees();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'components':
        $data = $componentModel->getAllComponents();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

