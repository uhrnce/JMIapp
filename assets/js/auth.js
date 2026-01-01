/**
 * Authentication JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Show loading state
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
            // Hide previous messages
            hideMessage();
            
            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('username', username);
                formData.append('password', password);
                
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    // Show specific message for pending approval
                    const message = data.pending_approval 
                        ? data.message 
                        : (data.message || 'Login failed. Please try again.');
                    showMessage(message, data.pending_approval ? 'info' : 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                console.error('Login error:', error);
                showMessage('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});

function showMessage(message, type = 'info') {
    const messageEl = document.getElementById('loginMessage');
    if (messageEl) {
        messageEl.textContent = message;
        messageEl.className = `message message-${type} show`;
    }
}

function hideMessage() {
    const messageEl = document.getElementById('loginMessage');
    if (messageEl) {
        messageEl.className = 'message';
    }
}

