/**
 * Document Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDocuments();
});

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.remove('dragover');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        uploadFile(files[0]);
    }
}

function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        uploadFile(files[0]);
    }
}

async function uploadFile(file) {
    const uploadArea = document.getElementById('uploadArea');
    const uploadProgress = document.getElementById('uploadProgress');
    
    // Show progress
    uploadProgress.style.display = 'block';
    uploadArea.style.pointerEvents = 'none';
    
    try {
        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('file', file);
        formData.append('document_name', file.name);
        formData.append('category', 'Report');
        formData.append('description', '');
        
        const response = await fetch('api/documents.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            loadDocuments();
        } else {
            showMessage(result.message || 'Error uploading file', 'error');
        }
    } catch (error) {
        console.error('Error uploading file:', error);
        showMessage('An error occurred. Please try again.', 'error');
    } finally {
        uploadProgress.style.display = 'none';
        uploadArea.style.pointerEvents = 'auto';
        document.getElementById('fileInput').value = '';
    }
}

async function loadDocuments() {
    try {
        const filters = {
            category: document.getElementById('filterCategory').value,
            project_id: document.getElementById('filterProjectId').value,
            search: document.getElementById('filterSearch').value
        };
        
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        const response = await fetch('api/documents.php?action=list&' + params.toString());
        const result = await response.json();
        
        const tbody = document.getElementById('documentsTable');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const fileSize = formatFileSize(item.file_size);
                const fileIcon = getFileIcon(item.file_type);
                
                return `
                    <tr>
                        <td>
                            <strong>${item.document_name}</strong>
                            ${item.description ? '<br><small>' + item.description + '</small>' : ''}
                        </td>
                        <td><span class="badge badge-info">${item.category}</span></td>
                        <td>${item.project_id || 'N/A'}</td>
                        <td>${fileIcon} ${item.file_type.toUpperCase()}</td>
                        <td>${fileSize}</td>
                        <td>${item.uploaded_by_name || 'N/A'}</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="downloadDocument(${item.id})">Download</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDocument(${item.id})">Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No documents found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading documents:', error);
        document.getElementById('documentsTable').innerHTML = 
            '<tr><td colspan="8" class="text-center">Error loading data</td></tr>';
    }
}

function resetFilters() {
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterProjectId').value = '';
    document.getElementById('filterSearch').value = '';
    loadDocuments();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function getFileIcon(fileType) {
    const icons = {
        'pdf': '📄',
        'jpg': '🖼️',
        'jpeg': '🖼️',
        'png': '🖼️',
        'doc': '📝',
        'docx': '📝'
    };
    return icons[fileType.toLowerCase()] || '📎';
}

function downloadDocument(id) {
    window.open(`api/documents.php?action=download&id=${id}`, '_blank');
}

async function deleteDocument(id) {
    if (!confirmDelete('Are you sure you want to delete this document?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const response = await fetch('api/documents.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            loadDocuments();
        } else {
            showMessage(result.message || 'Error deleting document', 'error');
        }
    } catch (error) {
        console.error('Error deleting document:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

