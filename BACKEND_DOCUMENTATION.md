# LEAVE RMS Backend Documentation

> **Version:** 2.0  
> **Last Updated:** 2024  
> **Author:** System Administrator  

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Database Configuration](#database-configuration)
4. [API Endpoints](#api-endpoints)
5. [LMS Sub-platforms Configuration](#lms-sub-platforms-configuration)
6. [URL Rewriting System](#url-rewriting-system)
7. [Notification System](#notification-system)
8. [Error Handling and Logging](#error-handling-and-logging)
9. [Security Considerations](#security-considerations)
10. [Configuration and Deployment](#configuration-and-deployment)
11. [Usage Examples](#usage-examples)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The **LEAVE RMS** (Resource Management System) backend is a PHP-based API system that serves as a central hub for integrating multiple Learning Management System (LMS) platforms with a Leave Management System. It provides authentication proxying, notification aggregation, and session management across multiple platforms.

### Core Components

| Component | Purpose |
|-----------|---------|
| `api.php` | Main API handler that processes requests, manages proxying, and handles business logic |
| `db_connection.php` | Handles database connection and configuration |

---

## 🏗️ System Architecture

The system follows a **proxy-based architecture** where the API acts as an intermediary between the frontend and multiple backend systems (LMS platforms and Leave Management System). It handles authentication, session management, and URL rewriting to create a unified user experience across disparate systems.

```
┌─────────────┐    ┌─────────────────┐    ┌─────────────────────────────┐
│   Frontend  │───▶│  PHP API API    ───▶│  Multiple LMS Platforms &  │
│             │    │                 │    │      Leave System           │
└─────────────┘    └─────────────────┘    └─────────────────────────────┘
```

### Architecture Benefits

- ✅ **Unified Interface** - Single entry point for multiple systems
- ✅ **Session Management** - Centralized authentication handling
- ✅ **URL Rewriting** - Seamless navigation across platforms
- ✅ **Notification Aggregation** - Combined notifications from all sources

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
| `get_platforms()` | Retrieves all available platforms |
| `get_notifications()` | Fetches user notifications |
| `save_platform_credentials()` | Stores platform-specific credentials |

### 3.2 Database Schema

#### Users Table
```sql
CREATE TABLE users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

#### Notifications Table//Not needed anymore
```sql
CREATE TABLE notifications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    platform VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    url VARCHAR(255) NOT NULL,
    status ENUM('unread','read') DEFAULT 'unread',
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

---

## 🔌 API Endpoints

The main API file handles multiple endpoints through a single entry point with endpoint routing.

### 4.1 Core Configuration

| Feature | Description |
|---------|-------------|
| **Session Management** | Starts session at the beginning of execution |
| **Error Handling** | Configured to log errors to `php_errors.log` |
| **CORS Headers** | Configured for cross-origin requests |
| **Logging** | Comprehensive logging for debugging |

### 4.2 Main Endpoints

#### 4.2.1 LMS Sub-platform Proxy (`lms_subplatform_proxy`)

**Purpose:** Acts as a reverse proxy for multiple LMS platforms, handling authentication and URL rewriting.

**Parameters:**
- `username`: User identifier
- `subplatform`: Name of the LMS sub-platform
- `path`: Resource path to fetch

**Functionality:**
- ✅ Manages user sessions via cookie files
- ✅ Handles authentication flow for LMS platforms
- ✅ Rewrites URLs to maintain proxy session
- ✅ Special handling for different resource types

**Resource Type Handling:**

| Resource Type | Handling |
|---------------|----------|
| HTML | Extensive URL rewriting |
| CSS/JS | Path rewriting to maintain functionality |
| Fonts | Special proxy endpoint (`lms_font_proxy`) |
| AJAX requests | Special handling with appropriate headers |

#### 4.2.2 LMS Font Proxy (`lms_font_proxy`)

**Purpose:** Specialized proxy for font resources to handle CORS and path issues.

**Parameters:**
- `username`: User identifier
- `subplatform`: Name of the LMS sub-platform
- `path`: Font resource path

**Functionality:**
- ✅ Handles font requests (woff, woff2, ttf, eot, otf)
- ✅ Proper content-type headers for font resources
- ✅ Path normalization and encoding

#### 4.2.3 RMS Dashboard Proxy (`rms_dashboard_proxy`)

**Purpose:** Proxy for the Leave Management System dashboard.

**Parameters:**
- `username`: User identifier
- `path`: Resource path to fetch

**Functionality:**
- ✅ Similar to LMS proxy but tailored for RMS system
- ✅ URL rewriting specific to RMS application structure
- ✅ Session management for RMS authentication

#### 4.2.4 Leave Portal Proxy (`leave_portal_proxy`)

**Purpose:** Proxy for the Leave and Absence management portal.

**Parameters:**
- `username`: User identifier
- `page`: Page to fetch

**Functionality:**
- ✅ Specialized handling for leave management system
- ✅ Notification extraction from portal responses

#### 4.2.5 Notification Management

##### Get Notifications
- Automatically aggregated from all connected platforms
- No direct endpoint - integrated into page responses

##### Delete Notification (`delete_notification`)
- **Method:** POST with JSON body
- **Parameters:** `{"id": notification_id}`
- **Restriction:** Only notifications with platform "Platform Navigation" can be deleted
- **Response:** JSON with success status

### 4.3 Authentication Flow

The system implements a sophisticated authentication flow:

#### Initial Authentication
- ✅ Uses stored credentials from database (`get_platform_credentials()`)
- ✅ Simulates browser login with proper headers and cookies
- ✅ Manages session cookies in `/cookies/` directory

#### Session Maintenance
- ✅ Cookie files named as `[username]_[host].txt`
- ✅ Automatic re-authentication when session expires
- ✅ Detection of login redirects to trigger re-authentication

#### Multi-Platform Support
- ✅ Handles authentication for multiple LMS sub-platforms
- ✅ Configurable login endpoints for each platform
- ✅ Special handling for platform-specific requirements

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

### 6.1 HTML Content Rewriting

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

### 6.2 Special Handling for Resource Types

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

### 7.1 Notification Sources



#### Leave and Absence System
- ✅ Special handling for leave portal notifications
- ✅ Custom parsing logic for platform-specific format

#### Platform Navigation
- ✅ System-generated notifications
- ✅ Can be deleted through API

### 7.2 Notification Processing

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

## ⚠️ Error Handling and Logging

### 8.1 Error Types Handled

| Error Type | Description |
|------------|-------------|
| Database connection errors | Connection failures and timeouts |
| Authentication failures | Invalid credentials or expired sessions |
| Session expiration | Automatic re-authentication triggers |
| Resource not found | 404 handling for missing resources |
| Invalid parameters | Parameter validation errors |
| Proxy connection issues | Network connectivity problems |

### 8.2 Logging System

All significant operations logged to `php_errors.log`:

- ✅ Timestamped entries for debugging
- ✅ Detailed information about:
  - Authentication attempts
  - Proxy requests/responses
  - URL rewriting operations
  - Error conditions

**Example log entry:**
```
[2023-10-05 14:30:22] LMS Sub-platform Direct Link request for: 
Üniversite Ortak/University Common, user: jsmith
```

---

## 🔒 Security Considerations

### 9.1 Implemented Protections

| Protection | Description |
|------------|-------------|
| **Input Validation** | Parameters are checked before use |
| **Session Management** | Secure cookie handling |
| **Error Handling** | No sensitive information exposed to clients |
| **Content-Type Headers** | Properly set for all responses |
| **CORS Configuration** | Controlled cross-origin access |

### 9.2 Security Notes

- ✅ Credentials are stored encrypted in the database
- ✅ Session cookies are stored server-side, not exposed to client
- ✅ No direct database access from client-side
- ✅ Special handling for sensitive operations (e.g., notification deletion requires platform check)

---

## ⚙️ Configuration and Deployment

### 10.1 Required Configuration

#### Database
- ✅ Update `DB_CONFIG` in `db_connection.php` with proper credentials
- ✅ Create required tables (notifications, platform_credentials)

#### Cookie Directory
- ✅ Create `cookies/` directory with proper permissions
- ✅ Should be outside web root for security

#### Error Logging
- ✅ Ensure `php_errors.log` is writable
- ✅ Configure log rotation for production

### 10.2 Deployment Requirements

| Requirement | Version/Details |
|-------------|----------------|
| PHP | 7.4+ (based on code patterns) |
| Database | MySQL/MariaDB |
| Extensions | cURL extension (for proxy functionality) |
| Sessions | Session support enabled |
| Permissions | Proper directory permissions |

---



## 🔧 Troubleshooting

### Common Issues and Solutions

#### Authentication Failures
**Symptoms:**
- Users cannot access platforms
- Session errors in logs

**Solutions:**
- ✅ Check `php_errors.log` for authentication details
- ✅ Verify credentials in database
- ✅ Ensure cookie directory is writable

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

The **LEAVE RMS** backend provides a sophisticated integration layer between multiple LMS platforms and a leave management system. Its proxy-based architecture, comprehensive URL rewriting, and notification aggregation create a unified user experience while maintaining compatibility with disparate backend systems.

### Key Features

- ✅ **Extensible Design** - New LMS platforms can be added through configuration
- ✅ **Robust Error Handling** - Comprehensive logging for maintenance and troubleshooting
- ✅ **Security Focused** - Multiple layers of protection and validation
- ✅ **Performance Optimized** - Efficient caching and session management
- ✅ **User Friendly** - Seamless navigation across multiple platforms

### Future Enhancements

- 🔄 **API Rate Limiting** - Prevent abuse and ensure fair usage
- 🔄 **Advanced Caching** - Improve performance for frequently accessed resources
- 🔄 **Real-time Notifications** - WebSocket support for live updates
- 🔄 **Analytics Dashboard** - Usage statistics and monitoring
- 🔄 **Multi-language Support** - Internationalization for global users

---

