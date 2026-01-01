<?php
/**
 * Production Monitoring Page
 */

$pageTitle = 'Production Monitoring';
require_once __DIR__ . '/includes/header.php';

// Check role access
if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<div class="container">
    <div class="page-header">
        <h1>Production Monitoring</h1>
        <p>Manage and monitor daily production logs</p>
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <div class="filters-row">
            <div class="filter-group">
                <label>Date</label>
                <input type="date" id="filterDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">All Status</option>
                    <option value="In-progress">In-progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Component</label>
                <select id="filterComponent" class="form-control">
                    <option value="">All Components</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Employee</label>
                <select id="filterEmployee" class="form-control">
                    <option value="">All Employees</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="filterSearch" class="form-control" placeholder="Search...">
            </div>
            <div class="filter-group">
                <button type="button" class="btn btn-primary" onclick="loadProductionLogs()">Filter</button>
                <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>
    
    <!-- Add New Button -->
    <div style="margin-bottom: 20px;">
        <button type="button" class="btn btn-primary" onclick="showProductionModal()">+ Add Production Log</button>
    </div>
    
    <!-- Production Logs Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Production Logs</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Component</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Performance</th>
                        <th>Status</th>
                        <th>QC Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productionTable">
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Production Modal -->
<div id="productionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Production Log</h2>
            <span class="close" onclick="closeProductionModal()">&times;</span>
        </div>
        <form id="productionForm" onsubmit="saveProductionLog(event)">
            <input type="hidden" id="productionId" name="id">
            <div class="form-group">
                <label for="modalEmployee">Employee *</label>
                <select id="modalEmployee" name="employee_id" class="form-control" required>
                    <option value="">Select Employee</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modalComponent">Component *</label>
                <select id="modalComponent" name="component_id" class="form-control" required>
                    <option value="">Select Component</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modalDate">Production Date *</label>
                <input type="date" id="modalDate" name="production_date" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="modalTarget">Target Quantity *</label>
                        <input type="number" id="modalTarget" name="target_quantity" class="form-control" min="0" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="modalActual">Actual Quantity *</label>
                        <input type="number" id="modalActual" name="actual_quantity" class="form-control" min="0" required oninput="updatePerformancePreview()">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Performance Preview</label>
                <div id="performancePreview" style="padding: 10px; background: #f8fafc; border-radius: 6px;">
                    <span id="performanceValue">0%</span>
                    <span id="performanceBadge" class="badge badge-secondary" style="margin-left: 10px;">-</span>
                </div>
            </div>
            <div class="form-group">
                <label for="modalStatus">Status *</label>
                <select id="modalStatus" name="status" class="form-control" required>
                    <option value="In-progress">In-progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modalQCStatus">QC Status *</label>
                <select id="modalQCStatus" name="qc_status" class="form-control" required>
                    <option value="Pending">Pending</option>
                    <option value="Passed">Passed</option>
                    <option value="Failed">Failed</option>
                    <option value="Rework Required">Rework Required</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modalNotes">Notes</label>
                <textarea id="modalNotes" name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeProductionModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
    margin: 0;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: var(--text-secondary);
}

.close:hover {
    color: var(--text-primary);
}

.modal-content form {
    padding: 20px;
}

.text-center {
    text-align: center;
}
</style>

<script src="assets/js/production.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

