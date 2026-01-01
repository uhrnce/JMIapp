/**
 * Dashboard JavaScript
 */

let trendsChart, targetVsActualChart, componentChart;

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    // Refresh data every 5 minutes
    setInterval(loadDashboardData, 300000);
});

async function loadDashboardData() {
    await loadStats();
    await loadTrends();
    await loadTargetVsActual();
    await loadComponentWise();
    await loadRecentActivity();
    await loadAlerts();
}

async function loadStats() {
    try {
        const response = await fetch('api/dashboard.php?action=stats');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('todaysOutput').textContent = result.data.todays_output || 0;
            document.getElementById('ongoingProjects').textContent = result.data.ongoing_projects || 0;
            document.getElementById('pendingInspections').textContent = result.data.pending_inspections || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadTrends() {
    try {
        const response = await fetch('api/dashboard.php?action=trends&days=7');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const labels = result.data.map(item => formatDate(item.date));
            const targetData = result.data.map(item => parseInt(item.target) || 0);
            const actualData = result.data.map(item => parseInt(item.actual) || 0);
            
            const ctx = document.getElementById('trendsChart').getContext('2d');
            
            if (trendsChart) {
                trendsChart.destroy();
            }
            
            trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Target',
                        data: targetData,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Actual',
                        data: actualData,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading trends:', error);
    }
}

async function loadTargetVsActual() {
    try {
        const response = await fetch('api/dashboard.php?action=target-vs-actual');
        const result = await response.json();
        
        if (result.success) {
            const target = parseInt(result.data.target) || 0;
            const actual = parseInt(result.data.actual) || 0;
            
            const ctx = document.getElementById('targetVsActualChart').getContext('2d');
            
            if (targetVsActualChart) {
                targetVsActualChart.destroy();
            }
            
            targetVsActualChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Target', 'Actual'],
                    datasets: [{
                        label: 'Quantity',
                        data: [target, actual],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(37, 99, 235, 0.8)'
                        ],
                        borderColor: [
                            'rgb(239, 68, 68)',
                            'rgb(37, 99, 235)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading target vs actual:', error);
    }
}

async function loadComponentWise() {
    try {
        const response = await fetch('api/dashboard.php?action=component-wise');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const labels = result.data.map(item => item.component_name);
            const data = result.data.map(item => parseInt(item.total) || 0);
            
            const ctx = document.getElementById('componentChart').getContext('2d');
            
            if (componentChart) {
                componentChart.destroy();
            }
            
            componentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(251, 146, 60, 0.8)',
                            'rgba(168, 85, 247, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading component-wise data:', error);
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch('api/dashboard.php?action=recent-activity&limit=10');
        const result = await response.json();
        
        const tbody = document.getElementById('recentActivityTable');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => `
                <tr>
                    <td>${formatDate(item.production_date)}</td>
                    <td>${item.employee_name}</td>
                    <td>${item.component_name}</td>
                    <td>${item.target_quantity}</td>
                    <td>${item.actual_quantity}</td>
                    <td>
                        <span class="badge ${getPerformanceBadgeClass(item.performance_percentage)}">
                            ${item.performance_percentage}%
                        </span>
                    </td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(item.status)}">
                            ${item.status}
                        </span>
                    </td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(item.qc_status)}">
                            ${item.qc_status}
                        </span>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No recent activity</td></tr>';
        }
    } catch (error) {
        console.error('Error loading recent activity:', error);
        document.getElementById('recentActivityTable').innerHTML = 
            '<tr><td colspan="8" class="text-center">Error loading data</td></tr>';
    }
}

async function loadAlerts() {
    try {
        const response = await fetch('api/dashboard.php?action=alerts');
        const result = await response.json();
        
        const alertsCard = document.getElementById('alertsCard');
        const alertsList = document.getElementById('alertsList');
        
        if (result.success && result.data.length > 0) {
            alertsCard.style.display = 'block';
            alertsList.innerHTML = result.data.map(alert => `
                <div class="message message-${alert.type}" style="margin-bottom: 10px;">
                    ${alert.message}
                </div>
            `).join('');
        } else {
            alertsCard.style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading alerts:', error);
    }
}

