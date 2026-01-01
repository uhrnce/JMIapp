<?php
/**
 * Inspections & Quality Control Page
 */

$pageTitle = 'Inspections & Quality Control';
require_once __DIR__ . '/includes/header.php';

if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<div class="container">
    <div class="page-header">
        <h1>Inspections & Quality Control</h1>
        <p>Manage quality control inspections and checklists</p>
    </div>
    
    <!-- Summary Cards -->
    <div class="stats-grid" id="inspectionSummary">
        <!-- Will be populated by JavaScript -->
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <div class="filters-row">
            <div class="filter-group">
                <label>Date</label>
                <input type="date" id="filterDate" class="form-control">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Passed">Passed</option>
                    <option value="Failed">Failed</option>
                    <option value="Rework Required">Rework Required</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Component</label>
                <select id="filterComponent" class="form-control">
                    <option value="">All Components</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Batch ID</label>
                <input type="text" id="filterBatchId" class="form-control" placeholder="Search batch ID...">
            </div>
            <div class="filter-group">
                <button type="button" class="btn btn-primary" onclick="loadInspections()">Filter</button>
                <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>
    
    <!-- Add New Button -->
    <div style="margin-bottom: 20px;">
        <button type="button" class="btn btn-primary" onclick="showInspectionModal()">+ Add Inspection</button>
    </div>
    
    <!-- Inspections Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Inspection Records</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Batch ID</th>
                        <th>Date</th>
                        <th>Component</th>
                        <th>Thread Quality</th>
                        <th>Pressure Test</th>
                        <th>Dimensions</th>
                        <th>Overall Status</th>
                        <th>Inspector</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inspectionsTable">
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

<!-- Inspection Modal -->
<div id="inspectionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Inspection</h2>
            <span class="close" onclick="closeInspectionModal()">&times;</span>
        </div>
        <form id="inspectionForm" onsubmit="saveInspection(event)">
            <input type="hidden" id="inspectionId" name="id">
            <div class="form-group">
                <label for="modalBatchId">Batch ID *</label>
                <input type="text" id="modalBatchId" name="batch_id" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="modalComponent">Component *</label>
                <select id="modalComponent" name="component_id" class="form-control" required>
                    <option value="">Select Component</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modalInspector">Inspector</label>
                <select id="modalInspector" name="inspector_id" class="form-control">
                    <option value="">Select Inspector</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modalDate">Inspection Date *</label>
                <input type="date" id="modalDate" name="inspection_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Quality Checks</label>
                <div class="row">
                    <div class="col-4">
                        <label for="modalThreadQuality">Thread Quality</label>
                        <select id="modalThreadQuality" name="thread_quality" class="form-control">
                            <option value="Pending">Pending</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label for="modalPressureTest">Pressure Test</label>
                        <select id="modalPressureTest" name="pressure_test" class="form-control">
                            <option value="Pending">Pending</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label for="modalDimensions">Dimensions</label>
                        <select id="modalDimensions" name="dimensions" class="form-control">
                            <option value="Pending">Pending</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="modalNotes">Notes</label>
                <textarea id="modalNotes" name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeInspectionModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/inspections.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

