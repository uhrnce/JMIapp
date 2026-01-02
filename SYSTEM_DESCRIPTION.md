# JMI Industrial Production Management System - Documentation

## 1. System Identity

### System Name
**JMI Industrial Production Management System** (also referred to as "JMI Industrial System")

### Type of System
**Web-based Application**

### Business/Organization Name
**JMI Industrial** (Manufacturing/Industrial Company specializing in pipe fitting and component production)

### Main Purpose of the System
The system is designed to manage and monitor production operations for an industrial manufacturing company, specifically focusing on pipe fitting and component production. It automates production tracking, employee performance monitoring, quality control inspections, document management, and reporting processes.

---

## 2. General Objectives (3.1)

The JMI Industrial Production Management System addresses the critical need for efficient production monitoring and management in a manufacturing environment. The system is being developed to replace manual paper-based tracking methods and scattered data management with a centralized, automated solution. It automates the entire production workflow from daily production logging, performance calculation, quality control inspections, to comprehensive reporting and analytics. This system eliminates time-consuming manual calculations, reduces human errors in data entry and performance tracking, and provides real-time visibility into production metrics, employee performance, and quality control status for better decision-making and operational efficiency.

---

## 3. Specific Objectives (3.2)

1. **Improve Production Monitoring Speed and Accuracy**
   - Automate daily production log entry and tracking
   - Real-time calculation of performance percentages (Actual vs Target)
   - Instant status updates (In-progress, Completed)

2. **Automate Performance Computations**
   - Automatic calculation of employee performance percentages
   - Daily, Weekly, and Monthly performance aggregation
   - Target met percentage tracking

3. **Improve Quality Control Monitoring**
   - Digital inspection checklist system
   - Automated overall status calculation based on inspection criteria
   - Batch ID tracking for traceability

4. **Improve Report Generation**
   - Automated generation of Daily, Weekly, and Monthly reports
   - Export capabilities to PDF and Excel/CSV formats
   - Real-time analytics and visualization using charts

5. **Improve Data Retrieval**
   - Advanced filtering system (by date, status, component, employee)
   - Quick search functionality across production logs
   - Organized document management with category-based filtering

6. **Enhance Employee Performance Tracking**
   - Track individual employee performance over time
   - Identify top performers automatically
   - Performance distribution analysis

---

## 4. System Alternatives (3.3)

**Recommended: 2-3 Alternatives**

### Alternative 1: Web-Based Centralized System (Recommended/Current Implementation)
### Alternative 2: Desktop Standalone Application
### Alternative 3: Hybrid Cloud-Based System

---

## 5. Alternative Description

### Alternative 1: Web-Based Centralized System

**System Type:** Web Application (Client-Server Architecture)

**Deployment Location:** Local server (XAMPP environment) - Can be deployed on office server or cloud

**User Access:** Users access the system through web browsers (Chrome, Firefox, Edge, etc.) from any device (PCs, laptops, tablets) connected to the network

**Key Advantages:**
- Centralized data storage and management
- Multi-user access from multiple devices simultaneously
- No installation required on client machines (only browser needed)
- Easy updates and maintenance (update once, all users benefit)
- Cross-platform compatibility
- Real-time data synchronization across all users
- Lower deployment and maintenance costs

---

### Alternative 2: Desktop Standalone Application

**System Type:** Standalone Desktop Application

**Deployment Location:** Installed on individual office computers/laptops

**User Access:** Users access the system through installed desktop application on their local machines

**Key Advantages:**
- Works offline without internet connection
- Potentially faster performance (local processing)
- No server maintenance required
- Complete control over the application environment

---

### Alternative 3: Hybrid Cloud-Based System

**System Type:** Cloud-Based Web Application with Mobile Support

**Deployment Location:** Cloud server (AWS, Azure, or similar cloud platform)

**User Access:** Users access through web browsers or mobile applications from anywhere with internet connection

**Key Advantages:**
- Accessible from anywhere (remote work capability)
- Automatic backups and data redundancy
- Scalable infrastructure
- No local server maintenance
- Mobile device support for field workers
- Automatic software updates

---

## 6. Users & Access Levels

### User Roles and Permissions:

1. **Owner/General Manager**
   - **Access:** Full access to all modules
   - **Capabilities:**
     - View dashboard with all metrics
     - Access Production Monitoring (view, add, edit, delete)
     - View Employee Performance reports
     - Access Inspections module
     - Manage Documents (upload, view, download, delete)
     - Generate and export Reports
     - Manage Employees
     - Approve/Reject new user registrations

2. **Operations Manager**
   - **Access:** Production, Performance, Inspections, Documents, Reports, Employee Management, User Approval
   - **Capabilities:**
     - View dashboard
     - Manage Production logs
     - View Performance analytics
     - Conduct and manage Inspections
     - Upload and manage Documents
     - Generate Reports
     - Manage Employee records
     - Approve new users

3. **Supervisor**
   - **Access:** Production, Performance, Inspections
   - **Capabilities:**
     - View dashboard
     - Add, edit, and view Production logs
     - View Employee Performance data
     - Conduct Inspections and update inspection records
     - View (but not manage) Documents

4. **Pipe Fitter/Helper**
   - **Access:** Worker Production Form only
   - **Capabilities:**
     - Access simplified production entry form
     - Submit daily production data (target and actual quantities)
     - View their own performance percentage
     - Cannot access other modules or view other employees' data

5. **Admin**
   - **Access:** Full access to all modules (same as Owner/General Manager)
   - **Capabilities:**
     - Complete system administration
     - All capabilities of Owner/General Manager
     - System configuration and maintenance

---

## 7. System Flow (Narrative Only)

### User Authentication Flow:
Users access the system through a web browser and are presented with a login page. They enter their username (email or phone number) and password. The system authenticates credentials against the database, verifies the user's active status, and creates a session. Upon successful authentication, users are redirected to the dashboard based on their role. If authentication fails, an error message is displayed.

### Production Transaction Processing:
Production data entry begins when a Supervisor or authorized user navigates to the Production module. They select an employee, choose a component, enter the production date, and input target and actual quantities. The system automatically calculates the performance percentage (Actual/Target × 100) and determines the status (color-coded: Green for target met, Yellow for near target, Red for below target). For Pipe Fitters/Helpers, they use a simplified worker form that provides the same functionality with a streamlined interface. The production log is saved to the database with timestamps and creator information. All production data is immediately available for viewing, filtering, and reporting.

### Inspection Processing:
Quality control inspections are initiated by Supervisors, Operations Managers, or authorized personnel. They navigate to the Inspections module, create a new inspection record with a unique Batch ID, select the component being inspected, and assign an inspector. The inspector completes the digital checklist, marking each criterion (Thread Quality, Pressure Test, Dimensions) as Pass, Fail, or Pending. The system automatically calculates the overall inspection status based on the individual criteria results. Inspection records are saved with dates and can be filtered by batch ID, component, date, or status.

### Report Generation:
Reports are generated through the Reports module, accessible to Owners, Operations Managers, and Admins. Users select the report type (Daily, Weekly, or Monthly) and specify date ranges. The system queries the database to aggregate production data, performance metrics, and inspection results for the selected period. Data is compiled and displayed in tabular format with summary statistics. Users can export reports to PDF format using jsPDF library or to Excel/CSV format for further analysis. The system also provides visual analytics through interactive charts showing production trends, target vs actual comparisons, and component-wise output.

### Document Management Flow:
Authorized users (Owners, Operations Managers, Admins) can upload documents through the Documents module using drag-and-drop or file selection. Documents are categorized (Certificate, Report, Manual, Contract, Inspection) and can be assigned to specific projects. The system validates file types and sizes, stores files in the uploads directory, and records metadata in the database. Users can search, filter by category or project, preview, and download documents. All document activities are logged with uploader information and timestamps.

---

## 8. Physical Elements (3.3.x.2)

### Hardware Requirements:

**Client Devices:**
- PCs or Laptops (Windows, macOS, or Linux)
- Minimum: 4GB RAM, 2GHz processor
- Recommended: 8GB RAM, 3GHz+ processor

**Server (for local deployment):**
- Server computer or dedicated workstation
- Minimum: 8GB RAM, 4-core processor, 500GB storage
- Recommended: 16GB RAM, 8-core processor, 1TB+ storage

**Printer:** Yes (for printing reports and documents)

**Internet Requirement:** 
- For local network deployment: Local network connection required
- For cloud deployment: Internet connection required
- For standalone desktop: Internet not required (optional for updates)

### Software Requirements:

**Operating System:**
- Server: Windows Server, Linux (Ubuntu/CentOS), or macOS Server
- Client: Windows 10/11, macOS, or Linux (any modern distribution)

**Programming Language / Framework:**
- Backend: PHP 7.4 or higher
- Frontend: HTML5, CSS3, JavaScript (ES6+)
- Web Server: Apache (via XAMPP) or Nginx
- Database: MySQL 5.7 or higher / MariaDB

**Database:**
- MySQL 5.7+ or MariaDB 10.3+
- Database name: `production_management`

**Browser (Web-based):**
- Google Chrome (recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari (macOS/iOS)
- Any modern browser with JavaScript enabled

**Additional Software:**
- XAMPP (for local development/deployment) - includes Apache, MySQL, PHP
- phpMyAdmin (for database management, included in XAMPP)

---

## 9. Database Overview (3.3.x.2.2)

### Main Database Tables/Entities:

1. **users**
   - Stores user authentication information
   - Fields: id, username, password_hash, role, status, created_at
   - Roles: Owner/General Manager, Operations Manager, Supervisor, Pipe Fitter/Helper, Admin

2. **employees**
   - Stores employee master data
   - Fields: id, employee_code, full_name, position, department, status, created_at

3. **components**
   - Stores product/component master data
   - Fields: id, component_code, component_name, description, unit, status, created_at

4. **production_logs**
   - Stores daily production records
   - Fields: id, employee_id, component_id, production_date, target_quantity, actual_quantity, performance_percentage, status, qc_status, notes, created_by, created_at, updated_at
   - Links to: employees, components, users

5. **inspections**
   - Stores quality control inspection records
   - Fields: id, batch_id, component_id, inspector_id, inspection_date, thread_quality, pressure_test, dimensions, overall_status, notes, created_at, updated_at
   - Links to: components, users (inspector)

6. **documents**
   - Stores document metadata and file information
   - Fields: id, document_name, file_path, file_type, file_size, category, project_id, description, uploaded_by, created_at
   - Links to: users (uploader)
   - Categories: Certificate, Report, Manual, Contract, Inspection

7. **projects**
   - Stores project information (optional, for better organization)
   - Fields: id, project_code, project_name, status, start_date, end_date, created_at
   - Status: Planning, Ongoing, Completed, On Hold

---

## Additional Notes

- All timestamps are stored in UTC and converted to local timezone (Asia/Manila) for display
- File uploads are stored in the `uploads/` directory with unique filenames
- Password security uses PHP's `password_hash()` with bcrypt algorithm
- Session management ensures secure user authentication
- Database uses InnoDB engine with foreign key constraints for data integrity
- All user inputs are sanitized to prevent SQL injection and XSS attacks

