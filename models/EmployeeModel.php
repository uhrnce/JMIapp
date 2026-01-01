<?php
/**
 * Employee Model
 */

require_once __DIR__ . '/../config/database.php';

class EmployeeModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all active employees
     */
    public function getAllEmployees() {
        $result = $this->conn->query("SELECT id, employee_code, full_name, position, department 
                                      FROM employees 
                                      WHERE status = 'active' 
                                      ORDER BY full_name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get employee by ID
     */
    public function getEmployeeById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

