<?php
/**
 * Inspection Model
 */

require_once __DIR__ . '/../config/database.php';

class InspectionModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all inspections with filters
     */
    public function getInspections($filters = []) {
        $where = ["1=1"];
        $params = [];
        $types = "";
        
        if (!empty($filters['date'])) {
            $where[] = "i.inspection_date = ?";
            $params[] = $filters['date'];
            $types .= "s";
        }
        
        if (!empty($filters['status'])) {
            $where[] = "i.overall_status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['component_id'])) {
            $where[] = "i.component_id = ?";
            $params[] = $filters['component_id'];
            $types .= "i";
        }
        
        if (!empty($filters['batch_id'])) {
            $where[] = "i.batch_id LIKE ?";
            $params[] = "%" . $filters['batch_id'] . "%";
            $types .= "s";
        }
        
        $query = "SELECT 
                    i.id,
                    i.batch_id,
                    i.inspection_date,
                    i.thread_quality,
                    i.pressure_test,
                    i.dimensions,
                    i.overall_status,
                    i.notes,
                    i.created_at,
                    c.component_name,
                    c.component_code,
                    u.username as inspector_name
                  FROM inspections i
                  JOIN components c ON i.component_id = c.id
                  LEFT JOIN users u ON i.inspector_id = u.id
                  WHERE " . implode(" AND ", $where) . "
                  ORDER BY i.inspection_date DESC, i.created_at DESC
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
     * Get inspection by ID
     */
    public function getInspectionById($id) {
        $stmt = $this->conn->prepare("SELECT i.*, c.component_name, c.component_code 
                                      FROM inspections i
                                      JOIN components c ON i.component_id = c.id
                                      WHERE i.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Create inspection
     */
    public function createInspection($data) {
        // Determine overall status based on individual checks
        $overallStatus = 'Pending';
        if ($data['thread_quality'] === 'Pass' && $data['pressure_test'] === 'Pass' && $data['dimensions'] === 'Pass') {
            $overallStatus = 'Passed';
        } elseif ($data['thread_quality'] === 'Fail' || $data['pressure_test'] === 'Fail' || $data['dimensions'] === 'Fail') {
            $overallStatus = 'Failed';
        }
        
        $stmt = $this->conn->prepare("INSERT INTO inspections 
            (batch_id, component_id, inspector_id, inspection_date, thread_quality, 
             pressure_test, dimensions, overall_status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("siissssss",
            $data['batch_id'],
            $data['component_id'],
            $data['inspector_id'],
            $data['inspection_date'],
            $data['thread_quality'],
            $data['pressure_test'],
            $data['dimensions'],
            $overallStatus,
            $data['notes']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update inspection
     */
    public function updateInspection($id, $data) {
        // Determine overall status
        $overallStatus = 'Pending';
        if ($data['thread_quality'] === 'Pass' && $data['pressure_test'] === 'Pass' && $data['dimensions'] === 'Pass') {
            $overallStatus = 'Passed';
        } elseif ($data['thread_quality'] === 'Fail' || $data['pressure_test'] === 'Fail' || $data['dimensions'] === 'Fail') {
            $overallStatus = 'Failed';
        }
        
        $stmt = $this->conn->prepare("UPDATE inspections SET
            batch_id = ?,
            component_id = ?,
            inspector_id = ?,
            inspection_date = ?,
            thread_quality = ?,
            pressure_test = ?,
            dimensions = ?,
            overall_status = ?,
            notes = ?
            WHERE id = ?");
        
        $stmt->bind_param("siissssssi",
            $data['batch_id'],
            $data['component_id'],
            $data['inspector_id'],
            $data['inspection_date'],
            $data['thread_quality'],
            $data['pressure_test'],
            $data['dimensions'],
            $overallStatus,
            $data['notes'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete inspection
     */
    public function deleteInspection($id) {
        $stmt = $this->conn->prepare("DELETE FROM inspections WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Get inspection summary
     */
    public function getInspectionSummary() {
        $query = "SELECT 
                    overall_status,
                    COUNT(*) as count
                  FROM inspections
                  GROUP BY overall_status";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

