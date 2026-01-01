<?php
/**
 * Document Model
 */

require_once __DIR__ . '/../config/database.php';

class DocumentModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all documents with filters
     */
    public function getDocuments($filters = []) {
        $where = ["1=1"];
        $params = [];
        $types = "";
        
        if (!empty($filters['category'])) {
            $where[] = "d.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['project_id'])) {
            $where[] = "d.project_id = ?";
            $params[] = $filters['project_id'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(d.document_name LIKE ? OR d.description LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        $query = "SELECT 
                    d.id,
                    d.document_name,
                    d.file_path,
                    d.file_type,
                    d.file_size,
                    d.category,
                    d.project_id,
                    d.description,
                    d.created_at,
                    u.username as uploaded_by_name
                  FROM documents d
                  LEFT JOIN users u ON d.uploaded_by = u.id
                  WHERE " . implode(" AND ", $where) . "
                  ORDER BY d.created_at DESC
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
     * Get document by ID
     */
    public function getDocumentById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Create document record
     */
    public function createDocument($data) {
        $stmt = $this->conn->prepare("INSERT INTO documents 
            (document_name, file_path, file_type, file_size, category, project_id, description, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssisssi",
            $data['document_name'],
            $data['file_path'],
            $data['file_type'],
            $data['file_size'],
            $data['category'],
            $data['project_id'],
            $data['description'],
            $data['uploaded_by']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update document
     */
    public function updateDocument($id, $data) {
        $stmt = $this->conn->prepare("UPDATE documents SET
            document_name = ?,
            category = ?,
            project_id = ?,
            description = ?
            WHERE id = ?");
        
        $stmt->bind_param("ssssi",
            $data['document_name'],
            $data['category'],
            $data['project_id'],
            $data['description'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete document
     */
    public function deleteDocument($id) {
        // Get file path first
        $doc = $this->getDocumentById($id);
        
        $stmt = $this->conn->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete physical file
            if ($doc && file_exists($doc['file_path'])) {
                @unlink($doc['file_path']);
            }
            return true;
        }
        
        return false;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

