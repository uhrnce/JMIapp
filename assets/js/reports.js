/**
 * Reports JavaScript
 */

let currentReportData = null;
let currentReportType = null;

async function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const reportPeriod = document.getElementById('reportPeriod').value;
    const reportDate = document.getElementById('reportDate').value;
    
    currentReportType = reportType;
    
    try {
        const response = await fetch(`api/reports.php?action=${reportType}&period=${reportPeriod}&date=${reportDate}`);
        const result = await response.json();
        
        if (result.success) {
            currentReportData = result.data;
            displayReport(reportType, result);
        } else {
            showMessage('Error generating report', 'error');
        }
    } catch (error) {
        console.error('Error generating report:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

function displayReport(type, result) {
    const preview = document.getElementById('reportPreview');
    const content = document.getElementById('reportContent');
    const title = document.getElementById('reportTitle');
    
    preview.style.display = 'block';
    
    let html = `<div style="padding: 20px;">`;
    html += `<h3>${type.charAt(0).toUpperCase() + type.slice(1)} Report - ${result.period.charAt(0).toUpperCase() + result.period.slice(1)}</h3>`;
    html += `<p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>`;
    html += `<hr>`;
    
    if (type === 'production') {
        html += generateProductionReport(result.data);
    } else if (type === 'inspection') {
        html += generateInspectionReport(result.data);
    } else if (type === 'performance') {
        html += generatePerformanceReport(result.data);
    }
    
    html += `</div>`;
    content.innerHTML = html;
    
    // Scroll to report
    preview.scrollIntoView({ behavior: 'smooth' });
}

function generateProductionReport(data) {
    if (!data || data.length === 0) {
        return '<p>No production data available for the selected period.</p>';
    }
    
    let html = '<table class="table" style="margin-top: 20px;">';
    html += '<thead><tr><th>Date</th><th>Employee</th><th>Component</th><th>Target</th><th>Actual</th><th>Performance</th><th>Status</th></tr></thead>';
    html += '<tbody>';
    
    let totalTarget = 0;
    let totalActual = 0;
    
    data.forEach(item => {
        totalTarget += parseInt(item.target_quantity);
        totalActual += parseInt(item.actual_quantity);
        html += `<tr>
            <td>${formatDate(item.production_date)}</td>
            <td>${item.employee_name}</td>
            <td>${item.component_name}</td>
            <td>${item.target_quantity}</td>
            <td>${item.actual_quantity}</td>
            <td>${parseFloat(item.performance_percentage).toFixed(2)}%</td>
            <td>${item.status}</td>
        </tr>`;
    });
    
    html += '</tbody>';
    html += '<tfoot><tr><th colspan="3">Total</th><th>' + totalTarget + '</th><th>' + totalActual + '</th><th>' + 
            ((totalActual / totalTarget) * 100).toFixed(2) + '%</th><th></th></tr></tfoot>';
    html += '</table>';
    
    return html;
}

function generateInspectionReport(data) {
    if (!data || data.length === 0) {
        return '<p>No inspection data available for the selected period.</p>';
    }
    
    let html = '<table class="table" style="margin-top: 20px;">';
    html += '<thead><tr><th>Batch ID</th><th>Date</th><th>Component</th><th>Thread Quality</th><th>Pressure Test</th><th>Dimensions</th><th>Overall Status</th></tr></thead>';
    html += '<tbody>';
    
    data.forEach(item => {
        html += `<tr>
            <td>${item.batch_id}</td>
            <td>${formatDate(item.inspection_date)}</td>
            <td>${item.component_name}</td>
            <td>${item.thread_quality}</td>
            <td>${item.pressure_test}</td>
            <td>${item.dimensions}</td>
            <td>${item.overall_status}</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    
    return html;
}

function generatePerformanceReport(data) {
    if (!data || data.length === 0) {
        return '<p>No performance data available for the selected period.</p>';
    }
    
    let html = '<table class="table" style="margin-top: 20px;">';
    html += '<thead><tr><th>Employee</th><th>Days Worked</th><th>Total Target</th><th>Total Actual</th><th>Avg Performance</th><th>Target Met</th></tr></thead>';
    html += '<tbody>';
    
    data.forEach(item => {
        const targetMetPct = item.total_records > 0 
            ? ((item.target_met_count / item.total_records) * 100).toFixed(1) 
            : 0;
        html += `<tr>
            <td>${item.full_name} (${item.employee_code})</td>
            <td>${item.days_worked}</td>
            <td>${item.total_target}</td>
            <td>${item.total_actual}</td>
            <td>${parseFloat(item.avg_performance).toFixed(2)}%</td>
            <td>${targetMetPct}%</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    
    return html;
}

function exportPDF() {
    if (!currentReportData) {
        showMessage('Please generate a report first', 'warning');
        return;
    }
    
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    const reportType = document.getElementById('reportType').value;
    const reportPeriod = document.getElementById('reportPeriod').value;
    
    doc.setFontSize(16);
    doc.text(`${reportType.charAt(0).toUpperCase() + reportType.slice(1)} Report - ${reportPeriod}`, 14, 20);
    doc.setFontSize(10);
    doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 30);
    
    // Simple table export (you may want to use a library like jspdf-autotable for better tables)
    let y = 40;
    doc.setFontSize(8);
    
    if (currentReportType === 'production') {
        doc.text('Date | Employee | Component | Target | Actual | Performance', 14, y);
        y += 10;
        currentReportData.slice(0, 20).forEach(item => {
            doc.text(`${item.production_date} | ${item.employee_name} | ${item.component_name} | ${item.target_quantity} | ${item.actual_quantity} | ${item.performance_percentage}%`, 14, y);
            y += 7;
            if (y > 280) {
                doc.addPage();
                y = 20;
            }
        });
    }
    
    doc.save(`report_${reportType}_${reportPeriod}_${Date.now()}.pdf`);
    showMessage('PDF exported successfully', 'success');
}

function exportExcel() {
    if (!currentReportData) {
        showMessage('Please generate a report first', 'warning');
        return;
    }
    
    const reportType = document.getElementById('reportType').value;
    const reportPeriod = document.getElementById('reportPeriod').value;
    
    let csv = '';
    
    if (currentReportType === 'production') {
        csv = 'Date,Employee,Component,Target,Actual,Performance,Status\n';
        currentReportData.forEach(item => {
            csv += `${item.production_date},${item.employee_name},${item.component_name},${item.target_quantity},${item.actual_quantity},${item.performance_percentage},${item.status}\n`;
        });
    } else if (currentReportType === 'inspection') {
        csv = 'Batch ID,Date,Component,Thread Quality,Pressure Test,Dimensions,Overall Status\n';
        currentReportData.forEach(item => {
            csv += `${item.batch_id},${item.inspection_date},${item.component_name},${item.thread_quality},${item.pressure_test},${item.dimensions},${item.overall_status}\n`;
        });
    } else if (currentReportType === 'performance') {
        csv = 'Employee,Employee Code,Days Worked,Total Target,Total Actual,Avg Performance,Target Met\n';
        currentReportData.forEach(item => {
            const targetMetPct = item.total_records > 0 
                ? ((item.target_met_count / item.total_records) * 100).toFixed(1) 
                : 0;
            csv += `${item.full_name},${item.employee_code},${item.days_worked},${item.total_target},${item.total_actual},${item.avg_performance},${targetMetPct}%\n`;
        });
    }
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `report_${reportType}_${reportPeriod}_${Date.now()}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    
    showMessage('Excel/CSV exported successfully', 'success');
}

