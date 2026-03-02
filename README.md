# Employee Management System with AI Features

A comprehensive, full-fledged Employee Management System built with PHP and integrated with Google AI Studio API for intelligent workforce analytics.

## Features

### Core Features
- **Employee Management**: Add, edit, view, and manage employee records
- **Department Management**: Organize employees by departments
- **Attendance Tracking**: Check-in/check-out system with daily and monthly views
- **Leave Management**: Apply, approve, and track leave requests
- **Payroll Management**: Salary tracking and management
- **User Authentication**: Secure login system with role-based access

### AI-Powered Features (Google AI Studio Integration)
- **Sentiment Analysis**: Analyze employee feedback and satisfaction
- **Performance Prediction**: Predict employee performance trends
- **Job Description Generator**: AI-generated professional job descriptions
- **Team Dynamics Analysis**: Analyze team collaboration and composition

### Design Features
- **Sky Blue Theme**: Modern, professional color scheme
- **3D Motion Elements**: Interactive 3D animations and effects
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Real-time Updates**: Live clock and dynamic notifications

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js
- **AI API**: Google AI Studio (Gemini Pro)
- **Icons**: Font Awesome

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone/Extract the project** to your web server directory

2. **Create the database**:
   ```sql
   -- Import the database schema
   mysql -u root -p < includes/database.sql
   ```

3. **Configure database connection**:
   Edit `includes/config.php` and update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'employee_management');
   ```

4. **Google AI Studio API Key**:
   The API key is already configured in `includes/config.php`:
   ```php
   define('GOOGLE_AI_API_KEY', 'AIzaSyCOUEXmc-k82Pgv48VBATeotWj7Mg_RFdo');
   ```

5. **Access the application**:
   Open your browser and navigate to:
   ```
   http://localhost/employee-management-system/
   ```

6. **Default Login Credentials**:
   - Username: `admin`
   - Password: `admin123`

## Project Structure

```
employee-management-system/
├── api/                    # API endpoints
│   ├── add_employee.php
│   ├── ai_sentiment.php
│   ├── ai_predict.php
│   ├── ai_jobdesc.php
│   ├── ai_team.php
│   ├── get_employee.php
│   └── export_employees.php
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet with sky blue theme
│   ├── js/
│   │   └── main.js        # JavaScript with 3D animations
│   └── images/
├── includes/
│   ├── config.php         # Database & API configuration
│   └── database.sql       # Database schema
├── pages/                 # Additional pages
├── index.php             # Redirects to login
├── login.php             # Login page
├── register.php          # Registration page
├── dashboard.php         # Main dashboard
├── employees.php         # Employee management
├── departments.php       # Department management
├── attendance.php        # Attendance tracking
├── leave.php             # Leave management
├── payroll.php           # Payroll management
├── ai-features.php       # AI-powered features
├── analytics.php         # Analytics & reports
├── profile.php           # User profile
└── logout.php            # Logout handler
```

## AI Features Usage

### 1. Sentiment Analysis
- Navigate to **AI Features > Sentiment Analysis**
- Enter employee feedback text
- Click "Analyze Sentiment"
- View positivity score and suggestions

### 2. Performance Prediction
- Navigate to **AI Features > Performance Prediction**
- Select an employee from the dropdown
- Click "Predict Performance"
- View prediction score, trends, and recommendations

### 3. Job Description Generator
- Navigate to **AI Features > Job Description Generator**
- Enter job role and requirements
- Click "Generate Description"
- Copy the AI-generated job description

### 4. Team Dynamics Analysis
- Navigate to **AI Features > Team Dynamics**
- Select a department (optional)
- Click "Analyze Team"
- View collaboration metrics and recommendations

## User Roles

- **Admin**: Full access to all features
- **HR**: Manage employees, attendance, leave, and payroll
- **Manager**: View team data and approve leaves
- **Employee**: View personal data and apply for leaves

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with output sanitization
- Session management
- Role-based access control
- Activity logging

## Customization

### Changing the Theme Color
Edit `assets/css/style.css` and modify the CSS variables:
```css
:root {
    --primary-color: #0ea5e9;    /* Change to your preferred color */
    --primary-dark: #0284c7;
    --primary-light: #7dd3fc;
    /* ... other variables */
}
```

### Adding New AI Features
1. Create a new API file in `api/` directory
2. Use the GoogleAI class from `config.php`
3. Call the `generateContent()` method with your prompt
4. Create a UI component in `ai-features.php`

## Troubleshooting

### Database Connection Issues
- Verify database credentials in `includes/config.php`
- Ensure MySQL service is running
- Check database exists: `SHOW DATABASES;`

### AI API Issues
- Verify API key is valid
- Check internet connectivity
- Review error logs in browser console

### Permission Issues
- Ensure `uploads/` directory is writable
- Set correct permissions: `chmod 755 uploads/`

## License

This project is open-source and available for personal and commercial use.

## Support

For issues or questions, please contact the development team.

---

**Built with ❤️ using PHP, MySQL, and Google AI Studio**