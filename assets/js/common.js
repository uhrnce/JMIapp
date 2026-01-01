/**
 * Common JavaScript Functions
 */

// Show message function
function showMessage(message, type = 'info', duration = 5000) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message-toast');
    existingMessages.forEach(msg => msg.remove());
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `message message-${type} message-toast`;
    messageEl.textContent = message;
    messageEl.style.position = 'fixed';
    messageEl.style.top = '20px';
    messageEl.style.right = '20px';
    messageEl.style.zIndex = '9999';
    messageEl.style.minWidth = '300px';
    messageEl.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
    
    document.body.appendChild(messageEl);
    
    // Auto remove after duration
    setTimeout(() => {
        messageEl.style.opacity = '0';
        messageEl.style.transition = 'opacity 0.3s';
        setTimeout(() => messageEl.remove(), 300);
    }, duration);
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Format datetime for display
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Calculate performance percentage
function calculatePerformance(target, actual) {
    if (target === 0) return 0;
    return ((actual / target) * 100).toFixed(2);
}

// Get status badge class
function getStatusBadgeClass(status) {
    const statusMap = {
        'Completed': 'badge-success',
        'In-progress': 'badge-warning',
        'Passed': 'badge-success',
        'Failed': 'badge-danger',
        'Pending': 'badge-secondary',
        'Rework Required': 'badge-warning'
    };
    return statusMap[status] || 'badge-secondary';
}

// Get performance badge class
function getPerformanceBadgeClass(percentage) {
    if (percentage >= 100) return 'badge-success';
    if (percentage >= 90) return 'badge-warning';
    return 'badge-danger';
}

// Confirm delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// AJAX helper
async function makeRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {}
    };
    
    if (data) {
        if (data instanceof FormData) {
            options.body = data;
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
    }
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Request error:', error);
        throw error;
    }
}

