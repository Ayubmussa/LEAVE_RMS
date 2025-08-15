<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connection.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Get the endpoint from the path (if it exists)
$endpoint = end($path_parts);

// Also check for endpoint in query string
if (isset($_GET['endpoint'])) {
    $endpoint = $_GET['endpoint'];
}

// Debug logging
error_log("Admin API - Request Method: " . $request_method);
error_log("Admin API - Endpoint: " . $endpoint);
error_log("Admin API - Path: " . $path);
error_log("Admin API - Path Parts: " . print_r($path_parts, true));

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}



try {
    switch ($request_method) {
        case 'POST':
            // Check if this is a direct admin login request
            if (isset($input['username']) && isset($input['password']) && !isset($input['action'])) {
                handleAdminLogin($input);
                break;
            }
            
            // Check for specific actions in the request
            $action = isset($input['action']) ? $input['action'] : $endpoint;
            
            switch ($action) {
                case 'admin-login':
                    handleAdminLogin($input);
                    break;
                case 'admin-create':
                    handleAdminCreate($input);
                    break;
                case 'admin-update':
                    handleAdminUpdate($input);
                    break;
                case 'admin-delete':
                    handleAdminDelete($input);
                    break;
                case 'admin-change-password':
                    handleAdminChangePassword($input);
                    break;
                case 'announcement-create':
                    handleAnnouncementCreate($input);
                    break;
                case 'announcement-update':
                    handleAnnouncementUpdate($input);
                    break;
                case 'announcement-delete':
                    handleAnnouncementDelete($input);
                    break;
                case 'user-promote-to-admin':
                    handleUserPromoteToAdmin($input);
                    break;
                case 'admin-demote-to-user':
                    handleAdminDemoteToUser($input);
                    break;
                case 'dining-menu-create':
                    handleDiningMenuCreate($input);
                    break;
                case 'dining-menu-update':
                    handleDiningMenuUpdate($input);
                    break;
                case 'dining-menu-delete':
                    handleDiningMenuDelete($input);
                    break;
                case 'holiday-create':
                    handleHolidayCreate($input);
                    break;
                case 'holiday-update':
                    handleHolidayUpdate($input);
                    break;
                case 'holiday-delete':
                    handleHolidayDelete($input);
                    break;
                case 'holiday-delete-by-year':
                    handleHolidayDeleteByYear($input);
                    break;
                case 'holiday-upload':
                    handleHolidayUpload($input);
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
            break;
            
        case 'GET':
            switch ($endpoint) {
                case 'admin-list':
                    handleAdminList();
                    break;
                case 'dashboard-stats':
                    handleDashboardStats();
                    break;
                case 'users-list':
                    handleUsersList();
                    break;
                case 'platforms-list':
                    handlePlatformsList();
                    break;
                case 'announcement-list':
                    handleAnnouncementList();
                    break;
                case 'announcement-get':
                    handleAnnouncementGet();
                    break;
                case 'admin-by-username':
                    handleAdminByUsername();
                    break;
                case 'dining-menu-list':
                    handleDiningMenuList();
                    break;
                case 'dining-menu-get':
                    handleDiningMenuGet();
                    break;
                case 'dining-menu-today':
                    handleDiningMenuToday();
                    break;
                case 'holiday-list':
                    handleHolidayList();
                    break;
                case 'holiday-get':
                    handleHolidayGet();
                    break;
                case 'holiday-export':
                    handleHolidayExport();
                    break;
                case 'holiday-template':
                    handleHolidayTemplate();
                    break;
                case 'check-date-availability':
                    handleCheckDateAvailability();
                    break;
                case 'cleanup-dining-menus':
                    handleCleanupDiningMenus();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

function handleAdminLogin($input) {
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        return;
    }
    
    $admin = validate_admin($input['username'], $input['password']);
    
    if ($admin) {
        echo json_encode([
            'success' => true,
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'role' => $admin['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid admin credentials']);
    }
}

function handleAdminList() {
    // Check if the requesting user is a super_admin
    if (!isset($_GET['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    // Get the current admin's role
    $currentAdmin = get_admin_by_id($_GET['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can view admin list.']);
        return;
    }
    
    // Only manage regular admins, exclude super_admins
    $admins = get_admins_by_role('admin');
    echo json_encode([
        'success' => true,
        'admins' => $admins
    ]);
}

function handleDashboardStats() {
    $stats = get_dashboard_stats();
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function handleAdminCreate($input) {
    // Check if the requesting user is a super_admin
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    // Get the current admin's role
    $currentAdmin = get_admin_by_id($input['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can create new admins.']);
        return;
    }
    
    if (!isset($input['username']) || !isset($input['password']) || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username, password, and email are required']);
        return;
    }
    
    // Force creation as regular admin only
    $success = create_admin($input['username'], $input['password'], $input['email'], 'admin');
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Admin created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create admin. Username might already exist.']);
    }
}

function handleAdminUpdate($input) {
    // Check if the requesting user is a super_admin
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    // Get the current admin's role
    $currentAdmin = get_admin_by_id($input['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can update admins.']);
        return;
    }
    
    if (!isset($input['id']) || !isset($input['username']) || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, username, and email are required']);
        return;
    }
    
    $isActive = isset($input['is_active']) ? $input['is_active'] : true;
    // Lock role to admin (cannot elevate to super_admin via this endpoint)
    $success = update_admin($input['id'], $input['username'], $input['email'], 'admin', $isActive);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Admin updated successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to update admin']);
    }
}

function handleAdminDelete($input) {
    // Check if the requesting user is a super_admin
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    // Get the current admin's role
    $currentAdmin = get_admin_by_id($input['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can delete admins.']);
        return;
    }
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Admin ID is required']);
        return;
    }
    
    $success = delete_admin($input['id']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Admin deleted successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to delete admin']);
    }
}

function handleAdminChangePassword($input) {
    // Check if the requesting user is a super_admin
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    // Get the current admin's role
    $currentAdmin = get_admin_by_id($input['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can change admin passwords.']);
        return;
    }
    
    if (!isset($input['id']) || !isset($input['new_password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Admin ID and new password are required']);
        return;
    }
    
    $success = change_admin_password($input['id'], $input['new_password']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to change password']);
    }
}

function handleUsersList() {
    $users = get_all_users();
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
}

function handlePlatformsList() {
    $platforms = get_platforms();
    echo json_encode([
        'success' => true,
        'platforms' => $platforms
    ]);
}

function handleAdminByUsername() {
    if (!isset($_GET['username'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username is required']);
        return;
    }
    $admin = get_admin_by_username($_GET['username']);
    if ($admin) {
        echo json_encode(['success' => true, 'admin' => $admin]);
    } else {
        echo json_encode(['success' => false]);
    }
}

function handleUserPromoteToAdmin($input) {
    // Only super_admin can promote/demote
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    $currentAdmin = get_admin_by_id($input['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can promote users.']);
        return;
    }

    if (!isset($input['username'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username is required']);
        return;
    }

    // If admin exists -> update role; else create admin with default password
    $username = $input['username'];

    // Look up existing admin by username
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing = $res->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // Update role and activate
        $stmt2 = $conn->prepare("UPDATE admins SET role = ?, is_active = 1 WHERE username = ?");
        $stmt2->bind_param("ss", $role, $username);
        $ok = $stmt2->execute();
        $stmt2->close();
    } else {
        // Find the user's email if any to reuse
        $email = null;
        $stmt3 = $conn->prepare("SELECT email FROM users WHERE username = ?");
        $stmt3->bind_param("s", $username);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        $row3 = $res3->fetch_assoc();
        $stmt3->close();
        if ($row3 && isset($row3['email'])) {
            $email = $row3['email'];
        }

        // Create admin. For now set default password equal to 'admin123' (same as seed).
        $defaultPassword = 'admin123';
        $stmt4 = $conn->prepare("INSERT INTO admins (username, password, email, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt4->bind_param("ssss", $username, $defaultPassword, $email, $role);
        $ok = $stmt4->execute();
        $stmt4->close();
    }

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'User promoted to admin successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to promote user']);
    }
}

function handleAdminDemoteToUser($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    $currentAdmin = get_admin_by_id($input['current_admin_id']);
    if (!$currentAdmin || $currentAdmin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Only Super Administrators can demote admins.']);
        return;
    }

    if (!isset($input['username'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username is required']);
        return;
    }

    global $conn;
    $username = $input['username'];
    $stmt = $conn->prepare("DELETE FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Admin demoted to user successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to demote admin']);
    }
}

// =============================================================================
// ANNOUNCEMENTS HANDLERS
// =============================================================================

function handleAnnouncementList() {
    $announcements = get_all_announcements();
    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);
}

function handleAnnouncementGet() {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Announcement ID is required']);
        return;
    }
    
    $announcement = get_announcement_by_id($_GET['id']);
    
    if ($announcement) {
        echo json_encode([
            'success' => true,
            'announcement' => $announcement
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Announcement not found']);
    }
}

function handleAnnouncementCreate($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['title']) || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and content are required']);
        return;
    }
    
    $priority = isset($input['priority']) ? $input['priority'] : 'medium';
    
    $success = create_announcement($input['title'], $input['content'], $input['current_admin_id'], $priority);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Announcement created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create announcement']);
    }
}

function handleAnnouncementUpdate($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['id']) || !isset($input['title']) || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, title, and content are required']);
        return;
    }
    
    $priority = isset($input['priority']) ? $input['priority'] : 'medium';
    $isActive = isset($input['is_active']) ? $input['is_active'] : true;
    
    $success = update_announcement($input['id'], $input['title'], $input['content'], $priority, $isActive);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to update announcement']);
    }
}

function handleAnnouncementDelete($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Announcement ID is required']);
        return;
    }
    
    $success = delete_announcement($input['id']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to delete announcement']);
    }
}

// =============================================================================
// DINING MENU HANDLERS
// =============================================================================

function handleDiningMenuList() {
    $menus = get_all_dining_menus();
    echo json_encode([
        'success' => true,
        'menus' => $menus
    ]);
}

function handleDiningMenuGet() {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Menu ID is required']);
        return;
    }
    
    $menu = get_dining_menu_by_id($_GET['id']);
    
    if ($menu) {
        echo json_encode([
            'success' => true,
            'menu' => $menu
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Menu not found']);
    }
}

function handleDiningMenuToday() {
    // Get date parameter, default to today if not provided
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid date format',
            'message' => 'Date must be in YYYY-MM-DD format.'
        ]);
        return;
    }
    
    $menu = get_dining_menu_by_date($date);
    
    if ($menu) {
        echo json_encode([
            'success' => true,
            'menu' => $menu
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'menu' => null,
            'message' => 'No menu found for ' . $date
        ]);
    }
}

function handleDiningMenuCreate($input) {
    // Debug logging
    error_log("Dining menu create request: " . print_r($input, true));
    
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['date']) || !isset($input['breakfast_menu']) || !isset($input['lunch_menu'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Date, breakfast menu, and lunch menu are required']);
        return;
    }
    
    // Check if the date is a holiday or day off
    $holiday = is_date_holiday($input['date']);
    if ($holiday) {
        $message = 'Date is not available: ' . $holiday['holiday_name'];
        if ($holiday['type'] === 'weekend') {
            $message = 'Cannot create dining menu for weekends (Saturday/Sunday)';
        }
        
        http_response_code(400);
        echo json_encode([
            'error' => 'Cannot create dining menu for this date',
            'message' => $message,
            'holiday' => $holiday
        ]);
        return;
    }
    
    $breakfast_start_time = isset($input['breakfast_start_time']) ? $input['breakfast_start_time'] : '07:00:00';
    $breakfast_end_time = isset($input['breakfast_end_time']) ? $input['breakfast_end_time'] : '09:00:00';
    $lunch_start_time = isset($input['lunch_start_time']) ? $input['lunch_start_time'] : '12:00:00';
    $lunch_end_time = isset($input['lunch_end_time']) ? $input['lunch_end_time'] : '14:00:00';
    $is_recurring = isset($input['is_recurring']) ? (bool)$input['is_recurring'] : false;
    
    // Create the initial menu
    try {
        $success = create_dining_menu(
            $input['date'],
            $input['breakfast_menu'],
            $breakfast_start_time,
            $breakfast_end_time,
            $input['lunch_menu'],
            $lunch_start_time,
            $lunch_end_time,
            $input['current_admin_id'],
            $is_recurring
        );
    } catch (Exception $e) {
        error_log("Error creating dining menu: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        return;
    }
    
    if ($success) {
        $message = 'Dining menu created successfully';
        $recurring_menus_created = 0;
        
        // If recurring is enabled, create future menus
        if ($is_recurring) {
            try {
                $day_of_week = date('l', strtotime($input['date']));
                $weeks_ahead = isset($input['weeks_ahead']) ? (int)$input['weeks_ahead'] : 12;
                
                $recurring_menus_created = create_recurring_dining_menus(
                    $day_of_week,
                    $input['breakfast_menu'],
                    $breakfast_start_time,
                    $breakfast_end_time,
                    $input['lunch_menu'],
                    $lunch_start_time,
                    $lunch_end_time,
                    $input['current_admin_id'],
                    $weeks_ahead
                );
            } catch (Exception $e) {
                error_log("Error creating recurring dining menus: " . $e->getMessage());
                $recurring_menus_created = 0;
            }
        }
        
        if ($recurring_menus_created > 0) {
            $message .= " and $recurring_menus_created recurring menus created for future $day_of_week dates";
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'recurring_menus_created' => $recurring_menus_created
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create dining menu']);
    }
}

function handleDiningMenuUpdate($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['id']) || !isset($input['breakfast_menu']) || !isset($input['lunch_menu'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, breakfast menu, and lunch menu are required']);
        return;
    }
    
    $breakfast_start_time = isset($input['breakfast_start_time']) ? $input['breakfast_start_time'] : '07:00:00';
    $breakfast_end_time = isset($input['breakfast_end_time']) ? $input['breakfast_end_time'] : '09:00:00';
    $lunch_start_time = isset($input['lunch_start_time']) ? $input['lunch_start_time'] : '12:00:00';
    $lunch_end_time = isset($input['lunch_end_time']) ? $input['lunch_end_time'] : '14:00:00';
    
    // First, update the selected menu
    $success = update_dining_menu(
        $input['id'],
        $input['breakfast_menu'],
        $breakfast_start_time,
        $breakfast_end_time,
        $input['lunch_menu'],
        $lunch_start_time,
        $lunch_end_time
    );

    if (!$success) {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to update dining menu']);
        return;
    }

    // If the menu is part of a recurring series, propagate changes to all future recurring menus of same weekday
    $propagated = 0;
    try {
        $menu = get_dining_menu_by_id($input['id']);
        if ($menu && isset($menu['is_recurring']) && (int)$menu['is_recurring'] === 1) {
            $day_of_week = isset($menu['day_of_week']) ? $menu['day_of_week'] : date('l', strtotime($menu['date']));
            // Update recurring menus from the edited menu's date onward
            $start_date = isset($menu['date']) ? $menu['date'] : date('Y-m-d');
            $propagated = update_recurring_dining_menus(
                $day_of_week,
                $input['breakfast_menu'],
                $breakfast_start_time,
                $breakfast_end_time,
                $input['lunch_menu'],
                $lunch_start_time,
                $lunch_end_time,
                $start_date
            );
        }
    } catch (Exception $e) {
        error_log('Error updating recurring dining menus: ' . $e->getMessage());
        $propagated = 0;
    }

    $message = 'Dining menu updated successfully';
    if ($propagated > 0) {
        $message .= " and $propagated recurring menus updated";
    }

    echo json_encode(['success' => true, 'message' => $message]);
}

function handleDiningMenuDelete($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Menu ID is required']);
        return;
    }
    
    $success = delete_dining_menu($input['id']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Dining menu deleted successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to delete dining menu']);
    }
}

// =============================================================================
// HOLIDAYS AND DAYS OFF MANAGEMENT
// =============================================================================

function handleHolidayList() {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $holidays = get_holidays_by_year($year);
    echo json_encode([
        'success' => true,
        'holidays' => $holidays,
        'year' => $year
    ]);
}

function handleHolidayGet() {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Holiday ID is required']);
        return;
    }
    
    $holiday = get_holiday_by_id($_GET['id']);
    
    if ($holiday) {
        echo json_encode([
            'success' => true,
            'holiday' => $holiday
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Holiday not found']);
    }
}

function handleHolidayCreate($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['date']) || !isset($input['holiday_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Date and holiday name are required']);
        return;
    }
    
    // Parse and validate date
    $dateInfo = validate_and_parse_date($input['date']);
    if (!$dateInfo) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        return;
    }
    
    $type = isset($input['type']) ? $input['type'] : 'holiday';
    $description = isset($input['description']) ? $input['description'] : '';
    $is_recurring = isset($input['is_recurring']) ? (bool)$input['is_recurring'] : false;
    
    $success = create_holiday(
        $dateInfo['date'],
        $dateInfo['year'],
        $dateInfo['day_of_week'],
        $input['holiday_name'],
        $type,
        $description,
        $is_recurring,
        $input['current_admin_id']
    );
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Holiday created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create holiday']);
    }
}

function handleHolidayUpdate($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['id']) || !isset($input['date']) || !isset($input['holiday_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, date, and holiday name are required']);
        return;
    }
    
    // Parse and validate date
    $dateInfo = validate_and_parse_date($input['date']);
    if (!$dateInfo) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        return;
    }
    
    $type = isset($input['type']) ? $input['type'] : 'holiday';
    $description = isset($input['description']) ? $input['description'] : '';
    $is_recurring = isset($input['is_recurring']) ? (bool)$input['is_recurring'] : false;
    
    $success = update_holiday(
        $input['id'],
        $dateInfo['date'],
        $dateInfo['year'],
        $dateInfo['day_of_week'],
        $input['holiday_name'],
        $type,
        $description,
        $is_recurring
    );
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Holiday updated successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to update holiday']);
    }
}

function handleHolidayDelete($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Holiday ID is required']);
        return;
    }
    
    $success = delete_holiday($input['id']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Holiday deleted successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to delete holiday']);
    }
}

function handleHolidayDeleteByYear($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['year'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Year is required']);
        return;
    }
    
    $success = delete_holidays_by_year($input['year']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'All holidays for year ' . $input['year'] . ' deleted successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to delete holidays']);
    }
}

function handleHolidayUpload($input) {
    if (!isset($input['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    if (!isset($input['year']) || !isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Year and file are required']);
        return;
    }
    
    $year = (int)$input['year'];
    $file = $_FILES['file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed']);
        return;
    }
    
    // Check file extension as fallback for MIME type validation
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['csv', 'xlsx', 'docx'];
    $allowedTypes = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    if (!in_array($file['type'], $allowedTypes) && !in_array($extension, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only CSV, Excel (.xlsx), and Word (.docx) files are allowed']);
        return;
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        http_response_code(400);
        echo json_encode(['error' => 'File size too large. Maximum 10MB allowed']);
        return;
    }
    
    $result = processHolidayFile($file, $year, $input['current_admin_id']);
    
    if ($result['success']) {
        $message = 'Holidays uploaded successfully';
        if (isset($result['deleted_menus']) && $result['deleted_menus'] > 0) {
            $message .= " and {$result['deleted_menus']} dining menu(s) deleted for holiday dates";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'imported' => $result['imported'],
            'errors' => $result['errors'],
            'deleted_menus' => $result['deleted_menus'] ?? 0
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $result['error']]);
    }
}

function handleHolidayExport() {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $holidays = get_holidays_by_year($year);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="holidays_' . $year . '.csv"');
    
    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, ['Date', 'DayOfWeek', 'HolidayName', 'Type', 'Description', 'IsRecurring']);
    
    // Add data
    foreach ($holidays as $holiday) {
        fputcsv($output, [
            $holiday['date'],
            $holiday['day_of_week'],
            $holiday['holiday_name'],
            $holiday['type'],
            $holiday['description'],
            $holiday['is_recurring'] ? 'Yes' : 'No'
        ]);
    }
    
    fclose($output);
}

function handleHolidayTemplate() {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="holiday_template_' . $year . '.csv"');
    
    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, ['Date', 'DayOfWeek', 'HolidayName']);
    
    // Add example data
    fputcsv($output, [$year . '-01-01', 'Monday', 'New Year\'s Day']);
    fputcsv($output, [$year . '-01-15', 'Monday', 'University Closure']);
    fputcsv($output, ['1st March', 'Saturday', 'Spring Break']);
    fputcsv($output, ['15th April', 'Tuesday', 'University Closure']);
    fputcsv($output, [$year . '-07-04', 'Thursday', 'Independence Day']);
    fputcsv($output, [$year . '-12-25', 'Wednesday', 'Christmas Day']);
    
    fclose($output);
}

function handleCheckDateAvailability() {
    if (!isset($_GET['date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Date parameter is required']);
        return;
    }
    
    $date = $_GET['date'];
    $holiday = is_date_holiday($date);
    
    if ($holiday) {
        $message = 'Date is not available: ' . $holiday['holiday_name'];
        if ($holiday['type'] === 'weekend') {
            $message = 'Date is not available: Weekend (Saturday/Sunday)';
        }
        
        echo json_encode([
            'success' => true,
            'available' => false,
            'holiday' => $holiday,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'available' => true,
            'message' => 'Date is available for dining menu'
        ]);
    }
}

/**
 * Process uploaded holiday file
 * @param array $file Uploaded file information
 * @param int $year Year for holidays
 * @param int $adminId Admin ID
 * @return array Result with success status and details
 */
function processHolidayFile($file, $year, $adminId) {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $data = [];
    $errors = [];
    $imported = 0;
    
    try {
        switch ($extension) {
            case 'csv':
                $data = processCSVFile($file['tmp_name']);
                break;
            case 'xlsx':
                $data = processExcelFile($file['tmp_name']);
                break;
            case 'docx':
                $data = processWordFile($file['tmp_name']);
                break;
            default:
                return ['success' => false, 'error' => 'Unsupported file type'];
        }
        
        if (empty($data)) {
            return ['success' => false, 'error' => 'No data found in file'];
        }
        
        // Delete existing holidays for the year
        delete_holidays_by_year($year);
        
        // Process each row
        foreach ($data as $rowIndex => $row) {
            if (empty($row['Date']) || empty($row['HolidayName'])) {
                $errors[] = "Row " . ($rowIndex + 1) . ": Missing required fields";
                continue;
            }
            
            $dateInfo = validate_and_parse_date($row['Date']);
            if (!$dateInfo) {
                $errors[] = "Row " . ($rowIndex + 1) . ": Invalid date format '" . $row['Date'] . "'";
                continue;
            }
            
            // Check if day of week matches
            if (!empty($row['DayOfWeek']) && strtolower($dateInfo['day_of_week']) !== strtolower($row['DayOfWeek'])) {
                $errors[] = "Row " . ($rowIndex + 1) . ": Day of week mismatch (Date: " . $dateInfo['day_of_week'] . ", Expected: " . $row['DayOfWeek'] . ")";
                continue;
            }
            
            $success = create_holiday(
                $dateInfo['date'],
                $year,
                $dateInfo['day_of_week'],
                $row['HolidayName'],
                'holiday',
                '',
                false,
                $adminId
            );
            
            if ($success) {
                $imported++;
            } else {
                $errors[] = "Row " . ($rowIndex + 1) . ": Failed to import holiday";
            }
        }
        
        // After processing all holidays, do a comprehensive cleanup of dining menus
        $deletedMenus = cleanup_dining_menus_for_holidays();
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'deleted_menus' => $deletedMenus
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'File processing error: ' . $e->getMessage()];
    }
}

/**
 * Process CSV file
 * @param string $filepath Path to CSV file
 * @return array Array of data rows
 */
function processCSVFile($filepath) {
    $data = [];
    $handle = fopen($filepath, 'r');
    
    if ($handle === false) {
        throw new Exception('Could not open CSV file');
    }
    
    // Read headers
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        throw new Exception('Could not read CSV headers');
    }
    
    // Normalize headers
    $headers = array_map('trim', $headers);
    
    // Read data rows
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) >= 3) {
            $dataRow = [];
            foreach ($headers as $index => $header) {
                $dataRow[$header] = isset($row[$index]) ? trim($row[$index]) : '';
            }
            $data[] = $dataRow;
        }
    }
    
    fclose($handle);
    return $data;
}

/**
 * Process Excel file
 * @param string $filepath Path to Excel file
 * @return array Array of data rows
 */
function processExcelFile($filepath) {
    // For now, we'll use a simple approach
    // In production, you might want to use a library like PhpSpreadsheet
    return processCSVFile($filepath); // Fallback to CSV processing
}

/**
 * Process Word file
 * @param string $filepath Path to Word file
 * @return array Array of data rows
 */
function processWordFile($filepath) {
    // For now, we'll use a simple approach
    // In production, you might want to use a library like PhpWord
    return processCSVFile($filepath); // Fallback to CSV processing
}

/**
 * Handle cleanup of dining menus for holiday dates
 */
function handleCleanupDiningMenus() {
    if (!isset($_GET['current_admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        return;
    }
    
    $deletedCount = cleanup_dining_menus_for_holidays();
    
    echo json_encode([
        'success' => true,
        'message' => "Cleanup completed. {$deletedCount} dining menu(s) deleted for holiday dates.",
        'deleted_count' => $deletedCount
    ]);
}
?>
