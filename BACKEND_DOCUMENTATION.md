# LEAVE RMS Backend Documentation



## 📋 Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Database Configuration](#database-configuration)
4. [API Endpoints](#api-endpoints)
5. [Admin Panel System](#admin-panel-system)
6. [LMS Sub-platforms Configuration](#lms-sub-platforms-configuration)
7. [URL Rewriting System](#url-rewriting-system)
8. [Notification System](#notification-system)
9. [Announcements System](#announcements-system)
10. [Dining Menu System](#dining-menu-system)
11. [User Management System](#user-management-system)
12. [Error Handling and Logging](#error-handling-and-logging)
13. [Security Considerations](#security-considerations)
14. [Configuration and Deployment](#configuration-and-deployment)
15. [Usage Examples](#usage-examples)
16. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The **LEAVE RMS** (Resource Management System) backend is a PHP-based API system that serves as a central hub for integrating multiple  platforms. It provides authentication proxying, notification aggregation, session management, and administrative features across multiple platforms.

### Core Components

| Component | Purpose |
|-----------|---------|
| `api.php` | Main API handler for user-facing operations |
| `admin_api.php` | Admin panel API handler for administrative operations |
| `db_connection.php` | Handles database connection and core functions |
| `rms_auth_bridge.php` | Authentication bridge for RMS system |

---

## 🏗️ System Architecture

The system follows a **multi-tier architecture** with separate APIs for user and admin operations, unified authentication, and comprehensive administrative features.

```
┌─────────────┐    ┌─────────────────┐    ┌─────────────────────────────┐
│   Frontend  │───▶│  PHP API Layer  ───▶│  Multiple Platforms         │
│             │    │  (User + Admin) │    │                             │
└─────────────┘    └─────────────────┘    └─────────────────────────────┘
```

### Architecture Benefits

- ✅ **Unified Interface** - Single entry point for multiple systems
- ✅ **Role-Based Access** - Separate user and admin interfaces
- ✅ **Session Management** - Centralized authentication handling
- ✅ **URL Rewriting** - Seamless navigation across platforms
- ✅ **Notification Aggregation** - Combined notifications from all sources
- ✅ **Administrative Control** - Comprehensive admin panel for system management

---

## 🗄️ Database Configuration

### 3.1 Database Connection (`db_connection.php`)

The database configuration is defined using constants for better maintainability:

```php
const DB_CONFIG = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'leave_rms_db'
];
```

#### Key Functions

| Function | Purpose |
|----------|---------|
| `createDatabaseConnection()` | Creates and returns a MySQLi database connection |
| `sanitize_input()` | Sanitizes input data to prevent SQL injection |
| `validate_user()` | Validates user credentials with multiple methods |
| `validate_admin()` | Validates admin credentials |
| `get_platforms()` | Retrieves all available platforms |
| `get_notifications()` | Fetches user notifications |
| `get_active_announcements()` | Fetches active announcements for users |
| `get_todays_dining_menu()` | Fetches today's dining menu |
| `save_platform_credentials()` | Stores platform-specific credentials |

### 3.2 Database Schema

#### Users Table
```sql
CREATE TABLE users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(50),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Admins Table
```sql
CREATE TABLE admins (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Announcements Table
```sql
CREATE TABLE announcements (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_by INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);
```

#### Dining Menu Table
```sql
CREATE TABLE dining_menu (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    breakfast_menu TEXT,
    breakfast_start_time TIME,
    breakfast_end_time TIME,
    lunch_menu TEXT,
    lunch_start_time TIME,
    lunch_end_time TIME,
    created_by INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);
```

#### Platforms Table
```sql
CREATE TABLE platforms (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(255) NOT NULL,
    notifications_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Platform Credentials Table
```sql
CREATE TABLE platform_credentials (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    platform VARCHAR(100) NOT NULL,
    platform_username VARCHAR(100) NOT NULL,
    platform_password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_platform (username, platform)
);
```
#### Holidays & Days Off Table
```sql
 CREATE TABLE IF NOT EXISTS holidays_days_off (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        year INT(4) NOT NULL,
        day_of_week VARCHAR(20) NOT NULL,
        holiday_name VARCHAR(255) NOT NULL,
        type ENUM('holiday', 'weekend', 'closure', 'custom') NOT NULL DEFAULT 'holiday',
        description TEXT,
        is_recurring BOOLEAN DEFAULT FALSE,
        created_by INT(6) UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
        INDEX idx_year (year),
        INDEX idx_date (date),
        INDEX idx_type (type)
    );
```

---

## 🔌 API Endpoints

The system uses two main API files: `api.php` for user operations and `admin_api.php` for administrative operations.

### 4.1 User API (`api.php`)

#### 4.1.1 Authentication Endpoints

##### Login (`login`)
- **Method:** POST
- **Parameters:** `username`, `password`
- **Functionality:** Unified login for both users and admins
- **Response:** JSON with user/admin data and redirect information

##### User Validation (`validate_user`)
- **Method:** GET
- **Parameters:** `username`, `password`
- **Functionality:** Validates user credentials
- **Response:** JSON with validation result

#### 4.1.2 Platform Management

##### Get Platforms (`platforms`)
- **Method:** GET
- **Functionality:** Retrieves all available platforms
- **Response:** JSON with platform list

##### LMS Sub-platforms (`lms_subplatforms`)
- **Method:** GET
- **Functionality:** Retrieves LMS sub-platform configuration
- **Response:** JSON with sub-platform list

##### LMS Direct Link (`lms_subplatform_direct_link`)
- **Method:** GET
- **Parameters:** `username`, `subplatform`
- **Functionality:** Generates direct access links for LMS platforms
- **Response:** JSON with access URL

#### 4.1.3 Proxy Endpoints

##### LMS Sub-platform Proxy (`lms_subplatform_proxy`)
- **Method:** GET
- **Parameters:** `username`, `subplatform`, `path`
- **Functionality:** Proxies requests to LMS platforms
- **Response:** Proxied content with URL rewriting

##### LMS Font Proxy (`lms_font_proxy`)
- **Method:** GET
- **Parameters:** `username`, `subplatform`, `path`
- **Functionality:** Proxies font resources
- **Response:** Font files with proper headers

##### Leave Portal Proxy (`leave_portal_proxy`)
- **Method:** GET
- **Parameters:** `username`, `page`
- **Functionality:** Proxies leave portal requests
- **Response:** Proxied content

#### 4.1.4 Content Endpoints

##### Announcements (`announcements`)
- **Method:** GET
- **Functionality:** Retrieves active announcements for users
- **Response:** JSON with announcement list

##### Dining Menu Today (`dining-menu-today`)
- **Method:** GET
- **Functionality:** Retrieves today's dining menu
- **Response:** JSON with dining menu data

##### Notifications (`notifications`)
- **Method:** GET
- **Parameters:** `username`
- **Functionality:** Retrieves user notifications
- **Response:** JSON with notification list

##### Delete Notification (`delete_notification`)
- **Method:** POST
- **Parameters:** `{"id": notification_id}`
- **Functionality:** Deletes user notification
- **Response:** JSON with success status

### 4.2 Admin API (`admin_api.php`)

#### 4.2.1 Authentication

##### Admin Login (`admin-login`)
- **Method:** POST
- **Parameters:** `username`, `password`
- **Functionality:** Authenticates admin users
- **Response:** JSON with admin data

##### Validate Admin (`validate-admin`)
- **Method:** GET
- **Parameters:** `username`, `password`
- **Functionality:** Validates admin credentials
- **Response:** JSON with validation result

#### 4.2.2 Dashboard Management

##### Dashboard Statistics (`dashboard-stats`)
- **Method:** GET
- **Functionality:** Retrieves dashboard statistics
- **Response:** JSON with user count, admin count, platform count

##### Get Admins (`admin-list`)
- **Method:** GET
- **Functionality:** Retrieves admin list (filtered by role)
- **Response:** JSON with admin list

##### Get Users (`users-list`)
- **Method:** GET
- **Functionality:** Retrieves user list with admin status
- **Response:** JSON with user list

##### Get Platforms (`platforms-list`)
- **Method:** GET
- **Functionality:** Retrieves platform list
- **Response:** JSON with platform list

#### 4.2.3 Admin Management

##### Create Admin (`admin-create`)
- **Method:** POST
- **Parameters:** `username`, `email`, `password`, `role`
- **Functionality:** Creates new admin account
- **Response:** JSON with success status

##### Update Admin (`admin-update`)
- **Method:** POST
- **Parameters:** `id`, `username`, `email`, `role`
- **Functionality:** Updates admin account
- **Response:** JSON with success status

##### Delete Admin (`admin-delete`)
- **Method:** POST
- **Parameters:** `id`
- **Functionality:** Deletes admin account
- **Response:** JSON with success status

##### Change Admin Password (`admin-change-password`)
- **Method:** POST
- **Parameters:** `id`, `new_password`
- **Functionality:** Changes admin password
- **Response:** JSON with success status

##### Get Admin by Username (`admin-by-username`)
- **Method:** GET
- **Parameters:** `username`
- **Functionality:** Retrieves admin by username
- **Response:** JSON with admin data

#### 4.2.4 User Management

##### Promote User (`promote-user`)
- **Method:** POST
- **Parameters:** `username`
- **Functionality:** Promotes user to admin role
- **Response:** JSON with success status

##### Demote User (`demote-user`)
- **Method:** POST
- **Parameters:** `username`
- **Functionality:** Demotes admin back to user role
- **Response:** JSON with success status

#### 4.2.5 Announcements Management

##### Get Announcements (`announcements-list`)
- **Method:** GET
- **Functionality:** Retrieves all announcements
- **Response:** JSON with announcement list

##### Get Announcement (`announcement-get`)
- **Method:** GET
- **Parameters:** `id`
- **Functionality:** Retrieves specific announcement
- **Response:** JSON with announcement data

##### Create Announcement (`announcement-create`)
- **Method:** POST
- **Parameters:** `title`, `content`
- **Functionality:** Creates new announcement
- **Response:** JSON with success status

##### Update Announcement (`announcement-update`)
- **Method:** POST
- **Parameters:** `id`, `title`, `content`
- **Functionality:** Updates announcement
- **Response:** JSON with success status

##### Delete Announcement (`announcement-delete`)
- **Method:** POST
- **Parameters:** `id`
- **Functionality:** Deletes announcement
- **Response:** JSON with success status

#### 4.2.6 Dining Menu Management

##### Get Dining Menus (`dining-menu-list`)
- **Method:** GET
- **Functionality:** Retrieves all dining menus
- **Response:** JSON with dining menu list

##### Get Dining Menu (`dining-menu-get`)
- **Method:** GET
- **Parameters:** `id`
- **Functionality:** Retrieves specific dining menu
- **Response:** JSON with dining menu data

##### Get Today's Dining Menu (`dining-menu-today`)
- **Method:** GET
- **Functionality:** Retrieves today's dining menu
- **Response:** JSON with dining menu data

##### Create Dining Menu (`dining-menu-create`)
- **Method:** POST
- **Parameters:** `date`, `breakfast_menu`, `breakfast_start_time`, `breakfast_end_time`, `lunch_menu`, `lunch_start_time`, `lunch_end_time`
- **Functionality:** Creates new dining menu
- **Response:** JSON with success status

##### Update Dining Menu (`dining-menu-update`)
- **Method:** POST
- **Parameters:** `id`, `date`, `breakfast_menu`, `breakfast_start_time`, `breakfast_end_time`, `lunch_menu`, `lunch_start_time`, `lunch_end_time`
- **Functionality:** Updates dining menu
- **Response:** JSON with success status

##### Delete Dining Menu (`dining-menu-delete`)
- **Method:** POST
- **Parameters:** `id`
- **Functionality:** Deletes dining menu
- **Response:** JSON with success status

---

## 👨‍💼 Admin Panel System

### 5.1 Role-Based Access Control (RBAC)

The system implements a two-tier admin system:

#### Admin Roles
- **`admin`**: Regular admin with limited permissions
- **`super_admin`**: Super admin with full system access

#### Permission Matrix

| Feature | Admin | Super Admin |
|---------|-------|-------------|
| Dashboard Statistics | ✅ | ✅ |
| Announcements Management | ✅ | ✅ |
| Dining Menu Management | ✅ | ✅ |
| Admin Management | ❌ | ✅ |
| User Promotion/Demotion | ❌ | ✅ |
| System Configuration | ❌ | ✅ |

### 5.2 Admin Panel Features

#### Dashboard
- Real-time statistics (users, admins, platforms)
- Quick access to management functions
- System health monitoring

#### Announcements Management
- Create, edit, delete announcements
- Rich text content support
- Date tracking and management

#### Dining Menu Management
- Daily menu creation and editing
- Breakfast and lunch scheduling
- Time-based meal planning

#### Admin Management (Super Admin Only)
- Create new admin accounts
- Edit existing admin details
- Delete admin accounts
- Role assignment and management

#### User Management (Super Admin Only)
- View all system users
- Promote users to admin role
- Demote admins back to user role
- User status monitoring

---

## 🎛️ LMS Sub-platforms Configuration

The system supports multiple LMS sub-platforms, defined in an array:

```php
$lms_subplatforms = [
    [
        'name' => 'Üniversite Ortak/University Common',
        'url' => 'https://lms1.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Sağlık Bilimleri Fakültesi/Faculty of Health Sciences',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    // Additional sub-platforms...
];
```

### Sub-platform Configuration

Each sub-platform has:

| Property | Description |
|----------|-------------|
| **Name** | Bilingual: Turkish/English |
| **Base URL** | Platform's base URL |
| **Login Endpoint** | Authentication endpoint path |
| **Notifications Endpoint** | Notifications retrieval endpoint |

---

## 🔄 URL Rewriting System

A critical component of the proxy system is the comprehensive URL rewriting:

### 7.1 HTML Content Rewriting

The system performs multiple regex-based rewrites on HTML content:

#### Link/Script/Img Sources
```php
preg_replace_callback('/(href|src)=["\']([^"\']*)["\']/i', ...)
```
- ✅ Converts absolute URLs to proxy URLs
- ✅ Handles relative paths
- ✅ Skips external URLs and special protocols

#### Form Actions
```php
preg_replace_callback('/<form\s+[^>]*action\s*=\s*["\']([^"\']*)["\'][^>]*>/i', ...)
```
- ✅ Rewrites form submission endpoints
- ✅ Maintains CSRF tokens and other form data

#### Data-Action Attributes
- ✅ Special handling for JavaScript-driven actions

#### JavaScript Content
- ✅ Less aggressive rewriting to avoid breaking code
- ✅ Focuses on resource references only

### 7.2 Special Handling for Resource Types

| Resource Type | Handling |
|---------------|----------|
| HTML | Comprehensive URL rewriting |
| CSS | Path rewriting for imports and resources |
| JavaScript | Selective URL rewriting |
| Fonts | Special font proxy endpoint |
| Images | Direct path rewriting |
| AJAX | Special headers (X-Requested-With) |

---

## 🔔 Notification System

The system aggregates notifications from multiple platforms:

### 8.1 Notification Sources

#### Leave and Absence System
- ✅ Special handling for leave portal notifications
- ✅ Custom parsing logic for platform-specific format

#### Platform Navigation
- ✅ System-generated notifications
- ✅ Can be deleted through API

### 8.2 Notification Processing

#### Extraction
- ✅ HTML parsing of notification endpoints
- ✅ Pattern matching for notification elements
- ✅ Date and context extraction

#### Aggregation
- ✅ Combined from all connected platforms
- ✅ Deduplication for Leave and Absence notifications
- ✅ Structured into consistent format

#### Delivery
- ✅ Injected into appropriate UI elements
- ✅ Available via the unified notification interface

---

## 📢 Announcements System

### 9.1 System Overview

The announcements system allows administrators to create and manage system-wide announcements visible to all users.

### 9.2 Features

#### Announcement Management
- ✅ Create announcements with title and content
- ✅ Edit existing announcements
- ✅ Delete announcements
- ✅ Date tracking and management

#### User Display
- ✅ Real-time announcement display
- ✅ Clickable announcement cards
- ✅ Detailed modal view
- ✅ Multi-language support

#### Content Management
- ✅ Rich text content support
- ✅ Author tracking
- ✅ Creation and update timestamps

---

## 🍽️ Dining Menu System

### 10.1 System Overview

The dining menu system allows administrators to create and manage daily meal schedules and menus.

### 10.2 Features

#### Menu Management
- ✅ Daily menu creation and editing
- ✅ Breakfast and lunch scheduling
- ✅ Time-based meal planning
- ✅ Menu content management

#### User Display
- ✅ Today's menu display
- ✅ Meal time information
- ✅ Clickable menu cards
- ✅ Detailed modal view
- ✅ Multi-language support

#### Content Structure
- ✅ Breakfast menu and times
- ✅ Lunch menu and times
- ✅ Date-based organization
- ✅ Author tracking

---

## 👥 User Management System

### 11.1 User Types

#### Regular Users
- ✅ Access to platform integration
- ✅ View announcements and dining menu
- ✅ Receive notifications
- ✅ Language and theme preferences

#### Admin Users
- ✅ All regular user features
- ✅ Access to admin panel
- ✅ Management capabilities based on role

#### Super Admin Users
- ✅ All admin features
- ✅ Full system administration
- ✅ User promotion/demotion
- ✅ Admin account management

### 11.2 User Features

#### Authentication
- ✅ Unified login system
- ✅ Session management
- ✅ Role-based access control
- ✅ Secure credential storage

#### Profile Management
- ✅ Language preferences
- ✅ Theme preferences
- ✅ Session management
- ✅ Account settings



## ⚠️ Error Handling and Logging

### 12.1 Error Types Handled

| Error Type | Description |
|------------|-------------|
| Database connection errors | Connection failures and timeouts |
| Authentication failures | Invalid credentials or expired sessions |
| Session expiration | Automatic re-authentication triggers |
| Resource not found | 404 handling for missing resources |
| Invalid parameters | Parameter validation errors |
| Proxy connection issues | Network connectivity problems |
| Permission errors | Role-based access violations |
| API endpoint errors | Invalid endpoint requests |

### 12.2 Logging System

All significant operations logged to `php_errors.log`:

- ✅ Timestamped entries for debugging
- ✅ Detailed information about:
  - Authentication attempts
  - Proxy requests/responses
  - URL rewriting operations
  - Admin operations
  - Error conditions

**Example log entry:**
```
[2025-01-15 14:30:22] Admin login attempt for user: admin
[2025-01-15 14:30:23] Announcement created by admin ID: 1
[2025-01-15 14:30:24] Dining menu updated for date: 2025-01-15
```

---

## 🔒 Security Considerations

### 13.1 Implemented Protections

| Protection | Description |
|------------|-------------|
| **Input Validation** | Parameters are checked before use |
| **Session Management** | Secure cookie handling |
| **Role-Based Access** | Permission-based feature access |
| **Error Handling** | No sensitive information exposed to clients |
| **Content-Type Headers** | Properly set for all responses |
| **CORS Configuration** | Controlled cross-origin access |
| **SQL Injection Prevention** | Prepared statements and input sanitization |
| **XSS Prevention** | Output encoding and validation |

### 13.2 Security Notes

- ✅ Credentials are stored encrypted in the database
- ✅ Session cookies are stored server-side, not exposed to client
- ✅ No direct database access from client-side
- ✅ Special handling for sensitive operations
- ✅ Role-based access control for admin functions
- ✅ Input sanitization for all user inputs
- ✅ Secure password handling and storage

---

## ⚙️ Configuration and Deployment

### 14.1 Required Configuration

#### Database
- ✅ Update `DB_CONFIG` in `db_connection.php` with proper credentials
- ✅ Create required tables (users, admins, announcements, dining_menu, platforms, platform_credentials)

#### Cookie Directory
- ✅ Create `cookies/` directory with proper permissions
- ✅ Should be outside web root for security

#### Error Logging
- ✅ Ensure `php_errors.log` is writable
- ✅ Configure log rotation for production

#### Admin Setup
- ✅ Create initial super admin account
- ✅ Configure admin roles and permissions
- ✅ Set up announcement and dining menu defaults




## 📖 Usage Examples

### 15.1 User Authentication

```php
// Login request
POST /database/api.php?endpoint=login
{
    "username": "user123",
    "password": "password123"
}

// Response
{
    "success": true,
    "user": {
        "id": 1,
        "username": "user123",
        "email": "user@example.com",
        "is_admin": 0
    },
    "redirect": "index.html"
}
```

### 15.2 Admin Operations

```php
// Create announcement
POST /database/admin_api.php?endpoint=announcement-create
{
    "title": "System Maintenance",
    "content": "Scheduled maintenance on Sunday..."
}

// Create dining menu
POST /database/admin_api.php?endpoint=dining-menu-create
{
    "date": "2025-01-20",
    "breakfast_menu": "Eggs, toast, coffee",
    "breakfast_start_time": "07:00:00",
    "breakfast_end_time": "09:00:00",
    "lunch_menu": "Chicken, rice, vegetables",
    "lunch_start_time": "12:00:00",
    "lunch_end_time": "14:00:00"
}
```

### 15.3 Platform Integration

```php
// Get platforms
GET /database/api.php?endpoint=platforms

// LMS direct access
GET /database/api.php?endpoint=lms_subplatform_direct_link&username=user123&subplatform=University%20Common
```

---

## 🔧 Troubleshooting

### 16.1 Common Issues and Solutions

#### Authentication Failures
**Symptoms:**
- Users cannot access platforms
- Session errors in logs

**Solutions:**
- ✅ Check `php_errors.log` for authentication details
- ✅ Verify credentials in database
- ✅ Ensure cookie directory is writable

#### Admin Panel Issues
**Symptoms:**
- Admin cannot access admin panel
- Permission errors

**Solutions:**
- ✅ Verify admin role in database
- ✅ Check admin session validity
- ✅ Review role-based access permissions

#### Announcement/Dining Menu Issues
**Symptoms:**
- Content not displaying
- Creation/editing failures

**Solutions:**
- ✅ Check database table structure
- ✅ Verify admin permissions
- ✅ Review API endpoint responses

#### Broken Resources (CSS/JS)
**Symptoms:**
- Missing styles or functionality
- Console errors

**Solutions:**
- ✅ Check URL rewriting patterns
- ✅ Verify font proxy is working
- ✅ Review resource path handling

#### Session Expiration
**Symptoms:**
- Users logged out unexpectedly
- Authentication loops

**Solutions:**
- ✅ Check cookie file creation/modification times
- ✅ Verify re-authentication logic is triggering
- ✅ Review session timeout settings

#### Notification Issues
**Symptoms:**
- Missing notifications
- Duplicate notifications

**Solutions:**
- ✅ Check notification endpoint responses
- ✅ Verify parsing patterns match current HTML structure
- ✅ Review platform-specific notification formats

---

## 🎯 Conclusion

The **LEAVE RMS** backend provides a comprehensive integration layer between multiple LMS platforms and a leave management system. Its multi-tier architecture, role-based access control, comprehensive administrative features, and robust proxy system create a unified user experience while maintaining compatibility with disparate backend systems.

### Key Features

- ✅ **Extensible Design** - New LMS platforms can be added through configuration
- ✅ **Role-Based Access** - Comprehensive admin and super admin roles
- ✅ **Content Management** - Announcements and dining menu systems
- ✅ **User Management** - Promotion/demotion and role management
- ✅ **Robust Error Handling** - Comprehensive logging for maintenance and troubleshooting
- ✅ **Security Focused** - Multiple layers of protection and validation
- ✅ **Performance Optimized** - Efficient caching and session management
- ✅ **User Friendly** - Seamless navigation across multiple platforms
- ✅ **Multi-language Support** - Internationalization for global users

