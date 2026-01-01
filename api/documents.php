<?php
/**
 * Documents API Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/DocumentModel.php';

requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$documentModel = new DocumentModel();

switch ($action) {
    case 'list':
        $filters = [
            'category' => $_GET['category'] ?? '',
            'project_id' => $_GET['project_id'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $data = $documentModel->getDocuments($filters);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $data = $documentModel->getDocumentById($id);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        break;
        
    case 'upload':
        if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'File upload error']);
            exit();
        }
        
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file type
        if (!in_array($fileType, ALLOWED_FILE_TYPES)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_FILE_TYPES)]);
            exit();
        }
        
        // Validate file size
        if ($fileSize > MAX_UPLOAD_SIZE) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds maximum allowed size']);
            exit();
        }
        
        // Create upload directory if it doesn't exist
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        
        // Generate unique filename
        $uniqueFileName = uniqid() . '_' . time() . '.' . $fileType;
        $filePath = UPLOAD_DIR . $uniqueFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmp, $filePath)) {
            $data = [
                'document_name' => sanitizeInput($_POST['document_name'] ?? $fileName),
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'category' => sanitizeInput($_POST['category'] ?? 'Report'),
                'project_id' => sanitizeInput($_POST['project_id'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'uploaded_by' => $_SESSION['user_id']
            ];
            
            $id = $documentModel->createDocument($data);
            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Document uploaded successfully', 'id' => $id]);
            } else {
                @unlink($filePath);
                echo json_encode(['success' => false, 'message' => 'Failed to save document record']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        }
        break;
        
    case 'update':
        if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $id = $_POST['id'] ?? 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit();
        }
        
        $data = [
            'document_name' => sanitizeInput($_POST['document_name'] ?? ''),
            'category' => sanitizeInput($_POST['category'] ?? 'Report'),
            'project_id' => sanitizeInput($_POST['project_id'] ?? ''),
            'description' => sanitizeInput($_POST['description'] ?? '')
        ];
        
        if ($documentModel->updateDocument($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update document']);
        }
        break;
        
    case 'delete':
        if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $id = $_POST['id'] ?? 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit();
        }
        
        if ($documentModel->deleteDocument($id)) {
            echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete document']);
        }
        break;
        
    case 'download':
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $doc = $documentModel->getDocumentById($id);
            if ($doc && file_exists($doc['file_path'])) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($doc['document_name']) . '"');
                header('Content-Length: ' . filesize($doc['file_path']));
                readfile($doc['file_path']);
                exit();
            }
        }
        http_response_code(404);
        echo 'File not found';
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

