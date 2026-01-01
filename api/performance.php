<?php
/**
 * Performance API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/PerformanceModel.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$performanceModel = new PerformanceModel();

switch ($action) {
    case 'list':
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;
        $period = $_GET['period'] ?? 'monthly';
        
        $data = $performanceModel->getEmployeePerformance($employeeId, $period);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'top-performers':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $period = $_GET['period'] ?? 'monthly';
        
        $data = $performanceModel->getTopPerformers($limit, $period);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'distribution':
        $period = $_GET['period'] ?? 'monthly';
        
        $data = $performanceModel->getPerformanceDistribution($period);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

