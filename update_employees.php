<?php
/**
 * Update Employees with Real Names
 * This script allows you to update employee names and link them to users
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

// Only allow admins and managers
if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
    die('Unauthorized access');
}

require_once __DIR__ . '/models/EmployeeModel.php';
require_once __DIR__ . '/models/UserModel.php';

$employeeModel = new EmployeeModel();
$userModel = new UserModel();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_employee') {
        $employeeId = intval($_POST['employee_id']);
        $fullName = trim($_POST['full_name']);
        $employeeCode = trim($_POST['employee_code']);
        $position = trim($_POST['position']);
        $department = trim($_POST['department']);
        $userId = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
        
        if ($employeeId > 0 && !empty($fullName)) {
            // Update employee
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE employees SET full_name = ?, employee_code = ?, position = ?, department = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $fullName, $employeeCode, $position, $department, $employeeId);
            
            if ($stmt->execute()) {
                // If user_id is provided, update the link
                if ($userId) {
                    // Check if employees table has user_id column, if not we'll add it
                    $checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
                    if ($checkColumn->num_rows == 0) {
                        // Add user_id column
                        $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL AFTER id");
                        // Try to add foreign key, but ignore if it fails
                        try {
                            $conn->query("ALTER TABLE employees ADD CONSTRAINT fk_employee_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
                        } catch (Exception $e) {
                            // Foreign key might already exist, ignore
                        }
                    }
                    
                    $stmt2 = $conn->prepare("UPDATE employees SET user_id = ? WHERE id = ?");
                    $stmt2->bind_param("ii", $userId, $employeeId);
                    $stmt2->execute();
                } else {
                    // If user_id is empty, remove the link
                    $checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
                    if ($checkColumn->num_rows > 0) {
                        $stmt2 = $conn->prepare("UPDATE employees SET user_id = NULL WHERE id = ?");
                        $stmt2->bind_param("i", $employeeId);
                        $stmt2->execute();
                    }
                }
                
                $message = "Employee updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating employee: " . $conn->error;
                $messageType = "error";
            }
            $conn->close();
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'add_employee') {
        $fullName = trim($_POST['full_name']);
        $employeeCode = trim($_POST['employee_code']);
        $position = trim($_POST['position']);
        $department = trim($_POST['department']);
        $userId = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
        
        if (!empty($fullName)) {
            $conn = getDBConnection();
            
            // Check if employees table has user_id column
            $checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
            if ($checkColumn->num_rows == 0) {
                $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL AFTER id");
                // Try to add foreign key, but ignore if it fails
                try {
                    $conn->query("ALTER TABLE employees ADD CONSTRAINT fk_employee_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
                } catch (Exception $e) {
                    // Foreign key might already exist, ignore
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO employees (full_name, employee_code, position, department, user_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("ssssi", $fullName, $employeeCode, $position, $department, $userId);
            
            if ($stmt->execute()) {
                $message = "Employee added successfully!";
                $messageType = "success";
            } else {
                $message = "Error adding employee: " . $conn->error;
                $messageType = "error";
            }
            $conn->close();
        }
    }
}

// Get all employees and users
$conn = getDBConnection();

// Check if user_id column exists, if not create it
$checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
if ($checkColumn->num_rows == 0) {
    // Add user_id column
    $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL AFTER id");
    // Add foreign key constraint if it doesn't exist
    try {
        $conn->query("ALTER TABLE employees ADD CONSTRAINT fk_employee_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    } catch (Exception $e) {
        // Foreign key might already exist or table might not support it, ignore
    }
}

// Now get employees with user links
$employees = $conn->query("SELECT e.*, u.username FROM employees e LEFT JOIN users u ON e.user_id = u.id ORDER BY e.full_name ASC")->fetch_all(MYSQLI_ASSOC);
$users = $userModel->getAllUsers();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employees - Production Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .employee-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .employee-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .employee-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .employee-item:last-child {
            border-bottom: none;
        }
        .employee-item:hover {
            background: #f8f9fa;
        }
        .btn-edit {
            padding: 5px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-edit:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Manage Employees</h1>
            <p>Update employee names and link them to user accounts</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Add New Employee Form -->
        <div class="employee-form">
            <h2>Add New Employee</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_employee">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Employee Code</label>
                        <input type="text" name="employee_code" placeholder="e.g., EMP001">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="position" placeholder="e.g., Pipe Fitter">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" placeholder="e.g., Production">
                    </div>
                </div>
                <div class="form-group">
                    <label>Link to User Account (Optional)</label>
                    <select name="user_id">
                        <option value="">-- Select User --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Link this employee to a user account so they can log in and submit production logs</small>
                </div>
                <button type="submit" class="btn btn-primary">Add Employee</button>
            </form>
        </div>
        
        <!-- Employee List -->
        <div class="employee-list">
            <h2>Current Employees</h2>
            <div class="employee-item" style="font-weight: bold; background: #f8f9fa;">
                <div>Name</div>
                <div>Code</div>
                <div>Position</div>
                <div>Department</div>
                <div>Linked User</div>
                <div>Action</div>
            </div>
            <?php foreach ($employees as $emp): ?>
                <div class="employee-item">
                    <div><strong><?php echo htmlspecialchars($emp['full_name']); ?></strong></div>
                    <div><?php echo htmlspecialchars($emp['employee_code']); ?></div>
                    <div><?php echo htmlspecialchars($emp['position']); ?></div>
                    <div><?php echo htmlspecialchars($emp['department']); ?></div>
                    <div><?php echo $emp['username'] ? htmlspecialchars($emp['username']) : '<em>Not linked</em>'; ?></div>
                    <div>
                        <button class="btn-edit" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($emp)); ?>)">Edit</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Edit Modal -->
        <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: white; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%;">
                <h2>Edit Employee</h2>
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="update_employee">
                    <input type="hidden" name="employee_id" id="edit_employee_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" id="edit_full_name" required>
                        </div>
                        <div class="form-group">
                            <label>Employee Code</label>
                            <input type="text" name="employee_code" id="edit_employee_code">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="position" id="edit_position">
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" name="department" id="edit_department">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Link to User Account</label>
                        <select name="user_id" id="edit_user_id">
                            <option value="">-- Not Linked --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function editEmployee(emp) {
            document.getElementById('edit_employee_id').value = emp.id;
            document.getElementById('edit_full_name').value = emp.full_name || '';
            document.getElementById('edit_employee_code').value = emp.employee_code || '';
            document.getElementById('edit_position').value = emp.position || '';
            document.getElementById('edit_department').value = emp.department || '';
            document.getElementById('edit_user_id').value = emp.user_id || '';
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
    
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

