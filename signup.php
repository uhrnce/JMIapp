<?php
/**
 * Signup Page
 */

require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Production Management System</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="login-page">

<div class="login-container">
    <div class="login-box">

        <div class="login-header">
            <h1>Create Account</h1>
            <p>Production Management System</p>
        </div>

        <form id="signupForm" class="login-form" novalidate>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <div class="input-wrapper">
                    <input type="text"
                           id="username"
                           name="username"
                           required
                           minlength="3"
                           autocomplete="username"
                           placeholder="Username">
                </div>
            </div>

            <div class="form-group">
    <div class="input-wrapper password-wrapper">
        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="6"
            autocomplete="new-password"
            placeholder="Create a password"
        >

        <button
            type="button"
            class="password-toggle"
            id="togglePassword"
            aria-label="Show or hide password"
        >
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>

            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20
                         c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4
                         c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        </button>
    </div>

    <small class="form-hint">
        Password must be at least 6 characters.
    </small>
</div>

<div class="form-group">
    <div class="input-wrapper password-wrapper">
        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="6"
            autocomplete="new-password"
            placeholder="Create a password"
        >

        <button
            type="button"
            class="password-toggle"
            id="togglePassword"
        >
            Show
        </button>
    </div>

    <small class="form-hint">
        Must be at least 6 characters.
    </small>
</div>


            <div class="form-group">
                <div class="input-wrapper">
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select role</option>
                        <option value="Pipe Fitter/Helper">Pipe Fitter / Helper</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Operations Manager">Operations Manager</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Sign Up
            </button>

            <div id="signupMessage" class="message"></div>
        </form>

        <div class="login-footer">
            <p>Already have an account?
                <a href="index.php">Login here</a>
            </p>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('signupForm');
    const messageBox = document.getElementById('signupMessage');
    const submitBtn = form.querySelector('button[type="submit"]');
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('confirm_password');

    // Password visibility toggles
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');
    const eyeOffIconConfirm = document.getElementById('eyeOffIconConfirm');

    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });
    }

    if (toggleConfirmPassword && confirmField) {
        toggleConfirmPassword.addEventListener('click', () => {
            const type = confirmField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmField.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIconConfirm.style.display = 'none';
                eyeOffIconConfirm.style.display = 'block';
            } else {
                eyeIconConfirm.style.display = 'block';
                eyeOffIconConfirm.style.display = 'none';
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const password = passwordField.value;
        const confirm = confirmField.value;

        if (password !== confirm) {
            showMessage('Passwords do not match.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';
        hideMessage();

        try {
            const formData = new FormData(form);
            formData.append('action', 'signup');

            const res = await fetch('api/auth.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (!data.success) {
                showMessage(data.message || 'Signup failed.', 'error');
                resetButton();
                return;
            }

            showMessage(data.message, data.pending_approval ? 'info' : 'success');

            setTimeout(() => {
                window.location.href = 'index.php';
            }, data.pending_approval ? 4000 : 2000);

        } catch (err) {
            console.error(err);
            showMessage('Unexpected error. Please try again.', 'error');
            resetButton();
        }
    });

    function resetButton() {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Account';
    }

    function showMessage(msg, type) {
        messageBox.textContent = msg;
        messageBox.className = `message message-${type} show`;
    }

    function hideMessage() {
        messageBox.className = 'message';
    }
});
</script>

</body>
</html>
