<?php
/**
 * Reports & Analytics Page
 */

$pageTitle = 'Reports & Analytics';
require_once __DIR__ . '/includes/header.php';

if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Admin'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<div class="container">
    <div class="page-header">
        <h1>Reports & Analytics</h1>
        <p>Generate and export production and inspection reports</p>
    </div>
    
    <!-- Report Options -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h2 class="card-title">Generate Report</h2>
        </div>
        <div class="row">
            <div class="col-4">
                <div class="form-group">
                    <label>Report Type</label>
                    <select id="reportType" class="form-control">
                        <option value="production">Production Report</option>
                        <option value="inspection">Inspection Report</option>
                        <option value="performance">Performance Report</option>
                    </select>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label>Period</label>
                    <select id="reportPeriod" class="form-control">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected>Monthly</option>
                    </select>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="reportDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="generateReport()">Generate Report</button>
            <button type="button" class="btn btn-success" onclick="exportPDF()">Export PDF</button>
            <button type="button" class="btn btn-warning" onclick="exportExcel()">Export Excel</button>
        </div>
    </div>
    
    <!-- Report Preview -->
    <div class="card" id="reportPreview" style="display: none;">
        <div class="card-header">
            <h2 class="card-title" id="reportTitle">Report Preview</h2>
        </div>
        <div id="reportContent">
            <!-- Report content will be loaded here -->
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="assets/js/reports.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

