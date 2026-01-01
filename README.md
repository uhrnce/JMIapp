# Production Management System

A comprehensive Production Management System built with HTML, CSS, JavaScript (frontend) and PHP + MySQL (backend) for XAMPP.

## Features

1. **User Authentication & Roles**
   - Login system with password hashing
   - Role-based access control (Owner/General Manager, Operations Manager, Supervisor, Pipe Fitter/Helper, Admin)
   - Session management

2. **Dashboard Module**
   - Overview cards (Today's output, Ongoing projects, Pending inspections)
   - Interactive charts using Chart.js
   - Recent production activity
   - Alerts & notifications

3. **Production Monitoring Module**
   - Daily production logs with CRUD operations
   - Performance percentage calculation
   - Filters (Date, Status, Component, Employee, Search)
   - Color-coded status display

4. **Employee Performance Module**
   - Performance tracking (Daily/Weekly/Monthly)
   - Top performers chart
   - Performance distribution analysis

5. **Inspection & Quality Control Module**
   - Digital inspection checklist
   - Thread quality, Pressure test, Dimensions checks
   - Batch ID tracking
   - Inspector assignment

6. **Document Management Module**
   - File upload with drag-and-drop support
   - Document categories (Certificate, Report, Manual, Contract, Inspection)
   - Preview, download, search, and filter

7. **Reports & Analytics Module**
   - Daily/Weekly/Monthly reports
   - Export to PDF (jsPDF) and Excel/CSV
   - Performance analytics

8. **Worker Production Form**
   - Simple and fast UI for Pipe Fitters/Helpers
   - Live performance percentage preview
   - Target vs Actual display

## Installation Instructions

### Prerequisites
- XAMPP installed on your system
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, etc.)

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to your system (default location: `C:\xampp` on Windows)
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project
1. Copy the entire project folder to `C:\xampp\htdocs\production_management`
   - Or place it in your preferred location and update the BASE_URL in `config/config.php`

2. Create the `uploads` directory:
   - Navigate to the project root
   - Create a folder named `uploads`
   - Set permissions to allow file uploads (777 on Linux/Mac, full control on Windows)

### Step 3: Database Setup
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database:
   - Click "New" in the left sidebar
   - Database name: `production_management`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. Import database structure:
   - Select the `production_management` database
   - Click "Import" tab
   - Choose file: `sql/database.sql`
   - Click "Go"

4. Import sample data (optional):
   - Still in the `production_management` database
   - Click "Import" tab again
   - Choose file: `sql/sample_data.sql`
   - Click "Go"

### Step 4: Configure Database Connection
1. Open `config/database.php`
2. Update database credentials if needed (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty by default in XAMPP
   define('DB_NAME', 'production_management');
   ```

### Step 5: Configure Base URL
1. Open `config/config.php`
2. Update BASE_URL if your project is in a subdirectory:
   ```php
   define('BASE_URL', 'http://localhost/production_management/');
   ```

### Step 6: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/production_management/`
3. Login with default credentials:
   - **Username:** `owner`
   - **Password:** `password123`

## Default User Accounts

The sample data includes the following user accounts (all with password: `password123`):

- **owner** - Owner/General Manager
- **ops_manager** - Operations Manager
- **supervisor1** - Supervisor
- **fitter1** - Pipe Fitter/Helper
- **fitter2** - Pipe Fitter/Helper
- **admin** - Admin

## Project Structure

```
production_management/
├── api/                    # API endpoints (AJAX handlers)
│   ├── auth.php
│   ├── dashboard.php
│   ├── production.php
│   ├── performance.php
│   ├── inspections.php
│   ├── documents.php
│   └── reports.php
├── assets/
│   ├── css/               # Stylesheets
│   │   ├── style.css
│   │   └── auth.css
│   └── js/                # JavaScript files
│       ├── auth.js
│       ├── common.js
│       ├── dashboard.js
│       ├── production.js
│       ├── performance.js
│       ├── inspections.js
│       ├── documents.js
│       ├── worker-form.js
│       └── reports.js
├── config/                # Configuration files
│   ├── config.php
│   └── database.php
├── controllers/           # PHP controllers
│   └── AuthController.php
├── includes/              # Reusable PHP includes
│   ├── header.php
│   └── footer.php
├── models/                # Data models
│   ├── UserModel.php
│   ├── DashboardModel.php
│   ├── ProductionModel.php
│   ├── EmployeeModel.php
│   ├── ComponentModel.php
│   ├── PerformanceModel.php
│   ├── InspectionModel.php
│   └── DocumentModel.php
├── sql/                   # Database scripts
│   ├── database.sql
│   └── sample_data.sql
├── uploads/               # File upload directory
├── index.php              # Login page
├── dashboard.php          # Dashboard
├── production.php         # Production monitoring
├── performance.php        # Employee performance
├── inspections.php        # Quality control
├── documents.php          # Document management
├── reports.php            # Reports & analytics
├── worker-form.php        # Worker production form
├── logout.php             # Logout handler
└── README.md              # This file
```

## Role-Based Access

- **Owner/General Manager**: Full access to all modules
- **Operations Manager**: Access to Production, Performance, Inspections, Documents, Reports
- **Supervisor**: Access to Production, Performance, Inspections
- **Pipe Fitter/Helper**: Access only to Worker Production Form
- **Admin**: Full access to all modules

## Features in Detail

### Production Monitoring
- Add, edit, delete production logs
- Filter by date, status, component, employee
- Auto-calculate performance percentage
- Color-coded status (Green=Target met, Yellow=Near target, Red=Below target)

### Employee Performance
- View performance by Daily/Weekly/Monthly periods
- Top performers chart
- Performance distribution analysis
- Target met percentage tracking

### Inspections
- Digital checklist (Thread quality, Pressure test, Dimensions)
- Batch ID tracking
- Inspector assignment
- Overall status auto-calculation

### Document Management
- Drag-and-drop file upload
- Support for PDF, images, Word documents
- Category-based organization
- Project assignment

### Reports
- Generate Daily/Weekly/Monthly reports
- Export to PDF using jsPDF
- Export to Excel/CSV
- Production, Inspection, and Performance reports

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP Control Panel
- Check database credentials in `config/database.php`
- Verify database name is correct

### File Upload Not Working
- Check `uploads` directory exists and has write permissions
- Verify `MAX_UPLOAD_SIZE` in `config/config.php`
- Check PHP upload settings in `php.ini`

### Session Issues
- Ensure cookies are enabled in your browser
- Check PHP session settings in `php.ini`
- Clear browser cache and cookies

### Charts Not Displaying
- Check browser console for JavaScript errors
- Ensure Chart.js CDN is accessible
- Verify internet connection for CDN resources

## Security Notes

- Change default passwords after first login
- Keep database credentials secure
- Regularly backup the database
- Update PHP and MySQL to latest versions
- Restrict file upload types and sizes
- Use HTTPS in production environment

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review PHP error logs in XAMPP
3. Check browser console for JavaScript errors
4. Verify all files are in correct locations

## License

This project is provided as-is for educational and commercial use.

## Version

Version 1.0.0 - Initial Release

