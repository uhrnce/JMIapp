<?php
/**
 * Dashboard Model
 * Handles dashboard data retrieval
 */

require_once __DIR__ . '/../config/database.php';

class DashboardModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get today's production output
     */
    public function getTodaysOutput() {
        $query = "SELECT SUM(actual_quantity) as total FROM production_logs WHERE production_date = CURDATE()";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    /**
     * Get ongoing projects count
     */
    public function getOngoingProjects() {
        $query = "SELECT COUNT(*) as count FROM projects WHERE status = 'Ongoing'";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Get pending inspections count
     */
    public function getPendingInspections() {
        $query = "SELECT COUNT(*) as count FROM inspections WHERE overall_status = 'Pending'";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Get production trends (last 7 days)
     */
    public function getProductionTrends($days = 7) {
        $query = "SELECT 
                    production_date as date,
                    SUM(target_quantity) as target,
                    SUM(actual_quantity) as actual
                  FROM production_logs 
                  WHERE production_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                  GROUP BY production_date
                  ORDER BY production_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $days);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get target vs actual (current month)
     */
    public function getTargetVsActual() {
        $query = "SELECT 
                    SUM(target_quantity) as target,
                    SUM(actual_quantity) as actual
                  FROM production_logs 
                  WHERE MONTH(production_date) = MONTH(CURDATE())
                  AND YEAR(production_date) = YEAR(CURDATE())";
        
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }
    
    /**
     * Get component-wise output (current month)
     */
    public function getComponentWiseOutput() {
        $query = "SELECT 
                    c.component_name,
                    SUM(pl.actual_quantity) as total
                  FROM production_logs pl
                  JOIN components c ON pl.component_id = c.id
                  WHERE MONTH(pl.production_date) = MONTH(CURDATE())
                  AND YEAR(pl.production_date) = YEAR(CURDATE())
                  GROUP BY c.id, c.component_name
                  ORDER BY total DESC
                  LIMIT 10";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get recent production activity
     */
    public function getRecentActivity($limit = 10) {
        $query = "SELECT 
                    pl.id,
                    pl.production_date,
                    e.full_name as employee_name,
                    c.component_name,
                    pl.actual_quantity,
                    pl.target_quantity,
                    pl.performance_percentage,
                    pl.status,
                    pl.qc_status
                  FROM production_logs pl
                  JOIN employees e ON pl.employee_id = e.id
                  JOIN components c ON pl.component_id = c.id
                  ORDER BY pl.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get alerts and notifications
     */
    public function getAlerts() {
        $alerts = [];
        
        // Low performance alerts
        $query = "SELECT COUNT(*) as count FROM production_logs 
                  WHERE production_date = CURDATE() 
                  AND performance_percentage < 90 
                  AND status = 'Completed'";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => $row['count'] . ' production record(s) below 90% performance today'
            ];
        }
        
        // Pending inspections
        $query = "SELECT COUNT(*) as count FROM inspections WHERE overall_status = 'Pending'";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => $row['count'] . ' inspection(s) pending'
            ];
        }
        
        // Failed inspections
        $query = "SELECT COUNT(*) as count FROM inspections 
                  WHERE overall_status = 'Failed' 
                  AND inspection_date = CURDATE()";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => $row['count'] . ' inspection(s) failed today'
            ];
        }
        
        return $alerts;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

