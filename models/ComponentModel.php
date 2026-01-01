<?php
/**
 * Component Model
 */

require_once __DIR__ . '/../config/database.php';

class ComponentModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all active components
     */
    public function getAllComponents() {
        $result = $this->conn->query("SELECT id, component_code, component_name, description, unit 
                                      FROM components 
                                      WHERE status = 'active' 
                                      ORDER BY component_name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get component by ID
     */
    public function getComponentById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM components WHERE id = ?");
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

