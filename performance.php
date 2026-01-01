<?php
/**
 * Employee Performance Page
 */

$pageTitle = 'Employee Performance';
require_once __DIR__ . '/includes/header.php';

if (!hasRole(['Owner/General Manager', 'Operations Manager', 'Supervisor', 'Admin'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<div class="container">
    <div class="page-header">
        <h1>Employee Performance</h1>
        <p>Track and analyze employee performance metrics</p>
    </div>
    
    <!-- Period Filter -->
    <div class="filters">
        <div class="filters-row">
            <div class="filter-group">
                <label>Period</label>
                <select id="periodFilter" class="form-control" onchange="loadPerformanceData()">
                    <option value="daily">Today</option>
                    <option value="weekly">Last 7 Days</option>
                    <option value="monthly" selected>This Month</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Performance Summary -->
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Top Performers</h2>
                </div>
                <canvas id="topPerformersChart"></canvas>
            </div>
        </div>
        
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Performance Distribution</h2>
                </div>
                <canvas id="performanceDistributionChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Performance Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Employee Performance Summary</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Days Worked</th>
                        <th>Total Target</th>
                        <th>Total Actual</th>
                        <th>Avg Performance</th>
                        <th>Target Met</th>
                        <th>Total Records</th>
                    </tr>
                </thead>
                <tbody id="performanceTable">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/performance.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

