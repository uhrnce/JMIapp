/**
 * Employee Performance JavaScript
 */

let topPerformersChart, performanceDistributionChart;

document.addEventListener('DOMContentLoaded', function() {
    loadPerformanceData();
});

async function loadPerformanceData() {
    const period = document.getElementById('periodFilter').value;
    await loadTopPerformers(period);
    await loadPerformanceDistribution(period);
    await loadPerformanceTable(period);
}

async function loadTopPerformers(period) {
    try {
        const response = await fetch(`api/performance.php?action=top-performers&limit=10&period=${period}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const labels = result.data.map(item => item.full_name);
            const data = result.data.map(item => parseFloat(item.avg_performance).toFixed(2));
            
            const ctx = document.getElementById('topPerformersChart').getContext('2d');
            
            if (topPerformersChart) {
                topPerformersChart.destroy();
            }
            
            topPerformersChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Performance %',
                        data: data,
                        backgroundColor: 'rgba(37, 99, 235, 0.8)',
                        borderColor: 'rgb(37, 99, 235)',
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
                            beginAtZero: true,
                            max: 120
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading top performers:', error);
    }
}

async function loadPerformanceDistribution(period) {
    try {
        const response = await fetch(`api/performance.php?action=distribution&period=${period}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const labels = result.data.map(item => item.performance_range);
            const data = result.data.map(item => parseInt(item.count));
            
            const ctx = document.getElementById('performanceDistributionChart').getContext('2d');
            
            if (performanceDistributionChart) {
                performanceDistributionChart.destroy();
            }
            
            performanceDistributionChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(100, 116, 139, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading performance distribution:', error);
    }
}

async function loadPerformanceTable(period) {
    try {
        const response = await fetch(`api/performance.php?action=list&period=${period}`);
        const result = await response.json();
        
        const tbody = document.getElementById('performanceTable');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const avgPerformance = parseFloat(item.avg_performance);
                const performanceClass = getPerformanceBadgeClass(avgPerformance);
                const targetMetPercentage = item.total_records > 0 
                    ? ((item.target_met_count / item.total_records) * 100).toFixed(1) 
                    : 0;
                
                return `
                    <tr>
                        <td>
                            <strong>${item.full_name}</strong><br>
                            <small>${item.employee_code}</small>
                        </td>
                        <td>${item.days_worked}</td>
                        <td>${item.total_target}</td>
                        <td>${item.total_actual}</td>
                        <td>
                            <span class="badge ${performanceClass}">
                                ${avgPerformance.toFixed(2)}%
                            </span>
                        </td>
                        <td>${targetMetPercentage}% (${item.target_met_count}/${item.total_records})</td>
                        <td>${item.total_records}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No performance data available</td></tr>';
        }
    } catch (error) {
        console.error('Error loading performance table:', error);
        document.getElementById('performanceTable').innerHTML = 
            '<tr><td colspan="7" class="text-center">Error loading data</td></tr>';
    }
}

