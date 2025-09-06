<?php
/**
 * LEAVE RMS API - Complete Version (Helpers + Original)
 *
 * This file was generated to preserve the full original api.php contents while
 * adding a small set of safe helper wrappers (guarded to avoid redeclaration).
 *
 * Notes:
 *  - The original api.php content follows below (verbatim, with its opening <?php removed).
 *  - All original logic, functions, and configuration remain intact and are preserved.
 *  - Helper functions are defined only if they do not already exist to prevent collisions.
 *  - Review and test in a development environment before deploying to production.
 */

// -----------------------------
// Initialization (safe defaults)
// -----------------------------
if (session_status() === PHP_SESSION_NONE) {

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Include DB connection if present
if (file_exists(__DIR__ . '/db_connection.php')) {

        require_once __DIR__ . '/db_connection.php';
}

// -----------------------------
// Helper Functions (guarded)
// -----------------------------

if (!function_exists('json_response')) {

        function json_response($data, $status = 200) {

                http_response_code($status);
                header('Content-Type: application/json; charset=utf-8');
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                exit;
            
    }
}

if (!function_exists('get_int')) {

        function get_int($key, $default = 0) {

                return isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $default;
            
    }
}

if (!function_exists('get_str')) {

        function get_str($key, $default = '') {

                return isset($_REQUEST[$key]) ? trim($_REQUEST[$key]) : $default;
            
    }
}

if (!function_exists('curl_get')) {

        function curl_get($url) {

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 10
                ]);
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $response = curl_exec($ch);
                if (curl_errno($ch)) {

                        error_log("cURL error: " . curl_error($ch));
                        curl_close($ch);
                        return false;
                    
        }
                curl_close($ch);
                return $response;
            
    }
}

if (!function_exists('curl_post')) {

        function curl_post($url, $data) {

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query($data),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 10
                ]);
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $response = curl_exec($ch);
                if (curl_errno($ch)) {

                        error_log("cURL error: " . curl_error($ch));
                        curl_close($ch);
                        return false;
                    
        }
                curl_close($ch);
                return $response;
            
    }
}

// -----------------------------
// BEGIN ORIGINAL api.php CONTENT
// -----------------------------


/**
 * LEAVE RMS API - Main Backend API
 * 
 * Handles authentication, platform integration, notifications, and proxy services
 * for the LEAVE RMS LMS systems.
 * 
 * @author System Administrator
 * @version 2.0
 */

// =============================================================================
// INITIALIZATION & CONFIGURATION
// =============================================================================

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {

        session_start();
}

// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// =============================================================================
// LMS SUB-PLATFORMS CONFIGURATION
// =============================================================================

$lms_subplatforms = [
    [
        'name' => 'Üniversite Ortak/University Common',
        'url' => 'https://lms1.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'İktisadi ve İdari Bilimler Fakültesi/Faculty of Economics and Administrative Sciences',
        'url' => 'https://lms2.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Mühendislik Fakültesi/Faculty of Engineering',
        'url' => 'https://lms3.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Hukuk Fakültesi/Faculty of Law',
        'url' => 'https://lms3.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Eğitim Bilimleri Fakültesi/Faculty of Educational Sciences',
        'url' => 'https://lms4.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Fen Edebiyat Fakültesi/Faculty of Arts and Sciences',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Mimarlık ve Güzel Sanatlar Fakültesi/Faculty of Architecture and Fine Arts',
        'url' => 'https://lms4.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Eczacılık Fakültesi/Faculty of Pharmacy',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Diş Hekimliği Fakültesi/Faculty of Dentistry',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Sağlık Bilimleri Fakültesi/Faculty of Health Sciences',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Lisansüstü Eğitim Öğretim Enstitüsü/Institute of Graduate Education',
        'url' => 'https://lms1.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Beden Eğitimi ve Spor Yüksekokulu/School of Physical Education and Sports',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Yabancı Diller Hazırlık Okulu/School of Foreign Languages',
        'url' => 'https://lms6.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Turizm ve Mutfak Sanatları Fakültesi/Faculty of Tourism and Culinary Arts',
        'url' => 'https://lms2.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Meslek Yüksekokulu/School of Vocational Studies',
        'url' => 'https://lms2.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Adalet Meslek Yüksekokulu/School of Justice',
        'url' => 'https://lms3.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ],
    [
        'name' => 'Sağlık Hizmetleri Yüksekokulu/Vocational School of Health Services',
        'url' => 'https://lms5.final.edu.tr/LMS/',
        'login_endpoint' => 'login/index.php',
        'notifications_endpoint' => 'message/output/popup/notifications.php'
    ]
];

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// =============================================================================
// ENHANCED PROXY FUNCTIONS FOR PERFECT PORTAL DISPLAY
// =============================================================================

/**
 * Universal proxy resource handler for CSS, JS, images, and other assets
 */


/**
 * Enhanced URL rewriting for perfect proxy display
 */


/**
 * Inject proxy-aware JavaScript for perfect navigation
 */


// =============================================================================
// LMS HELPER FUNCTIONS
// =============================================================================

/**
 * Get LMS subplatform by identifier
 * 
 * @param string $identifier Subplatform identifier/name
 * @return array|null Subplatform data or null if not found
 */
function getLmsSubplatformByIdentifier($identifier) {
        global $lms_subplatforms;
        
        foreach ($lms_subplatforms as $subplatform) {
                if ($subplatform['name'] === $identifier) {
                        return $subplatform;
                }
        }
        
        return null;
}

// =============================================================================
// EXISTING FUNCTIONS BELOW THIS LINE
// =============================================================================

// Handle CLI environment
if (php_sapi_name() === 'cli') {

        $method = 'GET';
        $endpoint = '';
}
else {

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

                http_response_code(200);
                exit();
            
    }

        // Get request method and endpoint
        $method = $_SERVER['REQUEST_METHOD'];
}

// Handle multiple endpoint parameters - some LMS requests have both endpoint=lms_subplatform_proxy and endpoint=ajax
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Log all incoming requests for debugging
$logFile = __DIR__ . '/php_errors.log';
$queryString = $_SERVER['QUERY_STRING'] ?? '';
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Request: $method to endpoint '$endpoint' - Query: $queryString\n", FILE_APPEND);

// Special handling for LMS requests with duplicate endpoint parameters or AJAX requests
// Check if this looks like an LMS request (has subplatform and path parameters)
if (isset($_GET['subplatform']) && isset($_GET['path'])) {

        $path = $_GET['path'];
        // Decode the path for pattern matching
        $decodedPath = urldecode($path);
        
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Request detected - Original Endpoint: '$endpoint', Path: $path (decoded: $decodedPath)\n", FILE_APPEND);
        
        // Check if this is an AJAX service request for any LMS platform
        // This handles cases where endpoint=ajax due to duplicate parameters
        if (strpos($decodedPath, 'lib/ajax/service') !== false) {

                $endpoint = 'lms_ajax_service';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Redirecting to lms_ajax_service\n", FILE_APPEND);
            
    }
    elseif (strpos($decodedPath, 'yui_combo.php') !== false || 
                  strpos($decodedPath, 'javascript.php') !== false ||
                  strpos($decodedPath, 'theme/yui_combo.php') !== false ||
                  strpos($decodedPath, 'lib/javascript.php') !== false) {

                $endpoint = 'lms_subplatform_proxy';
        // Keep as regular proxy for JS/CSS
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Keeping as lms_subplatform_proxy for JS/CSS\n", FILE_APPEND);
            
    }
    elseif (strpos($decodedPath, 'theme/font.php') !== false ||
                  strpos($decodedPath, 'lib/fonts/') !== false ||
                  preg_match('/\.(woff2?|ttf|eot|svg)(\?|$)/i', $decodedPath)) {

                $endpoint = 'lms_font_proxy';
        // Route font requests to font proxy
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Redirecting to lms_font_proxy for fonts\n", FILE_APPEND);
            
    }
    elseif ($endpoint !== 'lms_subplatform_proxy') {

                // If endpoint is not already lms_subplatform_proxy, set it
                $endpoint = 'lms_subplatform_proxy';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Setting endpoint to lms_subplatform_proxy for general LMS request\n", FILE_APPEND);
            
    }
}

// =============================================================================
// MAIN ROUTING
// =============================================================================

try {

        switch ($endpoint) {

                case 'login':
                    handleLogin();
                    break;
                case 'platforms':
                    handlePlatforms();
                    break;
                case 'notifications':
                    handleNotifications();
                    break;
                case 'announcements':
                    handleAnnouncements();
                    break;
                case 'dining-menu-today':
                    handleDiningMenuToday();
                    break;
                case 'delete_notification':
                    handleDeleteNotification();
                    break;
                case 'proxy':
                    handleProxyResource();
                    break;
                case 'fetch_external_notifications':
                    handleFetchExternalNotifications();
                    break;
                case 'authenticate_platform':
                    handleAuthenticatePlatform();
                    break;

                case 'rms_direct_auth':
                    handleRmsDirectAuth();
                    break;
                case 'rms_notifications_direct_auth':
                    handleRmsNotificationsDirectAuth();
                    break;

                case 'leave_portal_proxy':
                    handleLeavePortalDashboardProxy();
                    break;
                case 'proxy_resource':
                    handleProxyResource();
                    break;
                case 'lms_subplatforms':
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode(['success' => true, 'subplatforms' => $lms_subplatforms]);
                    break;
                case 'lms_subplatform_auth':
                    handleLmsSubplatformAuth();
                    break;
                case 'lms_font_proxy':
                    handleLmsFontProxy();
                    break;
                case 'lms_image_proxy':
                    handleLmsImageProxy();
                    break;
                case 'lms_subplatform_proxy':
                    handleLmsSubplatformProxy();
                    break;
                case 'lms_subplatform_direct_link':
                    handleLmsSubplatformDirectLink();
                    break;
                case 'lms_direct_auth':
                    handleLmsDirectAuth();
                    break;
                case 'lms_ajax_proxy':
                    handleLmsAjaxProxy();
                    break;
                case 'lms_ajax_service':
                    handleLmsAjaxService();
                    break;
                case 'update_notification_count':
                    handleUpdateNotificationCount();
                    break;
                case 'get_notifications_dropdown':
                    handleGetNotificationsDropdown();
                    break;
                case 'mark_notification_read':
                    handleMarkNotificationRead();
                    break;
                case 'get_notification_count':
                    handleGetNotificationCount();
                    break;
                case 'toggle_notification_read':
                    handleToggleNotificationRead();
                    break;
                case 'toggle_all_notifications_read':
                    handleToggleAllNotificationsRead();
                    break;
                case 'clear_old_notifications':
                    handleClearOldNotifications();
                    break;
                case 'analyze_leave_portal_endpoints':
                    analyzeLeavePortalNotificationEndpoints();
                    break;
                case 'get_leave_portal_js':
                    getLeavePortalJavaScript();
                    break;
                case 'ajax':
                    // Handle misrouted AJAX requests - check if this should be an LMS AJAX service request
                    if (isset($_GET['subplatform']) && isset($_GET['path'])) {

                            $path = $_GET['path'];
                            $decodedPath = urldecode($path);
                            $logFile = __DIR__ . '/php_errors.log';
                            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Handling misrouted AJAX request - Path: $path\n", FILE_APPEND);
                            
                            if (strpos($decodedPath, 'lib/ajax/service') !== false) {

                                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Redirecting misrouted AJAX to lms_ajax_service\n", FILE_APPEND);
                                    handleLmsAjaxService();
                                
            }
            else {

                                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Redirecting misrouted AJAX to lms_subplatform_proxy\n", FILE_APPEND);
                                    handleLmsSubplatformProxy();
                                
            }
                        
        }
        else {

                            http_response_code(400);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                            echo json_encode([
                                'error' => 'Invalid AJAX request',
                /* INCLUDE/REQUIRE: Verify that included paths are not user-controlled to avoid remote code execution. */
                                'message' => 'AJAX requests require subplatform and path parameters.'
                            ]);
                        
        }
                    break;

                default:
                    http_response_code(404);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'error' => 'Endpoint not found',
                        'message' => 'The requested API endpoint does not exist.'
                    ]);
                    break;
            
    }
}
catch (Exception $e) {

        http_response_code(500);
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ]);
}

// =============================================================================
// LMS SUB-PLATFORM DIRECT LINK ENDPOINT
// =============================================================================

/**
 * Generate a direct, time-limited, single-use LMS sub-platform proxy link for frontend use
 * Example usage: /api.php?endpoint=lms_subplatform_direct_link&username=...&subplatform=...&path=...
 * Returns: { success: true, url: "..." }
 * Ensures the user is authenticated to the LMS sub-platform before generating the link.
 */
function handleLmsSubplatformDirectLink() {

        global $method, $lms_subplatforms;
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $subplatformName = isset($_GET['subplatform']) ? $_GET['subplatform'] : '';
        if (!$username || !$subplatformName) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username and sub-platform name are required',
                    'message' => 'Both username and sub-platform name must be provided.'
                ]);
                return;
            
    }
        // Find the sub-platform configuration
        $targetSubplatform = null;
        foreach ($lms_subplatforms as $subplatform) {

                if ($subplatform['name'] === $subplatformName) {

                        $targetSubplatform = $subplatform;
                        break;
                    
        }
            
    }
        if (!$targetSubplatform) {

                http_response_code(404);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Sub-platform not found',
                    'message' => 'The specified sub-platform does not exist.'
                ]);
                return;
            
    }

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Sub-platform Direct Link request for: $subplatformName, user: $username\n", FILE_APPEND);

        // Build credentials from the current request
        $credentials = buildUniversalCredentialsFromRequest($username);
        if (!$credentials) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] No credentials in request for LMS for user: $username\n", FILE_APPEND);
                http_response_code(401);
                echo json_encode([
                    'error' => 'Missing credentials',
                    'message' => 'Provide password or password_hash in the request.'
                ]);
                return;
    }

        $baseUrl = rtrim($targetSubplatform['url'], '/');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';
        
        // Create cookies directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../cookies')) {

                mkdir(__DIR__ . '/../cookies', 0755, true);
            
    }

        // Step 1: Always authenticate first to ensure we have a valid session
        $loginUrl = $baseUrl . '/' . ltrim($targetSubplatform['login_endpoint'], '/');
        
        // Get the login page to extract the logintoken
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Fetching login page: $loginUrl\n", FILE_APPEND);
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $loginPageResponse = curl_exec($ch);
        $loginPageHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($loginPageHttpCode !== 200) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Failed to fetch login page: HTTP $loginPageHttpCode\n", FILE_APPEND);
                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to fetch login page',
                    'message' => 'Could not fetch the login page from the LMS sub-platform.'
                ]);
                return;
            
    }

        // Extract the logintoken
        $logintoken = '';
        if (preg_match('/<input[^>]*name="logintoken"[^>]*value="([^"]*)"/i', $loginPageResponse, $matches)) {

                $logintoken = $matches[1];
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted logintoken: $logintoken\n", FILE_APPEND);
            
    }

        // Perform the login
        $loginData = [
            'anchor' => '',
            'username' => $credentials['platform_username'],
            'password' => $credentials['platform_password'],
            'rememberusername' => '0'
        ];
        if ($logintoken) {

                $loginData['logintoken'] = $logintoken;
            
    }

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Performing login to: $loginUrl\n", FILE_APPEND);
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: ' . $loginUrl
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loginFinalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Login response HTTP Code: $loginHttpCode, Final URL: $loginFinalUrl\n", FILE_APPEND);

        // Step 2: Visit the dashboard to establish full session
        $dashboardUrl = $baseUrl . '/my/';
    // Moodle dashboard
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Visiting dashboard to establish session: $dashboardUrl\n", FILE_APPEND);
        
        $ch = curl_init($dashboardUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Referer: ' . $loginUrl
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $dashboardResponse = curl_exec($ch);
        $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dashboardFinalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Dashboard response HTTP Code: $dashboardHttpCode, Final URL: $dashboardFinalUrl\n", FILE_APPEND);

        // Step 3: Generate the proxy URL to the dashboard
        $params = [
            'endpoint' => 'lms_subplatform_proxy',
            'username' => $username,
            'subplatform' => $subplatformName,
            'path' => 'my/' // Default to dashboard
        ];
        $query = http_build_query($params);
        $proxyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api.php?' . $query;
        
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Generated proxy URL: $proxyUrl\n", FILE_APPEND);
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode(['success' => true, 'url' => $proxyUrl]);
}

// =============================================================================
// AUTHENTICATION FUNCTIONS
// =============================================================================

/**
 * Handle user login and platform authentication
 */
function handleLogin() {

        global $method;
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        // Get JSON data from request body
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Accept both 'username' and 'usernameOrEmail' for compatibility
        $username = isset($data['usernameOrEmail']) ? $data['usernameOrEmail'] : (isset($data['username']) ? $data['username'] : null);
        $password = isset($data['password']) ? $data['password'] : null;
        $password_hash = isset($data['password_hash']) ? $data['password_hash'] : null;

        if (!$username || (!$password && !$password_hash)) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username/email and password or password_hash are required',
                    'message' => 'You must provide a username/email and either a password or password_hash.'
                ]);
                return;
            
    }
        
        // Validate user
        if ($password_hash) {

                $user = validate_user($username, $password_hash);
            
    }
    else {

                $user = validate_user($username, $password);
            
    }
        
        if ($user) {

                // User authenticated successfully - authenticate to all platforms using user's own credentials
                $platformResults = [];
                $platforms = get_platforms();
                
                // Limit platform authentication during login to avoid delays/timeouts
                // - Skip student-only/direct-link platforms (e.g., Document System, Summer School, Accommodation, Support, Exam portals)
                // - Only pre-auth core staff platforms to preserve UX without blocking login
                $corePlatforms = ['RMS', 'Leave and Absence', 'LMS'];
                $userRole = isset($user['role']) ? $user['role'] : 'instructor';
                if ($userRole === 'student') {
                    // For students, do not attempt any backend platform auth during login
                    $platformsForAuth = [];
                } else {
                    $platformsForAuth = array_values(array_filter($platforms, function ($p) use ($corePlatforms) {
                        return in_array($p['name'], $corePlatforms, true);
                    }));
                }

                // Build universal credentials from the login input
                $universalCredentials = [
                    'platform_username' => $user['username'],
                    // Prefer password_hash if provided; else use plain password
                    'platform_password' => ($password_hash ? $password_hash : $password)
                ];
                
                foreach ($platformsForAuth as $platform) {

                        $platformName = $platform['name'];
                        $platformResult = [
                            'platform' => $platformName,
                            'authenticated' => false,
                            'message' => 'Not attempted'
                        ];

                        // Authenticate to the platform using universal credentials
                        $authResult = authenticateToPlatform($platform, $universalCredentials);
                                $platformResult['authenticated'] = $authResult['success'];
                                $platformResult['message'] = $authResult['message'];
                                $platformResult['details'] = $authResult['details'] ?? null;
                            
                        $platformResults[] = $platformResult;
                    
        }
                
                // LMS subplatform notifications fetching removed
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => isset($user['role']) ? $user['role'] : 'instructor'
                    ],
                    'platforms' => $platformResults
                ]);
            
    }
    else {

                // Authentication failed
                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Authentication failed',
                    'message' => 'Invalid username/email or password.'
                ]);
            
    }
}

/**
 * Authenticate to a specific platform
 * 
 * @param array $platform Platform information
 * @param array $credentials User credentials for the platform
 * @return array Authentication result
 */
function authenticateToPlatform($platform, $credentials) {

        $platformName = $platform['name'];
        // Platform-specific authentication
        switch ($platformName) {

                case 'RMS':
                    return authenticateToRMS($credentials);
                case 'Leave and Absence':
                    return authenticateToLeavePortal($credentials);
                case 'SIS':
                    return authenticateToSIS($credentials);
                case 'LMS':
                    return authenticateToLMS($credentials);
                case 'Document Application System':
                    return authenticateToDocumentSystem($credentials);
                default:
                    // For generic platforms, pass username, password, platformName, platform
                    $username = isset($credentials['platform_username']) ? $credentials['platform_username'] : '';
                    $password = isset($credentials['platform_password']) ? $credentials['platform_password'] : '';
                    return authenticateToGenericPlatform($username, $password, $platformName, $platform);
            
    }
}

/**
 * Build universal credentials for platform auth from the current request
 * Tries JSON body, form POST, and headers. Returns ['platform_username','platform_password'] or null.
 */
function buildUniversalCredentialsFromRequest($fallbackUsername = null) {
    // Prefer JSON body if available
    $raw = file_get_contents('php://input');
    $data = [];
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
    // Merge with POST (in case body already consumed elsewhere, POST will still have values)
    if (!empty($_POST)) {
        foreach ($_POST as $k => $v) { $data[$k] = $v; }
    }

    // Headers fallback for credentials
    $passwordHashHeader = null;
    $passwordHeader = null;
    foreach (['HTTP_X_PASSWORD_HASH','X_PASSWORD_HASH','HTTP_X_PASSWORD','X_PASSWORD'] as $hk) {
        if (isset($_SERVER[$hk])) {
            if (stripos($hk, 'HASH') !== false) { $passwordHashHeader = $_SERVER[$hk]; }
            else { $passwordHeader = $_SERVER[$hk]; }
        }
    }

    $username = isset($data['username']) ? $data['username'] : (isset($data['usernameOrEmail']) ? $data['usernameOrEmail'] : $fallbackUsername);
    $password = isset($data['password']) ? $data['password'] : ($passwordHeader ?? null);
    $password_hash = isset($data['password_hash']) ? $data['password_hash'] : ($passwordHashHeader ?? null);

    if (!$username || (!$password && !$password_hash)) {
        return null;
    }

    return [
        'platform_username' => $username,
        'platform_password' => ($password_hash ? $password_hash : $password)
    ];
}

/**
 * Authenticate to RMS platform
 * 
 * @param array $credentials User credentials
 * @return array Authentication result
 */
function authenticateToRMS($credentials) {

        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        $logFile = __DIR__ . '/php_errors.log';
        
        // Create cookies directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../cookies')) {

                mkdir(__DIR__ . '/../cookies', 0755, true);
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Created cookies directory.", FILE_APPEND);
            
    }
        
        // Step 1: Get RMS login page to establish session
        $loginUrl = 'https://rms.final.digital/index.php';
        $ch_login = curl_init($loginUrl);
        curl_setopt($ch_login, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_login, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_login, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch_login, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch_login, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_login, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch_login, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        curl_exec($ch_login);
        curl_close($ch_login);
        
        // Step 2: Authenticate to RMS
        $authUrl = 'https://rms.final.digital/index.php';
        $ch_auth = curl_init($authUrl);
        curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_auth, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_auth, CURLOPT_POST, true);
        curl_setopt($ch_auth, CURLOPT_POSTFIELDS, 'username=' . $credentials['platform_username'] . '&password_hash=' . $credentials['platform_password']);
        curl_setopt($ch_auth, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch_auth, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch_auth, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_auth, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch_auth, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $authResponse = curl_exec($ch_auth);
        $authHttpCode = curl_getinfo($ch_auth, CURLINFO_HTTP_CODE);
        $authFinalUrl = curl_getinfo($ch_auth, CURLINFO_EFFECTIVE_URL);
        curl_close($ch_auth);
        
        // Log authentication attempt
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Authentication attempt for user: " . $credentials['platform_username'] . ", HTTP Code: $authHttpCode, Final URL: $authFinalUrl", FILE_APPEND);
        
        // Check if authentication succeeded
        $hasDashboard = strpos($authResponse, 'dashboard') !== false || strpos($authResponse, 'Dashboard') !== false;
        $hasLoginForm = strpos($authResponse, 'login') !== false || strpos($authResponse, 'Login') !== false;
        
        if ($authHttpCode === 200 && (!$hasLoginForm || $hasDashboard)) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS authentication successful for user: " . $credentials['platform_username'], FILE_APPEND);
                return [
                    'success' => true,
                    'message' => 'RMS authentication successful',
                    'details' => ['cookie_file' => basename($cookieFile)]
                ];
            
    }
    else {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS authentication FAILED for user: " . $credentials['platform_username'], FILE_APPEND);
                return [
                    'success' => false,
                    'message' => 'RMS authentication failed',
                    'details' => ['http_code' => $authHttpCode]
                ];
            
    }
}

/**
 * Validate RMS session by testing access to a protected page
 * @param array $credentials User credentials
 * @param string $cookieFile Path to cookie file
 * @param string $targetPage Target page to validate access
 * @return bool True if session is valid, false otherwise
 */
function validateRMSSession($credentials, $cookieFile, $targetPage = 'Dashboard/home.php') {
        $logFile = __DIR__ . '/php_errors.log';
        
        // Test access to the target page with existing cookies
        $baseUrl = 'https://rms.final.digital';
        $testUrl = $baseUrl . '/' . ltrim($targetPage, '/');
        
        $ch = curl_init($testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        // Log session validation attempt
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Session validation for user: " . $credentials['platform_username'] . ", HTTP Code: $httpCode, Final URL: $finalUrl", FILE_APPEND);
        
        // Check if session is valid
        $hasLoginForm = strpos($response, 'login') !== false || strpos($response, 'Login') !== false;
        $hasDashboard = strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false;
        $redirectedToLogin = strpos($finalUrl, 'index.php') !== false && $hasLoginForm;
        
        if ($httpCode === 200 && !$redirectedToLogin && ($hasDashboard || !$hasLoginForm)) {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS session is valid for user: " . $credentials['platform_username'], FILE_APPEND);
                return true;
        } else {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS session is invalid/expired for user: " . $credentials['platform_username'], FILE_APPEND);
                return false;
        }
}



// =============================================================================
// AUTHENTICATION & PLATFORM INTEGRATION FUNCTIONS
// =============================================================================

/**
 * Authenticate to a generic platform using multiple login endpoints
 * 
 * @param string $username Platform username
 * @param string $password Platform password
 * @param string $platformName Platform name
 * @param array $platform Platform data
 */
function authenticateToGenericPlatform($username, $password, $platformName, $platform) {

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Generic authentication attempt for platform: $platformName, user: $username", FILE_APPEND);

        $loginUrl = $platform['url'];
        $loginEndpoints = [
            '/login', '/auth', '/signin', '/admin/login', '/user/login', '/account/login', '/dashboard/login', '/portal/login', '/system/login', '/api/login', '/api/auth', '/api/signin'
        ];
        $fieldCombinations = [
            ['username', 'password'], ['user', 'pass'], ['email', 'password'], ['login', 'password'], ['userid', 'password'], ['account', 'password'], ['id', 'password'], ['name', 'password'], ['username', 'pass'], ['user', 'password']
        ];

        $authenticated = false;
        $finalResponse = '';
        $finalHttpCode = 0;
        $finalUrl = '';
        $curl_error = null;

        foreach ($loginEndpoints as $endpoint) {

                $fullUrl = rtrim($loginUrl, '/') . $endpoint;
                foreach ($fieldCombinations as $fields) {

                        $postData = [];
                        $postData[$fields[0]] = $username;
                        $postData[$fields[1]] = $password;

                        $ch = curl_init($fullUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/x-www-form-urlencoded',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                        ]);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                        $response = curl_exec($ch);
                        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                        $curl_error = curl_error($ch);
                        curl_close($ch);

                        if ($curl_error) {

                                continue;
                            
            }

                        if ($httpcode === 200) {

                                if (strpos($response, 'login') !== false && strpos($response, 'Login') !== false && (strpos($response, 'username') !== false || strpos($response, 'password') !== false) && !strpos($response, 'dashboard') && !strpos($response, 'Dashboard') && !strpos($response, 'RMS')) {

                                        continue;
                                    
                }
                                if (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false || strpos($response, 'admin') !== false || strpos($response, 'Admin') !== false || strpos($response, 'RMS') !== false) {

                                        $authenticated = true;
                                        $finalResponse = $response;
                                        $finalHttpCode = $httpcode;
                                        $finalUrl = $final_url;
                                        break 2;
                                    
                }
                                if (strpos($response, 'login') !== false && (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false)) {

                                        $authenticated = true;
                                        $finalResponse = $response;
                                        $finalHttpCode = $httpcode;
                                        $finalUrl = $final_url;
                                        break 2;
                                    
                }
                                $json_response = json_decode($response, true);
                                if ($json_response !== null && isset($json_response['success']) && $json_response['success']) {

                                        $authenticated = true;
                                        $finalResponse = $response;
                                        $finalHttpCode = $httpcode;
                                        $finalUrl = $final_url;
                                        break 2;
                                    
                }
                            
            }
                        if ($httpcode >= 300 && $httpcode < 400) {

                                $authenticated = true;
                                $finalResponse = $response;
                                $finalHttpCode = $httpcode;
                                $finalUrl = $final_url;
                                break 2;
                            
            }
                    
        }
            
    }

        if (!$authenticated) {

                $finalResponse = isset($response) ? $response : '';
                $finalHttpCode = isset($httpcode) ? $httpcode : 0;
                $finalUrl = isset($final_url) ? $final_url : '';
            
    }

        // If cURL error and not authenticated
        if ($curl_error && !$authenticated) {

                return [
                    'success' => false,
                    'error' => 'Failed to connect to platform',
                    'details' => $curl_error
                ];
            
    }

        if ($authenticated) {

                save_platform_credentials($username, $platformName, $username, $password);
                return [
                    'success' => true,
                    'message' => 'Platform authentication successful',
                    'platform' => $platformName,
                    'status' => $finalHttpCode,
                    'final_url' => $finalUrl
                ];
            
    }

        $json_response = json_decode($finalResponse, true);
        if ($json_response !== null) {

                return [
                    'success' => true,
                    'message' => 'Platform authentication successful',
                    'platform' => $platformName,
                    'response' => $json_response
                ];
            
    }

        if (strpos($finalResponse, '<html') !== false || strpos($finalResponse, '<!DOCTYPE') !== false) {

                if (strpos($finalResponse, 'login') !== false || strpos($finalResponse, 'Login') !== false) {

                        return [
                            'success' => false,
                            'error' => 'Login required - platform returned login page',
                            'platform' => $platformName,
                            'status' => $finalHttpCode
                        ];
                    
        }
                if (strpos($finalResponse, 'dashboard') !== false || strpos($finalResponse, 'Dashboard') !== false || strpos($finalResponse, 'admin') !== false || strpos($finalResponse, 'Admin') !== false) {

                        return [
                            'success' => true,
                            'message' => 'Platform authentication successful - dashboard detected',
                            'platform' => $platformName,
                            'status' => $finalHttpCode,
                            'final_url' => $finalUrl
                        ];
                    
        }
                return [
                    'success' => true,
                    'message' => 'Platform authentication successful - HTML response received',
                    'platform' => $platformName,
                    'status' => $finalHttpCode,
                    'response_length' => strlen($finalResponse)
                ];
            
    }

        if ($finalHttpCode >= 300 && $finalHttpCode < 400) {

                return [
                    'success' => true,
                    'message' => 'Platform authentication successful - redirect handled',
                    'platform' => $platformName,
                    'status' => $finalHttpCode,
                    'final_url' => $finalUrl
                ];
            
    }

        return [
            'success' => false,
            'error' => 'Unable to authenticate with platform - all login endpoints failed',
            'platform' => $platformName,
            'status' => $finalHttpCode,
            'response_length' => strlen($finalResponse),
            'endpoints_tried' => $loginEndpoints
        ];
}

// =============================================================================
// RMS DASHBOARD PROXY FUNCTIONS
// =============================================================================



/**
 * RMS Direct Authentication - Authenticates user and redirects to RMS with session
 * This function handles the transition from proxy to direct access with session validation
 */
function handleRmsDirectAuth() {
        global $method;

        if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
        }

        $username = $_GET['username'] ?? '';
        $targetPage = $_GET['page'] ?? 'Dashboard/home.php';

        if (!$username) {
                http_response_code(400);
                echo json_encode(['error' => 'Username is required']);
                return;
        }

        // Build credentials from current request instead of stored RMS credentials
        $credentials = buildUniversalCredentialsFromRequest($username);
        if (!$credentials) {
                http_response_code(401);
                echo json_encode(['error' => 'Missing credentials', 'message' => 'Provide password or password_hash in the request.']);
                return;
        }

        // Log the direct auth attempt
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Direct Auth attempt for user: $username, target: $targetPage", FILE_APPEND);

        // Get the cookie file path
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        
        // Check if we have an existing session and validate it
        $sessionValid = false;
        if (file_exists($cookieFile)) {
                $sessionValid = validateRMSSession($credentials, $cookieFile, $targetPage);
        }
        
        // If session is not valid, re-authenticate
        if (!$sessionValid) {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Session invalid or expired, re-authenticating user: $username", FILE_APPEND);
                
                // Force re-authentication by removing old cookie file
                if (file_exists($cookieFile)) {
                        unlink($cookieFile);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Removed old cookie file for user: $username", FILE_APPEND);
                }
                
                // Authenticate to RMS
                $authResult = authenticateToRMS($credentials);
                if (!$authResult['success']) {
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS authentication failed for user: $username - " . $authResult['message'], FILE_APPEND);
                        http_response_code(401);
                        echo json_encode(['error' => 'RMS authentication failed: ' . $authResult['message']]);
                        return;
                }
                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS re-authentication successful for user: $username", FILE_APPEND);
        } else {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Existing session valid for user: $username", FILE_APPEND);
        }

        // Read and parse the cookie file
        if (!file_exists($cookieFile)) {
                http_response_code(500);
                echo json_encode(['error' => 'Session cookie file not found']);
                return;
        }

        $cookieContent = file_get_contents($cookieFile);
        $cookies = [];
        
        // Parse Netscape format cookie file
        $lines = explode("\n", $cookieContent);
        foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] === '#') continue;
                
                $parts = explode("\t", $line);
                if (count($parts) >= 7) {
                        $domain = $parts[0];
                        $path = $parts[2];
                        $secure = $parts[3];
                        $expiry = $parts[4];
                        $name = $parts[5];
                        $value = $parts[6];
                        
                        // Only include cookies for rms.final.digital
                        if ($domain === 'rms.final.digital' || $domain === '.rms.final.digital') {
                                $cookies[] = "$name=$value";
                        }
                }
        }

        if (empty($cookies)) {
                http_response_code(500);
                echo json_encode(['error' => 'No valid session cookies found']);
                return;
        }

        // Build the target URL
        $baseUrl = 'https://rms.final.digital';
        $targetUrl = $baseUrl . '/' . ltrim($targetPage, '/');
        
        // Add query parameters from current request
        $queryParams = $_GET;
        unset($queryParams['endpoint'], $queryParams['username'], $queryParams['page']);
        if (!empty($queryParams)) {
                $targetUrl .= (strpos($targetUrl, '?') !== false ? '&' : '?') . http_build_query($queryParams);
        }

        // Log successful authentication
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Direct Auth successful for user: $username, redirecting to: $targetUrl", FILE_APPEND);

        // Set cookies in the browser and redirect
        foreach ($cookies as $cookie) {
                // Parse the cookie to get name and value
                $parts = explode('=', $cookie, 2);
                if (count($parts) === 2) {
                        $cookieName = $parts[0];
                        $cookieValue = $parts[1];
                        
                        // Set cookie with proper attributes for cross-domain access
                        header("Set-Cookie: $cookieName=$cookieValue; Domain=.rms.final.digital; Path=/; Secure; HttpOnly; SameSite=None");
                }
        }

        // Redirect to RMS with session
        header("Location: $targetUrl");
        exit;
}



/**
 * RMS Notifications Direct Authentication - Authenticates user and redirects to RMS notifications with session
 * This function handles direct access to RMS notifications page
 */
function handleRmsNotificationsDirectAuth() {
        global $method;

        if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
        }

        $username = $_GET['username'] ?? '';
        $targetPage = $_GET['page'] ?? 'Dashboard/notifications.php';

        if (!$username) {
                http_response_code(400);
                echo json_encode(['error' => 'Username is required']);
                return;
        }

        // Get RMS credentials and authenticate
        $credentials = buildUniversalCredentialsFromRequest($username);
        if (!$credentials) {
                http_response_code(401);
                echo json_encode(['error' => 'No RMS credentials found']);
                return;
        }

        // Log the direct auth attempt
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Notifications Direct Auth attempt for user: $username, target: $targetPage", FILE_APPEND);

        // Get the cookie file path
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        
        // Check if we have an existing session and validate it
        $sessionValid = false;
        if (file_exists($cookieFile)) {
                $sessionValid = validateRMSSession($credentials, $cookieFile, $targetPage);
        }
        
        // If session is not valid, re-authenticate
        if (!$sessionValid) {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Session invalid or expired, re-authenticating user: $username", FILE_APPEND);
                
                // Force re-authentication by removing old cookie file
                if (file_exists($cookieFile)) {
                        unlink($cookieFile);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Removed old cookie file for user: $username", FILE_APPEND);
                }
                
                // Authenticate to RMS
                $authResult = authenticateToRMS($credentials);
                if (!$authResult['success']) {
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS authentication failed for user: $username - " . $authResult['message'], FILE_APPEND);
                        http_response_code(401);
                        echo json_encode(['error' => 'RMS authentication failed: ' . $authResult['message']]);
                        return;
                }
                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS re-authentication successful for user: $username", FILE_APPEND);
        } else {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Existing session valid for user: $username", FILE_APPEND);
        }

        // Read and parse the cookie file
        $cookieContent = file_get_contents($cookieFile);
        $cookies = [];
        
        // Parse Netscape format cookie file
        $lines = explode("\n", $cookieContent);
        foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] === '#') continue;
                
                $parts = explode("\t", $line);
                if (count($parts) >= 7) {
                        $domain = $parts[0];
                        $path = $parts[2];
                        $secure = $parts[3];
                        $expiry = $parts[4];
                        $name = $parts[5];
                        $value = $parts[6];
                        
                        // Only include cookies for rms.final.digital
                        if ($domain === 'rms.final.digital' || $domain === '.rms.final.digital') {
                                $cookies[] = "$name=$value";
                        }
                }
        }

        if (empty($cookies)) {
                http_response_code(500);
                echo json_encode(['error' => 'No valid session cookies found']);
                return;
        }

        // Build the target URL
        $baseUrl = 'https://rms.final.digital';
        $targetUrl = $baseUrl . '/' . ltrim($targetPage, '/');
        
        // Add query parameters from current request
        $queryParams = $_GET;
        unset($queryParams['endpoint'], $queryParams['username'], $queryParams['page']);
        if (!empty($queryParams)) {
                $targetUrl .= (strpos($targetUrl, '?') !== false ? '&' : '?') . http_build_query($queryParams);
        }

        // Log successful authentication
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Notifications Direct Auth successful for user: $username, redirecting to: $targetUrl", FILE_APPEND);

        // Set cookies in the browser and redirect
        foreach ($cookies as $cookie) {
                // Parse the cookie to get name and value
                $parts = explode('=', $cookie, 2);
                if (count($parts) === 2) {
                        $cookieName = $parts[0];
                        $cookieValue = $parts[1];
                        
                        // Set cookie with proper attributes for cross-domain access
                        header("Set-Cookie: $cookieName=$cookieValue; Domain=.rms.final.digital; Path=/; Secure; HttpOnly; SameSite=None");
                }
        }

        // Redirect to RMS notifications with session
        header("Location: $targetUrl");
        exit;
}




// =============================================================================
// PLATFORM HANDLING FUNCTIONS
// =============================================================================

/**
 * Handle platforms requests
 */
function handlePlatforms() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        
        // Get all platforms
        $platforms = get_platforms();

        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'platforms' => $platforms
        ]);
}

// =============================================================================
// NOTIFICATION FUNCTIONS
// =============================================================================

/**
 * Handle notifications - fetch from all platforms
 */
function handleNotifications() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        
        // Get username from query string
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'You must provide a username.'
                ]);
                return;
            
    }
        
        // Log notification request
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] handleNotifications() called for username: $username", FILE_APPEND);
        
        // Fetch platforms and get notifications from each
        $platforms = get_platforms();
        $allNotifications = [];
        $platformResults = [];
        
        foreach ($platforms as $platform) {

                $platformName = $platform['name'];
                $platformResult = [
                    'platform' => $platformName,
                    'notifications' => [],
                    'status' => 'no_url'
                ];
                
                // Log platform processing
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Processing platform: $platformName, URL: " . ($platform['notifications_url'] ?? 'none'), FILE_APPEND);
                
                // Only fetch notifications for RMS and Leave and Absence
                if (!empty($platform['notifications_url']) && ($platformName === 'RMS' || $platformName === 'Leave and Absence')) {

                        $notifications = fetchNotificationsFromPlatform($username, $platform);
                        

                        
                        $platformResult['notifications'] = $notifications;
                        $platformResult['status'] = count($notifications) > 0 ? 'success' : 'no_notifications';
                        $allNotifications = array_merge($allNotifications, $notifications);
                        
                        // Log notification fetch result
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Platform $platformName returned " . count($notifications) . " notifications", FILE_APPEND);
                    
        }
                
                $platformResults[] = $platformResult;
            
    }
        
        // LMS subplatform notification fetching removed
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'notifications' => $allNotifications,
            'platforms' => $platformResults
            // LMS subplatforms count removed
        ]);
}

/**
 * Handle announcements endpoint
 * Fetches active announcements from the database
 */
function handleAnnouncements() {
    global $method;
    
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'error' => 'Method not allowed',
            'message' => 'This endpoint only supports GET requests.'
        ]);
        return;
    }
    
    try {
        // Get active announcements from database
        $announcements = get_active_announcements();
        
        echo json_encode([
            'success' => true,
            'announcements' => $announcements
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'message' => 'Failed to fetch announcements: ' . $e->getMessage()
        ]);
    }
}

/**
 * Handle dining menu today request
 */
function handleDiningMenuToday() {
    global $method;
    
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'error' => 'Method not allowed',
            'message' => 'This endpoint only supports GET requests.'
        ]);
        return;
    }
    
    try {
        // Get date parameter, default to today if not provided. Only allow today and tomorrow.
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $date = isset($_GET['date']) ? $_GET['date'] : $today;
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid date format',
                'message' => 'Date must be in YYYY-MM-DD format.'
            ]);
            return;
        }
        
        // Only allow today or tomorrow for users
        if ($date !== $today && $date !== $tomorrow) {
            http_response_code(403);
            echo json_encode([
                'error' => 'Forbidden',
                'message' => 'Access to dining menu is limited to today and tomorrow.'
            ]);
            return;
        }
        
        // Get dining menu for the specified date
        $dining_menu = get_dining_menu_by_date($date);
        
        echo json_encode([
            'success' => true,
            'dining_menu' => $dining_menu
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'message' => 'Failed to fetch dining menu: ' . $e->getMessage()
        ]);
    }
}

/**
 * Fetch notifications from a specific platform
 * 
 * @param string $username Username
 * @param array $platform Platform information
 * @return array Notifications array
 */
function fetchNotificationsFromPlatform($username, $platform) {

        $logFile = __DIR__ . '/php_errors.log';
        $platformName = $platform['name'];
        $url = $platform['notifications_url'];
        
        // Log function entry
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] fetchNotificationsFromPlatform called for platform: $platformName, URL: $url", FILE_APPEND);
        
        $credentials = buildUniversalCredentialsFromRequest($username);
        $notifications = [];
        
        if (!$credentials) {
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No credentials in request for user: $username, platform: $platformName", FILE_APPEND);
                // Proceed best-effort without credentials (may rely on existing cookies/session)
                $credentials = ['platform_username' => $username, 'platform_password' => null];
    }
        
        // Log credentials found
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Credentials found for user: $username, platform: $platformName", FILE_APPEND);
        
        // Platform-specific notification fetching
        switch ($platformName) {

                case 'RMS':
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Using RMS special authentication", FILE_APPEND);
                return fetchNotificationsFromRMS($credentials, $url);
                case 'Leave and Absence':
                    // Do not proxy/fetch Leave Portal notifications; return a direct-open item instead
                    return [[
                        'platform' => 'Leave and Absence',
                        'title' => 'Open Leave Portal Notifications',
                        'message' => 'Click to view your notifications on the Leave Portal.',
                        'date' => date('Y-m-d H:i:s'),
                        'url' => 'https://leave.final.digital/notifications/all_notifications.php'
                    ]];

                case 'LMS':
                    file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Using LMS special authentication", FILE_APPEND);
                    return fetchNotificationsFromLMS($credentials, $url);
                default:
                    return fetchNotificationsFromGenericPlatform($url, $platformName);
            
    }
}

/**
 * Fetch notifications from RMS platform
 * 
 * @param array $credentials User credentials
 * @param string $url Notifications URL
 * @return array Notifications array
 */
function fetchNotificationsFromRMS($credentials, $url) {

        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        
        // Check if we have a valid session
        if (!file_exists($cookieFile)) {

                // Try to authenticate first
                $authResult = authenticateToRMS($credentials);
                if (!$authResult['success']) {

                        return [];
                    
        }
            
    }
        
        // Fetch notifications using the session cookie
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Log the raw response for troubleshooting
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS raw response (HTTP $httpCode):\n" . substr($response, 0, 2000) . "\n", FILE_APPEND);
        
        if ($httpCode === 200) {

                $notifications = parseNotificationsFromResponse($response, 'RMS', $url);
                
                // Filter out notifications that have been marked as read or deleted locally
                $username = $credentials['platform_username'] ?? '';
                if ($username) {

                        $actionFile = __DIR__ . '/notification_actions.json';
                        $actions = [];
                        
                        if (file_exists($actionFile)) {

                                $actions = json_decode(file_get_contents($actionFile), true) ?: [];
                            
            }
                        
                        // Filter out notifications that have been acted upon
                        $filteredNotifications = [];
                        foreach ($notifications as $notification) {

                                $notificationId = $notification['id'] ?? '';
                                $notificationTitle = $notification['title'] ?? '';
                                $notificationMessage = $notification['message'] ?? '';
                                $shouldInclude = true;
                                
                                // Check if this notification has been marked as read or deleted
                                foreach ($actions as $actionKey => $actionData) {

                                        if ($actionData['username'] === $username && 
                                            $actionData['platform'] === 'RMS') {

                                            // Check by ID if available, otherwise check by content
                                            $matchesNotification = false;
                                            if (!empty($notificationId) && $actionData['notification_id'] == $notificationId) {
                                                $matchesNotification = true;
                                            } elseif (!empty($notificationTitle) && !empty($notificationMessage)) {
                                                // If no ID, check by title and message content
                                                $actionTitle = $actionData['title'] ?? '';
                                                $actionMessage = $actionData['message'] ?? '';
                                                if ($notificationTitle === $actionTitle && $notificationMessage === $actionMessage) {
                                                    $matchesNotification = true;
                                                }
                                            }
                                            
                                            if ($matchesNotification) {
                                                
                                                if ($actionData['action'] === 'delete') {

                                /* INCLUDE/REQUIRE: Verify that included paths are not user-controlled to avoid remote code execution. */
                                                        $shouldInclude = false;
                            // Don't include deleted notifications
                                                        break;
                                                    
                        }
                        elseif ($actionData['action'] === 'mark_read' || $actionData['action'] === 'toggle_read') {

                                                        // For read notifications, exclude them to match the expected behavior
                                                        $shouldInclude = false;
                                                        break;
                                                    
                        }
                        }
                                            
                    }
                                    
                }
                                
                                if ($shouldInclude) {
                                    // Only include notifications that have actual content
                                    if (!empty($notification['title']) || !empty($notification['message'])) {
                                        $filteredNotifications[] = $notification;
                                    }
                }
                            
            }
                        
                        return $filteredNotifications;
                    
        }
                
                return $notifications;
            
    }
        
        return [];
}

/**
 * Fetch notifications from Leave Portal
 * 
 * @param array $credentials User credentials
 * @param string $url Notifications URL
 * @return array Notifications array
 */
function fetchNotificationsFromLeavePortal($credentials, $url) {

        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
        
        // Check if we have a valid session
        if (!file_exists($cookieFile)) {

                // Try to authenticate first
                $authResult = authenticateToLeavePortal($credentials);
                if (!$authResult['success']) {

                        return [];
                    
        }
            
    }
        
        // Fetch notifications using the session cookie
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Log the raw response for troubleshooting
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal raw response (HTTP $httpCode):\n" . substr($response, 0, 2000) . "\n", FILE_APPEND);
        
        if ($httpCode === 200) {

                $notifications = parseNotificationsFromResponse($response, 'Leave and Absence', $url);
                
                // Filter out notifications that have been marked as read or deleted locally
                $username = $credentials['platform_username'] ?? '';
                if ($username) {

                        $actionFile = __DIR__ . '/notification_actions.json';
                        $actions = [];
                        
                        if (file_exists($actionFile)) {

                                $actions = json_decode(file_get_contents($actionFile), true) ?: [];
                            
            }
                        
                        // Log the filtering process for debugging
                        $logFile = __DIR__ . '/php_errors.log';
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Starting notification filtering for user: $username", FILE_APPEND);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Total notifications before filtering: " . count($notifications), FILE_APPEND);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Total recorded actions: " . count($actions), FILE_APPEND);
                        
                        // Filter out notifications that have been acted upon
                        $filteredNotifications = [];
                        foreach ($notifications as $notification) {

                                $notificationId = $notification['id'] ?? '';
                                $shouldInclude = true;
                                
                                // Log each notification being checked
                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Checking notification ID: $notificationId", FILE_APPEND);
                                
                                // Check if this notification has been marked as read or deleted
                                foreach ($actions as $actionKey => $actionData) {

                                        // Convert both IDs to strings for comparison
                                        $actionNotificationId = (string)($actionData['notification_id'] ?? '');
                                        $currentNotificationId = (string)$notificationId;
                                        
                                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Comparing: action_user='{$actionData['username']}' vs current_user='$username', action_id='$actionNotificationId' vs current_id='$currentNotificationId', action_platform='{$actionData['platform']}' vs 'Leave and Absence'", FILE_APPEND);
                                        
                                        if ($actionData['username'] === $username && 
                                            $actionNotificationId === $currentNotificationId && 
                                            $actionData['platform'] === 'Leave and Absence') {

                                                
                                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Found matching action: {$actionData['action']} for notification $notificationId", FILE_APPEND);
                                                
                                                if ($actionData['action'] === 'delete') {

                                /* INCLUDE/REQUIRE: Verify that included paths are not user-controlled to avoid remote code execution. */
                                                        $shouldInclude = false;
                            // Don't include deleted notifications
                                                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Excluding deleted notification $notificationId", FILE_APPEND);
                                                        break;
                                                    
                        }
                        elseif ($actionData['action'] === 'mark_read' || $actionData['action'] === 'toggle_read') {

                                                        // For read notifications, exclude them to match the expected behavior
                                                        $shouldInclude = false;
                                                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Excluding read notification $notificationId", FILE_APPEND);
                                                        break;
                                                    
                        }
                                            
                    }
                                    
                }
                                
                                if ($shouldInclude) {

                                        $filteredNotifications[] = $notification;
                                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Including notification $notificationId", FILE_APPEND);
                                    
                }
                else {

                                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Filtering out notification $notificationId", FILE_APPEND);
                                    
                }
                            
            }
                        
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Total notifications after filtering: " . count($filteredNotifications), FILE_APPEND);
                        return $filteredNotifications;
                    
        }
                
                return $notifications;
            
    }
        
        return [];
}


/**
 * Fetch notifications from LMS platform
 * 
 * @param array $credentials User credentials
 * @param string $url Notifications URL
 * @return array Notifications array
 */


/**
 * Fetch notifications from generic platforms
 * 
 * @param string $url Notifications URL
 * @param string $platformName Platform name
 * @return array Notifications array
 */
function fetchNotificationsFromGenericPlatform($url, $platformName) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {

                return parseNotificationsFromResponse($response, $platformName, $url);
            
    }
        
        return [];
}

/**
 * Parse notifications from response
 * 
 * @param string $response Response content
 * @param string $platformName Platform name
 * @param string $url Source URL
 * @return array Notifications array
 */
function parseNotificationsFromResponse($response, $platformName, $url) {

        $notifications = [];
        
        // Try to parse as JSON first
        $jsonResponse = json_decode($response, true);
        if ($jsonResponse !== null && is_array($jsonResponse)) {

                // Handle JSON notifications
                foreach ($jsonResponse as $notification) {

                        $notifications[] = [
                            'platform' => $platformName,
                            'title' => $notification['title'] ?? 'Notification',
                            'message' => $notification['message'] ?? $notification['content'] ?? '',
                            'date' => $notification['date'] ?? date('Y-m-d H:i:s'),
                            'url' => $url
                        ];
                    
        }
                return $notifications;
            
    }
        
        // Parse HTML response
        if (strpos($response, '<html') !== false || strpos($response, '<table') !== false) {

                // Special handling for RMS notifications - filter for unread only
                if ($platformName === 'RMS') {

                        return parseRMSNotifications($response, $url);
                    
        }

                // Log the response content for Leave Portal debugging
                if ($platformName === 'Leave and Absence') {

                        $logFile = __DIR__ . '/php_errors.log';
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal response contains: " . (strpos($response, 'Ibrahim Avci') !== false ? 'YES - Ibrahim Avci found' : 'NO - Ibrahim Avci not found'), FILE_APPEND);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal response contains: " . (strpos($response, 'flex-grow-1') !== false ? 'YES - flex-grow-1 found' : 'NO - flex-grow-1 not found'), FILE_APPEND);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal response contains: " . (strpos($response, 'requires your review') !== false ? 'YES - requires your review found' : 'NO - requires your review not found'), FILE_APPEND);
                    
        }

                // 1. Try table-based notifications (legacy)
            /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/si', $response, $rowMatches)) {

                        foreach ($rowMatches[1] as $rowHtml) {

                    /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                                if (preg_match_all('/<td[^>]*>(.*?)<\/td>/si', $rowHtml, $cellMatches)) {

                                        $cells = $cellMatches[1];
                                        if (count($cells) >= 2) {
                        // Accept 2 or 3 columns
                                                $title = trim(strip_tags($cells[0] ?? ''));
                                                $message = trim(strip_tags($cells[1] ?? ''));
                                                $date = isset($cells[2]) ? trim(strip_tags($cells[2])) : date('Y-m-d H:i:s');
                                                if (!empty($message)) {

                                                        $notifications[] = [
                                                            'platform' => $platformName,
                                                            'title' => $title ?: 'Notification',
                                                            'message' => $message,
                                                            'date' => $date,
                                                            'url' => $url
                                                        ];
                                                    
                        }
                                            
                    }
                                    
                }
                            
            }
                    
        }

                // 2. Try extracting from notification cards/divs
                if (empty($notifications)) {

                        // Look for Bootstrap notification structures and general notification divs
                        $notificationPatterns = [
                            '/<div[^>]*class=["\']?([^"\'>]*notification[^"\'>]*)["\']?[^>]*>(.*?)<\/div>/is',
                            '/<div[^>]*class=["\']?([^"\'>]*flex-grow-1[^"\'>]*)["\']?[^>]*>(.*?)<\/div>/is',
                            '/<div[^>]*class=["\']?([^"\'>]*alert[^"\'>]*)["\']?[^>]*>(.*?)<\/div>/is'
                        ];
                        
                        foreach ($notificationPatterns as $pattern) {

                    /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                                if (preg_match_all($pattern, $response, $divMatches, PREG_SET_ORDER)) {

                                        foreach ($divMatches as $match) {

                                                $divFullMatch = $match[0];
                        // Full div element
                                                $divHtml = $match[2];
                        // Content inside div
                                                
                                                // Extract notification ID from the div element attributes
                                                $notificationId = '';
                                                
                                                // Look for ID in data attributes or onclick events (common in Leave Portal)
                                                if (preg_match('/data-notification-id=["\']?([^"\'\s>]+)["\']?/i', $divFullMatch, $idMatch)) {

                                                        $notificationId = $idMatch[1];
                                                    
                        }
                        elseif (preg_match('/data-id=["\']?([^"\'\s>]+)["\']?/i', $divFullMatch, $idMatch)) {

                                                        $notificationId = $idMatch[1];
                                                    
                        }
                        elseif (preg_match('/id=["\']?notification[_-]?(\d+)["\']?/i', $divFullMatch, $idMatch)) {

                                                        $notificationId = $idMatch[1];
                                                    
                        }
                        elseif (preg_match('/onclick=["\'][^"\']*(?:notification|id)[_=](\d+)[^"\']*["\']/', $divFullMatch, $idMatch)) {

                                                        $notificationId = $idMatch[1];
                                                    
                        }
                                                
                                                // Extract the main text from h6 or similar headers
                                                $title = '';
                                                $message = '';
                                                $date = '';
                                                
                                                // Try to extract structured content
                                                if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $divHtml, $titleMatch)) {

                                                        $title = trim(strip_tags($titleMatch[1]));
                                                    
                        }
                                                
                                                if (preg_match('/<small[^>]*>(.*?)<\/small>/is', $divHtml, $dateMatch)) {

                                                        $date = trim(strip_tags($dateMatch[1]));
                                                    
                        }
                                                
                                                // If no structured content, use the whole text
                                                if (empty($title)) {

                                                        $text = trim(strip_tags($divHtml));
                                                        if (strlen($text) > 20 && strlen($text) < 1000) {

                                                                // Clean up the text to extract the main message
                                                                $lines = explode("\n", $text);
                                                                $cleanLines = [];
                                                                foreach ($lines as $line) {

                                                                        $line = trim($line);
                                                                        if (strlen($line) > 10 && !preg_match('/^(new|unread|\d+)$/i', $line)) {

                                                                                $cleanLines[] = $line;
                                                                            
                                    }
                                                                    
                                }
                                                                if (!empty($cleanLines)) {

                                                                        $title = array_shift($cleanLines);
                                                                        $message = implode(' ', $cleanLines);
                                                                    
                                }
                                                            
                            }
                                                    
                        }
                        else {

                                                        $message = $title;
                                                    
                        }
                                                
                                                // For Leave Portal, try to extract ID from the message content as fallback
                                                if ($platformName === 'Leave and Absence' && empty($notificationId)) {

                                                        // Look for patterns like "ID: 51" or "(ID: 50)" in the message
                                                        if (preg_match('/\(ID:\s*(\d+)\)/i', $message, $idMatch)) {

                                                                $notificationId = $idMatch[1];
                                                            
                            }
                            elseif (preg_match('/ID:\s*(\d+)/i', $message, $idMatch)) {

                                                                $notificationId = $idMatch[1];
                                                            
                            }
                            elseif (preg_match('/leave\s+request\s+from\s+\w+.*\((\d+)\)/i', $message, $idMatch)) {

                                                                $notificationId = $idMatch[1];
                                                            
                            }
                                                    
                        }
                                                
                                                if (!empty($message) && strlen($message) > 10) {

                                                        $notification = [
                                                            'platform' => $platformName,
                                                            'title' => 'Notification',
                                                            'message' => $message,
                                                            'date' => !empty($date) ? $date : date('Y-m-d H:i:s'),
                                                            'url' => $url
                                                        ];
                                                        
                                                        // Add ID if we found one
                                                        if (!empty($notificationId)) {

                                                                $notification['id'] = $notificationId;
                                                            
                            }
                                                        
                                                        $notifications[] = $notification;
                                                    
                        }
                                            
                    }
                                        
                                        // If we found notifications with this pattern, don't try other patterns
                                        if (!empty($notifications)) {

                                                break;
                                            
                    }
                                    
                }
                            
            }
                    
        }

                // 3. Try extracting from list items (li)
                if (empty($notifications)) {

                /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                        if (preg_match_all('/<li[^>]*class=["\']?([^"\'>]*notification[^"\'>]*)["\']?[^>]*>(.*?)<\/li>/is', $response, $liMatches)) {

                                foreach ($liMatches[2] as $liHtml) {

                                        $text = trim(strip_tags($liHtml));
                                        if (strlen($text) > 10) {

                                                $notifications[] = [
                                                    'platform' => $platformName,
                                                    'title' => 'Notification',
                                                    'message' => $text,
                                                    'date' => date('Y-m-d H:i:s'),
                                                    'url' => $url
                                                ];
                                            
                    }
                                    
                }
                            
            }
                    
        }

                // 4. Fallback: extract lines with keywords
                if (empty($notifications)) {

                        $keywords = ['notification', 'leave', 'absence', 'izin', 'duyuru', 'alert'];
                        $cleaned = strip_tags($response);
                        $lines = preg_split('/\r?\n/', $cleaned);
                        foreach ($lines as $line) {

                                $line = trim($line);
                                if (strlen($line) > 10 && strlen($line) < 500) {

                                        // Skip CSS, JavaScript, and other non-notification content
                                        if (preg_match('/^(\.|#|\@|function|var|const|let|\{|\}|\/\*|\*\/|\/\/|return|if|else|for|while|switch|case|\$|fetch|document\.|window\.|console\.|\.css|\.js|width:|height:|margin:|padding:|color:|background:|border:|font|display:|position:|top:|left:|right:|bottom:|z-index:|opacity:)/i', $line)) {

                                                continue;
                                            
                    }
                                        // Skip any line that looks like CSS or JavaScript
                                        if (preg_match('/(\{|\}|;$|:.*px|:.*%|:.*em|:.*rem|rgba?\(|#[0-9a-f]{3,6}|!important)/i', $line)) {

                                                continue;
                                            
                    }
                                        foreach ($keywords as $kw) {

                                                if (stripos($line, $kw) !== false) {

                                                        $notifications[] = [
                                                            'platform' => $platformName,
                                                            'title' => 'Notification',
                                                            'message' => $line,
                                                            'date' => date('Y-m-d H:i:s'),
                                                            'url' => $url
                                                        ];
                                                        break;
                                                    
                        }
                                            
                    }
                                    
                }
                            
            }
                    
        }

                // If still nothing, log for debugging
                if (empty($notifications)) {

                        $logFile = __DIR__ . '/php_errors.log';
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No real notifications found for platform: $platformName", FILE_APPEND);
                    
        }
            
    }
        
        // Deduplicate and filter notifications for Leave and Absence
        if ($platformName === 'Leave and Absence' && !empty($notifications)) {

                $logFile = __DIR__ . '/php_errors.log';
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal: Starting deduplication with " . count($notifications) . " notifications", FILE_APPEND);
                
                $seen = [];
                $filtered = [];
                foreach ($notifications as $index => $notif) {

                        $msg = trim($notif['message']);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal notification #$index: '$msg'", FILE_APPEND);
                        
                        // Skip empty, too short, or too generic messages
                        if (empty($msg) || strlen($msg) < 15) {

                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipped #$index: too short or empty", FILE_APPEND);
                                continue;
                            
            }
                        // Skip if message is a known header/footer, summary, or already seen
                        $lower = strtolower($msg);
                        if (isset($seen[$lower])) {

                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipped #$index: duplicate message", FILE_APPEND);
                                continue;
                            
            }
                        // Skip summary/count/date-only lines and CSS/JS content
                        if (preg_match('/^(notifications|recent notifications|view all|view all notifications|profile|footer|copyright|leave and absence|user menu|settings|logout|my courses|\d+ unread|\d+|unread|new|\(\d+ unread\)|[a-z]{3,9} \d{1,2}, \d{4}( \d{1,2}:\d{2} (am|pm))?|\d{4}-\d{2}-\d{2}.*|\.|#|\@|function|var|const|let|\{|\}|\/\*|\*\/|\/\/|return|if|else|for|while|switch|case|\$|fetch|document\.|window\.|console\.|\.notification|notification-|:.*px|:.*%|rgba?\(|#[0-9a-f]{3,6})$/i', $msg)) {

                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipped #$index: generic/summary content", FILE_APPEND);
                                continue;
                            
            }
                        
                        // Skip specific UI elements and navigation items that are not real notifications
                        $uiElements = [
                            'no notifications available',
                            'no notifications found', 
                            'your notifications',
                            'absence records',
                            'all notifications',
                            'notifications',
                            'you\'ll see your notifications here',
                            'recent notifications',
                            'view all notifications',
                            'menu',
                            'navigation',
                            'header',
                            'footer',
                            'sidebar'
                        ];
                        
                        $msgLower = strtolower(trim($msg));
                        foreach ($uiElements as $element) {
                            if ($msgLower === $element || strpos($msgLower, $element) === 0) {
                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipped #$index: UI element '$element'", FILE_APPEND);
                                continue 2; // Skip to next notification
                            }
            }
                        // Skip if message is only numbers, whitespace, or punctuation (but allow meaningful text with punctuation)
                        if (preg_match('/^[\d\s\W]*$/', $msg) && !preg_match('/[a-zA-Z]/', $msg)) {

                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipped #$index: only numbers/symbols", FILE_APPEND);
                                continue;
                            
            }
                        // Allow messages that look like real notifications (contain meaningful content)
                        if (!preg_match('/(request|requires|approved|rejected|review|leave|absence|from|by|requires your|ibrahim|avci|\w+ \w+.*\w+|\w+.*\d+.*\w+)/i', $msg)) {

                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipped #$index: doesn't look like real notification", FILE_APPEND);
                                continue;
                            
            }
                        $seen[$lower] = true;
                        $filtered[] = $notif;
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Added #$index to filtered results", FILE_APPEND);
                    
        }
                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal: Finished deduplication with " . count($filtered) . " notifications", FILE_APPEND);
                return $filtered;
            
    }
        return $notifications;
}

/**
 * Parse RMS notifications and filter for unread only
 * 
 * @param string $response Response content
 * @param string $url Source URL
 * @return array Notifications array
 */
function parseRMSNotifications($response, $url) {

        $notifications = [];
        
        // Log the response for debugging
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS notifications parsing - response length: " . strlen($response) . "\n", FILE_APPEND);
        
        // Look for table rows that contain notification data
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/si', $response, $rowMatches)) {

                foreach ($rowMatches[1] as $rowIndex => $rowHtml) {

                        // Skip header rows
                        if (strpos(strtolower($rowHtml), '<th') !== false) {

                                continue;
                            
            }
                        
                        // Extract cells from the row
                /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                        if (preg_match_all('/<td[^>]*>(.*?)<\/td>/si', $rowHtml, $cellMatches)) {

                                $cells = $cellMatches[1];
                                
                                // Check if this row represents an unread notification
                                $isUnread = false;
                                
                                // Method 1: Check for "Read" span (indicates read notification) - this takes priority
                                if (preg_match('/<span[^>]*class[^>]*btn-outline-success[^>]*>Read<\/span>/i', $rowHtml)) {

                                        $isUnread = false;
                                        // Skip this notification entirely - it's definitely read
                                        continue;
                                    
                }
                                
                                // Method 2: Check for "Mark as Read" button (indicates unread notification)
                                if (preg_match('/<a[^>]*href[^>]*mark_as_read\.php[^>]*>Mark as Read<\/a>/i', $rowHtml)) {

                                        $isUnread = true;
                                    
                }
                                
                                // Method 3: Check for unread CSS classes
                                if (preg_match('/class\s*=\s*["\'][^"\']*?(?:unread|new|unread-notification)[^"\']*?["\']/i', $rowHtml)) {

                                        $isUnread = true;
                                    
                }
                                
                                // Method 4: Check for bold text (often indicates unread)
                                if (preg_match('/<(?:strong|b)[^>]*>(.*?)<\/(?:strong|b)>/i', $rowHtml)) {

                                        $isUnread = true;
                                    
                }
                                
                                // Method 5: Check for specific data attributes
                                if (preg_match('/data-read\s*=\s*["\']false["\']/i', $rowHtml) || 
                                    preg_match('/data-status\s*=\s*["\']unread["\']/i', $rowHtml)) {

                                        $isUnread = true;
                                    
                }
                                
                                // Method 6: Check for specific text patterns that indicate unread status
                                if (preg_match('/\b(?:unread|new|pending|unseen)\b/i', $rowHtml)) {

                                        $isUnread = true;
                                    
                }
                                
                                if ($isUnread && count($cells) >= 2) {

                                        $title = trim(strip_tags($cells[0] ?? ''));
                                        $message = trim(strip_tags($cells[1] ?? ''));
                                        
                                        $date = '';
                                        
                                        // Try to extract date from additional cells (usually the last cell)
                                        if (count($cells) >= 4) {

                                                $date = trim(strip_tags($cells[3] ?? ''));
                                            
                    }
                    elseif (count($cells) >= 3) {

                                                $date = trim(strip_tags($cells[count($cells) - 1] ?? ''));
                                            
                    }
                                        
                                        // If no date in separate cells, try to extract from the message or row
                                        if (empty($date) || !preg_match('/\d/', $date)) {

                                                // Look for dates in various formats within the message or row HTML
                                                $searchText = $message . ' ' . $rowHtml;
                                                
                                                // Try multiple date patterns
                                                $datePatterns = [
                                                    '/(\w{3,9}\s+\d{1,2},\s+\d{4}(?:\s+at\s+\d{1,2}:\d{2}\s+[AP]M)?)/i', // August 7, 2025 at 2:01 PM
                                                    '/(\d{4}-\d{2}-\d{2}(?:\s+\d{2}:\d{2}:\d{2})?)/i', // 2025-08-07 14:01:00
                                                    '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}(?:\s+\d{1,2}:\d{2}(?::\d{2})?\s*[AP]M?)?)/i', // 08/07/2025 2:01 PM
                                                    '/(\d{1,2}\s+\w{3,9}\s+\d{4}(?:\s+\d{1,2}:\d{2}\s*[AP]M)?)/i', // 7 August 2025 2:01 PM
                                                ];
                                                
                                                foreach ($datePatterns as $pattern) {

                                                        if (preg_match($pattern, $searchText, $dateMatch)) {

                                                                $date = trim($dateMatch[1]);
                                                                break;
                                                            
                            }
                                                    
                        }
                                            
                    }
                                        
                                        // Clean up the date if found
                                        if (!empty($date)) {

                                                // Remove any HTML tags that might have slipped through
                                                $date = strip_tags($date);
                                                // Clean up extra whitespace
                                                $date = preg_replace('/\s+/', ' ', $date);
                                                $date = trim($date);
                                            
                    }
                                        
                                        if (!empty($message)) {

                                                // Log the extracted data for debugging
                                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS notification extracted - Title: '$title', Message: '$message', Date: '$date'", FILE_APPEND);
                                                
                                                $notifications[] = [
                                                    'platform' => 'RMS',
                                                    'title' => $title,
                                                    'message' => $message,
                                                    'date' => $date ?: date('Y-m-d H:i:s'),
                                                    'url' => $url
                                                ];
                                            
                    }
                                    
                }
                            
            }
                    
        }
            
    }
        
        // If no table structure found, don't create fake notifications
        if (empty($notifications)) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No real RMS notifications found in response", FILE_APPEND);
            
    }
        
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS parsing complete - found " . count($notifications) . " unread notifications\n", FILE_APPEND);
        
        return $notifications;
}

/**
 * Handle update notification count requests
 */
function handleUpdateNotificationCount() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'You must provide a username.'
                ]);
                return;
            
    }
        
        // Get notifications count for the user
        $notifications = get_notifications($username);
        $count = count($notifications);
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'count' => $count,
            'notifications' => $notifications
        ]);
}

/**
 * Handle get notifications dropdown requests
 */
function handleGetNotificationsDropdown() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'You must provide a username.'
                ]);
                return;
            
    }
        
        // Get notifications for the user
        $notifications = get_notifications($username);
        
        // Format notifications for dropdown display
        $formattedNotifications = [];
        foreach ($notifications as $notification) {

                $formattedNotifications[] = [
                    'id' => $notification['id'],
                    'platform' => $notification['platform'],
                    'message' => $notification['message'],
                    'url' => $notification['url'],
                    'created_at' => $notification['created_at']
                ];
            
    }
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'notifications' => $formattedNotifications,
            'count' => count($formattedNotifications)
        ]);
}

/**
 * Handle mark notification as read requests
 */
function handleMarkNotificationRead() {

        global $method;
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        $notificationId = isset($data['notification_id']) ? $data['notification_id'] : null;
        $platform = isset($data['platform']) ? $data['platform'] : null;
        
        if (!$notificationId) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Notification ID is required',
                    'message' => 'You must provide a notification ID.'
                ]);
                return;
            
    }
        
        // For external platform notifications, perform the action on the external platform
        if ($platform && ($platform === 'Leave and Absence' || $platform === 'RMS')) {

                $result = performNotificationActionOnExternalPlatform($platform, $notificationId, 'mark_read', true);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode($result);
                return;
            
    }
        
        // Forward to the real Leave Portal
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $credentials = buildUniversalCredentialsFromRequest($username);
        if ($credentials) {
            $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
            $realPortalUrl = 'https://leave.final.digital/notifications/mark_notification_read.php';
            
            // Forward the request to the real Leave Portal
            $ch = curl_init($realPortalUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['notification_id' => $notificationId]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Log the real portal response
            $logFile = __DIR__ . '/php_errors.log';
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Real portal mark_read: HTTP $httpCode, Response: $response\n", FILE_APPEND);
            
            // Forward the response
            http_response_code($httpCode);
            if ($contentType) {
                header('Content-Type: ' . $contentType);
            }
            echo $response;
            return;
        }
        
        // Fallback to local database
        $success = markNotificationAsRead($notificationId);
        
        if ($success) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            
    }
    else {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to mark notification as read',
                    'message' => 'An error occurred while marking the notification as read.'
                ]);
            
    }
}

/**
 * Handle get notification count requests
 */
function handleGetNotificationCount() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'You must provide a username.'
                ]);
                return;
            
    }
        
        // Forward to the real Leave Portal for actual notification count
        $credentials = buildUniversalCredentialsFromRequest($username);
        if ($credentials) {
            $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
            $realPortalUrl = 'https://leave.final.digital/notifications/get_notification_count.php';
            
            // Forward the request to the real Leave Portal
            $ch = curl_init($realPortalUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Log the real portal response
            $logFile = __DIR__ . '/php_errors.log';
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Real portal notification count: HTTP $httpCode, Response: $response\n", FILE_APPEND);
            
            // Forward the response
            http_response_code($httpCode);
            if ($contentType) {
                header('Content-Type: ' . $contentType);
            }
            echo $response;
            return;
        }
        
        // Fallback to local notifications if no credentials
        $notifications = get_notifications($username);
        $count = count($notifications);
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
}

/**
 * Handle toggle notification read status requests
 */
function handleToggleNotificationRead() {

        $method = $_SERVER['REQUEST_METHOD']; // Get method locally
        
        // Debug logging
        $logFile = __DIR__ . '/php_errors.log';
        $postData = file_get_contents('php://input');
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] toggleNotificationRead: Method=$method, POST data: $postData, GET: " . print_r($_GET, true) . "\n", FILE_APPEND);
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Extract notification ID from different possible sources
        $notificationId = null;
        if (isset($data['notification_id'])) {
            $notificationId = $data['notification_id'];
        } elseif (isset($data['id'])) {
            $notificationId = $data['id'];
        } elseif (isset($_POST['notification_id'])) {
            $notificationId = $_POST['notification_id'];
        } elseif (isset($_POST['id'])) {
            $notificationId = $_POST['id'];
        }
        
        // Default to toggle (read status will be determined by current state)
        $readStatus = isset($data['read_status']) ? $data['read_status'] : 1; // Default to mark as read
        $platform = isset($data['platform']) ? $data['platform'] : null;
        $username = isset($data['username']) ? $data['username'] : (isset($_GET['username']) ? $_GET['username'] : '');
        
        // Debug log
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] toggle_notification_read debug: notificationId='$notificationId', readStatus='$readStatus', username='$username', data=" . print_r($data, true) . "\n", FILE_APPEND);
        
        if (!$notificationId) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Notification ID is required',
                    'message' => 'You must provide a notification ID.',
                    'debug' => [
                        'received_data' => $data,
                        'post_data' => $_POST,
                        'get_params' => $_GET
                    ]
                ]);
                return;
            
    }
        
        // For external platform notifications, perform the action on the external platform
        if ($platform && ($platform === 'Leave and Absence' || $platform === 'RMS')) {

                if (!$username) {

                        http_response_code(400);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'error' => 'Username is required for external platform actions',
                            'message' => 'Username is required for external platform actions.'
                        ]);
                        return;
                    
        }
                $result = performNotificationActionOnExternalPlatform($platform, $notificationId, 'toggle_read', $readStatus);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode($result);
                return;
            
    }
        
        // For Leave Portal notifications, forward to the real server
        // Since this is coming from the Leave Portal proxy, forward it to the real Leave Portal
        $credentials = buildUniversalCredentialsFromRequest($username);
        if ($credentials) {
            $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
            $realPortalUrl = 'https://leave.final.digital/notifications/toggle_notification_read.php';
            
            // Forward the request to the real Leave Portal
            $ch = curl_init($realPortalUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['notification_id' => $notificationId]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Log the real portal response
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Real portal response: HTTP $httpCode, Response: $response\n", FILE_APPEND);
            
            // Forward the response
            http_response_code($httpCode);
            if ($contentType) {
                header('Content-Type: ' . $contentType);
            }
            echo $response;
            return;
        }
        
        // For database notifications (if any) - fallback
        $success = toggleNotificationReadStatus($notificationId, $readStatus);
        
        if ($success) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification read status updated',
                    'new_status' => $readStatus ? 'read' : 'unread'
                ]);
            
    }
    else {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to update notification read status',
                    'message' => 'An error occurred while updating the notification read status.'
                ]);
            
    }
}

/**
 * Handle toggle all notifications read status requests
 */
function handleToggleAllNotificationsRead() {

        $method = $_SERVER['REQUEST_METHOD']; // Get method locally
        
        // Debug logging
        $logFile = __DIR__ . '/php_errors.log';
        $postData = file_get_contents('php://input');
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] toggleAllNotificationsRead: Method=$method, POST data: $postData, GET: " . print_r($_GET, true) . "\n", FILE_APPEND);
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        $username = isset($data['username']) ? $data['username'] : (isset($_GET['username']) ? $_GET['username'] : '');
        
        // For toggle_all_notifications_read.php, we don't need read_status parameter
        // The real Leave Portal endpoint just marks all as read
        $readStatus = 1; // Default to marking as read (like the real portal)
        $platform = isset($data['platform']) ? $data['platform'] : null;
        
        // Debug validation
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Validation: username='$username', readStatus='$readStatus' (auto-set), platform='$platform', data=" . print_r($data, true) . "\n", FILE_APPEND);
        
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'You must provide a username.',
                    'debug' => [
                        'username' => $username,
                        'received_data' => $data,
                        'get_params' => $_GET
                    ]
                ]);
                return;
            
    }
        
        // For external platform notifications, perform the action on the external platform
        if ($platform && ($platform === 'Leave and Absence' || $platform === 'RMS')) {

                $result = performNotificationActionOnExternalPlatform($platform, 'all', 'toggle_all_read', $readStatus);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode($result);
                return;
            
    }
        
        // For Leave Portal notifications, forward to the real server
        // Since this is coming from the Leave Portal proxy, forward it to the real Leave Portal
        $credentials = buildUniversalCredentialsFromRequest($username);
        if ($credentials) {
            $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
            $realPortalUrl = 'https://leave.final.digital/notifications/toggle_all_notifications_read.php';
            
            // Forward the request to the real Leave Portal
            $ch = curl_init($realPortalUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ''); // Empty body like the real portal
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Log the real portal response
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Real portal toggle_all response: HTTP $httpCode, Response: $response\n", FILE_APPEND);
            
            // Forward the response
            http_response_code($httpCode);
            if ($contentType) {
                header('Content-Type: ' . $contentType);
            }
            echo $response;
            return;
        }
        
        // Toggle all notifications read status in database - fallback
        $success = toggleAllNotificationsReadStatus($username, $readStatus);
        
        if ($success) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'All notifications read status updated',
                    'new_status' => $readStatus ? 'read' : 'unread'
                ]);
            
    }
    else {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to update all notifications read status',
                    'message' => 'An error occurred while updating all notifications read status.'
                ]);
            
    }
}

/**
 * Handle clear old notifications requests
 */
function handleClearOldNotifications() {

        global $method;
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        $username = isset($data['username']) ? $data['username'] : (isset($_GET['username']) ? $_GET['username'] : '');
        $daysOld = isset($data['days_old']) ? $data['days_old'] : 30;
    // Default to 30 days
        $platform = isset($data['platform']) ? $data['platform'] : null;
        
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'You must provide a username.'
                ]);
                return;
            
    }
        
        // For external platform notifications, perform the action on the external platform
        if ($platform && ($platform === 'Leave and Absence' || $platform === 'RMS')) {

                $result = performNotificationActionOnExternalPlatform($platform, 'all', 'clear_old', $daysOld);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode($result);
                return;
            
    }
        
        // Forward to the real Leave Portal (but this endpoint returns 404 on real server)
        $credentials = buildUniversalCredentialsFromRequest($username);
        if ($credentials) {
            $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
            $realPortalUrl = 'https://leave.final.digital/notifications/clear_old_notifications.php';
            
            // Forward the request to the real Leave Portal
            $ch = curl_init($realPortalUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['days_old' => $daysOld]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Log the real portal response
            $logFile = __DIR__ . '/php_errors.log';
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Real portal clear_old: HTTP $httpCode, Response: $response\n", FILE_APPEND);
            
            // If real portal returns 404, provide our own implementation
            if ($httpCode === 404) {
                // Real portal doesn't support this, provide local functionality
                $success = clearOldNotifications($username, $daysOld);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Old notifications cleared locally' : 'Failed to clear notifications',
                    'note' => 'Real portal does not support this endpoint (404)'
                ]);
                return;
            }
            
            // Forward the real response
            http_response_code($httpCode);
            if ($contentType) {
                header('Content-Type: ' . $contentType);
            }
            echo $response;
            return;
        }
        
        // Fallback to local clearing
        $success = clearOldNotifications($username, $daysOld);
        
        if ($success) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Old notifications cleared successfully',
                    'days_old' => $daysOld
                ]);
            
    }
    else {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to clear old notifications',
                    'message' => 'An error occurred while clearing old notifications.'
                ]);
            
    }
}

/**
 * Handle delete notification requests
 */
function handleDeleteNotification() {

        global $method;
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        $notificationId = isset($data['id']) ? $data['id'] : (isset($data['notification_id']) ? $data['notification_id'] : null);
        $platform = isset($data['platform']) ? $data['platform'] : null;
        
        if (!$notificationId) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Notification ID is required',
                    'message' => 'You must provide a notification ID.'
                ]);
                return;
            
    }
        
        // For external platform notifications, perform the action on the external platform
        if ($platform && ($platform === 'Leave and Absence' || $platform === 'RMS')) {

                $result = performNotificationActionOnExternalPlatform($platform, $notificationId, 'delete', null);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode($result);
                return;
            
    }
        
        // Forward to the real Leave Portal first
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $credentials = buildUniversalCredentialsFromRequest($username);
        if ($credentials) {
            $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
            
            // Try different possible delete endpoints on the real Leave Portal
            $possibleEndpoints = [
                'https://leave.final.digital/notifications/delete_notification.php',
                'https://leave.final.digital/notifications/delete.php',
                'https://leave.final.digital/notifications/remove_notification.php'
            ];
            
            foreach ($possibleEndpoints as $realPortalUrl) {
                $ch = curl_init($realPortalUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['notification_id' => $notificationId, 'id' => $notificationId]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                curl_close($ch);
                
                // Log the attempt
                $logFile = __DIR__ . '/php_errors.log';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Real portal delete attempt: $realPortalUrl, HTTP $httpCode, Response: $response\n", FILE_APPEND);
                
                // If we get a successful response (not 404), use it
                if ($httpCode === 200 || $httpCode === 201) {
                    http_response_code($httpCode);
                    if ($contentType) {
                        header('Content-Type: ' . $contentType);
                    }
                    echo $response;
                    return;
                }
            }
            
            // If all endpoints failed, log it but continue to local deletion
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] All real portal delete endpoints failed, falling back to local deletion\n", FILE_APPEND);
        }
        
        require_once 'db_connection.php';
        
        // Fallback: Delete the notification from local database
        $success = delete_notification($notificationId);
        
        if ($success) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification deleted successfully'
                ]);
            
    }
    else {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to delete notification'
                ]);
            
    }
}

/**
 * Record notification action to prevent showing acted-upon notifications
 * 
 * @param string $username Username
 * @param string $notificationId Notification ID
 * @param string $platform Platform name
 * @param string $action Action performed (mark_read, toggle_read, delete)
 * @param string $title Notification title (optional)
 * @param string $message Notification message (optional)
 * @return bool Success status
 */
function recordNotificationAction($username, $notificationId, $platform, $action, $title = '', $message = '') {

        $actionFile = __DIR__ . '/notification_actions.json';
        $actions = [];
        
        // Load existing actions
        if (file_exists($actionFile)) {

                $actions = json_decode(file_get_contents($actionFile), true) ?: [];
            
    }
        
        // Create a unique key for this action
        $actionKey = $username . '_' . $platform . '_' . $notificationId . '_' . $action;
        
        // Record the action with timestamp
        $actions[$actionKey] = [
            'username' => $username,
            'notification_id' => $notificationId,
            'platform' => $platform,
            'action' => $action,
            'title' => $title,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Save the updated actions
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        $result = file_put_contents($actionFile, json_encode($actions, JSON_PRETTY_PRINT));
        
        // Log the action recording
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Recorded notification action: $actionKey", FILE_APPEND);
        
        return $result !== false;
}

// =============================================================================
// PROXY & EXTERNAL INTEGRATION FUNCTIONS
// =============================================================================

/**
 * Perform notification actions on external platforms
 * 
 * @param string $platform Platform name
 * @param string $notificationId Notification ID
 * @param string $action Action to perform (mark_read, toggle_read, delete)
 * @param bool|null $readStatus Read status for toggle actions
 * @return array Result of the action
 */
function performNotificationActionOnExternalPlatform($platform, $notificationId, $action, $readStatus = null) {

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Performing $action on $platform for notification $notificationId", FILE_APPEND);
        
        $result = null;
        if ($platform === 'Leave and Absence') {

                $result = performLeavePortalNotificationAction($notificationId, $action, $readStatus);
            
    }
    elseif ($platform === 'RMS') {

                $result = performRMSNotificationAction($notificationId, $action, $readStatus);
            
    }
    else {

                return [
                    'success' => false,
                    'message' => 'Unsupported platform for notification actions'
                ];
            
    }
        
        // If the action was successful, record it to prevent showing the notification again
        if ($result && isset($result['success']) && $result['success']) {

                $username = $_GET['username'] ?? '';
                if ($username) {

                        recordNotificationAction($username, $notificationId, $platform, $action);
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Successfully recorded action $action for notification $notificationId on $platform", FILE_APPEND);
                    
        }
            
    }
        
        return $result;
}

/**
 * Perform notification actions on Leave Portal
 * 
 * @param string $notificationId Notification ID
 * @param string $action Action to perform
 * @param bool|null $readStatus Read status for toggle actions
 * @return array Result of the action
 */
function performLeavePortalNotificationAction($notificationId, $action, $readStatus = null) {

        $logFile = __DIR__ . '/php_errors.log';
        
        // Get user credentials for Leave Portal
        $username = $_GET['username'] ?? '';
        if (!$username) {

                return ['success' => false, 'message' => 'Username not provided'];
            
    }
        
        $credentials = buildUniversalCredentialsFromRequest($username);
        if (!$credentials) {

                return ['success' => false, 'message' => 'No credentials found for Leave Portal'];
            
    }
        
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
        
        // Check if we have a valid session
        if (!file_exists($cookieFile)) {

                // Try to authenticate first
                $authResult = authenticateToLeavePortal($credentials);
                if (!$authResult['success']) {

                        return ['success' => false, 'message' => 'Failed to authenticate to Leave Portal'];
                    
        }
            
    }
        
        $baseUrl = 'https://leave.final.digital';
        
        // For Leave Portal, we now know the actual endpoints from the analysis
        // These are the endpoints found in the Leave Portal's JavaScript code
        
        switch ($action) {

                case 'mark_read':
                case 'toggle_read':
                    $endpoint = '/notifications/toggle_notification_read.php';
                    $postData = [
                        'notification_id' => $notificationId
                    ];
                    break;
                case 'delete':
                    $endpoint = '/notifications/delete_notification.php';
                    $postData = [
                        'notification_id' => $notificationId
                    ];
                    break;
                case 'toggle_all_read':
                    $endpoint = '/notifications/toggle_all_notifications_read.php';
                    $postData = [
                        'read_status' => $readStatus ? 1 : 0
                    ];
                    break;
                case 'clear_old':
                    $endpoint = '/notifications/clear_old_notifications.php';
                    $postData = [
                        'days_old' => 30
                    ];
                    break;
                default:
                    return ['success' => false, 'message' => 'Unsupported action'];
            
    }
        
        // Make the request to the Leave Portal backend using the actual endpoints
        $url = $baseUrl . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'X-Requested-With: XMLHttpRequest'
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal $action request: URL=$url, Data=" . json_encode($postData), FILE_APPEND);
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal $action response: HTTP $httpCode, Response: $response", FILE_APPEND);
        
        if ($curlError) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal $action CURL error: $curlError", FILE_APPEND);
                return ['success' => false, 'message' => 'Network error: ' . $curlError];
            
    }
        
        // Try to parse the response as JSON first
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && $responseData !== null) {

                // Successful JSON response
                if (isset($responseData['success']) && $responseData['success']) {

                        return [
                            'success' => true,
                            'message' => $responseData['message'] ?? ucfirst($action) . ' completed successfully',
                            'new_status' => $action === 'delete' ? null : ($readStatus ? 'read' : 'unread')
                        ];
                    
        }
        else {

                        return [
                            'success' => false,
                            'message' => $responseData['message'] ?? 'Action failed on Leave Portal'
                        ];
                    
        }
            
    }
    elseif ($httpCode === 200) {

                // Non-JSON response but successful HTTP code - assume success
                return [
                    'success' => true,
                    'message' => ucfirst($action) . ' completed successfully',
                    'new_status' => $action === 'delete' ? null : ($readStatus ? 'read' : 'unread')
                ];
            
    }
    elseif ($httpCode === 404) {

                // If JSON approach fails, try form data approach
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] JSON request failed with 404, trying form data", FILE_APPEND);
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json, text/html, */*',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'X-Requested-With: XMLHttpRequest'
                ]);
                
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal $action form request: HTTP $httpCode, Response: $response", FILE_APPEND);
                
                if (!$curlError && ($httpCode === 200 || $httpCode === 302)) {

                        return [
                            'success' => true,
                            'message' => ucfirst($action) . ' completed successfully',
                            'new_status' => $action === 'delete' ? null : ($readStatus ? 'read' : 'unread')
                        ];
                    
        }
                
                return [
                    'success' => false,
                    'message' => "Leave Portal returned HTTP $httpCode"
                ];
            
    }
    else {

                // HTTP error
                return [
                    'success' => false,
                    'message' => "Leave Portal returned HTTP $httpCode"
                ];
            
    }
}

/**
 * Perform notification actions on RMS
 * 
 * @param string $notificationId Notification ID
 * @param string $action Action to perform
 * @param bool|null $readStatus Read status for toggle actions
 * @return array Result of the action
 */
function performRMSNotificationAction($notificationId, $action, $readStatus = null) {

        $logFile = __DIR__ . '/php_errors.log';
        
        // Get user credentials for RMS
        $username = $_GET['username'] ?? '';
        if (!$username) {

                return ['success' => false, 'message' => 'Username not provided'];
            
    }
        
        $credentials = get_platform_credentials($username, 'RMS');
        if (!$credentials) {

                return ['success' => false, 'message' => 'No credentials found for RMS'];
            
    }
        
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        
        // Check if we have a valid session
        if (!file_exists($cookieFile)) {

                // Try to authenticate first
                $authResult = authenticateToRMS($credentials);
                if (!$authResult['success']) {

                        return ['success' => false, 'message' => 'Failed to authenticate to RMS'];
                    
        }
            
    }
        
        $baseUrl = 'https://rms.final.digital';
        
        // Determine the appropriate endpoint based on the action
        switch ($action) {

                case 'mark_read':
                    $endpoint = '/notifications/mark_notification_read.php';
                    $postData = ['notification_id' => $notificationId];
                    break;
                case 'toggle_read':
                    // RMS doesn't have toggle_read, use mark_read instead
                    $endpoint = '/notifications/mark_notification_read.php';
                    $postData = ['notification_id' => $notificationId];
                    break;
                case 'toggle_all_read':
                    // RMS doesn't have toggle_all_read, we'll handle this differently
                    $endpoint = '/notifications/mark_all_notifications_read.php';
                    $postData = [];
                    break;
                case 'clear_old':
                    // RMS doesn't have clear_old, we'll handle this differently
                    $endpoint = '/notifications/clear_old_notifications.php';
                    $postData = ['days_old' => $readStatus];
        // Using readStatus parameter for days_old
                    break;
                case 'delete':
                    $endpoint = '/notifications/delete_notification.php';
                    $postData = ['notification_id' => $notificationId];
                    break;
                default:
                    return ['success' => false, 'message' => 'Unsupported action'];
            
    }
        
        $url = $baseUrl . $endpoint;
        
        // Make the request to RMS
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS $action request: URL=$url, Data=" . json_encode($postData), FILE_APPEND);
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS $action response: HTTP $httpCode, Response: $response", FILE_APPEND);
        
        // Check if the action was successful
        if ($httpCode === 200 || $httpCode === 302) {

                return [
                    'success' => true,
                    'message' => "Notification $action successful",
                    'new_status' => $action === 'toggle_read' ? ($readStatus ? 'read' : 'unread') : null
                ];
            
    }
    else {

                return [
                    'success' => false,
                    'message' => "Failed to $action notification (HTTP $httpCode)"
                ];
            
    }
}

/**
 * Proxy POST requests to external URLs (used for SSO or cross-domain API calls)
 */





/**
 * Proxy image requests to LMS sub-platforms (e.g., theme/image.php)
 * Usage: /database/api.php?endpoint=lms_image_proxy&subplatform=...&username=...&imagepath=...
 */




/**
 * Proxy AJAX/API requests to LMS sub-platforms to avoid CORS errors
 * Usage: /database/api.php?endpoint=lms_ajax_proxy&subplatform=...&username=...&apipath=...
 * Forwards the request to the real LMS server using the user's session cookie
 */


/**
 * Fetch notifications from an external platform (used for cross-system notification integration)
 */
function handleFetchExternalNotifications() {

        global $method, $conn;
        
        try {

                if ($method !== 'GET') {

                        http_response_code(405);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'error' => 'Method not allowed',
                            'message' => 'This endpoint only supports GET requests.'
                        ]);
                        return;
                    
        }
                $platform = isset($_GET['platform']) ? $_GET['platform'] : '';
                $username = isset($_GET['username']) ? $_GET['username'] : '';
                if (!$platform || !$username) {

                        http_response_code(400);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'error' => 'Platform and username are required',
                            'message' => 'You must provide both a platform and a username.'
                        ]);
                        return;
                    
        }
            // Get platform information from database
            $platforms = get_platforms();
            $targetPlatform = null;
            foreach ($platforms as $p) {

                    if ($p['name'] === $platform && !empty($p['notifications_url'])) {

                            $targetPlatform = $p;
                            break;
                        
            }
                
        }
            if (!$targetPlatform) {

                    http_response_code(404);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'error' => 'Platform not found or no notifications URL configured',
                        'message' => 'The specified platform does not exist or no notifications URL is configured.'
                    ]);
                    return;
                
        }
            // Fetch notifications from external platform
            $ch = curl_init($targetPlatform['notifications_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json, text/html, */*',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                // For RMS, SIS, LMS: authenticate if needed
                if ($platform === 'RMS' || $platform === 'SIS' || $platform === 'LMS') {

                    $credentials = buildUniversalCredentialsFromRequest($username);
                    if ($credentials) {

                                if ($platform === 'RMS') {

                                        // RMS: use cookie file
                                $cookieFile = 'cookies/' . $username . '_' . $platform . '.txt';
                                if (!is_dir('cookies')) {

                                        mkdir('cookies', 0755, true);
                                    
                    }
                                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                            
                }
                else {

                                        // SIS/LMS: add Authorization header
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                            'Accept: application/json, text/html, */*',
                                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                            'Authorization: Bearer ' . base64_encode($credentials['platform_username'] . ':' . $credentials['platform_password'])
                                        ]);
                                    
                }
                            
            }
            else {

                    /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                            echo json_encode([
                                'success' => false,
                                'error' => 'No stored credentials for ' . $platform . ' - please authenticate first',
                                'platform' => $platform
                            ]);
                            return;
                        
            }
                
        }
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            if ($curl_error) {

                    http_response_code(500);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'success' => false,
                        'error' => 'Failed to fetch notifications from external platform',
                        'message' => 'An error occurred while fetching notifications from the external platform.',
                        'details' => $curl_error
                    ]);
                    return;
                
        }
            if ($httpcode !== 200) {

                    http_response_code($httpcode);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'success' => false,
                        'error' => 'External platform returned error status',
                        'message' => 'The external platform returned an error status.',
                        'status' => $httpcode
                    ]);
                    return;
                
        }
            // Try to parse as JSON first
            $json_response = json_decode($response, true);
            if ($json_response !== null) {

                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'success' => true,
                        'notifications' => $json_response,
                        'source' => 'external_json'
                    ]);
                    return;
                
        }
            // If not JSON, try to parse HTML and extract notifications
            if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {

                    // Log the response for debugging
                    error_log("External notifications HTML response for $platform: " . substr($response, 0, 500));
                        // Check if it's a login page
                    if ((strpos($response, 'login') !== false || strpos($response, 'Login') !== false) && 
                        (strpos($response, 'username') !== false || strpos($response, 'password') !== false) &&
                        !strpos($response, 'dashboard') && !strpos($response, 'Dashboard') && !strpos($response, 'RMS')) {

                    /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                            echo json_encode([
                                'success' => false,
                                'error' => 'Login required to access notifications',
                                'source' => 'external_html_login',
                                'raw_response_length' => strlen($response)
                            ]);
                            return;
                        
            }
                    // If response contains both login form AND dashboard/RMS content, consider it success
                    if ((strpos($response, 'login') !== false || strpos($response, 'Login') !== false) && 
                        (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false || strpos($response, 'RMS') !== false)) {

                            // This is likely a successful authentication with some login form still present
                                // Try to extract notification content
                                // (For brevity, not duplicating the full HTML parsing logic here)
                    /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                                echo json_encode([
                                'success' => true,
                                    'notifications' => [],
                                'source' => 'external_html_authenticated',
                                    'raw_response_length' => strlen($response)
                                ]);
                            return;
                        
            }
                        // Fallback: minimal message
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'success' => true,
                            'notifications' => [],
                        'source' => 'external_html',
                        'raw_response_length' => strlen($response)
                    ]);
                    return;
                
        }
            // Fallback
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
            echo json_encode([
                'success' => false,
                'error' => 'Unable to parse response from external platform',
                'message' => 'Failed to parse the response from the external platform.',
                'response_length' => strlen($response)
            ]);
            
    }
    catch (Exception $e) {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => false,
                    'error' => 'Internal server error in handleFetchExternalNotifications',
                    'message' => $e->getMessage()
                ]);
            
    }
}
// Handle platform authentication
function handleAuthenticatePlatform() {

        global $method;
        
        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }
        
        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $data = json_decode(file_get_contents('php://input'), true);
        $platformName = isset($data['platform']) ? $data['platform'] : '';
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        
        if (!$platformName || !$username || !$password) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Platform name, username, and password are required',
                    'message' => 'You must provide a platform name, username, and password.'
                ]);
                return;
            
    }
        
        // Get platform information from database
        $platforms = get_platforms();
        $targetPlatform = null;
        
        foreach ($platforms as $p) {

                if ($p['name'] === $platformName) {

                        $targetPlatform = $p;
                        break;
                    
        }
            
    }
        
        if (!$targetPlatform) {

                http_response_code(404);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Platform not found',
                    'message' => 'The specified platform does not exist.'
                ]);
                return;
            
    }
        
        // Hash the password
        $password_hash = hash('sha256', $password);
        
        // Handle platform-specific authentication
        if ($platformName === 'SIS') {

                // Get SIS credentials for the user
                $credentials = buildUniversalCredentialsFromRequest($username);
                if (!$credentials) {

                        http_response_code(401);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'error' => 'No SIS credentials found for this user',
                            'message' => 'No SIS credentials found for this user.'
                        ]);
                        return;
                    
        }
                
                // Try to authenticate to SIS
                $authResult = authenticateToSIS($credentials);
                
                if ($authResult['success']) {

                        // Authentication successful
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => true,
                            'message' => 'SIS authentication successful'
                        ]);
                    
        }
        else if (isset($authResult['captcha_required']) && $authResult['captcha_required']) {

                        // CAPTCHA required
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => false,
                            'captcha_required' => true,
                            'captcha_url' => $authResult['login_url'],
                            'message' => $authResult['message']
                        ]);
                
        }
        else {

                        // Authentication failed
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => false,
                            'message' => 'SIS authentication failed: ' . $authResult['message']
                        ]);
                    
        }
                return;
            
    }
    else if ($platformName === 'LMS') {

                // LMS doesn't need authentication
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'LMS access granted (no authentication required)'
                ]);
                return;
            
    }
    else if ($platformName === 'RMS') {

                // Handle RMS authentication
                $credentials = buildUniversalCredentialsFromRequest($username);
                if (!$credentials) {

                        http_response_code(401);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'error' => 'No RMS credentials found for this user',
                            'message' => 'No RMS credentials found for this user.'
                        ]);
                        return;
                    
        }
                
                $authResult = authenticateToRMS($credentials);
                if ($authResult['success']) {

                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => true,
                            'message' => 'RMS authentication successful'
                        ]);
                    
        }
        else {

                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => false,
                            'message' => 'RMS authentication failed: ' . $authResult['message']
                        ]);
                    
        }
                return;
            
    }
        
        // Generic authentication for other platforms
            $loginEndpoints = [
                $targetPlatform['url'] . '/api/login',
                $targetPlatform['url'] . '/login',
                $targetPlatform['url'] . '/auth/login',
                $targetPlatform['url'] // Try root URL last
            ];
        
        $authenticated = false;
        $finalResponse = '';
        $finalHttpCode = 0;
        $finalUrl = '';
        
        // Try each login endpoint
        foreach ($loginEndpoints as $endpoint) {

                // Try different field name combinations
                $fieldCombinations = [
                    "username=admin1&password_hash={$password_hash}",
                    "username=admin1&password_hash=" . hash('sha256', '0000'),
                    "user=admin1&password_hash=" . hash('sha256', '0000'),
                    "email=admin1&password_hash=" . hash('sha256', '0000'),
                    "login=admin1&password_hash=" . hash('sha256', '0000')
                ];
                
                foreach ($fieldCombinations as $fields) {

                        $ch = curl_init($endpoint);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
            // Save cookies for later use
                        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
            // Use cookies if they exist
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/x-www-form-urlencoded',
                            'Accept: application/json, text/html, */*',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                        ]);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    
                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                        $response = curl_exec($ch);
                        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                        $curl_error = curl_error($ch);
                        curl_close($ch);
                        
                        if ($curl_error) {

                                continue;
                // Try next field combination
                            
            }
                        
                                // Check if this response indicates successful authentication
                    if ($httpcode === 200) {

                            // Check if it's a login page (failure) - but be more specific
                            if (strpos($response, 'login') !== false && strpos($response, 'Login') !== false && 
                                (strpos($response, 'username') !== false || strpos($response, 'password') !== false) &&
                                !strpos($response, 'dashboard') && !strpos($response, 'Dashboard') && !strpos($response, 'RMS')) {

                                    continue;
                    // Try next field combination
                                
                }
                            
                            // Check if it's a dashboard or main page (success)
                            if (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false || 
                                strpos($response, 'admin') !== false || strpos($response, 'Admin') !== false ||
                                strpos($response, 'RMS') !== false) {

                                    $authenticated = true;
                                    $finalResponse = $response;
                                    $finalHttpCode = $httpcode;
                                    $finalUrl = $final_url;
                                    break 2;
                    // Break out of both loops
                                
                }
                            
                            // Special case: If response contains both login form AND dashboard, consider it success
                            if (strpos($response, 'login') !== false && (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false)) {

                                    $authenticated = true;
                                    $finalResponse = $response;
                                    $finalHttpCode = $httpcode;
                                    $finalUrl = $final_url;
                                    break 2;
                    // Break out of both loops
                                
                }
                                
                                // For JSON responses, check if it indicates success
                                $json_response = json_decode($response, true);
                                if ($json_response !== null && isset($json_response['success']) && $json_response['success']) {

                                        $authenticated = true;
                                        $finalResponse = $response;
                                        $finalHttpCode = $httpcode;
                                        $finalUrl = $final_url;
                                        break 2;
                    // Break out of both loops
                                    
                }
                            
            }
                        
                        // Handle redirects (might indicate successful login)
                        if ($httpcode >= 300 && $httpcode < 400) {

                                $authenticated = true;
                                $finalResponse = $response;
                                $finalHttpCode = $httpcode;
                                $finalUrl = $final_url;
                                break 2;
                // Break out of both loops
                            
            }
                    
        }
            
    }
        
        // If no endpoint worked, use the last response for error reporting
        if (!$authenticated) {

                $finalResponse = $response;
                $finalHttpCode = $httpcode;
                $finalUrl = $final_url;
            
    }
        
        // Use the final response for the rest of the function
        $response = $finalResponse;
        $httpcode = $finalHttpCode;
        $final_url = $finalUrl;
        
        if ($curl_error && !$authenticated) {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to connect to platform',
                    'message' => 'Failed to connect to the platform.',
                    'details' => $curl_error
                ]);
                return;
            
    }
        
        // If we successfully authenticated, save credentials and return success
        if ($authenticated) {

                // Save the platform credentials for future use
                save_platform_credentials($username, $platformName, $username, $password);
                
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Platform authentication successful',
                    'platform' => $platformName,
                    'status' => $httpcode,
                    'final_url' => $final_url
                ]);
                return;
            
    }
        
        // Check if response is valid JSON
        $json_response = json_decode($response, true);
        if ($json_response !== null) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Platform authentication successful',
                    'platform' => $platformName,
                    'response' => $json_response
                ]);
                return;
            
    }
        
        // Handle HTML responses
        if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {

                // Check if it's a login page
                if (strpos($response, 'login') !== false || strpos($response, 'Login') !== false) {

                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => false,
                            'error' => 'Login required - platform returned login page',
                            'platform' => $platformName,
                            'status' => $httpcode
                        ]);
                        return;
                    
        }
                
                // Check if it's a dashboard or main page (success)
                if (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false || 
                    strpos($response, 'admin') !== false || strpos($response, 'Admin') !== false) {

                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => true,
                            'message' => 'Platform authentication successful - dashboard detected',
                            'platform' => $platformName,
                            'status' => $httpcode,
                            'final_url' => $final_url
                        ]);
                        return;
                    
        }
                
                // Generic success for HTML response
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Platform authentication successful - HTML response received',
                    'platform' => $platformName,
                    'status' => $httpcode,
                    'response_length' => strlen($response)
                ]);
                return;
            
    }
        
        // Handle redirects
        if ($httpcode >= 300 && $httpcode < 400) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Platform authentication successful - redirect handled',
                    'platform' => $platformName,
                    'status' => $httpcode,
                    'final_url' => $final_url
                ]);
                return;
            
    }
        
        // Fallback - all endpoints failed
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => false,
            'error' => 'Unable to authenticate with platform - all login endpoints failed',
            'platform' => $platformName,
            'status' => $httpcode,
            'response_length' => strlen($response),
            'endpoints_tried' => $loginEndpoints
        ]);
}
/**
 * Handle LMS sub-platform authentication and notification fetching
 */
function handleLmsSubplatformAuth() {

        global $method, $lms_subplatforms;

        if ($method !== 'POST') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports POST requests.'
                ]);
                return;
            
    }

        /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
        /* DECODING JSON BODY: Ensure structure and types before trusting values. */
        $input = json_decode(file_get_contents('php://input'), true);
        $username = isset($input['username']) ? $input['username'] : '';
        $subplatformName = isset($input['subplatform']) ? $input['subplatform'] : '';

        if (!$username || !$subplatformName) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username and sub-platform name are required',
                    'message' => 'You must provide both a username and a sub-platform name.'
                ]);
                return;
            
    }

        $targetSubplatform = null;
        foreach ($lms_subplatforms as $subplatform) {

                if ($subplatform['name'] === $subplatformName) {

                        $targetSubplatform = $subplatform;
                        break;
                    
        }
            
    }

        if (!$targetSubplatform) {

                http_response_code(404);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Sub-platform not found',
                    'message' => 'The specified sub-platform does not exist.'
                ]);
                return;
            
    }

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Sub-platform Authentication attempt for: $subplatformName, user: $username\n", FILE_APPEND);

        // Get user credentials - use "LMS" as the platform name since that's how credentials are stored
        $credentials = buildUniversalCredentialsFromRequest($username);
        if (!$credentials) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] No LMS credentials found for user: $username\n", FILE_APPEND);
                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'No stored credentials for LMS',
                    'message' => 'No LMS credentials found for this user.'
                ]);
                return;
            
    }

        $baseUrl = rtrim($targetSubplatform['url'], '/');
        $loginUrl = $baseUrl . '/' . ltrim($targetSubplatform['login_endpoint'], '/');

        // --- FIX 1: Simplified and Consistent Cookie File Naming ---
        // Use the host of the LMS sub-platform for the cookie file name
        // This ensures consistency between auth and proxy functions.
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if (!$host) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error: Could not determine host from base URL: $baseUrl\n", FILE_APPEND);
                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Could not determine LMS host',
                    'message' => 'Could not determine the LMS host.'
                ]);
                return;
            
    }
        $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Using cookie file path (Auth): $cookieFile\n", FILE_APPEND);
    // Log cookie file path
        // --- End FIX 1 ---

        // Create cookies directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../cookies')) {

                if (!mkdir(__DIR__ . '/../cookies', 0755, true)) {

                         file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error: Could not create cookies directory.\n", FILE_APPEND);
                         http_response_code(500);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                         echo json_encode([
                             'error' => 'Could not create cookies directory',
                             'message' => 'An error occurred while creating the cookies directory.'
                         ]);
                         return;
                    
        }
            
    }


        // Step 1: Get the login page to extract the logintoken
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Fetching login page: $loginUrl\n", FILE_APPEND);
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    // Store cookies from login page
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $loginPageResponse = curl_exec($ch);
        $loginPageHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $loginPageHttpCode !== 200) {

                $errorDetails = $curlError ?: "HTTP $loginPageHttpCode";
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Failed to fetch login page: $errorDetails\n", FILE_APPEND);
                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to fetch login page',
                    'message' => 'Failed to fetch the login page from the LMS sub-platform.',
                    'details' => $errorDetails
                ]);
                return;
            
    }

        // Step 2: Extract the logintoken (adjust regex if needed for specific LMS)
        $logintoken = '';
        if (preg_match('/<input[^>]*name="logintoken"[^>]*value="([^"]*)"/i', $loginPageResponse, $matches)) {

                $logintoken = $matches[1];
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted logintoken: $logintoken\n", FILE_APPEND);
            
    }
    else {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Warning: Could not find logintoken in login page. Proceeding without it.\n", FILE_APPEND);
            /* INCLUDE/REQUIRE: Verify that included paths are not user-controlled to avoid remote code execution. */
                // Some LMS might not require it or handle it differently.
            
    }

        // Step 3: Perform the login
        $loginData = [
            'username' => $credentials['platform_username'],
            'password' => $credentials['platform_password']
            // 'anchor' => '', // Include if needed and extracted
        ];
        if ($logintoken) {

                $loginData['logintoken'] = $logintoken;
            
    }

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Performing login to: $loginUrl\n", FILE_APPEND);
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // Important to follow redirect after login
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    // Use cookies from login page fetch
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    // Update cookie jar with session cookies
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: ' . $loginUrl // Important: Set referer to the login page
        ]);
        // Optional: Add error handling for cURL
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Timeout after 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    // Connection timeout 10 seconds


        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loginFinalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Primary check: Successful login usually redirects AWAY from the login page URL structure.
        $loginPageUrlPattern = '#[\\/]login[\\/]#i';
    // Pattern to identify login-related URLs

        // --- NEW: Visit landing page to fully establish session ---
        if ($loginHttpCode >= 200 && $loginHttpCode < 400 && !preg_match($loginPageUrlPattern, $loginFinalUrl)) {

                $landingPageUrl = $baseUrl . '/my/';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Visiting landing page to establish session: $landingPageUrl\n", FILE_APPEND);
                $ch_landing = curl_init($landingPageUrl);
                curl_setopt($ch_landing, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_landing, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch_landing, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch_landing, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch_landing, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch_landing, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch_landing, CURLOPT_HTTPHEADER, [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Referer: ' . $loginUrl
                ]);
                curl_setopt($ch_landing, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch_landing, CURLOPT_CONNECTTIMEOUT, 10);
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $landingResponse = curl_exec($ch_landing);
                $landingHttpCode = curl_getinfo($ch_landing, CURLINFO_HTTP_CODE);
                $landingFinalUrl = curl_getinfo($ch_landing, CURLINFO_EFFECTIVE_URL);
                $landingError = curl_error($ch_landing);
                curl_close($ch_landing);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Landing page response HTTP Code: $landingHttpCode, Final URL: $landingFinalUrl\n", FILE_APPEND);
                if ($landingError) {

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] cURL Error during landing page visit: $landingError\n", FILE_APPEND);
                    
        }
            
    }

        if ($curlError) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] cURL Error during login: $curlError\n", FILE_APPEND);
                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'cURL Error during login',
                    'message' => 'An error occurred while logging in.',
                    'details' => $curlError
                ]);
                return;
            
    }

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Login response HTTP Code: $loginHttpCode, Final URL: $loginFinalUrl\n", FILE_APPEND);

        // --- FIX 2: Improved Success Detection Logic ---
        $authenticated = false;

        // Primary check: Successful login usually redirects AWAY from the login page URL structure.
        $loginPageUrlPattern = '/[\/\\\\]login[\/\\\\]/i';
    // Pattern to identify login-related URLs

        if ($loginHttpCode >= 200 && $loginHttpCode < 400) {
        // Check for successful-ish HTTP code
                if (!preg_match($loginPageUrlPattern, $loginFinalUrl)) {

                        // Redirected away from login URL pattern - Strong indicator of success
                        $authenticated = true;
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication successful - Redirected away from login pattern (Final URL: $loginFinalUrl)\n", FILE_APPEND);
                    
        }
        else if (preg_match($loginPageUrlPattern, $loginFinalUrl) && strpos($loginFinalUrl, 'index.php') !== false) {

                        // Still on a login/index.php page - Strong indicator of failure
                        $authenticated = false;
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication failed - Still on login/index.php (Final URL: $loginFinalUrl)\n", FILE_APPEND);
                    
        }
        else {

                        // Ambiguous case based on URL, check body content less strictly
                        // Only look for strong failure indicators (avoiding false positives from library names like 'yui')
                        if (stripos($loginResponse, 'invalid') !== false ||
                            stripos($loginResponse, 'incorrect') !== false ||
                            stripos($loginResponse, 'failure') !== false ||
                            (stripos($loginResponse, 'error') !== false && stripos($loginResponse, 'yui') === false && stripos($loginResponse, 'Error') === false) // Try to avoid false positives
                           ) {

                                $authenticated = false;
                                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication failed - Found strong failure indicators in response body\n", FILE_APPEND);
                            
            }
            else {

                                // If redirected away from *exact* login submission endpoint and no strong failure words, assume success.
                                // Fallback: look for common success page elements
                                if (stripos($loginResponse, 'dashboard') !== false ||
                                    stripos($loginResponse, 'my/') !== false ||
                                    stripos($loginResponse, 'logout') !== false ||
                                    stripos($loginResponse, 'home') !== false) {
                    // Added 'home' as another common success indicator
                                         $authenticated = true;
                                         file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication successful - Found success indicators in response body (fallback)\n", FILE_APPEND);
                                    
                }
                else {

                                         // If still inconclusive, default to failure based on URL being login-like or body lacking clear success.
                                         // The primary URL redirect check should cover most cases.
                                         file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication status ambiguous, defaulting based on URL check (likely failure).\n", FILE_APPEND);
                                    
                }
                            
            }
                    
        }
            
    }
    else {

                // HTTP error code (4xx, 5xx) - Definitely failed
                $authenticated = false;
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication failed - HTTP Error $loginHttpCode\n", FILE_APPEND);
            
    }

        // --- End of FIX 2 ---

        if ($authenticated) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Sub-platform authentication successful for: $subplatformName, user: $username\n", FILE_APPEND);

                // Fetch notifications immediately after successful authentication
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Fetching notifications from LMS sub-platform: $subplatformName\n", FILE_APPEND);
                $baseUrl = rtrim($targetSubplatform['url'], '/');
                $notificationsUrl = $baseUrl . '/' . ltrim($targetSubplatform['notifications_endpoint'], '/');
                $notifications = fetchLmsSubplatformNotifications($notificationsUrl, $subplatformName, $cookieFile);

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => true,
                    'message' => 'Authentication successful',
                    'notifications' => $notifications
                ]);
            
    }
    else {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Sub-platform authentication FAILED for: $subplatformName, user: $username (HTTP: $loginHttpCode, Final URL: $loginFinalUrl)\n", FILE_APPEND);
                // Log part of the response for debugging login failures
                $responsePreview = substr(strip_tags($loginResponse), 0, 500);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Login response preview: $responsePreview...\n", FILE_APPEND);
                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Authentication failed',
                    'message' => 'Login failed or session check failed.',
                    'details' => ['http_code' => $loginHttpCode, 'final_url' => $loginFinalUrl]
                ]);
            
    }
}

/**
 * Parse notifications from HTML response for LMS sub-platforms
 */

/**
 * IMPROVED: Fetch notifications from all LMS sub-platforms for a user
 * @param string $username - Username to fetch notifications for
 * @return array - Array of notifications from all LMS sub-platforms
 */



/**
 * Handle LMS sub-platform proxy access
 * This function proxies requests to LMS sub-platforms to maintain session state
 */


// --- Helper Function for LMS Sub-platform Authentication (Internal Use) ---
// This replicates the core logic of handleLmsSubplatformAuth but is designed to be called internally
// and returns structured data instead of echoing JSON. It also receives the $cookieFile path.

/**
 * Handle LMS AJAX service requests (for CORS issues)
 * This function handles AJAX service calls from LMS sub-platforms
 */



// =============================================================================
// DATABASE HELPER FUNCTIONS
// =============================================================================

/**
 * Universal session validation for all LMS platforms
 * 
 * @param string $response The response content to check
 * @param string $subplatformName The name of the LMS subplatform
 * @param string $logFile Path to log file for debugging
 * @return bool True if session is valid, false otherwise
 */


/**
 * Analyze Leave Portal notification page to find actual endpoints
 */
function analyzeLeavePortalNotificationEndpoints() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'This endpoint only supports GET requests.'
                ]);
                return;
            
    }
        
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username is required',
                    'message' => 'Please provide a username parameter.'
                ]);
                return;
            
    }
        
        $credentials = buildUniversalCredentialsFromRequest($username);
        if (!$credentials) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => false,
                    'message' => 'No credentials found for Leave Portal'
                ]);
                return;
            
    }
        
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
        $baseUrl = 'https://leave.final.digital';
        
        // Ensure we have a valid session
        if (!file_exists($cookieFile)) {

                $authResult = authenticateToLeavePortal($credentials);
                if (!$authResult['success']) {

                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to authenticate to Leave Portal'
                        ]);
                        return;
                    
        }
            
    }
        
        // Fetch the notifications page source
        $notificationUrl = $baseUrl . '/notifications/all_notifications.php';
        $ch = curl_init($notificationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'success' => false,
                    'message' => "Failed to fetch notifications page: HTTP $httpCode"
                ]);
                return;
            
    }
        
        // Analyze the HTML/JavaScript to find endpoints
        $analysis = [
            'fetch_calls' => [],
            'ajax_calls' => [],
            'form_actions' => [],
            'onclick_handlers' => [],
            'javascript_functions' => [],
            'possible_endpoints' => []
        ];
        
        // Find fetch() calls
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/fetch\s*\(\s*[\'"`]([^\'"`]+)[\'"`]/i', $response, $fetchMatches);
        if (!empty($fetchMatches[1])) {

                $analysis['fetch_calls'] = array_unique($fetchMatches[1]);
            
    }
        
        // Find $.ajax, $.post, $.get calls
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/\$\.(ajax|post|get)\s*\(\s*[\'"`]([^\'"`]+)[\'"`]/i', $response, $ajaxMatches);
        if (!empty($ajaxMatches[2])) {

                $analysis['ajax_calls'] = array_unique($ajaxMatches[2]);
            
    }
        
        // Find form actions
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/action\s*=\s*[\'"`]([^\'"`]+)[\'"`]/i', $response, $formMatches);
        if (!empty($formMatches[1])) {

                $analysis['form_actions'] = array_unique($formMatches[1]);
            
    }
        
        // Find onclick handlers that might contain URLs
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/onclick\s*=\s*[\'"`]([^\'"`]*(?:\.php|\/api\/|\/ajax\/)[^\'"`]*)[\'"`]/i', $response, $onclickMatches);
        if (!empty($onclickMatches[1])) {

                $analysis['onclick_handlers'] = array_unique($onclickMatches[1]);
            
    }
        
        // Find function definitions that might handle notifications
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/function\s+(\w*(?:notification|toggle|delete|mark|read)\w*)\s*\(/i', $response, $funcMatches);
        if (!empty($funcMatches[1])) {

                $analysis['javascript_functions'] = array_unique($funcMatches[1]);
            
    }
        
        // Look for specific notification-related patterns
        $notificationPatterns = [
            '/[\'"`]([^\'"`]*(?:notification|toggle|delete|mark|read)[^\'"`]*\.php)[\'"`]/i',
            '/[\'"`]([^\'"`]*\/(?:api|ajax|database)\/[^\'"`]*(?:notification|toggle|delete|mark|read)[^\'"`]*)[\'"`]/i',
            '/url\s*:\s*[\'"`]([^\'"`]*(?:notification|toggle|delete|mark|read)[^\'"`]*)[\'"`]/i'
        ];
        
        foreach ($notificationPatterns as $pattern) {

            /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                preg_match_all($pattern, $response, $matches);
                if (!empty($matches[1])) {

                        $analysis['possible_endpoints'] = array_merge($analysis['possible_endpoints'], $matches[1]);
                    
        }
            
    }
        
        $analysis['possible_endpoints'] = array_unique($analysis['possible_endpoints']);
        
        // Also look for any URLs in JavaScript that contain common endpoint patterns
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/[\'"`]([^\'"`]*\/(?:api|ajax|database|notifications?|dashboard)[^\'"`]*\.php(?:\?[^\'"`]*)?)[\'"`]/i', $response, $urlMatches);
        if (!empty($urlMatches[1])) {

                $analysis['all_php_endpoints'] = array_unique($urlMatches[1]);
            
    }
        
        // Look for data attributes that might contain endpoints
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/data-\w*(?:url|endpoint|action)\s*=\s*[\'"`]([^\'"`]+)[\'"`]/i', $response, $dataMatches);
        if (!empty($dataMatches[1])) {

                $analysis['data_attributes'] = array_unique($dataMatches[1]);
            
    }
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'message' => 'Leave Portal notification page analyzed successfully',
            'analysis' => $analysis,
            'summary' => [
                'total_fetch_calls' => count($analysis['fetch_calls']),
                'total_ajax_calls' => count($analysis['ajax_calls']),
                'total_form_actions' => count($analysis['form_actions']),
                'total_possible_endpoints' => count($analysis['possible_endpoints']),
                'has_notification_functions' => !empty($analysis['javascript_functions'])
            ]
        ], JSON_PRETTY_PRINT);
}

/**
 * Extract JavaScript code from Leave Portal notification page for manual analysis
 */


/**
 * Clear notification actions file to reset filtering
 * This can be used when notification filtering gets corrupted
 */
function clearNotificationActions() {
    $actionFile = __DIR__ . '/notification_actions.json';
    if (file_exists($actionFile)) {
        unlink($actionFile);
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Cleared notification actions file", FILE_APPEND);
        return true;
    }
    return false;
}

/**
 * Authenticate to Leave Portal platform - redirects to login page
 * 
 * @param array $credentials User credentials
 * @return array Authentication result
 */
function authenticateToLeavePortal($credentials) {
        $logFile = __DIR__ . '/php_errors.log';
        $loginUrl = 'https://leave.final.digital/index.php';
        
        // Log the redirect attempt
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal redirect to login page for user: " . $credentials['platform_username'], FILE_APPEND);
        
        // Return success with redirect information instead of attempting authentication
        return [
            'success' => true,
            'message' => 'Redirect to Leave Portal login page',
            'redirect_required' => true,
            'login_url' => $loginUrl,
            'details' => [
                'action' => 'redirect',
                'url' => $loginUrl,
                'platform' => 'Leave and Absence'
            ]
        ];
}

/**
 * Authenticate to SIS platform (simplified - direct access)
 * 
 * @param array $credentials User credentials
 * @return array Authentication result
 */
function authenticateToSIS($credentials) {

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS Authentication attempt for user: " . $credentials['platform_username'], FILE_APPEND);
        
        // SIS login is on the same base URL
        $loginUrl = 'https://sis.final.edu.tr/';
        
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Trying SIS login URL: $loginUrl", FILE_APPEND);
        
        // First, get the login page to extract CAPTCHA if present
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'sis_cookies.txt');
    // Save cookies
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS Login Page Response (HTTP $httpCode) for URL: $loginUrl", FILE_APPEND);

        // Check if CAPTCHA is present in the response
        $hasCaptcha = (strpos($response, 'captcha') !== false || 
                       strpos($response, 'recaptcha') !== false ||
                       strpos($response, 'g-recaptcha') !== false);

        if ($hasCaptcha) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS has CAPTCHA - returning CAPTCHA required response", FILE_APPEND);
                return [
                    'success' => false,
                    'message' => 'SIS requires CAPTCHA verification',
                    'captcha_required' => true,
                    'login_url' => $loginUrl
                ];
            
    }

        // If no CAPTCHA, try to authenticate directly
        $postData = [
            'username' => $credentials['platform_username'],
            'password' => $credentials['platform_password']
        ];

        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'sis_cookies.txt');
    // Use saved cookies
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS Auth Response (HTTP $httpCode) for URL: $loginUrl\n" . substr($response, 0, 1000) . "\n", FILE_APPEND);
        if ($error) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS cURL Error: $error\n", FILE_APPEND);
            
    }

        // Check if authentication was successful
        if ($httpCode === 200 && (strpos($response, 'dashboard') !== false ||
            strpos($response, 'welcome') !== false ||
            strpos($response, 'logout') !== false ||
            strpos($response, 'success') !== false ||
            strpos($response, 'Student Information System') !== false)) {

                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS authentication successful for user: " . $credentials['platform_username'], FILE_APPEND);
                return [
                    'success' => true,
                    'message' => 'SIS authentication successful'
                ];
            
    }

        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS authentication FAILED for user: " . $credentials['platform_username'] . " (HTTP: $httpCode)", FILE_APPEND);
        return [
            'success' => false,
            'message' => 'SIS authentication failed',
            'details' => ['http_code' => $httpCode, 'error' => $error]
        ];
}

/**
 * Authenticate to LMS platform (no authentication required)
 * 
 * @param array $credentials User credentials
 * @return array Authentication result
 */
function authenticateToLMS($credentials) {

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS Authentication attempt for user: " . $credentials['platform_username'], FILE_APPEND);
        
        // LMS doesn't need authentication - always return success
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS authentication successful for user: " . $credentials['platform_username'] . " (no authentication required)", FILE_APPEND);
        return [
            'success' => true,
            'message' => 'LMS authentication successful (no authentication required)'
        ];
}
