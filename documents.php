<?php
/**
 * Document Management Page
 */

$pageTitle = 'Document Management';
require_once __DIR__ . '/includes/header.php';

if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<div class="container">
    <div class="page-header">
        <h1>Document Management</h1>
        <p>Upload and manage project documents</p>
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <div class="filters-row">
            <div class="filter-group">
                <label>Category</label>
                <select id="filterCategory" class="form-control">
                    <option value="">All Categories</option>
                    <option value="Certificate">Certificate</option>
                    <option value="Report">Report</option>
                    <option value="Manual">Manual</option>
                    <option value="Contract">Contract</option>
                    <option value="Inspection">Inspection</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Project ID</label>
                <input type="text" id="filterProjectId" class="form-control" placeholder="Search project ID...">
            </div>
            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="filterSearch" class="form-control" placeholder="Search documents...">
            </div>
            <div class="filter-group">
                <button type="button" class="btn btn-primary" onclick="loadDocuments()">Filter</button>
                <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>
    
    <!-- Upload Section -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h2 class="card-title">Upload Document</h2>
        </div>
        <div id="uploadArea" class="upload-area" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
            <p>Drag and drop files here or click to browse</p>
            <input type="file" id="fileInput" style="display: none;" onchange="handleFileSelect(event)" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">Browse Files</button>
        </div>
        <div id="uploadProgress" style="display: none; margin-top: 15px;">
            <div class="spinner"></div>
            <p>Uploading...</p>
        </div>
    </div>
    
    <!-- Documents Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Documents</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Category</th>
                        <th>Project ID</th>
                        <th>File Type</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="documentsTable">
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.upload-area:hover {
    border-color: var(--primary-color);
    background-color: var(--bg-color);
}

.upload-area.dragover {
    border-color: var(--primary-color);
    background-color: #dbeafe;
}
</style>

<script src="assets/js/documents.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

