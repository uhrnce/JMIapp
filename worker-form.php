<?php
/**
 * Worker Production Form
 * Simple and fast UI for Pipe Fitters/Helpers
 */

$pageTitle = 'My Production';
require_once __DIR__ . '/includes/header.php';

// Only allow Pipe Fitters/Helpers
if (!hasRole(['Pipe Fitter/Helper'])) {
    header('Location: dashboard.php');
    exit();
}

// Get current user's employee record
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/EmployeeModel.php';
require_once __DIR__ . '/models/ComponentModel.php';

$employeeModel = new EmployeeModel();
$componentModel = new ComponentModel();

// Find employee by user_id (proper linking)
$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Check if user_id column exists in employees table
$checkColumn = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
if ($checkColumn->num_rows == 0) {
    // Add user_id column if it doesn't exist
    $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL AFTER id");
    $conn->query("ALTER TABLE employees ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
}

// Try to find employee by user_id first
$stmt = $conn->prepare("SELECT * FROM employees WHERE user_id = ? AND status = 'active' LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentEmployee = $result->fetch_assoc();

// If not found by user_id, try matching by username (fallback)
if (!$currentEmployee) {
    $username = $_SESSION['username'];
    $allEmployees = $employeeModel->getAllEmployees();
    
    foreach ($allEmployees as $emp) {
        if (stripos($emp['employee_code'], $username) !== false || 
            stripos($emp['full_name'], $username) !== false) {
            $currentEmployee = $emp;
            break;
        }
    }
}

$conn->close();

$components = $componentModel->getAllComponents();
?>
<div class="container">
    <div class="page-header">
        <h1>My Production Log</h1>
        <p>Enter your daily production data</p>
    </div>
    
    <?php if ($currentEmployee): ?>
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h2 class="card-title">Employee Information</h2>
        </div>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($currentEmployee['full_name']); ?></p>
        <p><strong>Code:</strong> <?php echo htmlspecialchars($currentEmployee['employee_code']); ?></p>
        <p><strong>Position:</strong> <?php echo htmlspecialchars($currentEmployee['position']); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Production Form -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Add Production Entry</h2>
        </div>
        <form id="workerProductionForm" onsubmit="saveWorkerProduction(event)">
            <input type="hidden" id="employeeId" value="<?php echo $currentEmployee ? $currentEmployee['id'] : ''; ?>">
            
            <div class="form-group">
                <label for="component">Component *</label>
                <select id="component" name="component_id" class="form-control" required>
                    <option value="">Select Component</option>
                    <?php foreach ($components as $comp): ?>
                        <option value="<?php echo $comp['id']; ?>">
                            <?php echo htmlspecialchars($comp['component_name']); ?> (<?php echo htmlspecialchars($comp['component_code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="productionDate">Production Date *</label>
                <input type="date" id="productionDate" name="production_date" class="form-control" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="target">Target Quantity *</label>
                        <input type="number" id="target" name="target_quantity" class="form-control" 
                               min="0" required oninput="updatePerformancePreview()">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="actual">Actual Quantity *</label>
                        <input type="number" id="actual" name="actual_quantity" class="form-control" 
                               min="0" required oninput="updatePerformancePreview()">
                    </div>
                </div>
            </div>
            
            <!-- Performance Preview -->
            <div class="form-group">
                <label>Performance Preview</label>
                <div id="performancePreview" style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white; text-align: center;">
                    <div style="font-size: 3rem; font-weight: bold; margin-bottom: 10px;" id="performanceValue">0%</div>
                    <div style="font-size: 1.2rem;" id="performanceStatus">Enter target and actual quantities</div>
                    <div style="margin-top: 15px; font-size: 0.9rem;">
                        Target: <span id="previewTarget">0</span> | Actual: <span id="previewActual">0</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="In-progress">In-progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block" style="padding: 15px; font-size: 1.1rem;">
                    Submit Production Log
                </button>
            </div>
        </form>
    </div>
    
    <!-- Recent Entries -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h2 class="card-title">My Recent Entries</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Component</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Performance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="recentEntriesTable">
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/worker-form.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

