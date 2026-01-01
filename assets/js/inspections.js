/**
 * Inspections JavaScript
 */

let components = [];
let inspectors = [];

document.addEventListener('DOMContentLoaded', function() {
    loadComponents();
    loadInspectors();
    loadInspectionSummary();
    loadInspections();
    
    document.getElementById('modalDate').value = new Date().toISOString().split('T')[0];
});

async function loadComponents() {
    try {
        const response = await fetch('api/inspections.php?action=components');
        const result = await response.json();
        
        if (result.success) {
            components = result.data;
            const filterSelect = document.getElementById('filterComponent');
            const modalSelect = document.getElementById('modalComponent');
            
            filterSelect.innerHTML = '<option value="">All Components</option>';
            modalSelect.innerHTML = '<option value="">Select Component</option>';
            
            components.forEach(comp => {
                filterSelect.innerHTML += `<option value="${comp.id}">${comp.component_name} (${comp.component_code})</option>`;
                modalSelect.innerHTML += `<option value="${comp.id}">${comp.component_name} (${comp.component_code})</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading components:', error);
    }
}

async function loadInspectors() {
    try {
        const response = await fetch('api/inspections.php?action=inspectors');
        const result = await response.json();
        
        if (result.success) {
            inspectors = result.data;
            const select = document.getElementById('modalInspector');
            
            select.innerHTML = '<option value="">Select Inspector</option>';
            inspectors.forEach(ins => {
                select.innerHTML += `<option value="${ins.id}">${ins.username} (${ins.role})</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading inspectors:', error);
    }
}

async function loadInspectionSummary() {
    try {
        const response = await fetch('api/inspections.php?action=summary');
        const result = await response.json();
        
        if (result.success) {
            const summary = {};
            result.data.forEach(item => {
                summary[item.overall_status] = item.count;
            });
            
            const summaryDiv = document.getElementById('inspectionSummary');
            summaryDiv.innerHTML = `
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Pending</span>
                        <div class="stat-card-icon" style="background-color: #e2e8f0; color: #64748b;">⏳</div>
                    </div>
                    <div class="stat-card-value">${summary['Pending'] || 0}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Passed</span>
                        <div class="stat-card-icon" style="background-color: #d1fae5; color: #10b981;">✓</div>
                    </div>
                    <div class="stat-card-value">${summary['Passed'] || 0}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Failed</span>
                        <div class="stat-card-icon" style="background-color: #fee2e2; color: #ef4444;">✗</div>
                    </div>
                    <div class="stat-card-value">${summary['Failed'] || 0}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Rework Required</span>
                        <div class="stat-card-icon" style="background-color: #fef3c7; color: #f59e0b;">⚠</div>
                    </div>
                    <div class="stat-card-value">${summary['Rework Required'] || 0}</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading inspection summary:', error);
    }
}

async function loadInspections() {
    try {
        const filters = {
            date: document.getElementById('filterDate').value,
            status: document.getElementById('filterStatus').value,
            component_id: document.getElementById('filterComponent').value,
            batch_id: document.getElementById('filterBatchId').value
        };
        
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        const response = await fetch('api/inspections.php?action=list&' + params.toString());
        const result = await response.json();
        
        const tbody = document.getElementById('inspectionsTable');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const statusClass = getStatusBadgeClass(item.overall_status);
                const threadClass = item.thread_quality === 'Pass' ? 'badge-success' : 
                                   item.thread_quality === 'Fail' ? 'badge-danger' : 'badge-secondary';
                const pressureClass = item.pressure_test === 'Pass' ? 'badge-success' : 
                                     item.pressure_test === 'Fail' ? 'badge-danger' : 'badge-secondary';
                const dimClass = item.dimensions === 'Pass' ? 'badge-success' : 
                               item.dimensions === 'Fail' ? 'badge-danger' : 'badge-secondary';
                
                return `
                    <tr>
                        <td><strong>${item.batch_id}</strong></td>
                        <td>${formatDate(item.inspection_date)}</td>
                        <td>${item.component_name}<br><small>${item.component_code}</small></td>
                        <td><span class="badge ${threadClass}">${item.thread_quality}</span></td>
                        <td><span class="badge ${pressureClass}">${item.pressure_test}</span></td>
                        <td><span class="badge ${dimClass}">${item.dimensions}</span></td>
                        <td><span class="badge ${statusClass}">${item.overall_status}</span></td>
                        <td>${item.inspector_name || 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editInspection(${item.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteInspection(${item.id})">Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">No inspections found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading inspections:', error);
        document.getElementById('inspectionsTable').innerHTML = 
            '<tr><td colspan="9" class="text-center">Error loading data</td></tr>';
    }
}

function resetFilters() {
    document.getElementById('filterDate').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterComponent').value = '';
    document.getElementById('filterBatchId').value = '';
    loadInspections();
}

function showInspectionModal(id = null) {
    const modal = document.getElementById('inspectionModal');
    const form = document.getElementById('inspectionForm');
    const title = document.getElementById('modalTitle');
    
    form.reset();
    document.getElementById('inspectionId').value = '';
    document.getElementById('modalDate').value = new Date().toISOString().split('T')[0];
    
    if (id) {
        title.textContent = 'Edit Inspection';
        loadInspection(id);
    } else {
        title.textContent = 'Add Inspection';
    }
    
    modal.style.display = 'flex';
}

function closeInspectionModal() {
    document.getElementById('inspectionModal').style.display = 'none';
}

async function loadInspection(id) {
    try {
        const response = await fetch(`api/inspections.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            document.getElementById('inspectionId').value = data.id;
            document.getElementById('modalBatchId').value = data.batch_id;
            document.getElementById('modalComponent').value = data.component_id;
            document.getElementById('modalInspector').value = data.inspector_id || '';
            document.getElementById('modalDate').value = data.inspection_date;
            document.getElementById('modalThreadQuality').value = data.thread_quality;
            document.getElementById('modalPressureTest').value = data.pressure_test;
            document.getElementById('modalDimensions').value = data.dimensions;
            document.getElementById('modalNotes').value = data.notes || '';
        }
    } catch (error) {
        console.error('Error loading inspection:', error);
        showMessage('Error loading inspection', 'error');
    }
}

async function saveInspection(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', document.getElementById('inspectionId').value ? 'update' : 'create');
    
    if (document.getElementById('inspectionId').value) {
        formData.append('id', document.getElementById('inspectionId').value);
    }
    
    try {
        const response = await fetch('api/inspections.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            closeInspectionModal();
            loadInspections();
            loadInspectionSummary();
        } else {
            showMessage(result.message || 'Error saving inspection', 'error');
        }
    } catch (error) {
        console.error('Error saving inspection:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

async function editInspection(id) {
    showInspectionModal(id);
}

async function deleteInspection(id) {
    if (!confirmDelete('Are you sure you want to delete this inspection?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const response = await fetch('api/inspections.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            loadInspections();
            loadInspectionSummary();
        } else {
            showMessage(result.message || 'Error deleting inspection', 'error');
        }
    } catch (error) {
        console.error('Error deleting inspection:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

