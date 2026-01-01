-- Sample Data for Production Management System
-- Run this after creating the database structure

USE production_management;

-- Insert sample users (password for all: 'password123' - hashed with password_hash PHP function)
-- Default password hash for 'password123': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (username, password_hash, role, status) VALUES
('owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Owner/General Manager', 'active'),
('ops_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operations Manager', 'active'),
('supervisor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Supervisor', 'active'),
('fitter1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pipe Fitter/Helper', 'active'),
('fitter2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pipe Fitter/Helper', 'active'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'active');

-- Insert sample employees
INSERT INTO employees (employee_code, full_name, position, department, status) VALUES
('EMP001', 'John Doe', 'Pipe Fitter', 'Production', 'active'),
('EMP002', 'Jane Smith', 'Pipe Fitter', 'Production', 'active'),
('EMP003', 'Mike Johnson', 'Helper', 'Production', 'active'),
('EMP004', 'Sarah Williams', 'Supervisor', 'Production', 'active'),
('EMP005', 'David Brown', 'Pipe Fitter', 'Production', 'active');

-- Insert sample components
INSERT INTO components (component_code, component_name, description, unit, status) VALUES
('COMP001', 'Pipe Fitting 1/2"', 'Half inch pipe fitting', 'pcs', 'active'),
('COMP002', 'Pipe Fitting 3/4"', 'Three quarter inch pipe fitting', 'pcs', 'active'),
('COMP003', 'Pipe Fitting 1"', 'One inch pipe fitting', 'pcs', 'active'),
('COMP004', 'Elbow Joint 90°', '90 degree elbow joint', 'pcs', 'active'),
('COMP005', 'T-Joint', 'T-shaped pipe joint', 'pcs', 'active');

-- Insert sample production logs (last 30 days)
INSERT INTO production_logs (employee_id, component_id, production_date, target_quantity, actual_quantity, performance_percentage, status, qc_status, created_by) VALUES
(1, 1, CURDATE(), 100, 95, 95.00, 'Completed', 'Passed', 3),
(1, 2, CURDATE(), 80, 85, 106.25, 'Completed', 'Passed', 3),
(2, 1, CURDATE(), 100, 100, 100.00, 'Completed', 'Passed', 3),
(2, 3, CURDATE(), 90, 88, 97.78, 'Completed', 'Passed', 3),
(3, 4, CURDATE(), 70, 65, 92.86, 'In-progress', 'Pending', 3),
(5, 2, CURDATE(), 80, 75, 93.75, 'Completed', 'Passed', 3),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 100, 98, 98.00, 'Completed', 'Passed', 3),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 80, 82, 102.50, 'Completed', 'Passed', 3),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 90, 92, 102.22, 'Completed', 'Passed', 3),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 100, 97, 97.00, 'Completed', 'Passed', 3);

-- Insert sample inspections
INSERT INTO inspections (batch_id, component_id, inspector_id, inspection_date, thread_quality, pressure_test, dimensions, overall_status, notes) VALUES
('BATCH001', 1, 3, CURDATE(), 'Pass', 'Pass', 'Pass', 'Passed', 'All quality checks passed'),
('BATCH002', 2, 3, CURDATE(), 'Pass', 'Pass', 'Pass', 'Passed', 'Good quality'),
('BATCH003', 3, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Pass', 'Fail', 'Pass', 'Failed', 'Pressure test failed, requires rework'),
('BATCH004', 1, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Pass', 'Pass', 'Pass', 'Passed', 'Quality approved'),
('BATCH005', 4, 3, CURDATE(), 'Pending', 'Pending', 'Pending', 'Pending', 'Inspection in progress');

-- Insert sample projects
INSERT INTO projects (project_code, project_name, status, start_date, end_date) VALUES
('PROJ001', 'Residential Building Project A', 'Ongoing', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
('PROJ002', 'Commercial Complex B', 'Ongoing', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 45 DAY)),
('PROJ003', 'Industrial Plant C', 'Planning', NULL, NULL);

