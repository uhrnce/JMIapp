<?php
/**
 * Header Include
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>JMI 2</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand">JMI</a>
            <ul class="navbar-menu">
                <li><a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])): ?>
                    <li><a href="production.php" class="<?php echo $currentPage === 'production.php' ? 'active' : ''; ?>">Production</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])): ?>
                    <li><a href="performance.php" class="<?php echo $currentPage === 'performance.php' ? 'active' : ''; ?>">Performance</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])): ?>
                    <li><a href="inspections.php" class="<?php echo $currentPage === 'inspections.php' ? 'active' : ''; ?>">Inspections</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])): ?>
                    <li><a href="documents.php" class="<?php echo $currentPage === 'documents.php' ? 'active' : ''; ?>">Documents</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])): ?>
                    <li><a href="reports.php" class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">Reports</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])): ?>
                    <li><a href="update_employees.php" class="<?php echo $currentPage === 'update_employees.php' ? 'active' : ''; ?>">Employees</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])): ?>
                    <li>
                        <a href="approve_users.php" class="<?php echo $currentPage === 'approve_users.php' ? 'active' : ''; ?>">
                            Approve Users
                            <?php
                            // Show count of pending users (only if we're on a page that already loaded UserModel)
                            if (class_exists('UserModel')) {
                                try {
                                    $userModel = new UserModel();
                                    $pendingCount = count($userModel->getPendingUsers());
                                    if ($pendingCount > 0) {
                                        echo "<span style='background: #ff4444; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.8em; margin-left: 5px;'>$pendingCount</span>";
                                    }
                                } catch (Exception $e) {
                                    // Silently fail if UserModel not available
                                }
                            }
                            ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (hasRole(['Pipe Fitter/Helper'])): ?>
                    <li><a href="worker-form.php" class="<?php echo $currentPage === 'worker-form.php' ? 'active' : ''; ?>">My Production</a></li>
                <?php endif; ?>
            </ul>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($userRole); ?>)</span>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

