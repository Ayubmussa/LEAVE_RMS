<?php
/**
 * LEAVE RMS - Database Connection and Management
 *
 * Handles database connection, user authentication, platform management,
 * notifications, and database setup for the LEAVE RMS system.
 *
 * @author System Administrator
 * @version 2.0
 */

// =============================================================================
// DATABASE CONFIGURATION
// =============================================================================

const DB_CONFIG = [
    'host' => 'localhost',
    'username' => 'fnlsszma_fnlsszma',
    'password' => 'CJ8hW8GlaJWC',
    'database' => 'fnlsszma_FIU_GLOBAL'
];

// =============================================================================
// DATABASE CONNECTION
// =============================================================================

/**
 * Creates and returns a database connection
 * @return mysqli Database connection object
 */
function createDatabaseConnection() {
    // Use strict mysqli error mode so we can catch failures
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(
            DB_CONFIG['host'],
            DB_CONFIG['username'],
            DB_CONFIG['password'],
            DB_CONFIG['database']
        );
        // Ensure UTF-8 everywhere
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Throwable $e) {
        // Log and return a JSON error response so callers don't get a blank page
        $logFile = __DIR__ . '/php_errors.log';
        @file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] DB connect error: " . $e->getMessage(), FILE_APPEND);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection error']);
        exit;
    }
}

// Initialize database connection
$conn = createDatabaseConnection();

// =============================================================================
// INPUT SANITIZATION
// =============================================================================

/**
 * Sanitizes input data to prevent SQL injection and XSS attacks
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// =============================================================================
// USER AUTHENTICATION
// =============================================================================

/**
 * Validates user credentials
 * @param string $usernameOrEmail Username or email
 * @param string $password Password
 * @return array|false User data if valid, false otherwise
 */
function validate_user($usernameOrEmail, $password) {
    global $conn;
    
    $usernameOrEmail = sanitize_input($usernameOrEmail);
    
    // Check both username and email
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check password using multiple methods for compatibility
        if (isPasswordValid($password, $user['password'])) {
            $stmt->close();
            return $user;
        }
    }
    
    $stmt->close();
    return false;
}

/**
 * Checks if password is valid using multiple validation methods
 * @param string $inputPassword Password from user input
 * @param string $storedPassword Password stored in database
 * @return bool True if password is valid
 */
function isPasswordValid($inputPassword, $storedPassword) {
    // Check if password matches directly (for plain text passwords)
    if ($inputPassword === $storedPassword) {
        return true;
    }
    
    // Check if password matches SHA-256 hash (for hashed passwords from frontend)
    if (hash('sha256', $storedPassword) === $inputPassword) {
        return true;
    }
    
    // Check if password is already a hash and matches directly
    if ($storedPassword === $inputPassword) {
        return true;
    }
    
    return false;
}

// =============================================================================
// USERS MANAGEMENT
// =============================================================================

/**
 * Gets all users from database
 * @return array Array of user data
 */
function get_all_users() {
    global $conn;

    $sql = "SELECT 
                u.id, u.username, u.email, u.created_at,
                a.id AS admin_id, a.role AS admin_role
            FROM users u
            LEFT JOIN admins a ON a.username = u.username
            ORDER BY u.created_at DESC";
    $result = $conn->query($sql);

    $users = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    return $users;
}

// =============================================================================
// PLATFORM MANAGEMENT
// =============================================================================

/**
 * Gets all platforms from database
 * @return array Array of platform data
 */
function get_platforms() {
    global $conn;
    
    $sql = "SELECT * FROM platforms ORDER BY name";
    $result = $conn->query($sql);
    
    $platforms = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $platforms[] = $row;
        }
    }
    
    return $platforms;
}

/**
 * Gets platform credentials for a user
 * @param string $username Username
 * @param string $platform Platform name
 * @return array|false Platform credentials or false if not found
 */
function get_platform_credentials($username, $platform) {
	global $conn;
	
	// Fetch credentials from users table instead of platform_credentials
	$sql = "SELECT username, password FROM users WHERE username = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	$stmt->close();
	
	if (!$user) {
		return false;
	}
	
	// Return in the same shape as old platform_credentials consumers expect
	return [
		'username' => $username,
		'platform' => $platform,
		'platform_username' => $user['username'],
		'platform_password' => $user['password']
	];
}

/**
 * Saves platform credentials for a user
 * @param string $username Username
 * @param string $platform Platform name
 * @param string $platform_username Platform username
 * @param string $platform_password Platform password
 * @return bool True if successful
 */
function save_platform_credentials($username, $platform, $platform_username, $platform_password) {
    global $conn;
    
    $sql = "INSERT INTO platform_credentials (username, platform, platform_username, platform_password) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            platform_username = VALUES(platform_username), 
            platform_password = VALUES(platform_password)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $platform, $platform_username, $platform_password);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

// =============================================================================
// NOTIFICATION MANAGEMENT
// =============================================================================

/**
 * Gets notifications for a user
 * @param string $username Username
 * @return array Array of notifications
 */
function get_notifications($username) {
    global $conn;
    
    $sql = "SELECT * FROM notifications WHERE username = ? AND status = 'unread' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $notifications = [];
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    return $notifications;
}

/**
 * Gets a notification by ID
 * @param int $id Notification ID
 * @return array|false Notification data or false if not found
 */
function get_notification_by_id($id) {
    global $conn;
    
    $sql = "SELECT * FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $notification = $result->fetch_assoc();
    $stmt->close();
    
    return $notification;
}

/**
 * Deletes a notification by ID
 * @param int $id Notification ID
 * @return bool True if successful
 */
function delete_notification($id) {
    global $conn;
    
    $sql = "DELETE FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Marks a notification as read
 * @param int $id Notification ID
 * @return bool True if successful
 */
function markNotificationAsRead($id) {
    global $conn;
    
    $sql = "UPDATE notifications SET status = 'read' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Toggles notification read status
 * @param int $id Notification ID
 * @param bool $readStatus True for read, false for unread
 * @return bool True if successful
 */
function toggleNotificationReadStatus($id, $readStatus) {
    global $conn;
    
    $status = $readStatus ? 'read' : 'unread';
    $sql = "UPDATE notifications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Toggles all notifications read status for a user
 * @param string $username Username
 * @param bool $readStatus True for read, false for unread
 * @return bool True if successful
 */
function toggleAllNotificationsReadStatus($username, $readStatus) {
    global $conn;
    
    $status = $readStatus ? 'read' : 'unread';
    $sql = "UPDATE notifications SET status = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status, $username);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Clears old notifications for a user
 * @param string $username Username
 * @param int $daysOld Number of days old to consider for deletion
 * @return bool True if successful
 */
function clearOldNotifications($username, $daysOld = 30) {
    global $conn;
    
    $sql = "DELETE FROM notifications WHERE username = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $username, $daysOld);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Saves a notification to the database
 * @param string $username Username
 * @param string $platform Platform name
 * @param string $message Notification message
 * @param string $url Notification URL
 * @return bool True if successful
 */
function save_notification($username, $platform, $message, $url) {
    global $conn;
    
    // Check if notification already exists to avoid duplicates
    $sql = "SELECT id FROM notifications WHERE username = ? AND platform = ? AND message = ? AND url = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $platform, $message, $url);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        return true; // Already exists
    }
    $stmt->close();
    
    // Insert new notification
    $sql = "INSERT INTO notifications (username, platform, message, url, status) VALUES (?, ?, ?, ?, 'unread')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $platform, $message, $url);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Saves multiple notifications to the database
 * @param string $username Username
 * @param array $notifications Array of notifications
 * @return bool True if successful
 */
function save_notifications($username, $notifications) {
    global $conn;
    
    foreach ($notifications as $notification) {
        $platform = $notification['platform'];
        $message = $notification['message'];
        $url = $notification['url'];
        
        $success = save_notification($username, $platform, $message, $url);
        if (!$success) {
            return false;
        }
    }
    
    return true;
}

// =============================================================================
// ADMIN MANAGEMENT
// =============================================================================

/**
 * Validates admin credentials
 * @param string $username Admin username
 * @param string $password Admin password
 * @return array|false Admin data if valid, false otherwise
 */
function validate_admin($username, $password) {
    global $conn;
    
    $username = sanitize_input($username);
    
    $sql = "SELECT * FROM admins WHERE username = ? AND is_active = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Check password using multiple methods for compatibility
        if (isPasswordValid($password, $admin['password'])) {
            // Update last login
            $updateSql = "UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $admin['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            $stmt->close();
            return $admin;
        }
    }
    
    $stmt->close();
    return false;
}

/**
 * Gets all admins from database
 * @return array Array of admin data
 */
function get_all_admins() {
    global $conn;
    
    $sql = "SELECT id, username, email, role, is_active, created_at, last_login FROM admins ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $admins = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
    }
    
    return $admins;
}

/**
 * Gets admins filtered by role
 * @param string $role Role to filter by (e.g., 'admin')
 * @return array Array of admin data
 */
function get_admins_by_role($role) {
    global $conn;

    $sql = "SELECT id, username, email, role, is_active, created_at, last_login FROM admins WHERE role = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();

    $admins = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
    }

    $stmt->close();
    return $admins;
}

/**
 * Gets admin by ID
 * @param int $id Admin ID
 * @return array|false Admin data or false if not found
 */
function get_admin_by_id($id) {
    global $conn;
    
    $sql = "SELECT id, username, email, role, is_active, created_at, last_login FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    return $admin;
}

/**
 * Gets admin by username
 * @param string $username Admin username
 * @return array|false Admin data or false if not found
 */
function get_admin_by_username($username) {
    global $conn;
    $username = sanitize_input($username);

    $sql = "SELECT id, username, email, role, is_active, created_at, last_login FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $admin = $result->fetch_assoc();
    $stmt->close();

    return $admin;
}

/**
 * Creates a new admin user
 * @param string $username Admin username
 * @param string $password Admin password
 * @param string $email Admin email
 * @param string $role Admin role
 * @return bool True if successful
 */
function create_admin($username, $password, $email, $role = 'admin') {
    global $conn;
    
    $username = sanitize_input($username);
    $email = sanitize_input($email);
    $role = sanitize_input($role);
    
    // Check if admin already exists
    $checkSql = "SELECT id FROM admins WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $checkStmt->close();
        return false; // Admin already exists
    }
    $checkStmt->close();
    
    $sql = "INSERT INTO admins (username, password, email, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $email, $role);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Updates admin information
 * @param int $id Admin ID
 * @param string $email Admin email
 * @param string $role Admin role
 * @param bool $isActive Admin active status
 * @return bool True if successful
 */
function update_admin($id, $username, $email, $role, $isActive) {
    global $conn;
    
    $username = sanitize_input($username);
    $email = sanitize_input($email);
    $role = sanitize_input($role);
    $isActive = $isActive ? 1 : 0;
    
    $sql = "UPDATE admins SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $username, $email, $role, $isActive, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Changes admin password
 * @param int $id Admin ID
 * @param string $newPassword New password
 * @return bool True if successful
 */
function change_admin_password($id, $newPassword) {
    global $conn;
    
    $sql = "UPDATE admins SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newPassword, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Deletes an admin
 * @param int $id Admin ID
 * @return bool True if successful
 */
function delete_admin($id) {
    global $conn;
    
    $sql = "DELETE FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

// =============================================================================
// DASHBOARD STATISTICS
// =============================================================================

/**
 * Gets total number of users
 * @return int Total user count
 */
function get_total_users() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

/**
 * Gets total number of active admins
 * @return int Total active admin count
 */
function get_active_admins_count() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM admins WHERE is_active = TRUE";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

/**
 * Gets total number of platforms
 * @return int Total platform count
 */
function get_total_platforms() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM platforms";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return $row['total'];
}



/**
 * Gets dashboard statistics
 * @return array Array of dashboard statistics
 */
function get_dashboard_stats() {
    return [
        'total_users' => get_total_users(),
        'active_admins' => get_active_admins_count(),
        'total_platforms' => get_total_platforms(),
        'recent_logins' => get_recent_admin_logins(),
        'system_health' => get_system_health_status()
    ];
}

/**
 * Gets recent admin logins (last 7 days)
 * @return array Array of recent login data
 */
function get_recent_admin_logins() {
    global $conn;
    
    $sql = "SELECT username, last_login FROM admins 
            WHERE last_login IS NOT NULL 
            AND last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY last_login DESC 
            LIMIT 5";
    
    $result = $conn->query($sql);
    $logins = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $logins[] = $row;
        }
    }
    
    return $logins;
}

/**
 * Gets system health status
 * @return array Array of system health indicators
 */
function get_system_health_status() {
    global $conn;
    
    // Check database connection
    $db_status = $conn->ping() ? 'healthy' : 'error';
    
    // Check if tables exist
    $tables = ['users', 'admins', 'platforms', 'platform_credentials'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missing_tables[] = $table;
        }
    }
    
    $tables_status = empty($missing_tables) ? 'healthy' : 'warning';
    
    return [
        'database' => $db_status,
        'tables' => $tables_status,
        'missing_tables' => $missing_tables
    ];
}

// =============================================================================
// DATABASE SETUP
// =============================================================================

/**
 * Sets up the database and creates all necessary tables
 */
function setup_database() {
    global $conn;
    
    createDatabaseIfNotExists();
    createTables();
    insertSampleData();
}

/**
 * Creates the database if it doesn't exist
 */
function createDatabaseIfNotExists() {
    global $conn;
    
    $database = DB_CONFIG['database'];
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    $conn->query($sql);
    
    // Select the database
    $conn->select_db($database);
}

/**
 * Creates all necessary tables
 */
function createTables() {
    global $conn;
    
    createUsersTable();
    createPlatformsTable();
    dropTableIfExists('notifications');
    dropTableIfExists('platform_credentials');
    createAdminTable();
    createAnnouncementsTable();
    createDiningMenuTable();
    createHolidaysDaysOffTable();
}

/**
 * Creates the users table
 */
function createUsersTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(50),
        role ENUM('instructor','student') DEFAULT 'instructor',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql);

    // Ensure role column exists on existing installations
    addColumnIfNotExists('users', 'role', "ENUM('instructor','student') DEFAULT 'instructor' AFTER email");
}

/**
 * Creates the platforms table
 */
function createPlatformsTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS platforms (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        url VARCHAR(255) NOT NULL,
        notifications_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql);
    
    // Add notifications_url column if it doesn't exist
    addColumnIfNotExists('platforms', 'notifications_url', 'VARCHAR(255) AFTER url');
}

/**
 * Creates the notifications table
 */
function createNotificationsTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        platform VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        url VARCHAR(255) NOT NULL,
        status ENUM('unread','read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql);
    
    // Add status column if it doesn't exist
    addColumnIfNotExists('notifications', 'status', "ENUM('unread','read') DEFAULT 'unread' AFTER url");
}

/**
 * Creates the platform_credentials table
 */
function createPlatformCredentialsTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS platform_credentials (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        platform VARCHAR(100) NOT NULL,
        platform_username VARCHAR(100) NOT NULL,
        platform_password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_platform (username, platform)
    )";
    
    $conn->query($sql);
}

/**
 * Creates the admin table
 */
function createAdminTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(50),
        role ENUM('super_admin', 'admin') DEFAULT 'admin',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )";
    
    $conn->query($sql);
}

/**
 * Creates the announcements table
 */
function createAnnouncementsTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS announcements (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        author_id INT(6) UNSIGNED NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        target_audience ENUM('students','instructors','all') DEFAULT 'all',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE CASCADE
    )";
    
    $conn->query($sql);
    // Ensure target_audience column exists in older installations
    addColumnIfNotExists('announcements', 'target_audience', "ENUM('students','instructors','all') DEFAULT 'all' AFTER priority");
}

/**
 * Creates the dining menu table
 */
function createDiningMenuTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS dining_menu (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        day_of_week VARCHAR(20) NOT NULL,
        breakfast_menu TEXT,
        breakfast_start_time TIME,
        breakfast_end_time TIME,
        lunch_menu TEXT,
        lunch_start_time TIME,
        lunch_end_time TIME,
        is_recurring BOOLEAN DEFAULT FALSE,
        created_by INT(6) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE CASCADE,
        INDEX idx_day_of_week (day_of_week),
        INDEX idx_is_recurring (is_recurring)
    )";
    
    $conn->query($sql);
    
    // Add new columns to existing table if they don't exist
    addColumnIfNotExists('dining_menu', 'day_of_week', 'VARCHAR(20) NOT NULL DEFAULT "Monday"');
    addColumnIfNotExists('dining_menu', 'is_recurring', 'BOOLEAN DEFAULT FALSE');
}

/**
 * Creates the holidays and days off table
 */
function createHolidaysDaysOffTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS holidays_days_off (
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
    )";
    
    $conn->query($sql);
}

/**
 * Adds a column to a table if it doesn't exist
 * @param string $table Table name
 * @param string $column Column name
 * @param string $definition Column definition
 */
function addColumnIfNotExists($table, $column, $definition) {
    global $conn;
    
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
    if ($result->num_rows === 0) {
        $conn->query("ALTER TABLE $table ADD COLUMN $column $definition");
    }
}

/**
 * Drops a table if it exists (safe operation)
 * @param string $table Table name
 */
function dropTableIfExists($table) {
    global $conn;
    $conn->query("DROP TABLE IF EXISTS `$table`");
}

/**
 * Inserts sample data if tables are empty
 */
function insertSampleData() {
    global $conn;
    
    insertSampleUser();
    insertSamplePlatforms();
    insertSampleAdmin();
}

/**
 * Inserts sample user if users table is empty
 */
function insertSampleUser() {
    global $conn;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", 'admin', 'admin123', 'admin@example.com');
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Inserts sample platforms if platforms table is empty
 */
function insertSamplePlatforms() {
    global $conn;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM platforms");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        insertAllSamplePlatforms();
    } else {
        updateExistingPlatforms();
    }
}

/**
 * Inserts all platforms
 */
function insertAllSamplePlatforms() {
    global $conn;
    
    $platforms = [
        [
            'name' => 'Leave and Absence',
            'description' => 'Leave and Absence Portal',
            'url' => 'https://leave.final.digital/index.php',
            'notifications_url' => 'https://leave.final.digital/notifications/all_notifications.php'
        ],
        [
            'name' => 'RMS',
            'description' => 'Description for RMS',
            'url' => 'https://rms.final.digital/Dashboard/home.php',
            'notifications_url' => 'https://rms.final.digital/Dashboard/notifications.php'
        ],
        [
            'name' => 'SIS',
            'description' => 'Student Information System',
            'url' => 'https://sis.final.edu.tr',
            'notifications_url' => null
        ],
        [
            'name' => 'LMS',
            'description' => 'Description for LMS',
            'url' => 'https://lms0.final.edu.tr',
            'notifications_url' => null
        ]
    ];
    
    $sql = "INSERT INTO platforms (name, description, url, notifications_url) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($platforms as $platform) {
        $stmt->bind_param("ssss", 
            $platform['name'], 
            $platform['description'], 
            $platform['url'], 
            $platform['notifications_url']
        );
        $stmt->execute();
    }
    
    $stmt->close();
}

/**
 * Updates existing platforms with new data
 */
function updateExistingPlatforms() {
    global $conn;
    
    // Update RMS platform notifications URL
    $conn->query("UPDATE platforms SET notifications_url = 'https://rms.final.digital/Dashboard/notifications.php' WHERE name = 'RMS'");
    
    // Add or update Leave portal
    addOrUpdatePlatform('Leave and Absence', 'Leave and Absence Portal', 
        'https://leave.final.digital/index.php', 
        'https://leave.final.digital/notifications/all_notifications.php'
    );
    
    // Add SIS if it doesn't exist
    addPlatformIfNotExists('SIS', 'Student Information System', 'https://sis.final.edu.tr', null);
    
    // Add LMS if it doesn't exist
    addPlatformIfNotExists('LMS', 'Description for LMS', 'https://lms0.final.edu.tr', null);
    
    // Add Document Application System for students if it doesn't exist
    addPlatformIfNotExists('Document Application System', 'Document Application System for Students', 'https://docs.final.edu.tr/pages/form', null);
    
    // Add Summer School Application for students if it doesn't exist
    addPlatformIfNotExists('Summer School Application', 'Summer School Application / Yaz Okulu Başvurusu', 'https://online.final.edu.tr/yazokulu/login.php', null);
    
    // Add Accommodation Booking Portal for students if it doesn't exist
    addPlatformIfNotExists('Accommodation Booking Portal', 'Accommodation Booking Portal for Students', 'https://dorms.final.edu.tr/', null);
    
            // Add Support Center for students if it doesn't exist
        addPlatformIfNotExists('Support Center', 'Support Center / Destek Merkezine', 'https://destek.final.edu.tr/index.php', null);

        // Add Student Exam Registration for students if it doesn't exist
        addPlatformIfNotExists('Student Exam Registration', 'Student Exam Registration for Students', 'https://online.final.edu.tr/exam/', null);

        // Add Exemption exam form for students if it doesn't exist
        addPlatformIfNotExists('Exemption exam form', 'Exemption exam form for Students', 'https://online.final.edu.tr/muafiyet', null);

        // Add Resit Exams Application for students if it doesn't exist
        addPlatformIfNotExists('Resit Exams Application', 'Resit Exams Application / Bütünleme Sınavları Başvurusu', 'https://online.final.edu.tr/resit/login.php', null);


}

/**
 * Adds or updates a platform
 * @param string $name Platform name
 * @param string $description Platform description
 * @param string $url Platform URL
 * @param string|null $notificationsUrl Notifications URL
 */
function addOrUpdatePlatform($name, $description, $url, $notificationsUrl) {
    global $conn;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM platforms WHERE name = '$name'");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sql = "INSERT INTO platforms (name, description, url, notifications_url) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $description, $url, $notificationsUrl);
        $stmt->execute();
        $stmt->close();
    } else {
        $sql = "UPDATE platforms SET url = ?, notifications_url = ? WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $url, $notificationsUrl, $name);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Adds a platform if it doesn't exist
 * @param string $name Platform name
 * @param string $description Platform description
 * @param string $url Platform URL
 * @param string|null $notificationsUrl Notifications URL
 */
function addPlatformIfNotExists($name, $description, $url, $notificationsUrl) {
    global $conn;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM platforms WHERE name = '$name'");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sql = "INSERT INTO platforms (name, description, url, notifications_url) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $description, $url, $notificationsUrl);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Inserts sample admin if admins table is empty
 */
function insertSampleAdmin() {
    global $conn;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM admins");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sql = "INSERT INTO admins (username, password, email, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $adminUsername = 'admin';
        $adminPassword = 'admin123';
        $adminEmail = 'admin@finalglobal.com';
        $adminRole = 'super_admin';
        $stmt->bind_param("ssss", $adminUsername, $adminPassword, $adminEmail, $adminRole);
        $stmt->execute();
        $stmt->close();
    }
}

// =============================================================================
// ANNOUNCEMENTS MANAGEMENT
// =============================================================================

/**
 * Gets all announcements
 * @return array Array of announcements
 */
function get_all_announcements() {
    global $conn;
    
    $sql = "SELECT a.*, adm.username as author_name 
            FROM announcements a 
            JOIN admins adm ON a.author_id = adm.id 
            ORDER BY a.created_at DESC";
    $result = $conn->query($sql);
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    
    return $announcements;
}

/**
 * Gets announcement by ID
 * @param int $id Announcement ID
 * @return array|false Announcement data or false if not found
 */
function get_announcement_by_id($id) {
    global $conn;
    
    $sql = "SELECT a.*, adm.username as author_name 
            FROM announcements a 
            JOIN admins adm ON a.author_id = adm.id 
            WHERE a.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $announcement = $result->fetch_assoc();
    $stmt->close();
    
    return $announcement;
}

/**
 * Creates a new announcement
 * @param string $title Announcement title
 * @param string $content Announcement content
 * @param int $author_id Author admin ID
 * @param string $priority Priority level
 * @return bool Success status
 */
function create_announcement($title, $content, $author_id, $priority = 'medium', $target_audience = 'all') {
    global $conn;
    
    $sql = "INSERT INTO announcements (title, content, author_id, priority, target_audience) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $title, $content, $author_id, $priority, $target_audience);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Updates an announcement
 * @param int $id Announcement ID
 * @param string $title Announcement title
 * @param string $content Announcement content
 * @param string $priority Priority level
 * @param bool $is_active Active status
 * @return bool Success status
 */
function update_announcement($id, $title, $content, $priority = 'medium', $is_active = true, $target_audience = null) {
    global $conn;
    
    if ($target_audience !== null) {
        $sql = "UPDATE announcements SET title = ?, content = ?, priority = ?, is_active = ?, target_audience = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisi", $title, $content, $priority, $is_active, $target_audience, $id);
    } else {
        $sql = "UPDATE announcements SET title = ?, content = ?, priority = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $title, $content, $priority, $is_active, $id);
    }
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Deletes an announcement
 * @param int $id Announcement ID
 * @return bool Success status
 */
function delete_announcement($id) {
    global $conn;
    
    $sql = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Gets active announcements for public display
 * @return array Array of active announcements
 */
function get_active_announcements() {
    global $conn;
    
    $sql = "SELECT a.*, adm.username as author_name 
            FROM announcements a 
            JOIN admins adm ON a.author_id = adm.id 
            WHERE a.is_active = 1 
            ORDER BY a.priority DESC, a.created_at DESC";
    $result = $conn->query($sql);
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    
    return $announcements;
}

// =============================================================================
// DINING MENU MANAGEMENT
// =============================================================================

/**
 * Gets all dining menu entries
 * @return array Array of dining menu entries
 */
function get_all_dining_menus() {
    global $conn;
    
    $sql = "SELECT dm.*, adm.username as created_by_name 
            FROM dining_menu dm 
            JOIN admins adm ON dm.created_by = adm.id 
            ORDER BY dm.date DESC";
    $result = $conn->query($sql);
    
    $menus = [];
    while ($row = $result->fetch_assoc()) {
        $menus[] = $row;
    }
    
    return $menus;
}

/**
 * Gets dining menu by date
 * @param string $date Date in Y-m-d format
 * @return array|false Menu data or false if not found
 */
function get_dining_menu_by_date($date) {
    global $conn;
    
    $sql = "SELECT dm.*, adm.username as created_by_name 
            FROM dining_menu dm 
            JOIN admins adm ON dm.created_by = adm.id 
            WHERE dm.date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $menu = $result->fetch_assoc();
    $stmt->close();
    
    return $menu;
}

/**
 * Gets dining menu by ID
 * @param int $id Menu ID
 * @return array|false Menu data or false if not found
 */
function get_dining_menu_by_id($id) {
    global $conn;
    
    $sql = "SELECT dm.*, adm.username as created_by_name 
            FROM dining_menu dm 
            JOIN admins adm ON dm.created_by = adm.id 
            WHERE dm.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $menu = $result->fetch_assoc();
    $stmt->close();
    
    return $menu;
}

/**
 * Creates a new dining menu entry
 * @param string $date Date in Y-m-d format
 * @param string $breakfast_menu Breakfast menu text
 * @param string $breakfast_start_time Breakfast start time
 * @param string $breakfast_end_time Breakfast end time
 * @param string $lunch_menu Lunch menu text
 * @param string $lunch_start_time Lunch start time
 * @param string $lunch_end_time Lunch end time
 * @param int $created_by Admin ID who created the menu
 * @param bool $is_recurring Whether this menu should be recurring
 * @return bool Success status
 */
function create_dining_menu($date, $breakfast_menu, $breakfast_start_time, $breakfast_end_time, 
                          $lunch_menu, $lunch_start_time, $lunch_end_time, $created_by, $is_recurring = false) {
    global $conn;
    
    // Debug logging
    error_log("Creating dining menu for date: $date, created_by: $created_by, is_recurring: " . ($is_recurring ? 'true' : 'false'));
    
    // Get day of week from the date
    $day_of_week = date('l', strtotime($date));
    
    $sql = "INSERT INTO dining_menu (date, day_of_week, breakfast_menu, breakfast_start_time, breakfast_end_time, 
                                   lunch_menu, lunch_start_time, lunch_end_time, is_recurring, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("sssssssssi", $date, $day_of_week, $breakfast_menu, $breakfast_start_time, $breakfast_end_time, 
                          $lunch_menu, $lunch_start_time, $lunch_end_time, $is_recurring, $created_by);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Failed to execute statement: " . $stmt->error);
        }
        
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Exception in create_dining_menu: " . $e->getMessage());
        return false;
    }
}

/**
 * Updates a dining menu entry
 * @param int $id Menu ID
 * @param string $breakfast_menu Breakfast menu text
 * @param string $breakfast_start_time Breakfast start time
 * @param string $breakfast_end_time Breakfast end time
 * @param string $lunch_menu Lunch menu text
 * @param string $lunch_start_time Lunch start time
 * @param string $lunch_end_time Lunch end time
 * @return bool Success status
 */
function update_dining_menu($id, $breakfast_menu, $breakfast_start_time, $breakfast_end_time, 
                          $lunch_menu, $lunch_start_time, $lunch_end_time) {
    global $conn;
    
    $sql = "UPDATE dining_menu SET breakfast_menu = ?, breakfast_start_time = ?, breakfast_end_time = ?, 
                                   lunch_menu = ?, lunch_start_time = ?, lunch_end_time = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $breakfast_menu, $breakfast_start_time, $breakfast_end_time, 
                      $lunch_menu, $lunch_start_time, $lunch_end_time, $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Updates all recurring dining menus for a given day of the week
 * Optionally restricts to dates on/after a start date
 * @param string $day_of_week Day of week (e.g., Monday)
 * @param string $breakfast_menu Breakfast menu text
 * @param string $breakfast_start_time Breakfast start time
 * @param string $breakfast_end_time Breakfast end time
 * @param string $lunch_menu Lunch menu text
 * @param string $lunch_start_time Lunch start time
 * @param string $lunch_end_time Lunch end time
 * @param string|null $start_date Inclusive start date (Y-m-d) to limit updates; if null, update all
 * @return int Number of rows updated
 */
function update_recurring_dining_menus($day_of_week, $breakfast_menu, $breakfast_start_time, $breakfast_end_time, 
									 $lunch_menu, $lunch_start_time, $lunch_end_time, $start_date = null) {
    global $conn;

    if ($start_date) {
        $sql = "UPDATE dining_menu 
                SET breakfast_menu = ?, breakfast_start_time = ?, breakfast_end_time = ?,
                    lunch_menu = ?, lunch_start_time = ?, lunch_end_time = ?
                WHERE is_recurring = 1 AND day_of_week = ? AND date >= ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare update_recurring_dining_menus (with date) statement: " . $conn->error);
            return 0;
        }
        $stmt->bind_param(
            "ssssssss",
            $breakfast_menu,
            $breakfast_start_time,
            $breakfast_end_time,
            $lunch_menu,
            $lunch_start_time,
            $lunch_end_time,
            $day_of_week,
            $start_date
        );
    } else {
        $sql = "UPDATE dining_menu 
                SET breakfast_menu = ?, breakfast_start_time = ?, breakfast_end_time = ?,
                    lunch_menu = ?, lunch_start_time = ?, lunch_end_time = ?
                WHERE is_recurring = 1 AND day_of_week = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare update_recurring_dining_menus statement: " . $conn->error);
            return 0;
        }
        $stmt->bind_param(
            "sssssss",
            $breakfast_menu,
            $breakfast_start_time,
            $breakfast_end_time,
            $lunch_menu,
            $lunch_start_time,
            $lunch_end_time,
            $day_of_week
        );
    }

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

/**
 * Deletes a dining menu entry
 * @param int $id Menu ID
 * @return bool Success status
 */
function delete_dining_menu($id) {
    global $conn;
    
    $sql = "DELETE FROM dining_menu WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Creates recurring dining menus for future dates
 * @param string $day_of_week Day of week (Monday, Tuesday, etc.)
 * @param string $breakfast_menu Breakfast menu text
 * @param string $breakfast_start_time Breakfast start time
 * @param string $breakfast_end_time Breakfast end time
 * @param string $lunch_menu Lunch menu text
 * @param string $lunch_start_time Lunch start time
 * @param string $lunch_end_time Lunch end time
 * @param int $created_by Admin ID who created the menu
 * @param int $weeks_ahead Number of weeks ahead to create menus (default 12)
 * @return int Number of menus created
 */
function create_recurring_dining_menus($day_of_week, $breakfast_menu, $breakfast_start_time, $breakfast_end_time, 
                                     $lunch_menu, $lunch_start_time, $lunch_end_time, $created_by, $weeks_ahead = 12) {
    global $conn;
    
    $menus_created = 0;
    $current_date = date('Y-m-d');
    
    // Create menus for the specified number of weeks ahead
    for ($week = 1; $week <= $weeks_ahead; $week++) {
        // Calculate the next occurrence of the day of week
        $next_date = date('Y-m-d', strtotime("next $day_of_week +" . ($week - 1) . " weeks"));
        
        // Skip if the date is in the past
        if ($next_date <= $current_date) {
            continue;
        }
        
        // Check if menu already exists for this date
        $existing_menu = get_dining_menu_by_date($next_date);
        if ($existing_menu) {
            continue; // Skip if menu already exists
        }
        
        // Check if the date is a holiday or weekend
        $holiday = is_date_holiday($next_date);
        if ($holiday) {
            continue; // Skip holidays and weekends
        }
        
        // Create the menu
        $success = create_dining_menu(
            $next_date,
            $breakfast_menu,
            $breakfast_start_time,
            $breakfast_end_time,
            $lunch_menu,
            $lunch_start_time,
            $lunch_end_time,
            $created_by,
            true // Mark as recurring
        );
        
        if ($success) {
            $menus_created++;
        }
    }
    
    return $menus_created;
}

/**
 * Gets today's dining menu
 * @return array|false Today's menu or false if not found
 */
function get_todays_dining_menu() {
    $today = date('Y-m-d');
    return get_dining_menu_by_date($today);
}

// =============================================================================
// HOLIDAYS AND DAYS OFF MANAGEMENT
// =============================================================================

/**
 * Gets all holidays and days off for a specific year
 * @param int $year Year to get holidays for
 * @return array Array of holidays
 */
function get_holidays_by_year($year) {
    global $conn;
    
    $sql = "SELECT h.*, adm.username as created_by_name 
            FROM holidays_days_off h 
            LEFT JOIN admins adm ON h.created_by = adm.id 
            WHERE h.year = ? 
            ORDER BY h.date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $holidays = [];
    while ($row = $result->fetch_assoc()) {
        $holidays[] = $row;
    }
    $stmt->close();
    
    return $holidays;
}

/**
 * Gets all holidays and days off
 * @return array Array of all holidays
 */
function get_all_holidays() {
    global $conn;
    
    $sql = "SELECT h.*, adm.username as created_by_name 
            FROM holidays_days_off h 
            LEFT JOIN admins adm ON h.created_by = adm.id 
            ORDER BY h.year DESC, h.date ASC";
    $result = $conn->query($sql);
    
    $holidays = [];
    while ($row = $result->fetch_assoc()) {
        $holidays[] = $row;
    }
    
    return $holidays;
}

/**
 * Gets holiday by date
 * @param string $date Date in Y-m-d format
 * @return array|false Holiday data or false if not found
 */
function get_holiday_by_date($date) {
    global $conn;
    
    $sql = "SELECT h.*, adm.username as created_by_name 
            FROM holidays_days_off h 
            LEFT JOIN admins adm ON h.created_by = adm.id 
            WHERE h.date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $holiday = $result->fetch_assoc();
    $stmt->close();
    
    return $holiday;
}

/**
 * Gets holiday by ID
 * @param int $id Holiday ID
 * @return array|false Holiday data or false if not found
 */
function get_holiday_by_id($id) {
    global $conn;
    
    $sql = "SELECT h.*, adm.username as created_by_name 
            FROM holidays_days_off h 
            LEFT JOIN admins adm ON h.created_by = adm.id 
            WHERE h.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $holiday = $result->fetch_assoc();
    $stmt->close();
    
    return $holiday;
}

/**
 * Creates a new holiday entry
 * @param string $date Date in Y-m-d format
 * @param int $year Year
 * @param string $day_of_week Day of week
 * @param string $holiday_name Holiday name
 * @param string $type Holiday type
 * @param string $description Description
 * @param bool $is_recurring Is recurring holiday
 * @param int $created_by Admin ID who created the holiday
 * @return bool Success status
 */
function create_holiday($date, $year, $day_of_week, $holiday_name, $type = 'holiday', $description = '', $is_recurring = false, $created_by = null) {
    global $conn;
    
    $sql = "INSERT INTO holidays_days_off (date, year, day_of_week, holiday_name, type, description, is_recurring, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $created_by_int = (int)$created_by;
    $stmt->bind_param("sissssii", $date, $year, $day_of_week, $holiday_name, $type, $description, $is_recurring, $created_by_int);
    $result = $stmt->execute();
    $stmt->close();
    
    // If holiday was created successfully, clean up any dining menus for this date
    if ($result) {
        cleanup_dining_menus_for_holidays($date);
    }
    
    return $result;
}

/**
 * Updates a holiday entry
 * @param int $id Holiday ID
 * @param string $date Date in Y-m-d format
 * @param int $year Year
 * @param string $day_of_week Day of week
 * @param string $holiday_name Holiday name
 * @param string $type Holiday type
 * @param string $description Description
 * @param bool $is_recurring Is recurring holiday
 * @return bool Success status
 */
function update_holiday($id, $date, $year, $day_of_week, $holiday_name, $type = 'holiday', $description = '', $is_recurring = false) {
    global $conn;
    
    // Get the old date before updating
    $oldDate = null;
    $oldSql = "SELECT date FROM holidays_days_off WHERE id = ?";
    $oldStmt = $conn->prepare($oldSql);
    $oldStmt->bind_param("i", $id);
    $oldStmt->execute();
    $oldResult = $oldStmt->get_result();
    if ($oldRow = $oldResult->fetch_assoc()) {
        $oldDate = $oldRow['date'];
    }
    $oldStmt->close();
    
    $sql = "UPDATE holidays_days_off SET date = ?, year = ?, day_of_week = ?, holiday_name = ?, type = ?, description = ?, is_recurring = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $id_int = (int)$id;
    $stmt->bind_param("sissssii", $date, $year, $day_of_week, $holiday_name, $type, $description, $is_recurring, $id_int);
    $result = $stmt->execute();
    $stmt->close();
    
    // If holiday was updated successfully, clean up dining menus for both old and new dates
    if ($result) {
        if ($oldDate && $oldDate !== $date) {
            // If date changed, clean up dining menus for both dates
            cleanup_dining_menus_for_holidays($oldDate);
            cleanup_dining_menus_for_holidays($date);
        } else {
            // If date didn't change, just clean up for the current date
            cleanup_dining_menus_for_holidays($date);
        }
    }
    
    return $result;
}

/**
 * Deletes a holiday entry
 * @param int $id Holiday ID
 * @return bool Success status
 */
function delete_holiday($id) {
    global $conn;
    
    $sql = "DELETE FROM holidays_days_off WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Deletes all holidays for a specific year
 * @param int $year Year to delete holidays for
 * @return bool Success status
 */
function delete_holidays_by_year($year) {
    global $conn;
    
    $sql = "DELETE FROM holidays_days_off WHERE year = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $year);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Checks if a date is a holiday or day off
 * @param string $date Date in Y-m-d format
 * @return array|false Holiday data or false if date is available
 */
function is_date_holiday($date) {
    // First check if there's an explicit holiday in the database
    $holiday = get_holiday_by_date($date);
    if ($holiday) {
        return $holiday;
    }
    
    // Check if the date falls on a weekend (Saturday or Sunday)
    $day_of_week = get_day_of_week($date);
    if ($day_of_week === 'Saturday' || $day_of_week === 'Sunday') {
        return [
            'date' => $date,
            'day_of_week' => $day_of_week,
            'holiday_name' => 'Weekend',
            'type' => 'weekend',
            'description' => 'Weekend (Saturday/Sunday)',
            'is_recurring' => true
        ];
    }
    
    return false;
}

/**
 * Gets the day of week for a given date
 * @param string $date Date in Y-m-d format
 * @return string Day of week
 */
function get_day_of_week($date) {
    $timestamp = strtotime($date);
    return date('l', $timestamp);
}

/**
 * Validates date format and returns day of week
 * @param string $date Date string
 * @return array|false Array with date and day_of_week or false if invalid
 */
function validate_and_parse_date($date) {
    // Try different date formats
    $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'Y/m/d'];
    
    foreach ($formats as $format) {
        $parsed = DateTime::createFromFormat($format, $date);
        if ($parsed !== false) {
            return [
                'date' => $parsed->format('Y-m-d'),
                'day_of_week' => $parsed->format('l'),
                'year' => (int)$parsed->format('Y')
            ];
        }
    }
    
    // Try to parse "1st March" format (day with ordinal suffix + month name)
    if (preg_match('/^(\d{1,2})(st|nd|rd|th)?\s+([A-Za-z]+)$/i', trim($date), $matches)) {
        $day = (int)$matches[1];
        $monthName = $matches[3];
        
        // Get current year for the date
        $currentYear = date('Y');
        
        // Create date string in "day month year" format
        $dateString = $day . ' ' . $monthName . ' ' . $currentYear;
        
        // Try to parse using strtotime
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            $parsedDate = date('Y-m-d', $timestamp);
            return [
                'date' => $parsedDate,
                'day_of_week' => date('l', $timestamp),
                'year' => (int)date('Y', $timestamp)
            ];
        }
    }
    
    return false;
}

/**
 * Deletes dining menu entries for a specific date
 * @param string $date Date in Y-m-d format
 * @return bool True if successful
 */
function delete_dining_menu_by_date($date) {
    global $conn;
    
    $sql = "DELETE FROM dining_menu WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Deletes dining menu entries for multiple dates
 * @param array $dates Array of dates in Y-m-d format
 * @return int Number of deleted entries
 */
function delete_dining_menus_for_dates($dates) {
    global $conn;
    
    if (empty($dates)) {
        return 0;
    }
    
    $placeholders = str_repeat('?,', count($dates) - 1) . '?';
    $sql = "DELETE FROM dining_menu WHERE date IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    
    // Create array of references for bind_param
    $types = str_repeat('s', count($dates));
    $params = array_merge([$types], $dates);
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $result = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

/**
 * Deletes dining menus strictly before a cutoff date (YYYY-MM-DD)
 * @param string $cutoffDate
 * @return int Number of deleted entries
 */
function delete_dining_menus_for_past_dates($cutoffDate) {
    global $conn;
    $sql = "DELETE FROM dining_menu WHERE date < ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("s", $cutoffDate);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

/**
 * Checks and deletes dining menus for dates that are now holidays
 * This should be called after holidays are updated
 * @param string $date Optional specific date to check, if not provided checks all dining menu dates
 * @return int Number of deleted dining menus
 */
function cleanup_dining_menus_for_holidays($date = null) {
    global $conn;
    
    $deletedCount = 0;
    
    if ($date) {
        // Check specific date
        if (is_date_holiday($date)) {
            if (delete_dining_menu_by_date($date)) {
                $deletedCount = 1;
            }
        }
    } else {
        // Check all dining menu dates
        $sql = "SELECT DISTINCT date FROM dining_menu ORDER BY date";
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $menuDate = $row['date'];
                if (is_date_holiday($menuDate)) {
                    if (delete_dining_menu_by_date($menuDate)) {
                        $deletedCount++;
                    }
                }
            }
        }
    }
    
    return $deletedCount;
}

// =============================================================================
// INITIALIZATION
// =============================================================================

// Setup the database
setup_database();
?>