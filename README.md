# Employee Management System

A comprehensive, full-fledged Employee Management System built with PHP and optimized for serverless deployment on **Vercel**. 

## Features

### Core Features
- **Employee Management**: Add, edit, view, and manage employee records
- **Department Management**: Organize employees by departments
- **Attendance Tracking**: Check-in/check-out system with daily and monthly views
- **Leave Management**: Apply, approve, and track leave requests
- **Payroll Management**: Salary tracking and management
- **User Authentication**: Secure login system with role-based access

### Design Features
- **Sky Blue Theme**: Modern, professional color scheme
- **3D Motion Elements**: Interactive 3D animations and effects
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Real-time Updates**: Live clock and dynamic notifications

## Technology Stack

- **Backend**: PHP 8.2 (via `vercel-php`)
- **Database**: MySQL/MariaDB (Remote database required for Vercel, e.g., Railway, PlanetScale, Aiven, etc.)
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js
- **Icons**: Font Awesome
- **Hosting**: Vercel

## Deployment on Vercel

This application is configured to run flawlessly on Vercel's serverless infrastructure using the `vercel-php` community runtime.

### Prerequisites for Vercel Deployment
1. A Vercel account
2. A remote MySQL database that allows external connections

### Setup Steps

1. **Database Setup**:
   Import the database schema located in `includes/database.sql` into your remote MySQL database.

2. **Deploy to Vercel**:
   You can deploy this project to Vercel directly via GitHub or Vercel CLI.

3. **Configure Environment Variables**:
   In your Vercel project settings, go to the **Environment Variables** section and add the following keys with your remote database credentials:
   - `DB_HOST` (e.g., `db.your-provider.com`)
   - `DB_USER` (e.g., `admin`)
   - `DB_PASS` (e.g., `your_strong_password`)
   - `DB_NAME` (e.g., `employee_management`)

4. **Access the application**:
   Once deployed, Vercel will provide you with a live URL (e.g., `https://your-app-name.vercel.app`).

5. **Default Login Credentials**:
   - Username: `admin`
   - Password: `admin123`

## Project Structure

```
employee-management-system/
├── api/                    # API endpoints
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet with sky blue theme
│   ├── js/
│   │   └── main.js        # JavaScript with 3D animations
│   └── images/
├── includes/
│   ├── config.php         # Database configuration (reads ENV vars)
│   └── database.sql       # Database schema
├── vercel.json           # Vercel deployment configuration
├── index.php             # Redirects to login/dashboard
├── login.php             # Login page
├── register.php          # Registration page
├── dashboard.php         # Main dashboard
├── employees.php         # Employee management
├── departments.php       # Department management
├── attendance.php        # Attendance tracking
├── leave.php             # Leave management
├── payroll.php           # Payroll management
├── analytics.php         # Analytics & reports
├── profile.php           # User profile
└── logout.php            # Logout handler
```

## User Roles

- **Admin**: Full access to all features
- **HR**: Manage employees, attendance, leave, and payroll
- **Manager**: View team data and approve leaves
- **Employee**: View personal data and apply for leaves

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with output sanitization
- Secure session management adapted for serverless environments
- Role-based access control

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

## Troubleshooting

### Database Connection Issues on Vercel
- Ensure your database provider allows external connections (some providers require you to whitelist Vercel's IP addresses, or allow `0.0.0.0/0`).
- Double-check that all `DB_*` environment variables are correctly set in the Vercel dashboard.
- Remember that environment variables in Vercel need a redeployment to take effect if added after the initial deployment.

## License

This project is open-source and available for personal and commercial use.

---

**Built with ❤️ for Serverless PHP**