<?php
/**
 * Employee Performance Model
 */

require_once __DIR__ . '/../config/database.php';

class PerformanceModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get employee performance summary
     */
    public function getEmployeePerformance($employeeId = null, $period = 'monthly') {
        $dateCondition = $this->getDateCondition($period);
        
        $where = ["pl.status = 'Completed'"];
        $params = [];
        $types = "";
        
        if ($employeeId) {
            $where[] = "pl.employee_id = ?";
            $params[] = $employeeId;
            $types .= "i";
        }
        
        $where[] = $dateCondition['condition'];
        if (!empty($dateCondition['params'])) {
            $params = array_merge($params, $dateCondition['params']);
            $types .= $dateCondition['types'];
        }
        
        $query = "SELECT 
                    e.id,
                    e.full_name,
                    e.employee_code,
                    COUNT(DISTINCT pl.production_date) as days_worked,
                    SUM(pl.target_quantity) as total_target,
                    SUM(pl.actual_quantity) as total_actual,
                    AVG(pl.performance_percentage) as avg_performance,
                    COUNT(CASE WHEN pl.performance_percentage >= 100 THEN 1 END) as target_met_count,
                    COUNT(pl.id) as total_records
                  FROM production_logs pl
                  JOIN employees e ON pl.employee_id = e.id
                  WHERE " . implode(" AND ", $where) . "
                  GROUP BY e.id, e.full_name, e.employee_code
                  ORDER BY avg_performance DESC";
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($query);
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get top performers
     */
    public function getTopPerformers($limit = 10, $period = 'monthly') {
        $dateCondition = $this->getDateCondition($period);
        
        $query = "SELECT 
                    e.id,
                    e.full_name,
                    e.employee_code,
                    AVG(pl.performance_percentage) as avg_performance,
                    SUM(pl.actual_quantity) as total_output
                  FROM production_logs pl
                  JOIN employees e ON pl.employee_id = e.id
                  WHERE pl.status = 'Completed' AND " . $dateCondition['condition'] . "
                  GROUP BY e.id, e.full_name, e.employee_code
                  ORDER BY avg_performance DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $params = [$limit];
        $types = "i";
        
        if (!empty($dateCondition['params'])) {
            $params = array_merge($dateCondition['params'], $params);
            $types = $dateCondition['types'] . $types;
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get performance distribution
     */
    public function getPerformanceDistribution($period = 'monthly') {
        $dateCondition = $this->getDateCondition($period);
        
        $query = "SELECT 
                    CASE
                        WHEN pl.performance_percentage >= 100 THEN '100%+'
                        WHEN pl.performance_percentage >= 90 THEN '90-99%'
                        WHEN pl.performance_percentage >= 80 THEN '80-89%'
                        ELSE 'Below 80%'
                    END as performance_range,
                    COUNT(*) as count
                  FROM production_logs pl
                  WHERE pl.status = 'Completed' AND " . $dateCondition['condition'] . "
                  GROUP BY performance_range
                  ORDER BY performance_range DESC";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get date condition based on period
     */
    private function getDateCondition($period) {
        switch ($period) {
            case 'daily':
                return [
                    'condition' => "pl.production_date = CURDATE()",
                    'params' => [],
                    'types' => ''
                ];
            case 'weekly':
                return [
                    'condition' => "pl.production_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
                    'params' => [],
                    'types' => ''
                ];
            case 'monthly':
            default:
                return [
                    'condition' => "MONTH(pl.production_date) = MONTH(CURDATE()) AND YEAR(pl.production_date) = YEAR(CURDATE())",
                    'params' => [],
                    'types' => ''
                ];
        }
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

