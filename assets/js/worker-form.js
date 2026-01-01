/**
 * Worker Production Form JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    loadRecentEntries();
});

function updatePerformancePreview() {
    const target = parseFloat(document.getElementById('target').value) || 0;
    const actual = parseFloat(document.getElementById('actual').value) || 0;
    
    const performanceValue = document.getElementById('performanceValue');
    const performanceStatus = document.getElementById('performanceStatus');
    const previewTarget = document.getElementById('previewTarget');
    const previewActual = document.getElementById('previewActual');
    
    previewTarget.textContent = target;
    previewActual.textContent = actual;
    
    if (target > 0) {
        const performance = ((actual / target) * 100).toFixed(2);
        performanceValue.textContent = performance + '%';
        
        // Update status and color
        const previewDiv = document.getElementById('performancePreview');
        if (performance >= 100) {
            performanceStatus.textContent = '🎉 Target Met! Excellent Work!';
            previewDiv.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
        } else if (performance >= 90) {
            performanceStatus.textContent = '👍 Near Target - Keep Going!';
            previewDiv.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
        } else {
            performanceStatus.textContent = '📈 Below Target - You Can Do Better!';
            previewDiv.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
        }
    } else {
        performanceValue.textContent = '0%';
        performanceStatus.textContent = 'Enter target and actual quantities';
        document.getElementById('performancePreview').style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    }
}

async function saveWorkerProduction(e) {
    e.preventDefault();
    
    const employeeId = document.getElementById('employeeId').value;
    if (!employeeId) {
        showMessage('Employee information not found. Please contact administrator.', 'error');
        return;
    }
    
    // Get form button and disable it
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    const formData = new FormData(e.target);
    formData.append('action', 'create');
    formData.append('employee_id', employeeId);
    formData.append('qc_status', 'Pending');
    
    try {
        const response = await fetch('api/production.php', {
            method: 'POST',
            body: formData
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Production log submitted successfully!', 'success');
            e.target.reset();
            document.getElementById('productionDate').value = new Date().toISOString().split('T')[0];
            updatePerformancePreview();
            loadRecentEntries();
        } else {
            showMessage(result.message || 'Error submitting production log', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    } catch (error) {
        console.error('Error saving production log:', error);
        showMessage('An error occurred. Please check your connection and try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

async function loadRecentEntries() {
    try {
        const employeeId = document.getElementById('employeeId').value;
        if (!employeeId) {
            document.getElementById('recentEntriesTable').innerHTML = 
                '<tr><td colspan="6" class="text-center">Employee information not available</td></tr>';
            return;
        }
        
        const response = await fetch(`api/production.php?action=list&employee_id=${employeeId}`);
        const result = await response.json();
        
        const tbody = document.getElementById('recentEntriesTable');
        
        if (result.success && result.data.length > 0) {
            // Show only last 10 entries
            const recentData = result.data.slice(0, 10);
            
            tbody.innerHTML = recentData.map(item => {
                const performance = parseFloat(item.performance_percentage);
                const performanceClass = getPerformanceBadgeClass(performance);
                const statusClass = getStatusBadgeClass(item.status);
                
                return `
                    <tr>
                        <td>${formatDate(item.production_date)}</td>
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
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No entries yet. Submit your first production log above!</td></tr>';
        }
    } catch (error) {
        console.error('Error loading recent entries:', error);
        document.getElementById('recentEntriesTable').innerHTML = 
            '<tr><td colspan="6" class="text-center">Error loading data</td></tr>';
    }
}

