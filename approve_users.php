<?php
/**
 * User Approval Page
 * Allows Owners, Operations Managers, and Admins to approve/reject pending user registrations
 */

$pageTitle = 'Approve Users';
require_once __DIR__ . '/includes/header.php';

// Allow: Owner/General Manager, Operations Manager, and Admin
// Operations Manager CAN approve users
if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
    header('Location: dashboard.php');
    exit();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/UserModel.php';

$userModel = new UserModel();

// Make UserModel available for header
if (!class_exists('UserModel')) {
    require_once __DIR__ . '/models/UserModel.php';
}
$message = '';
$messageType = '';

// Handle approval/rejection/deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId > 0) {
        if ($action === 'approve') {
            if ($userModel->approveUser($userId)) {
                $message = 'User approved successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error approving user.';
                $messageType = 'error';
            }
        } elseif ($action === 'reject') {
            if ($userModel->rejectUser($userId)) {
                $message = 'User rejected.';
                $messageType = 'error';
            } else {
                $message = 'Error rejecting user.';
                $messageType = 'error';
            }
        } elseif ($action === 'delete') {
            // Prevent deleting yourself
            if ($userId == $_SESSION['user_id']) {
                $message = 'You cannot delete your own account.';
                $messageType = 'error';
            } else {
                if ($userModel->deleteUser($userId)) {
                    $message = 'User deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting user. You cannot delete your own account.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get pending users
$pendingUsers = $userModel->getPendingUsers();
$allUsers = $userModel->getAllUsers();
?>
<div class="container">
    <div class="page-header">
        <h1>User Approval</h1>
        <p>Review and approve pending user registrations</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message message-<?php echo $messageType; ?> show" style="margin-bottom: 20px; padding: 15px; border-radius: 4px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Pending Users -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Pending Approvals (<?php echo count($pendingUsers); ?>)</h2>
        </div>
        <?php if (count($pendingUsers) > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $user): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($user['role']); ?></span>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this user?')">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this user? They will not be able to login.')">
                                            ✗ Reject
                                        </button>
                                    </form>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline-block;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" style="background: #dc3545; border-color: #dc3545;">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 40px;">
                <p style="font-size: 1.2rem; color: #666;">No pending user approvals at this time.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- All Users Summary -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h2 class="card-title">All Users Summary</h2>
        </div>
        <div class="table-container">
            <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php foreach ($allUsers as $user): ?>
                        <?php
                        // Check if user is pending (pending, NULL, or empty)
                        $isPending = ($user['status'] === 'pending' || $user['status'] === '' || $user['status'] === null);
                        $statusClass = 'badge-secondary';
                        $statusText = $user['status'] ?: 'Pending';
                        
                        if ($user['status'] === 'active') {
                            $statusClass = 'badge-success';
                            $statusText = 'Active';
                        } elseif ($isPending) {
                            $statusClass = 'badge-warning';
                            $statusText = 'Pending';
                        } elseif ($user['status'] === 'inactive') {
                            $statusClass = 'badge-danger';
                            $statusText = 'Inactive';
                        }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($statusText); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <?php if ($isPending): ?>
                                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this user?')">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this user? They will not be able to login.')">
                                            ✗ Reject
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="background: #dc3545; border-color: #dc3545;">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 0.9em;">(Your account)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(username) {
    return confirm('⚠️ WARNING: Are you sure you want to PERMANENTLY DELETE user "' + username + '"?\n\nThis action cannot be undone and will:\n- Delete the user account\n- Remove all associated data\n- The user will no longer be able to login\n\nClick OK to confirm, or Cancel to abort.');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

