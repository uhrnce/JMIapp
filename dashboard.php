<?php
/**
 * Dashboard Page
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Overview of production activities and performance</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Today's Output</span>
                <div class="stat-card-icon" style="background-color: #dbeafe; color: #3b82f6;">
                    📊
                </div>
            </div>
            <div class="stat-card-value" id="todaysOutput">0</div>
            <div class="stat-card-change">Units produced today</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Ongoing Projects</span>
                <div class="stat-card-icon" style="background-color: #d1fae5; color: #10b981;">
                    🏗️
                </div>
            </div>
            <div class="stat-card-value" id="ongoingProjects">0</div>
            <div class="stat-card-change">Active projects</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Pending Inspections</span>
                <div class="stat-card-icon" style="background-color: #fef3c7; color: #f59e0b;">
                    🔍
                </div>
            </div>
            <div class="stat-card-value" id="pendingInspections">0</div>
            <div class="stat-card-change">Awaiting inspection</div>
        </div>
    </div>
    
    <!-- Alerts -->
    <div class="card" id="alertsCard" style="display: none;">
        <div class="card-header">
            <h2 class="card-title">Alerts & Notifications</h2>
        </div>
        <div id="alertsList"></div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Production Trends (Last 7 Days)</h2>
                </div>
                <canvas id="trendsChart"></canvas>
            </div>
        </div>
        
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Target vs Actual (This Month)</h2>
                </div>
                <canvas id="targetVsActualChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Component-wise Output -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Component-wise Output (This Month)</h2>
        </div>
        <canvas id="componentChart"></canvas>
    </div>
    
    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Production Activity</h2>
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
                    </tr>
                </thead>
                <tbody id="recentActivityTable">
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/dashboard.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

