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
    'username' => 'root',
    'password' => '',
    'database' => 'leave_rms_db'
];

// =============================================================================
// DATABASE CONNECTION
// =============================================================================

/**
 * Creates and returns a database connection
 * @return mysqli Database connection object
 */
function createDatabaseConnection() {
    $conn = new mysqli(
        DB_CONFIG['host'],
        DB_CONFIG['username'],
        DB_CONFIG['password'],
        DB_CONFIG['database']
    );

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
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
    
    $sql = "SELECT * FROM platform_credentials WHERE username = ? AND platform = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $platform);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $credentials = $result->fetch_assoc();
    $stmt->close();
    
    return $credentials;
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
    createNotificationsTable();
    createPlatformCredentialsTable();
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql);
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
 * Inserts sample data if tables are empty
 */
function insertSampleData() {
    global $conn;
    
    insertSampleUser();
    insertSamplePlatforms();
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

// =============================================================================
// INITIALIZATION
// =============================================================================

// Setup the database
setup_database();
?>