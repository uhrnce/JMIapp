<?php
/**
 * Dashboard API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/DashboardModel.php';

requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$dashboardModel = new DashboardModel();

switch ($action) {
    case 'stats':
        $data = [
            'todays_output' => $dashboardModel->getTodaysOutput(),
            'ongoing_projects' => $dashboardModel->getOngoingProjects(),
            'pending_inspections' => $dashboardModel->getPendingInspections()
        ];
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'trends':
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
        $data = $dashboardModel->getProductionTrends($days);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'target-vs-actual':
        $data = $dashboardModel->getTargetVsActual();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'component-wise':
        $data = $dashboardModel->getComponentWiseOutput();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'recent-activity':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $data = $dashboardModel->getRecentActivity($limit);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'alerts':
        $data = $dashboardModel->getAlerts();
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

