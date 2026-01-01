<?php
/**
 * Reports API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ProductionModel.php';
require_once __DIR__ . '/../models/InspectionModel.php';
require_once __DIR__ . '/../models/PerformanceModel.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$productionModel = new ProductionModel();
$inspectionModel = new InspectionModel();
$performanceModel = new PerformanceModel();

switch ($action) {
    case 'production':
        $period = $_GET['period'] ?? 'monthly';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $filters = [];
        if ($period === 'daily') {
            $filters['date'] = $date;
        } elseif ($period === 'weekly') {
            // Get start and end of week
            $startDate = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $endDate = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
            // Note: This is simplified - you may want to enhance the model to support date ranges
        }
        
        $data = $productionModel->getProductionLogs($filters);
        echo json_encode(['success' => true, 'data' => $data, 'period' => $period, 'date' => $date]);
        break;
        
    case 'inspection':
        $period = $_GET['period'] ?? 'monthly';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $filters = [];
        if ($period === 'daily') {
            $filters['date'] = $date;
        }
        
        $data = $inspectionModel->getInspections($filters);
        echo json_encode(['success' => true, 'data' => $data, 'period' => $period, 'date' => $date]);
        break;
        
    case 'performance':
        $period = $_GET['period'] ?? 'monthly';
        $data = $performanceModel->getEmployeePerformance(null, $period);
        echo json_encode(['success' => true, 'data' => $data, 'period' => $period]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        break;
}
?>

