/**
 * Production Monitoring JavaScript
 */

let employees = [];
let components = [];

document.addEventListener('DOMContentLoaded', function() {
    loadEmployees();
    loadComponents();
    loadProductionLogs();
    
    // Set default date to today
    document.getElementById('modalDate').value = new Date().toISOString().split('T')[0];
});

async function loadEmployees() {
    try {
        const response = await fetch('api/production.php?action=employees');
        const result = await response.json();
        
        if (result.success) {
            employees = result.data;
            const select = document.getElementById('filterEmployee');
            const modalSelect = document.getElementById('modalEmployee');
            
            select.innerHTML = '<option value="">All Employees</option>';
            modalSelect.innerHTML = '<option value="">Select Employee</option>';
            
            employees.forEach(emp => {
                select.innerHTML += `<option value="${emp.id}">${emp.full_name} (${emp.employee_code})</option>`;
                modalSelect.innerHTML += `<option value="${emp.id}">${emp.full_name} (${emp.employee_code})</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading employees:', error);
    }
}

async function loadComponents() {
    try {
        const response = await fetch('api/production.php?action=components');
        const result = await response.json();
        
        if (result.success) {
            components = result.data;
            const select = document.getElementById('filterComponent');
            const modalSelect = document.getElementById('modalComponent');
            
            select.innerHTML = '<option value="">All Components</option>';
            modalSelect.innerHTML = '<option value="">Select Component</option>';
            
            components.forEach(comp => {
                select.innerHTML += `<option value="${comp.id}">${comp.component_name} (${comp.component_code})</option>`;
                modalSelect.innerHTML += `<option value="${comp.id}">${comp.component_name} (${comp.component_code})</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading components:', error);
    }
}

async function loadProductionLogs() {
    try {
        const filters = {
            date: document.getElementById('filterDate').value,
            status: document.getElementById('filterStatus').value,
            component_id: document.getElementById('filterComponent').value,
            employee_id: document.getElementById('filterEmployee').value,
            search: document.getElementById('filterSearch').value
        };
        
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        const response = await fetch('api/production.php?action=list&' + params.toString());
        const result = await response.json();
        
        const tbody = document.getElementById('productionTable');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const performance = parseFloat(item.performance_percentage);
                const performanceClass = getPerformanceBadgeClass(performance);
                const statusClass = getStatusBadgeClass(item.status);
                const qcClass = getStatusBadgeClass(item.qc_status);
                
                return `
                    <tr>
                        <td>${formatDate(item.production_date)}</td>
                        <td>${item.employee_name}<br><small>${item.employee_code}</small></td>
                        <td>${item.component_name}<br><small>${item.component_code}</small></td>
                        <td>${item.target_quantity}</td>
                        <td>${item.actual_quantity}</td>
                        <td>
                            <span class="badge ${performanceClass}">
                                ${performance.toFixed(2)}%
                            </span>
                        </td>
                        <td>
                            <span class="badge ${statusClass}">
                                ${item.status}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${qcClass}">
                                ${item.qc_status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editProductionLog(${item.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProductionLog(${item.id})">Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">No production logs found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading production logs:', error);
        document.getElementById('productionTable').innerHTML = 
            '<tr><td colspan="9" class="text-center">Error loading data</td></tr>';
    }
}

function resetFilters() {
    document.getElementById('filterDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterComponent').value = '';
    document.getElementById('filterEmployee').value = '';
    document.getElementById('filterSearch').value = '';
    loadProductionLogs();
}

function showProductionModal(id = null) {
    const modal = document.getElementById('productionModal');
    const form = document.getElementById('productionForm');
    const title = document.getElementById('modalTitle');
    
    form.reset();
    document.getElementById('productionId').value = '';
    document.getElementById('modalDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('performancePreview').innerHTML = '<span id="performanceValue">0%</span><span id="performanceBadge" class="badge badge-secondary" style="margin-left: 10px;">-</span>';
    
    if (id) {
        title.textContent = 'Edit Production Log';
        loadProductionLog(id);
    } else {
        title.textContent = 'Add Production Log';
    }
    
    modal.style.display = 'flex';
}

function closeProductionModal() {
    document.getElementById('productionModal').style.display = 'none';
}

async function loadProductionLog(id) {
    try {
        const response = await fetch(`api/production.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            document.getElementById('productionId').value = data.id;
            document.getElementById('modalEmployee').value = data.employee_id;
            document.getElementById('modalComponent').value = data.component_id;
            document.getElementById('modalDate').value = data.production_date;
            document.getElementById('modalTarget').value = data.target_quantity;
            document.getElementById('modalActual').value = data.actual_quantity;
            document.getElementById('modalStatus').value = data.status;
            document.getElementById('modalQCStatus').value = data.qc_status;
            document.getElementById('modalNotes').value = data.notes || '';
            updatePerformancePreview();
        }
    } catch (error) {
        console.error('Error loading production log:', error);
        showMessage('Error loading production log', 'error');
    }
}

function updatePerformancePreview() {
    const target = parseFloat(document.getElementById('modalTarget').value) || 0;
    const actual = parseFloat(document.getElementById('modalActual').value) || 0;
    
    if (target > 0) {
        const performance = ((actual / target) * 100).toFixed(2);
        const performanceClass = getPerformanceBadgeClass(performance);
        const badgeText = performance >= 100 ? 'Target Met' : performance >= 90 ? 'Near Target' : 'Below Target';
        
        document.getElementById('performanceValue').textContent = performance + '%';
        document.getElementById('performanceBadge').textContent = badgeText;
        document.getElementById('performanceBadge').className = `badge ${performanceClass}`;
    } else {
        document.getElementById('performanceValue').textContent = '0%';
        document.getElementById('performanceBadge').textContent = '-';
        document.getElementById('performanceBadge').className = 'badge badge-secondary';
    }
}

async function saveProductionLog(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', document.getElementById('productionId').value ? 'update' : 'create');
    
    if (document.getElementById('productionId').value) {
        formData.append('id', document.getElementById('productionId').value);
    }
    
    try {
        const response = await fetch('api/production.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            closeProductionModal();
            loadProductionLogs();
        } else {
            showMessage(result.message || 'Error saving production log', 'error');
        }
    } catch (error) {
        console.error('Error saving production log:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

async function editProductionLog(id) {
    showProductionModal(id);
}

async function deleteProductionLog(id) {
    if (!confirmDelete('Are you sure you want to delete this production log?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const response = await fetch('api/production.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            loadProductionLogs();
        } else {
            showMessage(result.message || 'Error deleting production log', 'error');
        }
    } catch (error) {
        console.error('Error deleting production log:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

