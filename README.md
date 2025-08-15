# LEAVE RMS - Resource Management System

A comprehensive web-based platform integration system that provides unified access to multiple platforms and administrative features. Built with HTML/CSS/JavaScript frontend, PHP backend, and MySQL database.

## 🎯 Overview

LEAVE RMS serves as a central hub for university students and staff to access multiple platforms through a single, unified interface. The system includes comprehensive administrative features for managing announcements, dining menus, user accounts, and system configuration.

## 📁 Project Structure

```
LEAVE_RMS/
├── public/                     # Frontend files
│   ├── css/                    # CSS stylesheets
│   │   └── styles.css          # Main stylesheet with responsive design
│   ├── js/                     # JavaScript files
│   │   ├── main.js             # Main page functionality and platform integration
│   │   ├── admin.js            # Admin panel functionality
│   │   ├── tour.js             # Interactive tour system
│   │   ├── language.js         # Multi-language support
│   │   ├── login.js            # Login page functionality
│   │   └── user_admin_link.js  # Admin panel link management
│   ├── img/                    # Images and assets
│   ├── index.html              # Main user dashboard
│   ├── login.html              # Unified login page
│   ├── admin-panel.html        # Admin panel interface
│   └── rms_auth_bridge.php     # RMS authentication bridge
├── database/                   # Backend files
│   ├── db_connection.php       # Database connection and core functions
│   ├── api.php                 # User-facing API endpoints
│   ├── admin_api.php           # Admin panel API endpoints
│   └── php_errors.log          # Error logging
├── package.json                # Node.js project configuration
├── README.md                   # This file
└── BACKEND_DOCUMENTATION.md    # Comprehensive backend documentation
```

## ✨ Features

### 🔐 Authentication & User Management
- **Unified Login System** - Single login for both users and admins
- **Role-Based Access Control** - User, Admin, and Super Admin roles
- **Session Management** - Secure session handling across platforms
- **User Promotion/Demotion** - Super admins can promote users to admin roles

### 🌐 Platform Integration
- **Multi-Platform Access** - Unified access to LMS, RMS, SIS, and Leave Portal
- **Proxy System** - Seamless navigation across different platforms
- **URL Rewriting** - Maintains session state across platform boundaries
- **Direct Access Links** - Quick access to platform-specific features

### 📢 Content Management
- **Announcements System** - Create, edit, and manage system-wide announcements
- **Dining Menu Management** - Daily meal schedules and menu planning
- **Real-time Updates** - Dynamic content loading and updates
- **Multi-language Support** - English, Turkish, French, Russian, Arabic

### 👨‍💼 Administrative Features
- **Comprehensive Admin Panel** - Full administrative control interface
- **Dashboard Statistics** - Real-time user, admin, and platform statistics
- **Content Management** - Announcements and dining menu administration
- **User Management** - View, promote, and manage user accounts
- **Admin Management** - Create, edit, and manage admin accounts

### 🔔 Notification System
- **Aggregated Notifications** - Combined notifications from all platforms
- **Real-time Updates** - Live notification updates
- **Platform-specific Handling** - Custom notification processing per platform
- **Notification Management** - View and manage user notifications

### 🎨 User Interface
- **Dark/Light Mode** - User-selectable theme preferences
- **Interactive Tour** - Guided tour for new users
- **Modern UI/UX** - Clean, intuitive interface design

### 🌍 Internationalization
- **Multi-language Support** - 5 languages (English, Turkish, French, Russian, Arabic)
- **Dynamic Translation** - Real-time language switching
- **Localized Content** - Platform-specific language handling

## 🛠️ Prerequisites

- **PHP** (v7.4 or higher)
- **MySQL/MariaDB** database
- **Web Server** (Apache/Nginx)
- **cURL Extension** (for proxy functionality)
- **Session Support** (enabled in PHP)

## 🚀 Installation

### 1. Database Setup
```sql
-- Create database
CREATE DATABASE leave_rms_db;

-- Import schema (tables will be created automatically by db_connection.php)
```

### 2. Configuration
1. Update database configuration in `database/db_connection.php`:
```php
const DB_CONFIG = [
    'host' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'leave_rms_db'
];
```

2. Create required directories:
```bash
mkdir cookies/
chmod 755 cookies/
```

3. Set up initial admin account:
```sql
INSERT INTO admins (username, password, email, role) 
VALUES ('admin', 'hashed_password', 'admin@example.com', 'super_admin');
```

### 3. Web Server Configuration
- Ensure PHP is properly configured
- Set up virtual host pointing to the project directory
- Configure URL rewriting if needed

## 🔧 How It Works

### User Flow
1. **Login** - Users access the unified login page
2. **Authentication** - System validates credentials and determines role
3. **Dashboard** - Users are redirected to appropriate dashboard
4. **Platform Access** - Users can access various platforms through the interface
5. **Content Viewing** - Users can view announcements and dining menus

### Admin Flow
1. **Admin Login** - Admins access the same login page
2. **Admin Panel** - Admins are redirected to the admin panel
3. **Content Management** - Admins can manage announcements and dining menus
4. **User Management** - Super admins can manage users and other admins
5. **System Monitoring** - View dashboard statistics and system health

### Platform Integration
1. **Proxy Requests** - System acts as a proxy for external platforms
2. **Session Management** - Maintains authentication across platforms
3. **URL Rewriting** - Rewrites URLs to maintain proxy session
4. **Content Aggregation** - Combines content from multiple sources

## 🏗️ Technical Implementation

### Frontend Technologies
- **HTML5** - Semantic markup and structure
- **CSS3** - Responsive design and modern styling
- **JavaScript (ES6+)** - Client-side functionality and interactions
- **Fetch API** - Asynchronous communication with backend
- **Local Storage** - Client-side data persistence

### Backend Technologies
- **PHP 7.4+** - Server-side processing and API endpoints
- **MySQL/MariaDB** - Database management
- **cURL** - HTTP requests and proxy functionality
- **Session Management** - User session handling

### Database Design
- **Users Table** - User accounts and authentication
- **Admins Table** - Admin accounts with role-based permissions
- **Announcements Table** - System announcements and content
- **Dining Menu Table** - Daily meal schedules and menus
- **Platforms Table** - Platform configuration and metadata
- **Platform Credentials Table** - Platform-specific authentication

### API Architecture
- **RESTful Design** - Standard HTTP methods and status codes
- **Endpoint Routing** - Single entry point with endpoint parameter
- **JSON Responses** - Consistent data format
- **Error Handling** - Comprehensive error responses and logging

## 🔒 Security Features

- **Input Validation** - All user inputs are validated and sanitized
- **SQL Injection Prevention** - Prepared statements and parameterized queries
- **XSS Protection** - Output encoding and validation
- **Session Security** - Secure session handling and management
- **Role-Based Access** - Permission-based feature access
- **CSRF Protection** - Cross-site request forgery prevention
- **Secure Headers** - Proper HTTP security headers

## 📊 System Requirements

### Minimum Requirements
- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Memory**: 128MB RAM
- **Storage**: 100MB disk space

### Recommended Requirements
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Memory**: 512MB RAM
- **Storage**: 1GB disk space
- **SSL Certificate** - For production deployment

## 🚀 Deployment

### Development Environment
1. Clone the repository
2. Set up local web server (XAMPP, WAMP, etc.)
3. Configure database connection
4. Access via `http://localhost/LEAVE_RMS`

### Production Environment
1. Upload files to web server
2. Configure database with production credentials
3. Set up SSL certificate
4. Configure web server (Apache/Nginx)
5. Set proper file permissions
6. Enable error logging and monitoring

## 🔧 Configuration

### Environment Variables
- Database connection settings
- Platform URLs and endpoints
- Admin account credentials
- Logging configuration

### Platform Configuration
- LMS sub-platform settings
- Authentication endpoints
- Notification endpoints
- URL rewriting rules

## 📝 API Documentation

For detailed API documentation, see [BACKEND_DOCUMENTATION.md](BACKEND_DOCUMENTATION.md)

### Key Endpoints
- `POST /database/api.php?endpoint=login` - User authentication
- `GET /database/api.php?endpoint=platforms` - Get available platforms
- `GET /database/api.php?endpoint=announcements` - Get announcements
- `GET /database/api.php?endpoint=dining-menu-today` - Get today's dining menu
- `POST /database/admin_api.php?endpoint=admin-login` - Admin authentication
- `POST /database/admin_api.php?endpoint=announcement-create` - Create announcement


### Debugging
- Check `database/php_errors.log` for detailed error information
- Enable PHP error reporting for development
- Use browser developer tools for frontend debugging

