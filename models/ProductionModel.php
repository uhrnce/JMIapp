<?php
/**
 * Production Model
 * Handles production logs database operations
 */

require_once __DIR__ . '/../config/database.php';

class ProductionModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all production logs with filters
     */
    public function getProductionLogs($filters = []) {
        $where = ["1=1"];
        $params = [];
        $types = "";
        
        if (!empty($filters['date'])) {
            $where[] = "pl.production_date = ?";
            $params[] = $filters['date'];
            $types .= "s";
        }
        
        if (!empty($filters['status'])) {
            $where[] = "pl.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['component_id'])) {
            $where[] = "pl.component_id = ?";
            $params[] = $filters['component_id'];
            $types .= "i";
        }
        
        if (!empty($filters['employee_id'])) {
            $where[] = "pl.employee_id = ?";
            $params[] = $filters['employee_id'];
            $types .= "i";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(e.full_name LIKE ? OR c.component_name LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        $query = "SELECT 
                    pl.id,
                    pl.production_date,
                    pl.target_quantity,
                    pl.actual_quantity,
                    pl.performance_percentage,
                    pl.status,
                    pl.qc_status,
                    pl.notes,
                    pl.created_at,
                    e.id as employee_id,
                    e.full_name as employee_name,
                    e.employee_code,
                    c.id as component_id,
                    c.component_name,
                    c.component_code
                  FROM production_logs pl
                  JOIN employees e ON pl.employee_id = e.id
                  JOIN components c ON pl.component_id = c.id
                  WHERE " . implode(" AND ", $where) . "
                  ORDER BY pl.production_date DESC, pl.created_at DESC
                  LIMIT 1000";
        
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
     * Get production log by ID
     */
    public function getProductionLogById($id) {
        $stmt = $this->conn->prepare("SELECT 
                    pl.*,
                    e.full_name as employee_name,
                    e.employee_code,
                    c.component_name,
                    c.component_code
                  FROM production_logs pl
                  JOIN employees e ON pl.employee_id = e.id
                  JOIN components c ON pl.component_id = c.id
                  WHERE pl.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Create production log
     */
    public function createProductionLog($data) {
        $performance = $data['target_quantity'] > 0 
            ? (($data['actual_quantity'] / $data['target_quantity']) * 100) 
            : 0;
        
        $stmt = $this->conn->prepare("INSERT INTO production_logs 
            (employee_id, component_id, production_date, target_quantity, actual_quantity, 
             performance_percentage, status, qc_status, notes, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("iissidsssi",
            $data['employee_id'],
            $data['component_id'],
            $data['production_date'],
            $data['target_quantity'],
            $data['actual_quantity'],
            $performance,
            $data['status'],
            $data['qc_status'],
            $data['notes'],
            $data['created_by']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update production log
     */
    public function updateProductionLog($id, $data) {
        $performance = $data['target_quantity'] > 0 
            ? (($data['actual_quantity'] / $data['target_quantity']) * 100) 
            : 0;
        
        $stmt = $this->conn->prepare("UPDATE production_logs SET
            employee_id = ?,
            component_id = ?,
            production_date = ?,
            target_quantity = ?,
            actual_quantity = ?,
            performance_percentage = ?,
            status = ?,
            qc_status = ?,
            notes = ?
            WHERE id = ?");
        
        $stmt->bind_param("iissidsssi",
            $data['employee_id'],
            $data['component_id'],
            $data['production_date'],
            $data['target_quantity'],
            $data['actual_quantity'],
            $performance,
            $data['status'],
            $data['qc_status'],
            $data['notes'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete production log
     */
    public function deleteProductionLog($id) {
        $stmt = $this->conn->prepare("DELETE FROM production_logs WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

