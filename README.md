# Sleep Tracker CMS

A comprehensive sleep tracking content management system built with PHP and MySQL. Track your sleep patterns, analyze trends, and gain insights into your sleep health with an intuitive and responsive web interface.

## 🚀 Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js
- **Icons**: Unicode emojis and symbols
- **Responsive**: CSS Grid and Flexbox
- **Security**: CSRF tokens, prepared statements, password hashing
- **Server**: Apache/Nginx compatible with .htaccess support

## 📋 Project Overview

The Sleep Tracker CMS is a web-based application designed to help users monitor and analyze their sleep patterns. It provides a comprehensive solution for individuals who want to track their sleep quality, duration, and patterns over time. The system offers both basic sleep logging functionality and advanced analytics to help users understand their sleep habits and make informed decisions about their sleep health.

**Key Benefits:**
- Track sleep patterns over time
- Identify sleep quality trends
- Get personalized sleep insights
- Monitor sleep duration consistency
- Maintain a comprehensive sleep journal

## ✨ Key Features

### 🔐 User Authentication System
- Secure user registration and login system
- Password hashing with PHP's built-in functions
- Session management with CSRF protection
- User profile management and password updates

### 📊 Sleep Logging Functionality
- Add, edit, and delete sleep records
- Track bedtime, wake time, and sleep duration
- Rate sleep quality (Poor, Fair, Good, Excellent)
- Add optional notes for each sleep session
- Automatic duration calculation
- Input validation and sanitization

### 📈 Analytics & Insights
- Comprehensive sleep statistics dashboard
- Weekly and monthly sleep summaries
- Interactive charts and visualizations
- Sleep pattern analysis by day of week
- Sleep quality distribution
- Personalized sleep insights and recommendations

### 📱 Responsive Design
- Mobile-first approach with responsive design
- Clean and modern UI design
- Intuitive navigation and user experience
- Touch-friendly controls
- Cross-browser compatibility

### 🛡️ Security Features
- CSRF token protection for all forms
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Secure password hashing
- Session timeout management
- Input validation and sanitization

## 👥 User Roles

### Regular User
- **Capabilities:**
  - Register and manage personal account
  - Log sleep records (add, edit, delete)
  - View personal sleep statistics
  - Access sleep analytics and insights
  - Update profile information
  - Change account password

- **Access Level:**
  - Personal sleep data only
  - Dashboard with personal statistics
  - Sleep log management
  - Profile customization

### Administrator (Future Enhancement)
- **Capabilities:**
  - Manage all user accounts
  - View system-wide statistics
  - Monitor application usage
  - Access system logs
  - Manage application settings

## 🏗️ Project Structure

```
sleep-tracker-cms/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet with responsive design
│   └── js/
│       ├── dashboard.js       # Dashboard functionality and charts
│       └── sleep-form.js      # Form enhancements and validation
├── config/
│   ├── config.php            # Main application configuration
│   └── database.php          # Database connection settings
├── includes/
│   ├── User.php              # User authentication and management class
│   └── SleepRecord.php       # Sleep record CRUD operations class
├── sql/
│   └── setup.sql             # Database schema and initial data
├── api/
│   └── dashboard-stats.php   # Dashboard statistics API endpoint
├── index.php                 # Main entry point and redirect
├── login.php                 # User authentication page
├── register.php              # User registration page
├── logout.php                # Session termination handler
├── dashboard.php             # Main dashboard with statistics
├── add-sleep.php             # Sleep record creation form
├── edit-sleep.php            # Sleep record editing form
├── sleep-log.php             # Sleep records listing and management
├── summary.php               # Analytics and detailed insights
├── profile.php               # User profile management
├── install.php               # Installation and setup wizard
├── .htaccess                 # Apache server configuration
└── README.md                 # Project documentation
```

## ⚙️ Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, or built-in PHP server)
- PDO MySQL extension enabled
- Modern web browser with JavaScript enabled

### 1. Download and Extract
```bash
# Clone or download the project
git clone https://github.com/soikot-shahriaar/sleep-tracker-cms
cd sleep-tracker-cms
```

### 2. Database Setup
1. Create a MySQL database:
```sql
CREATE DATABASE sleep_tracker_cms;
```

2. Import the database schema:
```bash
mysql -u your_username -p sleep_tracker_cms < sql/setup.sql
```

### 3. Configuration
1. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sleep_tracker_cms');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Optionally, update application settings in `config/config.php`:
```php
define('APP_NAME', 'Sleep Tracker CMS');
define('APP_URL', 'http://localhost/project_name'); // Use 'http://localhost' for root, or 'http://localhost/project_name' for subdirectory
```

### 4. Web Server Setup

#### Option A: Apache
1. Copy files to your web root directory (e.g., `/var/www/html/` or `/var/www/html/project_name/` for subdirectory)
2. Ensure Apache has read permissions
3. Enable mod_rewrite for custom URLs
4. If using a subdirectory, update `APP_URL` in `config/config.php` to include the subdirectory path (e.g., `http://localhost/project_name`)

#### Option B: Nginx
1. Copy files to your web root directory (or subdirectory)
2. Configure Nginx to serve PHP files
3. Ensure proper PHP-FPM configuration
4. If using a subdirectory, update `APP_URL` in `config/config.php` accordingly

#### Option C: Built-in PHP Server (Development Only)
```bash
cd sleep-tracker-cms
php -S localhost:8000
```
**Note:** For production, use Apache or Nginx. The built-in PHP server is for development only.

### 5. File Permissions
```bash
chmod 755 sleep-tracker-cms/
chmod 644 sleep-tracker-cms/*.php
chmod 644 sleep-tracker-cms/assets/css/*.css
chmod 644 sleep-tracker-cms/assets/js/*.js
```

### 6. Installation Wizard
1. Navigate to your application URL
2. Follow the guided installation process using `install.php`
3. Complete the setup and verify functionality

## 📖 Usage

### Getting Started
1. **Access the Application**
   - Open your web browser and navigate to your installation URL
   - You'll be redirected to the login page

2. **Create Account**
   - Click "Register here" on the login page
   - Fill in your details and create an account
   - Login with your new credentials

### Dashboard Navigation
- **Statistics Cards**: Total records, average sleep, weekly average, sleep quality
- **Quick Actions**: Add new sleep record, view sleep log, view summary
- **Recent Records**: Your latest sleep entries

### Sleep Record Management
1. **Adding Records**: Click "Add Sleep" and fill in sleep details
2. **Editing Records**: Use the edit button to modify existing entries
3. **Deleting Records**: Remove entries with confirmation dialog
4. **Viewing History**: Access complete sleep log with pagination

### Analytics and Insights
- **Overall Statistics**: Comprehensive sleep data overview
- **Time Period Analysis**: Weekly and monthly breakdowns
- **Interactive Charts**: Visual representation of sleep patterns
- **Personalized Insights**: Data-driven recommendations

### Profile Management
- **Update Information**: Modify name and email address
- **Change Password**: Update account security credentials
- **Account Details**: View creation and update timestamps

## 🎯 Intended Use

### Personal Sleep Tracking
The Sleep Tracker CMS is designed for individuals who want to:
- Monitor their sleep patterns and quality
- Track sleep duration consistency
- Identify factors affecting sleep quality
- Maintain a personal sleep journal
- Get insights into sleep habits

### Health and Wellness
This application supports:
- Sleep health awareness
- Sleep pattern analysis
- Sleep quality improvement
- Sleep schedule optimization
- Health goal tracking

### Research and Analysis
Suitable for:
- Personal sleep research
- Sleep pattern documentation
- Sleep quality correlation studies
- Sleep schedule optimization
- Health professional consultations

### Educational Purposes
Ideal for:
- Learning about sleep patterns
- Understanding sleep quality factors
- Developing healthy sleep habits
- Sleep science education
- Personal health management

## 📄 License

**License for RiverTheme**
RiverTheme makes this project available for demo, instructional, and personal use. You can ask for or buy a license from [RiverTheme.com](https://RiverTheme.com) if you want a pro website, sophisticated features, or expert setup and assistance. A Pro license is needed for production deployments, customizations, and commercial use.

**Disclaimer**
The free version is offered "as is" with no warranty and might not function on all devices or browsers. It might also have some coding or security flaws. For additional information or to get a Pro license, please get in touch with [RiverTheme.com](https://RiverTheme.com).
