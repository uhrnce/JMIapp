<?php
/**
 * Cleanup Script - Remove Unnecessary Files
 * This script will delete one-time setup and test files
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

// Only allow admins
if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
    die('Unauthorized access');
}

$filesToDelete = [
    'test_db.php',
    'fix_passwords.php',
    'link_lance.php',
    'setup_database.php',
    'migrate_user_status.php',
    'fix_pending_status.php',
    'test_approval_access.php',
    'package-lock.json',
    'FILES_TO_REMOVE.md',
    'PROJECT_SUMMARY.md',
    'SETUP.md'
];

$deleted = [];
$notFound = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    foreach ($filesToDelete as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $deleted[] = $file;
            } else {
                $errors[] = $file . ' (could not delete)';
            }
        } else {
            $notFound[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Unnecessary Files</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .file-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .file-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .file-exists {
            color: #28a745;
        }
        .file-missing {
            color: #999;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Cleanup Unnecessary Files</h1>
            <p>Remove one-time setup and test scripts</p>
        </div>
        
        <?php if (count($deleted) > 0): ?>
            <div class="success-box">
                <h3>✓ Successfully Deleted:</h3>
                <ul>
                    <?php foreach ($deleted as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($notFound) > 0): ?>
            <div class="file-list">
                <h3>Files Not Found (already deleted):</h3>
                <ul>
                    <?php foreach ($notFound as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($errors) > 0): ?>
            <div class="warning-box">
                <h3>⚠️ Errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="warning-box">
            <h3>⚠️ Warning</h3>
            <p><strong>This will permanently delete the following files:</strong></p>
            <ul>
                <li>One-time setup scripts</li>
                <li>Test/debug scripts</li>
                <li>Migration scripts (if already run)</li>
                <li>Documentation files (optional)</li>
            </ul>
            <p><strong>Make sure:</strong></p>
            <ul>
                <li>✅ Your database is fully set up</li>
                <li>✅ All features are working</li>
                <li>✅ You've backed up your database</li>
            </ul>
        </div>
        
        <div class="file-list">
            <h3>Files to be Deleted:</h3>
            <?php foreach ($filesToDelete as $file): ?>
                <div class="file-item">
                    <span><?php echo htmlspecialchars($file); ?></span>
                    <span class="<?php echo file_exists(__DIR__ . '/' . $file) ? 'file-exists' : 'file-missing'; ?>">
                        <?php echo file_exists(__DIR__ . '/' . $file) ? '✓ Exists' : '✗ Not found'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <form method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete these files? This action cannot be undone!')">
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="btn btn-danger" style="padding: 15px 30px; font-size: 1.1rem;">
                🗑️ Delete All Unnecessary Files
            </button>
        </form>
        
        <p style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">Cancel - Go Back</a>
        </p>
    </div>
    
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>



