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
function handleProxyResource() {

        $platform = $_GET['platform'] ?? '';
        $username = $_GET['username'] ?? '';
        $resourcePath = $_GET['resource'] ?? '';
        $subplatform = $_GET['subplatform'] ?? '';
        
        if (!$platform || !$username || !$resourcePath) {

                http_response_code(400);
                echo json_encode(['error' => 'Missing required parameters']);
                return;
            
    }
        
        // Decode the resource path
        $resourcePath = urldecode($resourcePath);
        
        // Clean up malformed paths
        $resourcePath = preg_replace('/^\.\//', '', $resourcePath); // Remove leading ./
        $resourcePath = preg_replace('/^\/+/', '', $resourcePath);  // Remove leading slashes
        $resourcePath = trim($resourcePath);
        
        // Skip if resource path is empty or invalid
        if (empty($resourcePath) || $resourcePath === '.' || $resourcePath === '/') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid resource path']);
            return;
        }
        
        // Check if this is actually a PHP page that should be handled as a page request, not a resource
        if (preg_match('/\.php$/i', $resourcePath)) {
            // This is a PHP file, redirect to appropriate page handler
            if ($platform === 'leave') {
                $_GET['endpoint'] = 'leave_portal_proxy';
                $_GET['username'] = $username;
                $_GET['page'] = $resourcePath;
                handleLeavePortalDashboardProxy();
                return;
            } elseif ($platform === 'rms') {
                $_GET['endpoint'] = 'rms_dashboard_proxy';
                $_GET['username'] = $username;
                $_GET['page'] = $resourcePath;
                handleRmsDashboardProxy();
                return;
            }
        }
        
        // Debug logging
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Proxy resource request: platform=$platform, resource=$resourcePath\n", FILE_APPEND);
        
        // Platform base URLs
        $baseUrls = [
            'rms' => 'https://rms.final.digital',
            'leave' => 'https://leave.final.digital',
            'lms' => '' // Will be determined by subplatform
        ];
        
        if ($platform === 'lms') {

                $subplatform = $_GET['subplatform'] ?? '';
                $subplatformData = getLmsSubplatformByIdentifier($subplatform);
                if (!$subplatformData) {

                        http_response_code(404);
                        echo json_encode(['error' => 'LMS subplatform not found']);
                        return;
                    
        }
                $baseUrl = rtrim($subplatformData['url'], '/');
            
    }
    else {

                $baseUrl = $baseUrls[$platform] ?? '';
            
    }
        
        if (!$baseUrl) {

                http_response_code(400);
                echo json_encode(['error' => 'Invalid platform']);
                return;
            
    }
        
        // Construct full resource URL
        $resourceUrl = $baseUrl . '/' . ltrim($resourcePath, '/');
        
        // Debug logging for resource URL construction
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Constructed resource URL: $resourceUrl\n", FILE_APPEND);
        
        // Get credentials for authentication
        $platformName = $platform === 'leave' ? 'Leave and Absence' : strtoupper($platform);
        $credentials = get_platform_credentials($username, $platformName);
        
        if (!$credentials) {

                http_response_code(401);
                echo json_encode(['error' => 'No credentials found']);
                return;
            
    }
        
        // Fetch the resource with authentication
        $cookieFileName = $credentials['platform_username'] . '_' . ($platform === 'leave' ? 'LeavePortal' : ucfirst($platform)) . '.txt';
        $cookieFile = __DIR__ . '/../cookies/' . $cookieFileName;
        
        $ch = curl_init($resourceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache'
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode !== 200) {

                http_response_code($httpCode);
                echo $content;
                return;
            
    }
        
        // Set appropriate content type
        if ($contentType) {

                header('Content-Type: ' . $contentType);
            
    }
        
        // Cache headers for static resources
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/i', $resourcePath)) {

                header('Cache-Control: public, max-age=3600');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
            
    }
        
        // Special handling for CSS files to rewrite font URLs
        if ($platform === 'lms' && (stripos($contentType, 'text/css') !== false || strpos($resourcePath, 'styles.php') !== false)) {
            
            // Build font proxy URL
            $fontProxyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?endpoint=lms_font_proxy&username=' . urlencode($username);
            
            if ($subplatform) {
                $fontProxyUrl .= '&subplatform=' . urlencode($subplatform);
            }
            
            $fontProxyUrl .= '&fontpath=';
            
            // Rewrite CSS url() paths for fonts within the CSS file content
            $content = preg_replace_callback(
                '/url\(["\']?([^"\']*(?:\.(woff2?|ttf|eot|otf)|font\.php)[^"\']*)["\']?\)/i',
                function($matches) use ($fontProxyUrl, $resourcePath) {
                    $fontUrl = trim($matches[1], '"\'');
                    
                    // Don't rewrite if already contains our proxy endpoint
                    if (strpos($fontUrl, 'endpoint=lms_font_proxy') !== false) {
                        return $matches[0];
                    }
                    
                    // Handle relative font.php URLs
                    if (strpos($fontUrl, 'font.php') !== false && strpos($fontUrl, 'http') !== 0) {
                        if (strpos($fontUrl, '/LMS/') === 0) {
                            // URL starts with /LMS/, extract everything after /LMS/
                            $fontPath = substr($fontUrl, 5);
                        } else {
                            // Other relative font.php URLs
                            $fontPath = ltrim($fontUrl, '/');
                        }
                        $encodedFontPath = urlencode($fontPath);
                        $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath;
                        return 'url("' . $finalFontProxyUrl . '")';
                    }
                    
                    return $matches[0];
                },
                $content
            );
        }
        
        echo $content;
}

/**
 * Enhanced URL rewriting for perfect proxy display
 */
function rewriteUrlsForProxy($content, $platform, $username, $baseUrl, $proxyBaseUrl, $subplatform = null) {
    // DEBUG: Log function call
    error_log("[DEBUG] rewriteUrlsForProxy called: platform=$platform, subplatform=$subplatform, content_length=" . strlen($content));
    error_log("[DEBUG] baseUrl=$baseUrl, proxyBaseUrl=$proxyBaseUrl");
    
    // Trim any leading whitespace, BOM, or other content that might interfere with DOCTYPE
    $content = ltrim($content, "\x00\x0B\xEF\xBB\xBF \t\n\r");
    
    // Remove any XML declarations or other content before DOCTYPE
    $content = preg_replace('/^<\?xml[^>]*\?>\s*/i', '', $content);
    
    // Fix DOCTYPE to prevent KaTeX quirks mode warning
    if (!preg_match('/<!DOCTYPE\s+html/i', $content)) {
        // Check if there's any DOCTYPE and replace it, or add if none exists
        if (preg_match('/<!DOCTYPE[^>]*>/i', $content)) {
            // Replace any existing DOCTYPE with HTML5 DOCTYPE
            $content = preg_replace('/<!DOCTYPE[^>]*>/i', '<!DOCTYPE html>', $content);
        } else {
            // Add HTML5 DOCTYPE at the very beginning
            $content = "<!DOCTYPE html>\n" . $content;
        }
    }
    
    // Ensure the HTML tag follows immediately after DOCTYPE (no comments or whitespace)
    $content = preg_replace('/^(<!DOCTYPE html>)\s*<!--.*?-->\s*(<html)/is', '$1$2', $content);
    
    // Ensure proper meta charset is present as first meta tag
    if (!preg_match('/<meta[^>]*charset[^>]*>/i', $content)) {
        $content = preg_replace('/(<head[^>]*>)/i', '$1' . "\n" . '<meta charset="UTF-8">', $content);
    } else {
        // Move charset meta to be first in head
        $content = preg_replace('/(<head[^>]*>)(.*?)(<meta[^>]*charset[^>]*>)(.*?)/is', '$1$3$2$4', $content);
    }

    // Ensure proper viewport meta tag for responsive rendering
    if (!preg_match('/<meta[^>]*name=["\']viewport["\'][^>]*>/i', $content)) {
        $content = preg_replace('/(<meta charset[^>]*>)/i', '$1' . "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1.0">', $content);
    }

    // Protect script and style blocks from URL rewriting
    $protectedBlocks = [];
    $blockId = 0;
        
        // Protect JavaScript blocks
        $content = preg_replace_callback('/<script[^>]*>(.*?)<\/script>/is', function($matches) use (&$protectedBlocks, &$blockId) {

                $id = "PROTECTED_SCRIPT_BLOCK_" . $blockId++;
                $protectedBlocks[$id] = $matches[0];
                return $id;
            
    }
    , $content);
        
        // Protect CSS blocks
        $content = preg_replace_callback('/<style[^>]*>(.*?)<\/style>/is', function($matches) use (&$protectedBlocks, &$blockId) {

                $id = "PROTECTED_STYLE_BLOCK_" . $blockId++;
                $protectedBlocks[$id] = $matches[0];
                return $id;
            
    }
    , $content);
        
        // Simplified URL patterns for rewriting - only essential resources
        $patterns = [
            // Only rewrite specific resource types that we know need proxying
            // Static resources in attributes (src, href)
            '/((?:src|href)=["\'])(\\.?\\/?)([^"\'\s>)]+\.(?:css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf))/i',
            // Font resources specifically
            '/((?:src|href)=["\'])(\\.?\\/?)([^"\'\s>)]*font\.php[^"\'\s>)]*)/i',
            // Image resources specifically
            '/((?:src|href)=["\'])(\\.?\\/?)([^"\'\s>)]*image\.php[^"\'\s>)]*)/i',
            // CSS url() references for static resources
            '/url\(["\']?(\\.?\\/?)([^"\'()]+\.(?:css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf))["\']?\)/i',
            // CSS url() references for fonts
            '/url\(["\']?(\\.?\\/?)([^"\'()]*font\.php[^"\'()]*)["\']?\)/i',
            // Moodle resource files that need proxying (styles, yui_combo, javascript) - relative URLs
            '/((?:src|href)=["\'])(\\.?\\/?)([^"\'\s>)]*(?:styles|yui_combo|javascript)\.php[^"\'\s>)]*)(["\'])/i',
            // Moodle resource files that need proxying (styles, yui_combo, javascript) - absolute URLs
            '/((?:src|href)=["\'])(' . preg_quote($baseUrl, '/') . ')([^"\'\s>)]*(?:styles|yui_combo|javascript)\.php[^"\'\s>)]*)(["\'])/i',
            // JSON-escaped JavaScript URLs (for YUI config and RequireJS paths)
            '/(["\'`])(' . preg_quote($baseUrl, '/') . ')(\\/[^"\'`]*(?:styles|yui_combo|javascript)\.php[^"\'`]*)(["\'`])/i'
        ];
        
        // Separate patterns for PHP pages (these should use the page proxy, not resource proxy)
        // Exclude dynamic resource PHP files like font.php, image.php, file.php (but allow yui_combo.php, javascript.php, styles.php to be handled as resources)
        $pagePatterns = [
            // Absolute URLs to PHP pages from the target host (excluding resource PHP files)
            '/(' . preg_quote($baseUrl, '/') . ')([^"\'\s>)]*\.php(?!\/[^"\'\s>)]*\.(?:woff|woff2|ttf|png|jpg|jpeg|gif|svg|ico))(?!.*(?:font|image|file)\.php)[^"\'\s>)]*)/i',
            // Protocol-relative URLs to PHP pages (excluding resource PHP files) 
            '/(\/\/' . preg_quote(parse_url($baseUrl, PHP_URL_HOST) . parse_url($baseUrl, PHP_URL_PATH), '/') . ')([^"\'\s>)]*\.php(?!\/[^"\'\s>)]*\.(?:woff|woff2|ttf|png|jpg|jpeg|gif|svg|ico))(?!.*(?:font|image|file)\.php)[^"\'\s>)]*)/i',
            // Action attributes pointing to PHP files (excluding resource PHP files)
            '/(action=["\'])(\\.?\\/?)([^"\'\s>)]+\.php(?!\/[^"\'\s>)]*\.(?:woff|woff2|ttf|png|jpg|jpeg|gif|svg|ico))(?!.*(?:font|image|file)\.php))/i',
            // Links to PHP pages (excluding resource PHP files)
            '/(href=["\'])(\\.?\\/?)([^"\'\s>)]+\.php(?!\/[^"\'\s>)]*\.(?:woff|woff2|ttf|png|jpg|jpeg|gif|svg|ico))(?!.*(?:font|image|file)\.php))/i'
        ];
        
        // Process static resource patterns
        foreach ($patterns as $patternIndex => $pattern) {
            error_log("[DEBUG] Testing pattern $patternIndex: $pattern");
            $content = preg_replace_callback($pattern, function($matches) use ($platform, $username, $proxyBaseUrl, $subplatform, $baseUrl, $patternIndex) {
                error_log("[DEBUG] Pattern $patternIndex matched: " . json_encode($matches));
                // Handle different pattern types based on match count and content
                $resourcePath = '';
                $prefix = '';
                $suffix = '';
                
                // Determine pattern type and extract components
                if (count($matches) == 5 && ($matches[1] === '"' || $matches[1] === "'" || $matches[1] === '`')) {
                    // JSON-escaped URL pattern: ["']protocol://host[/path]["']
                    $prefix = $matches[1];
                    $resourcePath = $matches[3]; // Just the path part
                    $suffix = $matches[4];
                } elseif (count($matches) == 5 && strpos($matches[1], '=') !== false) {
                    // Absolute URL attribute pattern: src="protocol://host/path"
                    $prefix = $matches[1];
                    $resourcePath = $matches[3]; // Path part only
                    $suffix = $matches[4];
                } elseif (count($matches) == 5) {
                    // Relative URL attribute pattern with closing quote: src="./path"
                    $prefix = $matches[1];
                    $relativePart = $matches[2]; // ./ or / part
                    $resourcePath = $relativePart . $matches[3];
                    $suffix = $matches[4];
                } elseif (count($matches) == 4 && strpos($matches[1], '=') !== false) {
                    // Absolute URL attribute pattern without closing quote: src="protocol://host/path"
                    $prefix = $matches[1];
                    $resourcePath = $matches[3]; // Path part only
                    $suffix = '"';
                } elseif (count($matches) >= 4) {
                    // Relative URL attribute pattern: src="./path" or href="/path"
                    $prefix = $matches[1];
                    $relativePart = $matches[2]; // ./ or / part
                    $resourcePath = $relativePart . $matches[3];
                    $suffix = '';
                } elseif (count($matches) >= 3) {
                    if (strpos($matches[0], 'url(') === 0) {
                        // CSS url() pattern
                        $prefix = 'url("';
                        $resourcePath = $matches[1] . $matches[2];
                        $suffix = '")';
                    } else {
                        // Other patterns with prefix
                        $prefix = $matches[1];
                        $resourcePath = $matches[2];
                        $suffix = '';
                    }
                } else {
                    // Simple pattern
                    $resourcePath = $matches[1] ?? $matches[0];
                }
                
                // Clean up resource path
                $resourcePath = ltrim($resourcePath, './');
                
                // Skip external URLs and data URIs
                if (preg_match('/^(https?:\/\/|data:|#|javascript:|mailto:)/', $resourcePath)) {
                    return $matches[0];
                }
                
                // Skip if resource path is empty or just a dot
                if (empty($resourcePath) || $resourcePath === '.' || $resourcePath === './') {
                    return $matches[0];
                }
                
                // Build proxy URL for resource - create from base script path to avoid parameter duplication
                $scriptPath = parse_url($proxyBaseUrl, PHP_URL_SCHEME) . '://' . parse_url($proxyBaseUrl, PHP_URL_HOST) . parse_url($proxyBaseUrl, PHP_URL_PATH);
                
                // Special handling for font.php URLs - route to lms_font_proxy instead of proxy_resource
                if ($platform === 'lms' && strpos($resourcePath, 'font.php') !== false) {
                    $proxyUrl = $scriptPath . '?endpoint=lms_font_proxy&username=' . urlencode($username) . '&subplatform=' . urlencode($subplatform) . '&fontpath=' . urlencode($resourcePath);
                // Special handling for image.php URLs - route to lms_image_proxy instead of proxy_resource
                } elseif ($platform === 'lms' && strpos($resourcePath, 'image.php') !== false) {
                    $proxyUrl = $scriptPath . '?endpoint=lms_image_proxy&username=' . urlencode($username) . '&subplatform=' . urlencode($subplatform) . '&imagepath=' . urlencode($resourcePath);
                // Special handling for JavaScript and YUI combo files - route to lms_subplatform_proxy
                } elseif ($platform === 'lms' && (strpos($resourcePath, 'yui_combo.php') !== false || strpos($resourcePath, 'styles.php') !== false || strpos($resourcePath, 'javascript.php') !== false)) {
                    $proxyUrl = $scriptPath . '?endpoint=lms_subplatform_proxy&username=' . urlencode($username) . '&subplatform=' . urlencode($subplatform) . '&path=' . urlencode($resourcePath);
                } else {
                    $proxyUrl = $scriptPath . '?endpoint=proxy_resource&platform=' . $platform . '&username=' . urlencode($username) . '&resource=' . urlencode($resourcePath);
                    
                    if ($subplatform) {
                        $proxyUrl .= '&subplatform=' . urlencode($subplatform);
                    }
                }
                
                // Return the rewritten URL with proper format
                return $prefix . $proxyUrl . $suffix;
            }, $content);
        }
        
        // Process PHP page patterns (use page proxy instead of resource proxy)
        foreach ($pagePatterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) use ($platform, $username, $proxyBaseUrl, $subplatform, $baseUrl) {
                // Check if this is an absolute URL pattern
                if (preg_match('/^https?:\/\//', $matches[0]) || preg_match('/^\/\//', $matches[0])) {
                    // Absolute URL pattern: matches[1] = protocol+host, matches[2] = full path
                    $fullPath = $matches[2];
                    
                    // Extract the base path from the baseUrl to remove it from the resource path
                    $basePath = parse_url($baseUrl, PHP_URL_PATH);
                    if ($basePath && $basePath !== '/' && strpos($fullPath, $basePath) === 0) {
                        // Remove the base path from the full path
                        $pagePath = substr($fullPath, strlen($basePath));
                    } else {
                        $pagePath = ltrim($fullPath, '/');
                    }
                    
                    $prefix = '';
                } else {
                    // Attribute pattern
                    $prefix = $matches[1];
                    $relativePart = $matches[2]; // ./ or / part
                    $pagePath = $relativePart . $matches[3];
                }
                
                // Clean up page path
                $pagePath = ltrim($pagePath, './');
                
                // Skip external URLs
                if (preg_match('/^(https?:\/\/|data:|#|javascript:|mailto:)/', $pagePath)) {
                    return $matches[0];
                }
                
                // Build proxy URL for PHP page - create from base script path to avoid parameter duplication
                $scriptPath = parse_url($proxyBaseUrl, PHP_URL_SCHEME) . '://' . parse_url($proxyBaseUrl, PHP_URL_HOST) . parse_url($proxyBaseUrl, PHP_URL_PATH);
                
                // Choose correct endpoint based on platform
                if ($platform === 'leave') {
                    $endpointName = 'leave_portal_proxy';
                } elseif ($platform === 'lms') {
                    $endpointName = 'lms_subplatform_proxy';  // All LMS resources go through subplatform proxy
                } else {
                    $endpointName = $platform . '_dashboard_proxy';
                }
                
                $proxyUrl = $scriptPath . '?endpoint=' . $endpointName . '&username=' . urlencode($username) . '&page=' . urlencode($pagePath);
                
                if ($subplatform) {
                    $proxyUrl .= '&subplatform=' . urlencode($subplatform);
                }
                
                return $prefix . $proxyUrl;
            }, $content);
        }
        
        // Restore protected blocks
        foreach ($protectedBlocks as $id => $block) {

                $content = str_replace($id, $block, $content);
            
    }
        
        return $content;
}

/**
 * Inject proxy-aware JavaScript for perfect navigation
 */
function injectProxyJavaScript($content, $platform, $username, $proxyBaseUrl, $subplatform = null) {
    $cacheVersion = time(); // Cache busting timestamp
    $script = '<script type="text/javascript">
/* Proxy JavaScript v' . $cacheVersion . ' */
(function() {
    // Ensure standards mode for KaTeX compatibility
    if (document.compatMode !== "CSS1Compat") {
        console.warn("Document not in standards mode - KaTeX may not work properly");
        // Try to force standards mode by ensuring proper DOCTYPE
        if (!document.doctype || document.doctype.name !== "html") {
            console.warn("Missing or invalid DOCTYPE - adding HTML5 DOCTYPE");
        }
    }
    
    // Proxy configuration
    window.PROXY_CONFIG = {
        platform: "' . $platform . '",
        username: "' . addslashes($username) . '",
        proxyBaseUrl: "' . $proxyBaseUrl . '",
        subplatform: "' . ($subplatform ? addslashes($subplatform) : '') . '"
    };
    
    // Override form submissions to maintain proxy context
    document.addEventListener("DOMContentLoaded", function() {
        // Handle all forms
        document.querySelectorAll("form").forEach(function(form) {
            if (!form.hasAttribute("data-proxy-handled")) {
                form.setAttribute("data-proxy-handled", "true");
                
                // Add hidden inputs for proxy context
                if (!form.querySelector("input[name=\'proxy_platform\']")) {
                    var platformInput = document.createElement("input");
                    platformInput.type = "hidden";
                    platformInput.name = "proxy_platform";
                    platformInput.value = window.PROXY_CONFIG.platform;
                    form.appendChild(platformInput);
                    
                    var usernameInput = document.createElement("input");
                    usernameInput.type = "hidden";
                    usernameInput.name = "proxy_username";
                    usernameInput.value = window.PROXY_CONFIG.username;
                    form.appendChild(usernameInput);
                    
                    if (window.PROXY_CONFIG.subplatform) {
                        var subplatformInput = document.createElement("input");
                        subplatformInput.type = "hidden";
                        subplatformInput.name = "proxy_subplatform";
                        subplatformInput.value = window.PROXY_CONFIG.subplatform;
                        form.appendChild(subplatformInput);
                    }
                }
            }
        });
        
        // Extract and store session key for AJAX requests
        window.PROXY_CONFIG.sesskey = null;
        try {
            // Try to extract from M.cfg first (most reliable)
            if (window.M && window.M.cfg && window.M.cfg.sesskey) {
                window.PROXY_CONFIG.sesskey = window.M.cfg.sesskey;
            } else {
                // Fallback: extract from hidden input or logout links
                var sestkeyInput = document.querySelector("input[name=\'sesskey\']");
                if (sestkeyInput) {
                    window.PROXY_CONFIG.sesskey = sestkeyInput.value;
                } else {
                    // Extract from logout link as last resort
                    var logoutLink = document.querySelector("a[href*=\'sesskey=\']");
                    if (logoutLink) {
                        var match = logoutLink.href.match(/sesskey=([^&]+)/);
                        if (match) {
                            window.PROXY_CONFIG.sesskey = match[1];
                        }
                    }
                }
            }
            console.log("Extracted sesskey:", window.PROXY_CONFIG.sesskey);
        } catch (e) {
            console.warn("Failed to extract sesskey:", e);
        }
        
        // Handle dynamic content loading (AJAX)
        if (window.XMLHttpRequest) {
            var originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                // Rewrite AJAX URLs to go through proxy
                if (url && !url.match(/^(https?:|data:|#|javascript:|mailto:)/)) {
                    var endpointName;
                    // Special handling for different types of requests
                    if (window.PROXY_CONFIG.platform === "leave") {
                        endpointName = "leave_portal_proxy";
                    } else if (window.PROXY_CONFIG.platform === "lms") {
                        // For LMS, route different request types appropriately
                        if (url.indexOf(\'/lib/ajax/service.php\') !== -1) {
                            endpointName = "lms_ajax_proxy";
                        } else {
                            // All other LMS requests (CSS, images, JS) go through subplatform proxy
                            endpointName = "lms_subplatform_proxy";
                        }
                        console.log("LMS XHR routing:", url, "->", endpointName);
                    } else {
                        endpointName = window.PROXY_CONFIG.platform + "_dashboard_proxy";
                    }
                    
                    var proxyUrl = window.PROXY_CONFIG.proxyBaseUrl + "&endpoint=" + endpointName + "&username=" + encodeURIComponent(window.PROXY_CONFIG.username);
                    
                    if (endpointName === "lms_ajax_proxy") {
                        proxyUrl += "&apipath=" + encodeURIComponent(url);
                        // Add sesskey for AJAX requests if available
                        if (window.PROXY_CONFIG.sesskey) {
                            proxyUrl += "&sesskey=" + encodeURIComponent(window.PROXY_CONFIG.sesskey);
                        }
                    } else {
                        proxyUrl += "&page=" + encodeURIComponent(url);
                    }
                    
                    if (window.PROXY_CONFIG.subplatform) {
                        proxyUrl += "&subplatform=" + encodeURIComponent(window.PROXY_CONFIG.subplatform);
                    }
                    url = proxyUrl;
                    console.log("XMLHttpRequest proxied:", url);
                }
                return originalOpen.call(this, method, url, async, user, password);
            };
        }
        
        // Handle fetch API
        if (window.fetch) {
            var originalFetch = window.fetch;
            window.fetch = function(input, init) {
                if (typeof input === "string" && !input.match(/^(https?:|data:|#|javascript:|mailto:)/)) {
                    var endpointName;
                    // Special handling for different types of requests
                    if (window.PROXY_CONFIG.platform === "leave") {
                        endpointName = "leave_portal_proxy";
                    } else if (window.PROXY_CONFIG.platform === "lms") {
                        // For LMS, route different request types appropriately
                        if (input.indexOf(\'/lib/ajax/service.php\') !== -1) {
                            endpointName = "lms_ajax_proxy";
                        } else {
                            // All other LMS requests (CSS, images, JS) go through subplatform proxy
                            endpointName = "lms_subplatform_proxy";
                        }
                        console.log("LMS Fetch routing:", input, "->", endpointName);
                    } else {
                        endpointName = window.PROXY_CONFIG.platform + "_dashboard_proxy";
                    }
                    
                    var proxyUrl = window.PROXY_CONFIG.proxyBaseUrl + "&endpoint=" + endpointName + "&username=" + encodeURIComponent(window.PROXY_CONFIG.username);
                    
                    if (endpointName === "lms_ajax_proxy") {
                        proxyUrl += "&apipath=" + encodeURIComponent(input);
                        // Add sesskey for AJAX requests if available
                        if (window.PROXY_CONFIG.sesskey) {
                            proxyUrl += "&sesskey=" + encodeURIComponent(window.PROXY_CONFIG.sesskey);
                        }
                    } else {
                        proxyUrl += "&page=" + encodeURIComponent(input);
                    }
                    
                    if (window.PROXY_CONFIG.subplatform) {
                        proxyUrl += "&subplatform=" + encodeURIComponent(window.PROXY_CONFIG.subplatform);
                    }
                    input = proxyUrl;
                    console.log("Fetch API proxied:", input);
                }
                return originalFetch.call(this, input, init);
            };
        }
        
        // KaTeX compatibility checks
        if (window.katex || document.querySelector("script[src*=\'katex\']") || document.querySelector("link[href*=\'katex\']")) {
            if (document.compatMode !== "CSS1Compat") {
                console.error("KaTeX detected but document is in quirks mode. Math rendering may fail.");
                // Attempt to notify user
                setTimeout(function() {
                    if (window.console && console.warn) {
                        console.warn("PROXY WARNING: Page may have DOCTYPE issues affecting KaTeX math rendering.");
                    }
                }, 1000);
            } else {
                console.log("KaTeX compatibility: Document in standards mode ✓");
            }
        }
        
        // Monitor for dynamically loaded KaTeX
        if (window.MutationObserver) {
            var katexObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === "childList") {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                if (node.tagName === "SCRIPT" && node.src && node.src.includes("katex")) {
                                    if (document.compatMode !== "CSS1Compat") {
                                        console.error("KaTeX script loaded but document in quirks mode!");
                                    }
                                }
                            }
                        });
                    }
                });
            });
            katexObserver.observe(document.head || document.documentElement, {
                childList: true,
                subtree: true
            });
        }
    });
})();
</script>';
        
        // Inject before closing head tag, or before closing body tag as fallback
        if (strpos($content, '</head>') !== false) {

                $content = str_replace('</head>', $script . '</head>', $content);
            
    }
    elseif (strpos($content, '</body>') !== false) {

                $content = str_replace('</body>', $script . '</body>', $content);
            
    }
    else {

                $content .= $script;
            
    }
        
        return $content;
}

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
                case 'rms_dashboard_proxy':
                    handleRmsDashboardProxy();
                    break;
                case 'rms_notifications_proxy':
                    handleRmsNotificationsProxy();
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

        // Get user credentials - use "LMS" as the platform name since that's how credentials are stored
        $credentials = get_platform_credentials($username, 'LMS');
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
            'username' => $credentials['platform_username'],
            'password' => $credentials['platform_password']
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

                // User authenticated successfully - authenticate to all platforms
                $platformResults = [];
                $platforms = get_platforms();
                
                foreach ($platforms as $platform) {

                        $platformName = $platform['name'];
                        $platformResult = [
                            'platform' => $platformName,
                            'authenticated' => false,
                            'message' => 'No credentials found'
                        ];
                        // Get stored credentials for this platform
                        $credentials = get_platform_credentials($user['username'], $platformName);
                        if ($credentials) {

                                // Try to authenticate to the platform
                                $authResult = authenticateToPlatform($platform, $credentials);
                                $platformResult['authenticated'] = $authResult['success'];
                                $platformResult['message'] = $authResult['message'];
                                $platformResult['details'] = $authResult['details'] ?? null;
                            
            }
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
                        'email' => $user['email']
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
                default:
                    // For generic platforms, pass username, password, platformName, platform
                    $username = isset($credentials['platform_username']) ? $credentials['platform_username'] : '';
                    $password = isset($credentials['platform_password']) ? $credentials['platform_password'] : '';
                    return authenticateToGenericPlatform($username, $password, $platformName, $platform);
            
    }
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
 * Authenticate to Leave Portal
 * 
 * @param array $credentials User credentials
 * @return array Authentication result
 */
function authenticateToLeavePortal($credentials) {

        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
        $logFile = __DIR__ . '/php_errors.log';
        
        // Create cookies directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../cookies')) {

                mkdir(__DIR__ . '/../cookies', 0755, true);
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Created cookies directory.", FILE_APPEND);
            
    }
        
        // Step 1: Get Leave Portal login page to establish session and extract CSRF token
        $loginUrl = 'https://leave.final.digital/index.php';
        $ch_login = curl_init($loginUrl);
        curl_setopt($ch_login, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_login, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_login, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch_login, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch_login, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_login, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch_login, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $loginResponse = curl_exec($ch_login);
        $loginHttpCode = curl_getinfo($ch_login, CURLINFO_HTTP_CODE);
        curl_close($ch_login);
        
        if ($loginHttpCode !== 200) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal login page access failed with HTTP code: " . $loginHttpCode, FILE_APPEND);
                return ['success' => false, 'message' => 'Failed to access Leave Portal login page'];
            
    }
        
        // Extract CSRF token from the login page - try multiple patterns
        $csrfToken = '';
        $csrfPatterns = [
            '/name="csrf_token" value="([^"]+)"/',
            '/name="_token" value="([^"]+)"/',
            '/name="token" value="([^"]+)"/',
            '/<input[^>]*name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)["\']/',
            '/<input[^>]*value=["\']([^"\']+)["\'][^>]*name=["\']csrf_token["\']/',
            '/csrf[_-]?token["\']?\s*:\s*["\']([^"\']+)["\']/',
            '/meta\s+name=["\']csrf-token["\'][^>]*content=["\']([^"\']+)["\']/'
        ];
        
        foreach ($csrfPatterns as $pattern) {

                if (preg_match($pattern, $loginResponse, $matches)) {

                        $csrfToken = $matches[1];
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Found CSRF token using pattern: " . $pattern, FILE_APPEND);
                        break;
                    
        }
            
    }
        
        if (empty($csrfToken)) {

                // Log a sample of the login page to help debug
                $sample = substr($loginResponse, 0, 1000);
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Could not extract CSRF token. Login page sample: " . $sample, FILE_APPEND);
                
                // Try without CSRF token (some forms might not require it)
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Attempting login without CSRF token", FILE_APPEND);
            
    }
    else {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] CSRF token extracted: " . substr($csrfToken, 0, 10) . "...", FILE_APPEND);
            
    }
        
        // Step 2: Submit login form with or without CSRF token
        $authUrl = 'https://leave.final.digital/index.php';
        $loginData = [
            'username' => $credentials['platform_username'],
            'password' => $credentials['platform_password']
        ];
        
        // Add CSRF token if we found one
        if (!empty($csrfToken)) {

                $loginData['csrf_token'] = $csrfToken;
            
    }
        
        $ch_auth = curl_init($authUrl);
        curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_auth, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_auth, CURLOPT_POST, true);
        curl_setopt($ch_auth, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($ch_auth, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch_auth, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch_auth, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_auth, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch_auth, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Referer: ' . $authUrl
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $authResponse = curl_exec($ch_auth);
        $authHttpCode = curl_getinfo($ch_auth, CURLINFO_HTTP_CODE);
        $authFinalUrl = curl_getinfo($ch_auth, CURLINFO_EFFECTIVE_URL);
        curl_close($ch_auth);
        
        // Log authentication attempt
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal Authentication attempt for user: " . $credentials['platform_username'] . ", HTTP Code: $authHttpCode, Final URL: $authFinalUrl", FILE_APPEND);
        
        // Check if authentication succeeded
        $hasDashboard = strpos($authResponse, 'dashboard') !== false || strpos($authResponse, 'Dashboard') !== false || strpos($authResponse, 'adminDashboard') !== false;
        $hasLoginForm = strpos($authResponse, 'login') !== false || strpos($authResponse, 'Login') !== false;
        
        if ($authHttpCode === 200 && (!$hasLoginForm || $hasDashboard)) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal authentication successful for user: " . $credentials['platform_username'], FILE_APPEND);
                return [
                    'success' => true,
                    'message' => 'Leave Portal authentication successful',
                    'details' => ['cookie_file' => basename($cookieFile)]
                ];
            
    }
    else {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Leave Portal authentication FAILED for user: " . $credentials['platform_username'], FILE_APPEND);
                return [
                    'success' => false,
                    'message' => 'Leave Portal authentication failed',
                    'details' => ['http_code' => $authHttpCode]
                ];
            
    }
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
 * Handle RMS dashboard redirect requests (Simple redirect to real portal)
 */

function handleRmsDashboardProxy() {

        global $method;

        if (!in_array($method, ['GET', 'POST'])) {

                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            
    }

        $username = $_GET['username'] ?? '';
        $page = $_GET['page'] ?? 'Dashboard/home.php';

        if (!$username) {

                http_response_code(400);
                echo json_encode(['error' => 'Username is required']);
                return;
            
    }

        // Get RMS credentials and authenticate
        $credentials = get_platform_credentials($username, 'RMS');
        if (!$credentials) {

                http_response_code(401);
                echo json_encode(['error' => 'No RMS credentials found']);
                return;
            
    }

        $authResult = authenticateToRMS($credentials);
        if (!$authResult['success']) {

                http_response_code(401);
                echo json_encode(['error' => 'RMS authentication failed: ' . $authResult['message']]);
                return;
            
    }

        // Build RMS URL
        $baseUrl = 'https://rms.final.digital';
        $rmsUrl = $baseUrl . '/' . ltrim($page, '/');
        
        // Add query parameters from current request
        $queryParams = $_GET;
        unset($queryParams['endpoint'], $queryParams['username'], $queryParams['page']);
        if (!empty($queryParams)) {

                $rmsUrl .= (strpos($rmsUrl, '?') !== false ? '&' : '?') . http_build_query($queryParams);
            
    }

        // Fetch content with authentication
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        
        $ch = curl_init($rmsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Automatically handle compression (gzip, deflate)
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive'
        ]);

        if ($method === 'POST') {

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
            
    }

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode !== 200) {

                http_response_code($httpCode);
                echo $content;
                return;
            
    }

        // Enhanced URL rewriting for perfect display
        $proxyBaseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/api.php';
        $content = rewriteUrlsForProxy($content, 'rms', $username, $baseUrl, $proxyBaseUrl);
        
        // Inject proxy-aware JavaScript
        $content = injectProxyJavaScript($content, 'rms', $username, $proxyBaseUrl);

        // Set proper headers
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
}

/**
 * Enhanced Leave Portal dashboard proxy with perfect display
 */
function handleLeavePortalDashboardProxy() {

        $method = $_SERVER['REQUEST_METHOD']; // Get method locally instead of relying on global

        if (!in_array($method, ['GET', 'POST'])) {

                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            
    }

        $username = $_GET['username'] ?? '';
        $page = $_GET['page'] ?? 'Dashboard/adminDashboard.php';

        // Debug: Log all page requests
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Leave Portal page request: username=$username, page=$page, method=$method\n", FILE_APPEND);

        if (!$username) {

                http_response_code(400);
                echo json_encode(['error' => 'Username is required']);
                return;
            
    }

        // Check if this is a local API call that should be handled internally
        // This mapping ensures our APIs match the real Leave Portal endpoints
        $localApiEndpoints = [
            // Real notification endpoints from Leave Portal (exact matches)
            'notifications/get_notification_count.php' => 'get_notification_count',
            'notifications/toggle_all_notifications_read.php' => 'toggle_all_notifications_read',
            'notifications/clear_old_notifications.php' => 'clear_old_notifications',
            'notifications/toggle_notification_read.php' => 'toggle_notification_read',
            'notifications/all_notifications.php' => 'notifications',
            
            // Legacy notification endpoints for backward compatibility
            'get_notification_count.php' => 'get_notification_count',
            'get_notifications_dropdown.php' => 'get_notifications_dropdown',
            'mark_notification_read.php' => 'mark_notification_read',
            'toggle_notification_read.php' => 'toggle_notification_read',
            'toggle_all_notifications_read.php' => 'toggle_all_notifications_read',
            'clear_old_notifications.php' => 'clear_old_notifications',
            'update_notification_count.php' => 'update_notification_count',
            'delete_notification.php' => 'delete_notification',
            
            // Additional common Leave Portal APIs that might exist
            'notifications.php' => 'notifications',
            'all_notifications.php' => 'notifications',
            'fetch_notifications.php' => 'get_notifications_dropdown',
            'notification_details.php' => 'get_notifications_dropdown',
            'read_notification.php' => 'mark_notification_read',
            'unread_notification.php' => 'toggle_notification_read',
            'mark_all_read.php' => 'toggle_all_notifications_read',
            'clear_notifications.php' => 'clear_old_notifications',
            
            // User/Session Management APIs (if they exist in Leave Portal)
            'get_user_info.php' => 'get_user_info',
            'update_user_settings.php' => 'update_user_settings',
            'logout.php' => 'logout',
            
            // Leave Management APIs (common in leave portals)
            'submit_leave.php' => 'submit_leave',
            'get_leave_balance.php' => 'get_leave_balance',
            'get_leave_history.php' => 'get_leave_history',
            'cancel_leave.php' => 'cancel_leave',
            'approve_leave.php' => 'approve_leave',
            'reject_leave.php' => 'reject_leave',
            
            // Dashboard/Stats APIs
            'get_dashboard_stats.php' => 'get_dashboard_stats',
            'get_calendar_events.php' => 'get_calendar_events',
            'get_pending_approvals.php' => 'get_pending_approvals'
        ];

        if (isset($localApiEndpoints[$page])) {
            // This is a local API call, redirect to our internal handler
            $_GET['endpoint'] = $localApiEndpoints[$page];
            $_GET['username'] = $username;
            
            // Debug logging for API calls
            $logFile = __DIR__ . '/php_errors.log';
            $postData = file_get_contents('php://input');
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Local API call: $page -> {$localApiEndpoints[$page]}, Method: $method, POST data: " . substr($postData, 0, 200) . "\n", FILE_APPEND);
            
            // Include the request handling logic
            switch ($localApiEndpoints[$page]) {
                // Notification Management
                case 'get_notification_count':
                    handleGetNotificationCount();
                    return;
                case 'get_notifications_dropdown':
                    handleGetNotificationsDropdown();
                    return;
                case 'mark_notification_read':
                    handleMarkNotificationRead();
                    return;
                case 'toggle_notification_read':
                    handleToggleNotificationRead();
                    return;
                case 'toggle_all_notifications_read':
                    handleToggleAllNotificationsRead();
                    return;
                case 'clear_old_notifications':
                    handleClearOldNotifications();
                    return;
                case 'update_notification_count':
                    handleUpdateNotificationCount();
                    return;
                case 'delete_notification':
                    handleDeleteNotification();
                    return;
                case 'notifications':
                    // Handle general notifications page - might be HTML or JSON
                    if (isset($_GET['format']) && $_GET['format'] === 'json') {
                        handleGetNotificationsDropdown();
                        return;
                    } else {
                        // Let it pass through to the real server for HTML page
                        break;
                    }
                    
                // APIs that don't exist locally yet - could be implemented later
                case 'get_user_info':
                case 'update_user_settings':
                case 'logout':
                case 'submit_leave':
                case 'get_leave_balance':
                case 'get_leave_history':
                case 'cancel_leave':
                case 'approve_leave':
                case 'reject_leave':
                case 'get_dashboard_stats':
                case 'get_calendar_events':
                case 'get_pending_approvals':
                    // These APIs would be handled by the real Leave Portal
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] API '$page' mapped but not implemented locally - passing to remote server\n", FILE_APPEND);
                    break; // Let it pass through to remote server
                    
                default:
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Unknown API endpoint: $page\n", FILE_APPEND);
                    break; // Let unknown APIs pass through to remote server
            }
        }

        // Get Leave Portal credentials and authenticate
        $credentials = get_platform_credentials($username, 'Leave and Absence');
        if (!$credentials) {

                http_response_code(401);
                echo json_encode(['error' => 'No Leave Portal credentials found']);
                return;
            
    }

        $authResult = authenticateToLeavePortal($credentials);
        if (!$authResult['success']) {

                http_response_code(401);
                echo json_encode(['error' => 'Leave Portal authentication failed: ' . $authResult['message']]);
                return;
            
    }

        // Build Leave Portal URL
        $baseUrl = 'https://leave.final.digital';
        $leavePortalUrl = $baseUrl . '/' . ltrim($page, '/');
        
        // Add query parameters from current request
        $queryParams = $_GET;
        unset($queryParams['endpoint'], $queryParams['username'], $queryParams['page']);
        if (!empty($queryParams)) {

                $leavePortalUrl .= (strpos($leavePortalUrl, '?') !== false ? '&' : '?') . http_build_query($queryParams);
            
    }

        // Fetch content with authentication
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
        
        $ch = curl_init($leavePortalUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Enable automatic decompression
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive'
        ]);

        if ($method === 'POST') {

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
            
    }

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode !== 200) {

                http_response_code($httpCode);
                echo $content;
                return;
            
    }

        // Enhanced URL rewriting for perfect display
        $proxyBaseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/api.php';
        
        // Check if content is HTML before processing
        if (!$contentType || !preg_match('/text\/html|application\/xhtml/i', $contentType)) {
            // Not HTML content, pass through directly
            header('Content-Type: ' . ($contentType ?: 'text/plain'));
            echo $content;
            return;
        }

        // Ensure content is valid UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        // Debug: Log content start to check DOCTYPE issues
        $logFile = __DIR__ . '/php_errors.log';
        $contentStart = substr($content, 0, 500);
        $contentLength = strlen($content);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Leave Portal content length: $contentLength, start: " . bin2hex(substr($content, 0, 100)) . "\n", FILE_APPEND);
        
        $content = rewriteUrlsForProxy($content, 'leave', $username, $baseUrl, $proxyBaseUrl);
        
        // Debug: Log content start after rewriting
        $contentStartAfter = substr($content, 0, 200);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] After rewriting: " . $contentStartAfter . "\n", FILE_APPEND);
        
        // Inject proxy-aware JavaScript
        $content = injectProxyJavaScript($content, 'leave', $username, $proxyBaseUrl);

        // Set proper headers
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
}

/**
 * Handle RMS notifications proxy requests
 */
function handleRmsNotificationsProxy() {

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
        
        // Get RMS credentials
        $credentials = get_platform_credentials($username, 'RMS');
        if (!$credentials) {

                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'No RMS credentials found for this user',
                    'message' => 'No RMS credentials found for this user.'
                ]);
                return;
            
    }
        
        // Authenticate to RMS
        $authResult = authenticateToRMS($credentials);
        if (!$authResult['success']) {

                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to authenticate to RMS: ' . $authResult['message'],
                    'message' => 'Failed to authenticate to RMS: ' . $authResult['message']
                ]);
                return;
            
    }
        
        // Fetch RMS notifications page
        $notificationsUrl = 'https://rms.final.digital/Dashboard/notifications.php';
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_RMS.txt';
        
        $ch = curl_init($notificationsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {

                // Set headers for HTML response
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // Rewrite URLs to maintain session through proxy
                $proxyBase = 'http://localhost/LEAVE_RMS/database/api.php?endpoint=rms_dashboard_proxy&username=' . urlencode($username);
                $baseUrl = 'https://rms.final.digital';
                
                // Complete URL rewriting for RMS notifications
                // Step 1: Handle static resources (CSS, JS, images, fonts) - point directly to RMS
                $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'webp', 'mp4', 'webm'];
                $staticPattern = implode('|', $staticExtensions);
                
                // Fix relative paths for static resources
                $response = preg_replace('/(src|href)=["\']\/([^"\']*\.(' . $staticPattern . '))["\']/', '$1="' . $baseUrl . '/$2"', $response);
                $response = preg_replace('/(src|href)=["\']\.\.\/([^"\']*\.(' . $staticPattern . '))["\']/', '$1="' . $baseUrl . '/$2"', $response);
                $response = preg_replace('/(src|href)=["\']([^"\']*\.(' . $staticPattern . '))["\']/', '$1="' . $baseUrl . '/$2"', $response);
                
                // Fix absolute URLs for static resources
                $response = preg_replace('/(src|href)=["\']https:\/\/rms\.final\.digital\/([^"\']*\.(' . $staticPattern . '))["\']/', '$1="' . $baseUrl . '/$2"', $response);
                
                // Step 2: Handle navigation links - these should go through proxy
                // Fix absolute RMS URLs to go through proxy
                $response = preg_replace('/href=["\']https:\/\/rms\.final\.digital\/([^"\']*)["\']/', 'href="' . $proxyBase . '&page=$1"', $response);
                
                // Fix relative navigation links to go through proxy (but not external URLs)
                $response = preg_replace('/href=["\']\/([^"\']*)["\']/', 'href="' . $proxyBase . '&page=$1"', $response);
                $response = preg_replace('/href=["\']\.\.\/([^"\']*)["\']/', 'href="' . $proxyBase . '&page=../$1"', $response);
                
                // Handle form actions - these should go through proxy
                $response = preg_replace('/action=["\']https:\/\/rms\.final\.digital\/([^"\']*)["\']/', 'action="' . $proxyBase . '&page=$1"', $response);
                $response = preg_replace('/action=["\']\/([^"\']*)["\']/', 'action="' . $proxyBase . '&page=$1"', $response);
                
                // Step 3: Handle AJAX calls and API endpoints
                $response = preg_replace('/url:\s*["\']https:\/\/rms\.final\.digital\/([^"\']*)["\']/', 'url: "' . $proxyBase . '&page=$1"', $response);
                $response = preg_replace('/url:\s*["\']\/([^"\']*)["\']/', 'url: "' . $proxyBase . '&page=$1"', $response);
                
                // Step 4: Handle data attributes
                $response = preg_replace('/data-url=["\']https:\/\/rms\.final\.digital\/([^"\']*)["\']/', 'data-url="' . $proxyBase . '&page=$1"', $response);
                $response = preg_replace('/data-url=["\']\/([^"\']*)["\']/', 'data-url="' . $proxyBase . '&page=$1"', $response);
                
                // Step 5: Handle inline styles
                $response = preg_replace('/url\(["\']?https:\/\/rms\.final\.digital\/([^"\']*)["\']?\)/', 'url("' . $baseUrl . '/$1")', $response);
                $response = preg_replace('/url\(["\']?\/([^"\']*)["\']?\)/', 'url("' . $baseUrl . '/$1")', $response);
                
                // Step 6: Final cleanup - restore external URLs that were incorrectly rewritten
                $response = preg_replace('/href=["\']' . preg_quote($proxyBase, '/') . '&page=https:\/\/([^"\']*)["\']/', 'href="https://$1"', $response);
                $response = preg_replace('/src=["\']' . preg_quote($proxyBase, '/') . '&page=https:\/\/([^"\']*)["\']/', 'src="https://$1"', $response);
                $response = preg_replace('/action=["\']' . preg_quote($proxyBase, '/') . '&page=https:\/\/([^"\']*)["\']/', 'action="https://$1"', $response);
                
                // Step 7: Fix any remaining malformed URLs
                $response = preg_replace('/https:\/\/rms\.final\.digital\/https:\/\/rms\.final\.digital\//', 'https://rms.final.digital/', $response);
                $response = preg_replace('/https:\/\/rms\.final\.digital\/https:\/\/rms\.final\.digital\//', 'https://rms.final.digital/', $response);
                
                // Step 8: Fix JavaScript event handlers that interfere with Bootstrap dropdowns
                // Remove custom event listeners that prevent default behavior for dropdown buttons
                $response = preg_replace('/document\.getElementById\("notificationDropdown"\)\.addEventListener\("click",\s*function\(e\)\s*\{\s*e\.preventDefault\(\);\s*console\.log\("Notification button clicked"\);\s*\}\);/', '', $response);
                $response = preg_replace('/document\.getElementById\("userDropdown"\)\.addEventListener\("click",\s*function\(e\)\s*\{\s*e\.preventDefault\(\);\s*console\.log\("User button clicked"\);\s*\}\);/', '', $response);
                
                // Ensure Bootstrap dropdowns are properly initialized
                $response = str_replace(
                    '// Initialize Bootstrap dropdowns',
                    '// Initialize Bootstrap dropdowns - Fixed for proxy compatibility',
                    $response
                );
                
                // Add Bootstrap 4 dropdown initialization if not present
                if (!preg_match('/\$\(\[data-toggle="dropdown"\]\)\.dropdown\(\)/', $response)) {

                        $bootstrapInit = '
        <script>
        $(document).ready(function() {
            // Initialize Bootstrap 4 dropdowns
            $("[data-toggle=\'dropdown\']").dropdown();
        });
        </script>';
                        
                        // Insert after Bootstrap JS but before </body> tag
                        if (strpos($response, '</body>') !== false) {

                                $response = str_replace('</body>', $bootstrapInit . '</body>', $response);
                            
            }
            else {

                                // If no </body> tag, insert before </html>
                                $response = str_replace('</html>', $bootstrapInit . '</html>', $response);
                            
            }
                    
        }
                
                // Output the RMS notifications HTML
                echo $response;
            
    }
    else {

                http_response_code($httpCode);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to fetch RMS notifications',
                    'message' => 'An error occurred while fetching the RMS notifications.',
                    'status' => $httpCode
                ]);
            
    }
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
        
        $credentials = get_platform_credentials($username, $platformName);
        $notifications = [];
        
        if (!$credentials) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No credentials found for user: $username, platform: $platformName", FILE_APPEND);
                return $notifications;
            
    }
        
        // Log credentials found
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Credentials found for user: $username, platform: $platformName", FILE_APPEND);
        
        // Platform-specific notification fetching
        switch ($platformName) {

                case 'RMS':
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Using RMS special authentication", FILE_APPEND);
                return fetchNotificationsFromRMS($credentials, $url);
                case 'Leave and Absence':
                    file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Using Leave Portal special authentication", FILE_APPEND);
                    return fetchNotificationsFromLeavePortal($credentials, $url);
                case 'SIS':
                    file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Using SIS special authentication", FILE_APPEND);
                    return fetchNotificationsFromSIS($credentials, $url);
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
                                $shouldInclude = true;
                                
                                // Check if this notification has been marked as read or deleted
                                foreach ($actions as $actionKey => $actionData) {

                                        if ($actionData['username'] === $username && 
                                            $actionData['notification_id'] == $notificationId && 
                                            $actionData['platform'] === 'RMS') {

                                                
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
                                
                                if ($shouldInclude) {

                                        $filteredNotifications[] = $notification;
                                    
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
 * Fetch notifications from SIS platform
 * 
 * @param array $credentials User credentials
 * @param string $url Notifications URL
 * @return array Notifications array
 */
function fetchNotificationsFromSIS($credentials, $url) {

        $logFile = __DIR__ . '/php_errors.log';
        
            // Try to authenticate first
        $authResult = authenticateToSIS($credentials);
            if (!$authResult['success']) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS authentication failed, cannot fetch notifications", FILE_APPEND);
                    return [];
                
    }
        
        // SIS has no notifications - return empty array
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] SIS notifications: returning empty array (no notifications available)", FILE_APPEND);
        return [];
}

/**
 * Fetch notifications from LMS platform
 * 
 * @param array $credentials User credentials
 * @param string $url Notifications URL
 * @return array Notifications array
 */
function fetchNotificationsFromLMS($credentials, $url) {

        $logFile = __DIR__ . '/php_errors.log';
        
        // LMS notifications are handled by sub-platforms
        // This function is called for the main LMS platform, but notifications come from sub-platforms
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS notifications: using sub-platform notifications", FILE_APPEND);
        
        // Return empty array for main LMS platform - notifications come from sub-platforms
        return [];
}

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
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
 * @return bool Success status
 */
function recordNotificationAction($username, $notificationId, $platform, $action) {

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
        
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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



function handleLmsFontProxy() {

        global $method, $lms_subplatforms;

        $subplatformName = isset($_GET['subplatform']) ? $_GET['subplatform'] : '';
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $fontPath = isset($_GET['fontpath']) ? $_GET['fontpath'] : '';

        if (!$subplatformName || !$username || !$fontPath) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Sub-platform name, username, and fontpath are required',
                    'message' => 'You must provide a sub-platform name, username, and fontpath.'
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

        $baseUrl = rtrim($targetSubplatform['url'], '/');
        
        // Debug logging - Remove after testing
        $originalFontPath = $fontPath;
        
        // Remove leading LMS/ from fontPath if baseUrl already ends with /LMS  
        if (substr($baseUrl, -4) === '/LMS' && strpos($fontPath, 'LMS/') === 0) {

                $fontPath = substr($fontPath, 4);
        // Remove 'LMS/' from the beginning
                error_log("LMS Font URL Fix: '$originalFontPath' -> '$fontPath' for baseUrl: '$baseUrl'");
            
    }
        
        $targetUrl = (strpos($fontPath, 'http') === 0) ? $fontPath : $baseUrl . '/' . ltrim($fontPath, '/');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';
        
        // IMPROVED: Better error handling for missing session
        if (!file_exists($cookieFile)) {

                // Try to authenticate first
                $credentials = get_platform_credentials($username, 'LMS');
                if ($credentials) {

                        $logFile = __DIR__ . '/php_errors.log';
                        $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile);
                        if (!$authResult['success']) {

                                http_response_code(401);
                    /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                                echo json_encode([
                                    'error' => 'Failed to authenticate for font request',
                                    'message' => 'Failed to authenticate for font request.'
                                ]);
                                return;
                            
            }
                    
        }
        else {

                    http_response_code(401);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                    echo json_encode([
                        'error' => 'No session cookie for LMS sub-platform',
                        'message' => 'No session cookie for LMS sub-platform.'
                    ]);
                    return;
                    
        }
            
    }

        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'cURL Error: ' . $curlError,
                    'message' => 'An error occurred while fetching the font.'
                ]);
                return;
            
    }

        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // IMPROVED: Better error handling for missing fonts
        if ($httpCode !== 200) {

                // Try alternative font paths for common font files
                if (strpos($fontPath, 'fontawesome') !== false) {

                        $alternativePaths = [
                            '/theme/fonts/fontawesome-webfont.woff2',
                            '/lib/fonts/fontawesome-webfont.woff2',
                            '/theme/boost/fonts/fontawesome-webfont.woff2',
                            '/theme/font.php/boost/core/fontawesome-webfont.woff2'
                        ];
                        
                        foreach ($alternativePaths as $altPath) {

                                $altUrl = $baseUrl . $altPath;
                                $ch = curl_init($altUrl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                curl_setopt($ch, CURLOPT_HEADER, true);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                                
                    /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                                $altResponse = curl_exec($ch);
                                $altHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                                $altHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                curl_close($ch);
                                
                                if ($altHttpCode === 200) {

                                        // Found alternative font, serve it
                                        $altBody = substr($altResponse, $altHeaderSize);
                                        $altHeaders = substr($altResponse, 0, $altHeaderSize);
                                        
                                        // Extract content type from headers
                                        $contentType = 'application/octet-stream';
                                        if (preg_match('/^Content-Type:\s*([^\r\n]+)/mi', $altHeaders, $matches)) {

                                                $contentType = trim($matches[1]);
                                            
                    }
                                        
                                        header('Content-Type: ' . $contentType);
                                        header('Access-Control-Allow-Origin: *');
                                        header('Cache-Control: public, max-age=86400');
                                        http_response_code(200);
                                        echo $altBody;
                                        return;
                                    
                }
                            
            }
                    
        }
                
                http_response_code($httpCode);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Font not found (HTTP ' . $httpCode . ')',
                    'message' => 'The requested font could not be found.'
                ]);
                return;
            
    }

        if (preg_match('/^Content-Type:\s*([^\r\n]+)/mi', $headers, $matches)) {

                $contentType = trim($matches[1]);
                // If the content type indicates HTML instead of a font, it might be an error page
                if (strpos($contentType, 'text/html') !== false) {

                        // This is likely an error page, try to get the actual font
                        $actualFontUrl = $baseUrl . '/theme/font.php/boost/core/' . basename($fontPath);
                        $ch = curl_init($actualFontUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                        
                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                        $fontResponse = curl_exec($ch);
                        $fontHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $fontHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        if ($fontHttpCode === 200) {

                                $fontHeaders = substr($fontResponse, 0, $fontHeaderSize);
                                $fontBody = substr($fontResponse, $fontHeaderSize);
                                
                                if (preg_match('/^Content-Type:\s*([^\r\n]+)/mi', $fontHeaders, $fontMatches)) {

                                        header('Content-Type: ' . trim($fontMatches[1]));
                                    
                }
                else {

                                        header('Content-Type: application/octet-stream');
                                    
                }
                                header('Access-Control-Allow-Origin: *');
                                http_response_code(200);
                                echo $fontBody;
                                return;
                            
            }
                    
        }
                header('Content-Type: ' . $contentType);
            
    }
    else {

                header('Content-Type: application/octet-stream');
            
    }
        header('Access-Control-Allow-Origin: *');
        http_response_code($httpCode);
        echo $body;
}

/**
 * Proxy image requests to LMS sub-platforms (e.g., theme/image.php)
 * Usage: /database/api.php?endpoint=lms_image_proxy&subplatform=...&username=...&imagepath=...
 */
function handleLmsImageProxy() {
    global $method, $lms_subplatforms;

    $subplatformName = isset($_GET['subplatform']) ? $_GET['subplatform'] : '';
    $username = isset($_GET['username']) ? $_GET['username'] : '';
    $imagePath = isset($_GET['imagepath']) ? $_GET['imagepath'] : '';

    if (!$subplatformName || !$username || !$imagePath) {
        http_response_code(400);
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'error' => 'Sub-platform name, username, and imagepath are required',
            'message' => 'You must provide a sub-platform name, username, and imagepath.'
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

    $baseUrl = rtrim($targetSubplatform['url'], '/');
    
    // Debug logging - Remove after testing
    $originalImagePath = $imagePath;
    
    // Remove leading LMS/ from imagePath if baseUrl already ends with /LMS  
    if (substr($baseUrl, -4) === '/LMS' && strpos($imagePath, 'LMS/') === 0) {
        $imagePath = substr($imagePath, 4); // Remove 'LMS/' from the beginning
        error_log("LMS Image URL Fix: '$originalImagePath' -> '$imagePath' for baseUrl: '$baseUrl'");
    }
    
    $targetUrl = (strpos($imagePath, 'http') === 0) ? $imagePath : $baseUrl . '/' . ltrim($imagePath, '/');
    $host = parse_url($baseUrl, PHP_URL_HOST);
    $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';
    
    // IMPROVED: Better error handling for missing session
    if (!file_exists($cookieFile)) {
        // Try to authenticate first
        $credentials = get_platform_credentials($username, 'LMS');
        if ($credentials) {
            $logFile = __DIR__ . '/php_errors.log';
            $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile);
            if (!$authResult['success']) {
                http_response_code(401);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to authenticate for image request',
                    'message' => 'Failed to authenticate for image request.'
                ]);
                return;
            }
        } else {
            http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
            echo json_encode([
                'error' => 'No session cookie for LMS sub-platform',
                'message' => 'No session cookie for LMS sub-platform.'
            ]);
            return;
        }
    }

    $ch = curl_init($targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        http_response_code(500);
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'error' => 'cURL Error: ' . $curlError,
            'message' => 'An error occurred while fetching the image.'
        ]);
        return;
    }

    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    if ($httpCode !== 200) {
        http_response_code($httpCode);
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'error' => 'Image not found (HTTP ' . $httpCode . ')',
            'message' => 'The requested image could not be found.'
        ]);
        return;
    }

    // Extract and set content type
    if (preg_match('/^Content-Type:\s*([^\r\n]+)/mi', $headers, $matches)) {
        $contentType = trim($matches[1]);
        header('Content-Type: ' . $contentType);
    } else {
        // Default to SVG for Moodle image.php requests
        header('Content-Type: image/svg+xml');
    }
    
    // Set appropriate caching headers for images
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=86400');
    http_response_code($httpCode);
    echo $body;
}



/**
 * Proxy AJAX/API requests to LMS sub-platforms to avoid CORS errors
 * Usage: /database/api.php?endpoint=lms_ajax_proxy&subplatform=...&username=...&apipath=...
 * Forwards the request to the real LMS server using the user's session cookie
 */
function handleLmsAjaxProxy() {

        global $method, $lms_subplatforms;

        $subplatformName = isset($_GET['subplatform']) ? $_GET['subplatform'] : '';
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $apiPath = isset($_GET['apipath']) ? $_GET['apipath'] : '';
        $sesskey = isset($_GET['sesskey']) ? $_GET['sesskey'] : '';

        // Debug logging
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX Proxy - sesskey: '$sesskey', apiPath: '$apiPath'\n", FILE_APPEND);

        if (!$subplatformName || !$username || !$apiPath) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Sub-platform name, username, and apipath are required',
                    'message' => 'You must provide a sub-platform name, username, and API path.'
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

        $baseUrl = rtrim($targetSubplatform['url'], '/');
        
        // Debug logging - Remove after testing
        $originalApiPath = $apiPath;
        
        // Remove leading LMS/ from apiPath if baseUrl already ends with /LMS
        if (substr($baseUrl, -4) === '/LMS' && strpos($apiPath, 'LMS/') === 0) {

                $apiPath = substr($apiPath, 4);
        // Remove 'LMS/' from the beginning
                error_log("LMS Ajax URL Fix: '$originalApiPath' -> '$apiPath' for baseUrl: '$baseUrl'");
            
    }
        
        $targetUrl = $baseUrl . '/' . ltrim($apiPath, '/');
        
        // Add sesskey to URL for Moodle AJAX requests if provided
        if ($sesskey) {
            $separator = (strpos($targetUrl, '?') !== false) ? '&' : '?';
            $targetUrl .= $separator . 'sesskey=' . urlencode($sesskey);
        }

        // Debug: Log the exact URL and method
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX Target URL: $targetUrl\n", FILE_APPEND);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Request method: $method\n", FILE_APPEND);
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';
        if (!file_exists($cookieFile)) {

                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'No session cookie for LMS sub-platform',
                    'message' => 'No session cookie for LMS sub-platform.'
                ]);
                return;
            
    }

        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
    // To capture headers
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        // IMPROVED: Forward method and body for POST requests with proper handling
        if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                
                // Get POST data
            /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
                $postData = file_get_contents('php://input');
                
                // Handle sesskey for Moodle AJAX requests
                if ($sesskey && !empty($postData)) {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Adding sesskey '$sesskey' to POST data\n", FILE_APPEND);
                    // For JSON requests, decode, add sesskey, and re-encode
                    $decodedData = json_decode($postData, true);
                    if ($decodedData && is_array($decodedData)) {
                        // Add sesskey to each request in the array
                        foreach ($decodedData as &$request) {
                            if (isset($request['args'])) {
                                $request['args']['sesskey'] = $sesskey;
                            }
                        }
                        $postData = json_encode($decodedData);
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Modified POST data: " . substr($postData, 0, 200) . "\n", FILE_APPEND);
                    }
                }
                
                // Handle file uploads if present
                if (!empty($_FILES)) {

                        $postData = $_POST;
                        foreach ($_FILES as $fieldName => $fileInfo) {

                                if ($fileInfo['error'] === UPLOAD_ERR_OK) {

                                        $postData[$fieldName] = new CURLFile($fileInfo['tmp_name'], $fileInfo['type'], $fileInfo['name']);
                                    
                }
                            
            }
                    
        }
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                
                // Forward content-type with proper handling
                $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'application/x-www-form-urlencoded';
                $headers = [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Content-Type: ' . $contentType,
                    'X-Requested-With: XMLHttpRequest'
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
    }
    else {

                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'X-Requested-With: XMLHttpRequest'
                ]);
            
    }

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX: About to execute CURL to: $targetUrl\n", FILE_APPEND);
        if ($method === 'POST') {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX: POST data being sent: " . substr($postData, 0, 300) . "\n", FILE_APPEND);
        }
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);

        // Special handling for YUI combo loader failures
        if (strpos($apiPath, 'yui_combo.php') !== false && $httpCode !== 200) {

                // Force re-authentication and retry
                @unlink($cookieFile);
                // Assign credentials before using
                $credentials = get_platform_credentials($username, 'LMS');
                $logFile = __DIR__ . '/php_errors.log';
                $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile);
                if ($authResult['success']) {

                        // Retry the request with new authentication
                        $ch = curl_init($targetUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                        curl_setopt($ch, CURLOPT_COOKIESESSION, false);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Referer: ' . $baseUrl . '/',
                            'X-Requested-With: XMLHttpRequest'
                        ]);
                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                        $response = curl_exec($ch);
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $headers = substr($response, 0, $header_size);
                        $body = substr($response, $header_size);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                        $curlError = curl_error($ch);
                        curl_close($ch);
                        // Optionally log the retry attempt
                        $logMsg = "[" . date('Y-m-d H:i:s') . "] [YUI Combo Retry] Retried after re-auth for $targetUrl, HTTP $httpCode\n";
                        file_put_contents($logFile, $logMsg, FILE_APPEND);
                    
        }
            
    }

        // --- BEGIN DEBUG LOGGING ---
        $logFile = __DIR__ . '/php_errors.log';
        $logMsg = "[" . date('Y-m-d H:i:s') . "] [LmsAjaxProxy]\n";
        $logMsg .= "Target URL: $targetUrl\n";
        $logMsg .= "HTTP Code: $httpCode\n";
        $logMsg .= "Effective URL: $effectiveUrl\n";
        $logMsg .= "cURL Error: $curlError\n";
        $logMsg .= "Response Headers: " . substr($headers, 0, 500) . "\n";
        if ($httpCode !== 200) {

                $logMsg .= "Response Body (snippet): " . substr($body, 0, 500) . "\n";
            
    }
        file_put_contents($logFile, $logMsg, FILE_APPEND);
        // --- END DEBUG LOGGING ---

        if ($curlError) {

                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'cURL Error',
                    'message' => 'An error occurred while processing the AJAX request.',
                    'details' => $curlError
                ]);
                return;
            
    }

        // Split headers and body
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Forward relevant headers (content-type, etc.)
        if (preg_match('/^Content-Type:\\s*([^\r\n]+)/mi', $headers, $matches)) {

                header('Content-Type: ' . trim($matches[1]));
            
    }
    else {

                header('Content-Type: application/json');
            
    }
        // Allow CORS from localhost
        header('Access-Control-Allow-Origin: http://localhost');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

        http_response_code($httpCode);
        echo $body;
}

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

                    $credentials = get_platform_credentials($username, $platform);
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
                $credentials = get_platform_credentials($username, 'SIS');
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
                $credentials = get_platform_credentials($username, 'RMS');
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
        $credentials = get_platform_credentials($username, 'LMS');
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
 * Fetch notifications from LMS sub-platform
 */
function fetchLmsSubplatformNotifications($notificationsUrl, $subplatformName, $cookieFile = null) {

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Fetching notifications from LMS sub-platform: $subplatformName", FILE_APPEND);
        
        $ch = curl_init($notificationsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Referer: ' . $notificationsUrl
        ]);
        if ($cookieFile && file_exists($cookieFile)) {

                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            
    }
    else {

                curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
        // fallback
            
    }
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS Sub-platform Notifications Response (HTTP $httpCode):\n" . substr($response, 0, 1000) . "\n", FILE_APPEND);
        if ($error) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS Sub-platform Notifications cURL Error: $error\n", FILE_APPEND);
            
    }
        
        if ($httpCode === 200 && $response) {

                // Try to parse JSON response
                $jsonResponse = json_decode($response, true);
                if ($jsonResponse !== null) {

                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS Sub-platform notifications parsed as JSON: " . count($jsonResponse) . " items", FILE_APPEND);
                        return $jsonResponse;
                    
        }
                
                // Try to parse HTML response
                $notifications = parseLmsSubplatformNotificationsFromHtml($response, $subplatformName);
                if (!empty($notifications)) {

                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS Sub-platform notifications parsed from HTML: " . count($notifications) . " items", FILE_APPEND);
                        return $notifications;
                    
        }
            
    }
        
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] LMS Sub-platform notifications: no valid notifications found", FILE_APPEND);
        return [];
}
/**
 * Parse notifications from HTML response for LMS sub-platforms
 */
function parseLmsSubplatformNotificationsFromHtml($html, $subplatformName, $username = null) {

        $notifications = [];
        $logFile = __DIR__ . '/php_errors.log';
        $proxyBase = 'http://localhost/LEAVE_RMS/database/api.php?endpoint=lms_subplatform_proxy';
        $proxiedNotifPath = 'message/output/popup/notifications.php';
        
        // Skip if we detect login-related content (not authenticated)
        if (strpos($html, 'You are not logged in') !== false || 
            (strpos($html, 'login') !== false && strpos($html, 'username') !== false && strpos($html, 'password') !== false && strpos($html, 'form') !== false) ||
            strpos($html, 'Please log in') !== false ||
            strpos($html, 'Authentication required') !== false ||
            strpos($html, 'Invalid login') !== false ||
            strpos($html, 'Login failed') !== false) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Skipping notifications for $subplatformName - login required", FILE_APPEND);
                return $notifications;
            
    }
        
        // Look for UNREAD notification indicators specifically
        // Common patterns for unread notifications in LMS systems
        $unreadPatterns = [
            // Unread badge indicators
            '/<span[^>]*class[^>]*unread[^>]*>(.*?)<\/span>/is',
            '/<div[^>]*class[^>]*unread[^>]*>(.*?)<\/div>/is',
            '/<span[^>]*class[^>]*badge[^>]*>(.*?)<\/span>/is',
            '/<div[^>]*class[^>]*badge[^>]*>(.*?)<\/div>/is',
            
            // Notification items with unread indicators
            '/<div[^>]*class[^>]*notification-item[^>]*unread[^>]*>(.*?)<\/div>/is',
            '/<li[^>]*class[^>]*unread[^>]*>(.*?)<\/li>/is',
            '/<tr[^>]*class[^>]*unread[^>]*>(.*?)<\/tr>/is',
            
            // Messages with unread status
            '/<div[^>]*class[^>]*message[^>]*unread[^>]*>(.*?)<\/div>/is',
            '/<div[^>]*class[^>]*popup[^>]*unread[^>]*>(.*?)<\/div>/is',
            
            // Items with "new" or "unread" text
            '/<div[^>]*class[^>]*[^>]*>(.*?(?:new|unread|unread.*?|.*?unread).*?)<\/div>/is',
            '/<span[^>]*class[^>]*[^>]*>(.*?(?:new|unread|unread.*?|.*?unread).*?)<\/span>/is',
            '/<li[^>]*class[^>]*[^>]*>(.*?(?:new|unread|unread.*?|.*?unread).*?)<\/li>/is',
            
            // FontAwesome or icon indicators for unread
            '/<i[^>]*class[^>]*(?:fa|fas|far|fab)[^>]*unread[^>]*>(.*?)<\/i>/is',
            '/<i[^>]*class[^>]*(?:fa|fas|far|fab)[^>]*[^>]*>(.*?)<\/i>/is',
            
            // Items with data attributes indicating unread status
            '/<div[^>]*data-unread[^>]*>(.*?)<\/div>/is',
            '/<span[^>]*data-unread[^>]*>(.*?)<\/span>/is',
            '/<li[^>]*data-unread[^>]*>(.*?)<\/li>/is'
        ];
        
        foreach ($unreadPatterns as $pattern) {

            /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
                if (preg_match_all($pattern, $html, $matches)) {

                    foreach ($matches[1] as $match) {

                                $content = strip_tags($match);
                                if (!empty($content) && strlen($content) > 3 && 
                                    strpos($content, 'You are not logged in') === false &&
                                    strpos($content, 'login') === false &&
                    /* INCLUDE/REQUIRE: Verify that included paths are not user-controlled to avoid remote code execution. */
                                    // Only include if it contains unread indicators
                                    (strpos($content, 'unread') !== false || 
                                     strpos($content, 'new') !== false ||
                                     strpos($content, 'unread') !== false ||
                                     is_numeric($content))) {
                    // Numeric content might be unread count
                                        
                                $notifications[] = [
                                            'title' => 'Unread LMS Notification',
                                            'message' => $content,
                                            'platform' => $subplatformName,
                                            'timestamp' => date('Y-m-d H:i:s'),
                                            'url' => $username ? $proxyBase . '&subplatform=' . urlencode($subplatformName) . '&username=' . urlencode($username) . '&path=' . urlencode($proxiedNotifPath) : null
                                        ];
                                    
                }
                            
            }
                    
        }
            
    }
        
        // Look for notification counts (usually indicate unread notifications)
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        if (preg_match_all('/<span[^>]*class[^>]*notification-count[^>]*>(.*?)<\/span>/is', $html, $matches)) {

                foreach ($matches[1] as $match) {

                        $content = strip_tags($match);
                        if (!empty($content) && is_numeric($content) && intval($content) > 0) {

                                $notifications[] = [
                                    'title' => 'Unread Notification Count',
                                    'message' => "You have $content unread notifications",
                                'platform' => $subplatformName,
                                'timestamp' => date('Y-m-d H:i:s')
                            ];
                            
            }
                    
        }
            
    }
        
        // Look for specific unread message indicators
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        if (preg_match_all('/<div[^>]*class[^>]*message[^>]*>(.*?)<\/div>/is', $html, $matches)) {

                foreach ($matches[1] as $match) {

                        $content = strip_tags($match);
                        if (!empty($content) && strlen($content) > 5 && 
                            strpos($content, 'You are not logged in') === false &&
                            strpos($content, 'login') === false &&
                            (strpos($content, 'unread') !== false || 
                             strpos($content, 'new message') !== false ||
                             strpos($content, 'unread message') !== false)) {

                                $notifications[] = [
                                    'title' => 'Unread LMS Message',
                                    'message' => $content,
                                    'platform' => $subplatformName,
                                    'timestamp' => date('Y-m-d H:i:s')
                                ];
                            
            }
                    
        }
            
    }
        
        // Fallback: If no notifications found with specific patterns, try to capture any meaningful content
        if (empty($notifications)) {

                // Look for any content that might be a notification (fallback)
                $cleanedHtml = strip_tags($html);
                $lines = explode("\n", $cleanedHtml);
                foreach ($lines as $line) {

                        $line = trim($line);
                        if (strlen($line) > 10 && strlen($line) < 500 && 
                            strpos($line, 'You are not logged in') === false &&
                            strpos($line, 'login') === false &&
                            strpos($line, 'username') === false &&
                            strpos($line, 'password') === false &&
                            (strpos($line, 'notification') !== false || 
                             strpos($line, 'message') !== false ||
                             strpos($line, 'announcement') !== false ||
                             strpos($line, 'alert') !== false)) {

                                $notifications[] = [
                                    'title' => 'LMS Content',
                                    'message' => $line,
                                    'platform' => $subplatformName,
                                    'timestamp' => date('Y-m-d H:i:s')
                                ];
                            
            }
                    
        }
            
    }
        
        // Log if no real notifications were found
        if (empty($notifications)) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No real notifications found for LMS sub-platform: $subplatformName", FILE_APPEND);
            
    }
    else {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Found " . count($notifications) . " unread notifications for LMS sub-platform: $subplatformName", FILE_APPEND);
            
    }
        
        return $notifications;
}
/**
 * IMPROVED: Fetch notifications from all LMS sub-platforms for a user
 * @param string $username - Username to fetch notifications for
 * @return array - Array of notifications from all LMS sub-platforms
 */
function fetchAllLmsSubplatformNotifications($username) {

        global $lms_subplatforms;
        
        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] fetchAllLmsSubplatformNotifications called for user: $username", FILE_APPEND);
        
        $allNotifications = [];
        $credentials = get_platform_credentials($username, 'LMS');
        
        if (!$credentials) {

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No LMS credentials found for user: $username", FILE_APPEND);
                return $allNotifications;
            
    }
        
        // Process each LMS sub-platform
        foreach ($lms_subplatforms as $subplatform) {

                $subplatformName = $subplatform['name'];
                $baseUrl = rtrim($subplatform['url'], '/');
                $notificationsUrl = $baseUrl . '/' . ltrim($subplatform['notifications_endpoint'], '/');
                
                // Use consistent cookie file path
                $host = parse_url($baseUrl, PHP_URL_HOST);
                $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';
                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Fetching notifications from LMS sub-platform: $subplatformName", FILE_APPEND);

                // Step 1: Check if we need to authenticate
                $needsAuth = false;
                
                // Check if cookie file exists and is recent (less than 20 minutes old)
                if (!file_exists($cookieFile) || (time() - filemtime($cookieFile)) > 1200) {

                        $needsAuth = true;
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Cookie file missing or expired for $subplatformName, need to authenticate", FILE_APPEND);
                    
        }
        else {

                        // Try to access notifications page with existing cookie
                    $ch = curl_init($notificationsUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Accept: application/json, text/html, */*',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    ]);
                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                    $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                        // Check if we got redirected to login or got a login form
                        // Be more specific to avoid false positives
                        $isLoginRedirect = ($httpCode == 302 || $httpCode == 301);
                        $hasLoginForm = (strpos($response, '<form') !== false && 
                                       strpos($response, 'name="username"') !== false && 
                                       strpos($response, 'name="password"') !== false &&
                                       strpos($response, 'type="password"') !== false);
                        $hasNotLoggedInMessage = (strpos($response, 'You are not logged in') !== false ||
                                                strpos($response, 'Please log in') !== false ||
                                                strpos($response, 'Login required') !== false);
                        
                        if ($isLoginRedirect || $hasLoginForm || $hasNotLoggedInMessage) {
                                $needsAuth = true;
                                $logFile = __DIR__ . '/php_errors.log';
                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Authentication required for $subplatformName (redirect: $isLoginRedirect, form: $hasLoginForm, message: $hasNotLoggedInMessage)", FILE_APPEND);
                            
            }
                    
        }

                // Step 2: Authenticate if needed
                if ($needsAuth) {

                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Authenticating to $subplatformName", FILE_APPEND);
                        $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $subplatform, $credentials, $logFile, $cookieFile);
                        
                        if (!$authResult['success']) {

                                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Authentication failed for $subplatformName: " . ($authResult['error'] ?? 'Unknown error'), FILE_APPEND);
                                continue;
                // Skip this subplatform if authentication failed
                            
            }
                        
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Authentication successful for $subplatformName", FILE_APPEND);
                        
                        // Use the dashboard response from authentication if available
                        $dashboardResponse = $authResult['dashboardResponse'] ?? null;
                    
        }

                // Step 3: Fetch notifications with proper authentication
                // Use the dashboard response from Step 2.5 if we just authenticated
                if ($needsAuth) {

                        // We already have the dashboard response from the authentication step
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Using dashboard response from authentication for $subplatformName", FILE_APPEND);
                    
        }
        else {

                        // If we didn't need to authenticate, fetch dashboard now
                        $dashboardUrl = $baseUrl . '/my/';
                        $ch = curl_init($dashboardUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Accept: application/json, text/html, */*',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                        ]);
                        
                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                        $dashboardResponse = curl_exec($ch);
                        $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Dashboard response HTTP Code for $subplatformName: $dashboardHttpCode", FILE_APPEND);
                    
        }
                
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Dashboard response preview for $subplatformName: " . substr($dashboardResponse, 0, 500), FILE_APPEND);
                
                // Now try the notifications endpoint with the established session
                    $ch = curl_init($notificationsUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Accept: application/json, text/html, */*',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer: ' . $baseUrl . '/my/',
                    'Accept-Language: en-US,en;q=0.5',
                    'Connection: keep-alive'
                    ]);
                
                // Reduced delay to prevent timeout
                usleep(200000);
        // 0.2 second delay
                
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                    $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Notifications response HTTP Code for $subplatformName: $httpCode", FILE_APPEND);
                
                // Debug: Log a preview of the response content
                $responsePreview = substr(strip_tags($response), 0, 500);
                file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Response preview for $subplatformName: $responsePreview", FILE_APPEND);

                // Step 4: Parse notifications using LMS-specific logic
                // Try to parse notifications from dashboard first
                $notifications = parseLmsSubplatformNotificationsFromHtml($dashboardResponse, $subplatformName, $username);
                
                // If no notifications found from dashboard, try the notifications endpoint
                if (empty($notifications)) {

                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No notifications found in dashboard for $subplatformName, trying notifications endpoint", FILE_APPEND);
                        $notifications = parseLmsSubplatformNotificationsFromHtml($response, $subplatformName, $username);
                    
        }
                
                if (!empty($notifications)) {

                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Found " . count($notifications) . " notifications from $subplatformName", FILE_APPEND);
                        $allNotifications = array_merge($allNotifications, $notifications);
                    
        }
        else {

                        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] No notifications found from $subplatformName", FILE_APPEND);
                    
        }
            
    }
        
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Total LMS sub-platform notifications found: " . count($allNotifications), FILE_APPEND);
        return $allNotifications;
}


/**
 * Handle LMS sub-platform proxy access
 * This function proxies requests to LMS sub-platforms to maintain session state
 */

function handleLmsSubplatformProxy() {

        global $lms_subplatforms;

        $subplatformName = isset($_GET['subplatform']) ? urldecode($_GET['subplatform']) : '';
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $path = isset($_GET['path']) ? $_GET['path'] : (isset($_GET['page']) ? $_GET['page'] : '');

        if (!$subplatformName || !$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Sub-platform name and username are required',
                    'message' => 'You must provide both a sub-platform name and a username.'
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

        $baseUrl = rtrim($targetSubplatform['url'], '/');

        // Normalize path to avoid double /LMS issues and handle root navigation
        // If base ends with /LMS and incoming path is 'LMS' or '/LMS' or starts with it, strip the leading LMS segment
        $originalPath = $path;
        if (substr($baseUrl, -4) === '/LMS') {

                // Strip optional leading slash, then a single leading 'LMS' segment with optional trailing slash
                if (preg_match('/^\/?LMS(\/|$)/i', $path)) {

                        $path = preg_replace('/^\/?LMS\/?/i', '', $path);
                    
        }
                // Special case: if path becomes empty (i.e., was exactly 'LMS' or '/LMS'), keep it as base ("")
                if ($path === null) {
            $path = '';
        }
            
    }
        // Log normalization if any change
        if ($originalPath !== $path) {

                error_log("LMS Proxy Path Normalize: '$originalPath' -> '$path' for baseUrl: '$baseUrl'");
            
    }

        // Fix double-encoded YUI paths
        if (strpos($path, 'yui_combo.php') !== false) {

                $decodedPath = urldecode($path);
                if (strpos($decodedPath, 'yui_combo.php') !== false) {

                        $path = $decodedPath;
                    
        }
            
    }
        
        // Debug logging - Remove after testing
        $originalPath = $path;
        
        // Remove leading LMS/ from path if baseUrl already ends with /LMS
        if (substr($baseUrl, -4) === '/LMS' && strpos($path, 'LMS/') === 0) {

                $path = substr($path, 4);
        // Remove 'LMS/' from the beginning
                error_log("LMS Proxy URL Fix: '$originalPath' -> '$path' for baseUrl: '$baseUrl'");
            
    }
        
        // JAVASCRIPT EXTENSION FIX: Handle missing .js extensions for JavaScript files
        if (strpos($path, 'lib/javascript.php') !== false || strpos($path, 'lib/requirejs') !== false) {

                // Check if this looks like a JavaScript file missing its extension
                if (preg_match('/\/([^\/]+)$/', $path, $matches)) {

                        $filename = $matches[1];
                        // If it doesn't have an extension but looks like a JS file, add .js
                        if (!preg_match('/\.[a-zA-Z0-9]+$/', $filename) && 
                            (strpos($filename, 'jquery') !== false || 
                             strpos($filename, 'min') !== false || 
                /* INCLUDE/REQUIRE: Verify that included paths are not user-controlled to avoid remote code execution. */
                             strpos($filename, 'require') !== false ||
                             strpos($filename, 'polyfill') !== false ||
                             strpos($filename, 'babel') !== false ||
                             strpos($filename, 'javascript-static') !== false)) {

                                $path .= '.js';
                                error_log("JS Extension Fix: '$originalPath' -> '$path' (added .js extension)");
                            
            }
                    
        }
            
    }
        
        // SPECIFIC FIX: Handle jQuery path issues
        if (strpos($path, 'jquery-3.5.1.min') !== false && !strpos($path, '.js')) {

                $path .= '.js';
                error_log("jQuery Extension Fix: '$originalPath' -> '$path' (added .js extension)");
            
    }
        
        $targetUrl = $baseUrl . '/' . ltrim($path, '/');

        $credentials = get_platform_credentials($username, 'LMS');
        if (!$credentials) {

                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'LMS credentials not found for user',
                    'message' => 'LMS credentials not found for this user.'
                ]);
                return;
            
    }

        // Use consistent cookie file path with the direct link function
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';

        // Create cookies directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../cookies')) {

                mkdir(__DIR__ . '/../cookies', 0755, true);
            
    }

        // IMPROVED: Enhanced session management and re-authentication
        $needsReauth = false;
        $logFile = __DIR__ . '/php_errors.log';
        
        // Check if cookie file exists and is recent (within 25 minutes for course navigation)
        if (!file_exists($cookieFile)) {

                $needsReauth = true;
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] No cookie file found for $username on $host, will authenticate\n", FILE_APPEND);
            
    }
    else {

                $cookieAge = time() - filemtime($cookieFile);
                if ($cookieAge > 1500) {
            // Extended to 25 minutes for course navigation
                         $needsReauth = true;
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Cookie file for $username on $host is $cookieAge seconds old, will re-authenticate\n", FILE_APPEND);
                    
        }
        else {

                        // Update cookie file timestamp to keep session alive for course navigation
                        touch($cookieFile);
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Session keep-alive: Updated cookie timestamp for $username on $host (age: {$cookieAge}s)\n", FILE_APPEND);
                    
        }
            
    }

        if ($needsReauth) {

                // Use the internal authentication helper
                $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile);
                if (!$authResult['success']) {

                        http_response_code(401);
                /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                        echo json_encode([
                            'error' => 'Failed to authenticate with LMS sub-platform: ' . $authResult['error'],
                            'message' => 'Failed to authenticate with the LMS sub-platform.'
                        ]);
                        return;
                    
        }
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Successfully authenticated $username to $subplatformName\n", FILE_APPEND);
                
                // Verify session is established by visiting dashboard first
                $dashboardUrl = $baseUrl . '/my/';
                $ch = curl_init($dashboardUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Connection: keep-alive',
                    'Upgrade-Insecure-Requests: 1'
                ]);
                
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $dashboardResponse = curl_exec($ch);
                $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Session verification for $subplatformName (HTTP: $dashboardHttpCode)\n", FILE_APPEND);
                
                // If dashboard still shows login page, force re-authentication
                if (strpos($dashboardResponse, 'Log in to the site') !== false) {

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] WARNING: Session verification failed for $subplatformName, forcing re-authentication\n", FILE_APPEND);
                        $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile);
                        if (!$authResult['success']) {

                                http_response_code(401);
                    /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                                echo json_encode([
                                    'error' => 'Failed to establish session with LMS sub-platform',
                                    'message' => 'Failed to establish session with the LMS sub-platform.'
                                ]);
                                return;
                            
            }
                    
        }
            
    }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIESESSION, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Enhanced headers for better session management and navigation
        $requestHeaders = [
            'Referer: ' . $baseUrl . '/',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'DNT: 1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin'
        ];
        
        // Add special headers for course navigation and session persistence
        if (strpos($path, 'course/') !== false || strpos($path, 'mod/') !== false) {
                $requestHeaders[] = 'Cache-Control: no-cache';
                $requestHeaders[] = 'Pragma: no-cache';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Course navigation detected for path: $path\n", FILE_APPEND);
        }
        
        // Add navigation specific headers for better session tracking - but don't add AJAX headers for regular navigation
        if (strpos($path, '/') !== false && $path !== '') {
                // Only add cache control for navigation, not AJAX header that might confuse some LMS systems
                $requestHeaders[] = 'Cache-Control: no-cache';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Enhanced headers for navigation path: $path\n", FILE_APPEND);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        
        // Enhanced encoding and compression support with proper decompression
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate'); // Explicitly support gzip and deflate
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
        // Disable SSL verification for now to avoid connection issues
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        
        // Check for curl errors first
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $errorCode = curl_errno($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] CURL Error #$errorCode: $error\n", FILE_APPEND);
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Target URL: $targetUrl\n", FILE_APPEND);
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Effective URL: $effectiveUrl\n", FILE_APPEND);
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] HTTP Code: $httpCode\n", FILE_APPEND);
            
            http_response_code(500);
            echo json_encode([
                'error' => 'Connection failed', 
                'message' => 'Unable to connect to LMS server',
                'details' => "CURL Error #$errorCode: $error",
                'target_url' => $targetUrl
            ]);
            return;
        }
        
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        // Enhanced content validation and compression detection
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Proxy Response - HTTP: $httpCode, Length: " . strlen($body) . ", Content-Type: $contentType\n", FILE_APPEND);
        
        // Check if content appears to be binary/compressed
        $isBinary = !mb_check_encoding($body, 'UTF-8') && !ctype_print(substr($body, 0, 100));
        if ($isBinary) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] WARNING: Content appears to be binary/compressed, attempting manual decompression\n", FILE_APPEND);
            
            // Check if it's gzipped content that wasn't automatically decompressed
            if (substr($body, 0, 2) === "\x1f\x8b") {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Detected gzip header, attempting manual decompression\n", FILE_APPEND);
                $decompressed = @gzdecode($body);
                if ($decompressed !== false) {
                    $body = $decompressed;
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Successfully decompressed gzip content\n", FILE_APPEND);
                } else {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ERROR: Failed to decompress gzip content\n", FILE_APPEND);
                }
            }
            
            // Check if it's deflate compressed
            if (strlen($body) > 2 && ord($body[0]) === 0x78) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Detected deflate header, attempting manual decompression\n", FILE_APPEND);
                $decompressed = @gzinflate($body);
                if ($decompressed !== false) {
                    $body = $decompressed;
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Successfully decompressed deflate content\n", FILE_APPEND);
                } else {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ERROR: Failed to decompress deflate content\n", FILE_APPEND);
                }
            }
        }
        
        // Handle encoding issues and content validation after decompression
        if ($body && $contentType && stripos($contentType, 'text/html') !== false) {
            // Log content info for debugging
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] After decompression - Content length: " . strlen($body) . "\n", FILE_APPEND);
            
            // Check encoding and detect issues
            $isValidUTF8 = mb_check_encoding($body, 'UTF-8');
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] UTF-8 valid: " . ($isValidUTF8 ? 'YES' : 'NO') . "\n", FILE_APPEND);
            
            if (!$isValidUTF8) {
                $originalEncoding = mb_detect_encoding($body, 'auto', true);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Detected encoding: " . ($originalEncoding ?: 'UNKNOWN') . "\n", FILE_APPEND);
                $body = mb_convert_encoding($body, 'UTF-8', $originalEncoding ?: 'ISO-8859-1');
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Content encoding converted to UTF-8\n", FILE_APPEND);
            }
            
            // Remove any BOM characters that might cause issues
            $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
            
            // Check for valid HTML structure
            if (preg_match('/<html[^>]*>/i', $body) || preg_match('/<!DOCTYPE/i', $body)) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] HTML structure detected - content appears valid\n", FILE_APPEND);
            } else {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] WARNING: Content doesn't appear to be valid HTML\n", FILE_APPEND);
                // Log first 200 characters for debugging
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Content preview: " . substr($body, 0, 200) . "\n", FILE_APPEND);
            }
        }

        // Disable any PHP output compression to prevent double compression
        if (function_exists('ini_set')) {
            ini_set('zlib.output_compression', 'Off');
            ini_set('output_buffering', 'Off');
        }
        
        // Enhanced comprehensive session validation and re-authentication
        $needsReauth = false;
        $reAuthReason = '';
        
        // Check for HTTP redirects to login pages
        if (($httpCode == 302 || $httpCode == 301) && (strpos($effectiveUrl, 'login') !== false || strpos($effectiveUrl, 'auth') !== false)) {
            $needsReauth = true;
            $reAuthReason = "HTTP redirect to login page: $effectiveUrl";
        }
        
        // Check for login form in response body - be more specific to avoid false positives
        $hasActualLoginForm = false;
        if (stripos($body, '<form') !== false) {
            // Look for actual login forms with both username AND password fields in the same form
            if (preg_match('/<form[^>]*>.*?(<input[^>]*name\s*=\s*["\']username["\'][^>]*>.*?<input[^>]*type\s*=\s*["\']password["\'][^>]*>|<input[^>]*type\s*=\s*["\']password["\'][^>]*>.*?<input[^>]*name\s*=\s*["\']username["\'][^>]*>).*?<\/form>/is', $body)) {
                $hasActualLoginForm = true;
            }
        }
        
        if ($hasActualLoginForm) {
            $needsReauth = true;
            $reAuthReason = "Actual login form with username/password fields detected";
        }
        
        // Check for specific Moodle login indicators - be more specific
        if (stripos($body, 'You are not logged in') !== false ||
            stripos($body, 'Login required') !== false ||
            stripos($body, 'Your session has expired') !== false ||
            strpos($body, 'requireslogin') !== false) {
            $needsReauth = true;
            $reAuthReason = "Specific session expiry message detected";
        }
        
        // Check if we're being shown a login page (title check)
        if (preg_match('/<title[^>]*>([^<]*login[^<]*)<\/title>/i', $body, $matches)) {
            $needsReauth = true;
            $reAuthReason = "Login page title detected: " . trim($matches[1]);
        }
        
        // Check for session timeout indicators - only for HTML responses and very specific messages
        $isHtmlResponse = stripos($contentType, 'text/html') !== false || 
                          (!$contentType && stripos($body, '<html') !== false);
        
        if ($isHtmlResponse && (
            stripos($body, 'Your session has expired') !== false ||
            stripos($body, 'Session timeout') !== false ||
            stripos($body, 'Session invalid') !== false
        )) {
            $needsReauth = true;
            $reAuthReason = "Explicit session timeout message detected";
        }
        
        if ($needsReauth) {
            // Check if this is a CSS or resource request - if so, don't re-authenticate to avoid loops
            $isCssOrResourceRequest = strpos($path, 'theme/styles.php') !== false ||
                                    strpos($path, 'theme/image.php') !== false ||
                                    strpos($path, 'theme/font.php') !== false ||
                                    strpos($path, '.css') !== false ||
                                    strpos($path, '.js') !== false ||
                                    strpos($path, '.png') !== false ||
                                    strpos($path, '.jpg') !== false ||
                                    strpos($path, '.gif') !== false;
            
            // Debug: Log what path we're checking
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Checking path for CSS/resource: '$path', isCssOrResource: " . ($isCssOrResourceRequest ? 'YES' : 'NO') . "\n", FILE_APPEND);
            
            if ($isCssOrResourceRequest) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Skipping re-authentication for CSS/resource request: $path\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Returning empty response to prevent broken styling\n", FILE_APPEND);
                
                // Return an appropriate empty response based on content type
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                if (strpos($path, 'styles.php') !== false || strpos($path, '.css') !== false) {
                    header('Content-Type: text/css');
                    echo '/* CSS temporarily unavailable due to session timeout */';
                } else {
                    header('Content-Type: text/plain');
                    echo '';
                }
                return;
            }
            
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] SESSION ISSUE for $subplatformName: $reAuthReason\n", FILE_APPEND);
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Attempting re-authentication...\n", FILE_APPEND);
            
            // Try to re-authenticate
            $authResult = authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile);
            if ($authResult['success']) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Re-authentication successful, retrying request...\n", FILE_APPEND);
                
                // Retry the page fetch with fresh session and enhanced headers
                $ch = curl_init($targetUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIESESSION, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($requestHeaders, [
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Accept-Encoding: gzip, deflate',
                    'DNT: 1',
                    'Connection: keep-alive',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate'
                ]));

                /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $response = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headers = substr($response, 0, $header_size);
                $body = substr($response, $header_size);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                
                // Apply the same decompression logic for the retry request
                $isBinary = !mb_check_encoding($body, 'UTF-8') && !ctype_print(substr($body, 0, 100));
                if ($isBinary) {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: Content appears to be binary/compressed, attempting manual decompression\n", FILE_APPEND);
                    
                    // Check if it's gzipped content that wasn't automatically decompressed
                    if (substr($body, 0, 2) === "\x1f\x8b") {
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: Detected gzip header, attempting manual decompression\n", FILE_APPEND);
                        $decompressed = @gzdecode($body);
                        if ($decompressed !== false) {
                            $body = $decompressed;
                            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: Successfully decompressed gzip content\n", FILE_APPEND);
                        } else {
                            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: ERROR: Failed to decompress gzip content\n", FILE_APPEND);
                        }
                    }
                    
                    // Check if it's deflate compressed
                    if (strlen($body) > 2 && ord($body[0]) === 0x78) {
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: Detected deflate header, attempting manual decompression\n", FILE_APPEND);
                        $decompressed = @gzinflate($body);
                        if ($decompressed !== false) {
                            $body = $decompressed;
                            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: Successfully decompressed deflate content\n", FILE_APPEND);
                        } else {
                            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: ERROR: Failed to decompress deflate content\n", FILE_APPEND);
                        }
                    }
                }
                
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Re-authentication retry completed (HTTP: $httpCode, URL: $effectiveUrl)\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: After decompression - Body length: " . strlen($body) . " bytes\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RETRY: Body is valid UTF-8: " . (mb_check_encoding($body, 'UTF-8') ? 'YES' : 'NO') . "\n", FILE_APPEND);
                
                // Final check to verify the re-authentication worked
                if (stripos($body, 'Log in to the site') === false && 
                    stripos($body, 'You are not logged in') === false &&
                    !preg_match('/<title[^>]*>([^<]*login[^<]*)<\/title>/i', $body)) {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] SUCCESS: Re-authentication successful, session restored\n", FILE_APPEND);
                } else {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] WARNING: Re-authentication may have failed, still seeing login indicators\n", FILE_APPEND);
                }
            } else {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ERROR: Re-authentication failed: " . $authResult['error'] . "\n", FILE_APPEND);
            }
        }

        // Determine Content-Type from headers before error checking
        $contentType = 'text/html';
    // Default
        if (preg_match('/^Content-Type:\s*([^\r]+)/mi', $headers, $matches)) {

                $contentType = trim($matches[1]);
            
    }

        if ($httpCode !== 200) {

                http_response_code($httpCode);
                
                // For non-HTML resources (JS, CSS, fonts, etc.), just return the error status without JSON
                // This prevents JavaScript from trying to parse "Not Found" HTML as JSON
                if (!stripos($contentType, 'text/html') && !stripos($path, '.php') && !stripos($path, 'ajax')) {

                        // For static resources, just return empty content with proper status
                        echo '';
                        return;
                    
        }
                
                // For HTML/PHP/AJAX requests, return JSON error
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => "Error fetching LMS content: HTTP $httpCode",
                    'message' => 'An error occurred while fetching the LMS content.'
                ]);
                return;
            
    }

        // Determine Content-Type
        $contentType = 'text/html';
    // Default
        if (preg_match('/^Content-Type:\s*([^\r]+)/mi', $headers, $matches)) {

                $contentType = trim($matches[1]);
            
    }

        // Special handling for styles.php - force CSS content type
        if (strpos($path, 'styles.php') !== false) {
            $contentType = 'text/css';
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] CSS DETECTED! Path: $path, Setting content-type to: $contentType\n", FILE_APPEND);
        }

        // Set the correct content type for the response
        header('Content-Type: ' . $contentType);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] HEADER SET: Content-Type: $contentType\n", FILE_APPEND);

        // Process only HTML responses for URL rewriting
        if (stripos($contentType, 'text/html') !== false) {

                $response = $body;
                
                // Debug: Check the state of $body before any processing
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Before processing - Body length: " . strlen($body) . " bytes\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Before processing - Body is valid UTF-8: " . (mb_check_encoding($body, 'UTF-8') ? 'YES' : 'NO') . "\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Before processing - Body first 200 chars: " . substr($body, 0, 200) . "\n", FILE_APPEND);

                // Fix DOCTYPE to prevent KaTeX quirks mode warning
                if (!preg_match('/<!DOCTYPE/i', $response)) {

                        // If no DOCTYPE is present, add HTML5 DOCTYPE at the beginning
                        $response = "<!DOCTYPE html>\n" . $response;
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Added missing DOCTYPE to prevent KaTeX quirks mode\n", FILE_APPEND);
                    
        }
        else {

                        // Ensure DOCTYPE is HTML5 compliant to avoid quirks mode
                        $response = preg_replace('/<!DOCTYPE[^>]*>/i', '<!DOCTYPE html>', $response, 1);
                    
        }

                // Ensure proper meta charset is present to prevent encoding issues
                if (!preg_match('/<meta[^>]*charset[^>]*>/i', $response)) {

                        $response = preg_replace('/(<head[^>]*>)/i', '$1' . "\n" . '<meta charset="UTF-8">', $response, 1);
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Added missing charset meta tag\n", FILE_APPEND);
                    
        }
                
                // Debug: Check the state of $response after DOCTYPE/charset processing
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] After DOCTYPE/charset - Response length: " . strlen($response) . " bytes\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] After DOCTYPE/charset - Response is valid UTF-8: " . (mb_check_encoding($response, 'UTF-8') ? 'YES' : 'NO') . "\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] After DOCTYPE/charset - Response first 200 chars: " . substr($response, 0, 200) . "\n", FILE_APPEND);

                // Ensure proper viewport meta tag for responsive rendering
                if (!preg_match('/<meta[^>]*name=["\']viewport["\'][^>]*>/i', $response)) {

                        $response = preg_replace('/(<meta charset[^>]*>)/i', '$1' . "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1.0">', $response, 1);
                    
        }

                // Add early error prevention script right after head tag
                $earlyErrorPrevention = "
        <script>
        // Comprehensive early error prevention for LMS proxy
        (function() {
            // Wait for RequireJS to be fully loaded before configuring
            function waitForRequireJS(callback, maxAttempts) {
                maxAttempts = maxAttempts || 30; // 3 seconds max wait
                var attempts = 0;
                
                function check() {
                    attempts++;
                    
                    if (typeof require !== 'undefined' && 
                        typeof require.config === 'function' && 
                        typeof define === 'function') {
                        // RequireJS is ready
                        try {
                            callback();
                        } catch (e) {
                            console.warn('RequireJS callback error handled:', e.message);
                        }
                    } else if (attempts < maxAttempts) {
                        // Wait a bit more
                        setTimeout(check, 100);
                    } else {
                        // Timeout - set up fallbacks
                        console.warn('RequireJS not loaded after ' + (maxAttempts * 100) + 'ms, using fallbacks');
                        setupRequireJSFallbacks();
                        try {
                            callback();
                        } catch (e) {
                            console.warn('Fallback callback error handled:', e.message);
                        }
                    }
                }
                
                check();
            }
            
            // Set up RequireJS fallbacks
            function setupRequireJSFallbacks() {
                if (typeof window.require === 'undefined') {
                    window.require = function(deps, callback) {
                        if (typeof callback === 'function') {
                            setTimeout(function() {
                                try {
                                    callback();
                                } catch (e) {
                                    console.warn('Fallback require callback error:', e.message);
                                }
                            }, 10);
                        }
                    };
                    
                    window.require.config = function(config) {
                        console.warn('Fallback require.config called');
                        return this;
                    };
                }
                
                if (typeof window.define === 'undefined') {
                    window.define = function(name, deps, factory) {
                        console.warn('Fallback define called for:', name);
                        window.define._modules = window.define._modules || {};
                        window.define._modules[name] = factory;
                    };
                }
            }
            
            // Performance optimization: Debounce function
            function debounce(func, wait) {
                var timeout;
                return function executedFunction() {
                    var context = this;
                    var args = arguments;
                    var later = function() {
                        timeout = null;
                        func.apply(context, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            
            // Safe execution wrapper
            function safeExecute(fn, context, args) {
                try {
                    return fn.apply(context || window, args || []);
                } catch (e) {
                    console.warn('Safe execution handled error:', e.message);
                    return null;
                }
            }
            
            // Override addEventListener globally to prevent null errors
            var originalAddEventListener = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, listener, options) {
                if (this === null || this === undefined) {
                    console.warn('Prevented addEventListener on null element for event:', type);
                    return;
                }
                try {
                    return originalAddEventListener.call(this, type, listener, options);
                } catch (e) {
                    console.warn('addEventListener error prevented:', e.message);
                }
            };
            
            // Override setTimeout to prevent performance violations
            var originalSetTimeout = window.setTimeout;
            var timeoutQueue = [];
            var isProcessing = false;
            
            window.setTimeout = function(callback, delay) {
                if (delay < 50) {
                    // Use requestAnimationFrame for very short delays
                    return requestAnimationFrame(function() {
                        safeExecute(callback);
                    });
                }
                
                // Queue longer timeouts to prevent violations
                if (delay < 200) {
                    timeoutQueue.push({ callback: callback, delay: delay });
                    if (!isProcessing) {
                        processTimeoutQueue();
                    }
                    return timeoutQueue.length;
                }
                
                return originalSetTimeout.call(window, function() {
                    safeExecute(callback);
                }, delay);
            };
            
            function processTimeoutQueue() {
                if (timeoutQueue.length === 0) {
                    isProcessing = false;
                    return;
                }
                
                isProcessing = true;
                var item = timeoutQueue.shift();
                
                requestAnimationFrame(function() {
                    safeExecute(item.callback);
                    if (timeoutQueue.length > 0) {
                        requestAnimationFrame(processTimeoutQueue);
                    } else {
                        isProcessing = false;
                    }
                });
            }
            
            // Early RequireJS error handling
            window.requirejs = window.requirejs || {};
            window.requirejs.onError = function(err) {
                if (err.message && (err.message.indexOf('addEventListener') !== -1 || 
                                  err.message.indexOf('configure') !== -1)) {
                    console.warn('RequireJS error handled early:', err.message);
                    return;
                }
                console.error('RequireJS error:', err);
            };
            
            // Early YUI mock
            if (!window.YUI) {
                window.YUI = function(config) {
                    return {
                        use: function() {
                            var callback = arguments[arguments.length - 1];
                            if (typeof callback === 'function') {
                                requestAnimationFrame(function() {
                                    safeExecute(callback, null, [{
                                        configure: function() { return this; },
                                        on: function() { return this; },
                                        all: function() { return { each: function() {} }; },
                                        one: function() { return null; }
                                    }]);
                                });
                            }
                            return this;
                        },
                        configure: function() { return this; }
                    };
                };
                window.YUI.GlobalConfig = { configure: function() {} };
            }
            
            // Early modal function override
            window.initModal = window.initModal || function(element) {
                if (!element) {
                    console.warn('Early initModal: null element prevented');
                    var dummy = document.createElement('div');
                    dummy.style.display = 'none';
                    dummy.className = 'modal-dummy';
                    if (document.body) document.body.appendChild(dummy);
                    return dummy;
                }
                return element;
            };
            
            // Early configure function
            window.configure = window.configure || function() { 
                return this; 
            };
            
            // Pre-define problematic modules before they load
            if (typeof define !== 'undefined') {
                // Safe version of core/first to prevent modal errors
                define('core/first', [], function() {
                    return {
                        init: function() {
                            console.log('Safe core/first module loaded');
                        },
                        initModal: function(element) {
                            if (!element) {
                                console.warn('initModal called with null element - creating safe dummy');
                                var dummy = document.createElement('div');
                                dummy.style.display = 'none';
                                dummy.className = 'safe-modal-dummy';
                                return dummy;
                            }
                            
                            // Add safe event listener
                            if (element.addEventListener) {
                                var originalAddEvent = element.addEventListener;
                                element.addEventListener = function(type, listener, options) {
                                    try {
                                        return originalAddEvent.call(this, type, listener, options);
                                    } catch (e) {
                                        console.warn('Modal addEventListener error handled:', e.message);
                                    }
                                };
                            }
                            
                            return element;
                        }
                    };
                });
            }
            
            // Override common problematic functions
            var originalQuerySelector = Document.prototype.querySelector;
            Document.prototype.querySelector = function(selector) {
                try {
                    return originalQuerySelector.call(this, selector);
                } catch (e) {
                    console.warn('querySelector error:', e.message);
                    return null;
                }
            };
            
        })();
        </script>";
                
                $response = preg_replace('/(<head[^>]*>)/i', '$1' . $earlyErrorPrevention, $response, 1);

                // --- START: COMPREHENSIVE PROXY ENHANCEMENT SYSTEM ---
                
                // Generate base proxy URL for this LMS subplatform
                $proxyBaseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?endpoint=lms_subplatform_proxy&username=' . urlencode($username) . '&subplatform=' . urlencode($subplatformName) . '&path=';

                // --- END: COMPREHENSIVE PROXY ENHANCEMENT SYSTEM ---

                // ENHANCED: Apply comprehensive proxy system for perfect display
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BEFORE URL REWRITING - Response length: " . strlen($response) . "\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BEFORE URL REWRITING - First script tag: " . (preg_match('/<script[^>]*src="[^"]*"/i', $response, $matches) ? $matches[0] : 'NO SCRIPT TAG FOUND') . "\n", FILE_APPEND);
                
                $response = rewriteUrlsForProxy($response, 'lms', $username, $baseUrl, $proxyBaseUrl, $subplatformName);
                
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AFTER URL REWRITING - Response length: " . strlen($response) . "\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AFTER URL REWRITING - First script tag: " . (preg_match('/<script[^>]*src="[^"]*"/i', $response, $matches) ? $matches[0] : 'NO SCRIPT TAG FOUND') . "\n", FILE_APPEND);
                
                $response = injectProxyJavaScript($response, 'lms', $username, $proxyBaseUrl, $subplatformName);
                
                // Final check before sending to browser
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Final response length: " . strlen($response) . " bytes\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Final response is valid UTF-8: " . (mb_check_encoding($response, 'UTF-8') ? 'YES' : 'NO') . "\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Final response first 200 chars: " . substr($response, 0, 200) . "\n", FILE_APPEND);
                
                // Clear any output buffers and set proper headers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Only set HTML content-type for HTML responses
                if (stripos($contentType, 'text/html') !== false) {
                    header('Content-Type: text/html; charset=UTF-8');
                }
                header('Content-Encoding: identity');
                header('Content-Length: ' . strlen($response));
                
                echo $response;
                return; // Exit function after sending HTML response
        }
        else if (stripos($contentType, 'text/css') !== false) {

                // Handle direct CSS file requests via the proxy
                // This part handles CSS files fetched directly by the browser via the proxy URL.
                // The font rewriting logic for content *within* these CSS files is here.
                $response_body = $body;

                $fontProxyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?endpoint=lms_font_proxy&username=' . urlencode($username) . '&subplatform=' . urlencode($subplatformName) . '&fontpath=';

                // Rewrite CSS url() paths for fonts within the CSS file content itself.
                // Enhanced pattern to match font.php URLs as well as traditional font files
                $response_body = preg_replace_callback(
                    '/url\(["\']?([^"\']*(?:\.(woff2?|ttf|eot|otf)|font\.php)[^"\']*)["\']?\)/i',
                    function($matches) use ($fontProxyUrl, $baseUrl) {

                            $fontUrl = trim($matches[1], '"\'');

                            // Don't rewrite if already contains our proxy endpoint or if it's already a malformed nested URL
                            if (strpos($fontUrl, 'endpoint=lms_font_proxy') !== false || strpos($fontUrl, 'api.php?endpoint=') !== false) {

                                    return $matches[0];
                                
            }

                            // Handle relative font.php URLs directly (before making them absolute)
                            if (strpos($fontUrl, 'font.php') !== false && strpos($fontUrl, 'http') !== 0) {

                                    // For relative font.php URLs like "/LMS/theme/font.php/..."
                                    if (strpos($fontUrl, '/LMS/') === 0) {
                                        // URL starts with /LMS/, extract everything after /LMS/
                                        $fontPath = substr($fontUrl, 5); // Remove "/LMS/" prefix
                                    } else {
                                        // Other relative font.php URLs
                                        $fontPath = ltrim($fontUrl, '/');
                                    }
                                    $encodedFontPath = urlencode($fontPath);
                                    $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath;
                                    return 'url("' . $finalFontProxyUrl . '")';
                                
            }

                            // If it's a relative URL, make it absolute
                            if (strpos($fontUrl, 'http') !== 0) {

                                    $fontUrl = $baseUrl . '/' . ltrim($fontUrl, '/');
                                
            }

                            // Handle font.php URLs specifically for all LMS platforms (absolute URLs)
                            if (strpos($fontUrl, 'font.php') !== false) {

                                    // Extract the font path from font.php URLs
                                    $fontPath = str_replace($baseUrl . '/', '', $fontUrl);
                                    $encodedFontPath = urlencode($fontPath);
                                    $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath;
                                    return 'url("' . $finalFontProxyUrl . '")';
                                
            }
                            
                            // Handle direct font URLs for any LMS platform
                            $allLmsHosts = ['lms1.final.edu.tr', 'lms2.final.edu.tr', 'lms3.final.edu.tr', 'lms4.final.edu.tr', 'lms5.final.edu.tr', 'lms6.final.edu.tr'];
                            foreach ($allLmsHosts as $lmsHost) {

                                    if (strpos($fontUrl, $lmsHost . '/LMS/theme/font.php') !== false) {

                                            $fontPath = preg_replace('/https?:\/\/' . preg_quote($lmsHost, '/') . '\/LMS\//', '', $fontUrl);
                                            $encodedFontPath = urlencode($fontPath);
                                            $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath;
                                            return 'url("' . $finalFontProxyUrl . '")';
                                        
                }
                                
            }
            // Handle any font URLs that contain the LMS domain
                    if (strpos($fontUrl, 'lms5.final.edu.tr') !== false && strpos($fontUrl, '.woff') !== false) {

                            $fontPath = str_replace('https://lms5.final.edu.tr/LMS/', '', $fontUrl);
                            $fontPath = str_replace('http://lms5.final.edu.tr/LMS/', '', $fontUrl);
                            $fontPath = str_replace('https://lms5.final.edu.tr/', '', $fontUrl);
                            $fontPath = str_replace('http://lms5.final.edu.tr/', '', $fontUrl);
                            $encodedFontPath = urlencode($fontPath);
                            $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath;
                            return 'url("' . $finalFontProxyUrl . '")';
                        
            }
                    
                    // Handle lms3.final.edu.tr domain fonts
                    if (strpos($fontUrl, 'lms3.final.edu.tr') !== false && strpos($fontUrl, '.woff') !== false) {

                            $fontPath = str_replace('https://lms3.final.edu.tr/LMS/', '', $fontUrl);
                            $fontPath = str_replace('http://lms3.final.edu.tr/LMS/', '', $fontUrl);
                            $fontPath = str_replace('https://lms3.final.edu.tr/', '', $fontUrl);
                            $fontPath = str_replace('http://lms3.final.edu.tr/', '', $fontUrl);
                            $encodedFontPath = urlencode($fontPath);
                            $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath;
                            return 'url("' . $finalFontProxyUrl . '")';
                        
            }

                            // Handle regular font URLs
                            $fontPath = str_replace($baseUrl . '/', '', $fontUrl);
                            $encodedFontPath = urlencode($fontPath);
                            $finalFontProxyUrl = $fontProxyUrl . $encodedFontPath; // Append the path

                            return 'url("' . $finalFontProxyUrl . '")';
                    },
                    $response_body
                );

                // Set proper headers for content
                // Disable all compression and buffering
                while (ob_get_level()) {
                    ob_end_clean();
                }
                // Only set HTML content-type if CSS wasn't detected
                if (!isset($contentType) || $contentType !== 'text/css') {
                    header('Content-Type: text/html; charset=UTF-8');
                }
                header('Content-Encoding: identity'); // Disable any server compression
                header('Transfer-Encoding: '); // Clear transfer encoding
                header('Content-Length: ' . strlen($response_body));
                
                // Log the first few bytes being sent to browser for debugging
                $logFile = __DIR__ . '/php_errors.log';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Sending to browser - Length: " . strlen($response_body) . " bytes\n", FILE_APPEND);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] First 200 chars being sent: " . substr($response_body, 0, 200) . "\n", FILE_APPEND);
                
                echo $response_body;

            
    }
    else {

                // For other content types (images, fonts served directly by the proxy endpoint, PDFs, etc.), send as-is.
                header('Content-Type: ' . ($contentType ?: 'text/plain'));
                header('Content-Encoding: identity'); // Disable any server compression
                
                echo $body;
            
    }
}
// --- Helper Function for LMS Sub-platform Authentication (Internal Use) ---
// This replicates the core logic of handleLmsSubplatformAuth but is designed to be called internally
// and returns structured data instead of echoing JSON. It also receives the $cookieFile path.
function authenticateToLmsSubplatformInternal($username, $subplatformName, $targetSubplatform, $credentials, $logFile, $cookieFile) {

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] authenticateToLmsSubplatformInternal called for: $subplatformName, user: $username\n", FILE_APPEND);

        $baseUrl = rtrim($targetSubplatform['url'], '/');
        $loginUrl = $baseUrl . '/' . ltrim($targetSubplatform['login_endpoint'], '/');


        // Step 1: Get the login page to extract the logintoken
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Fetching login page (Internal): $loginUrl\n", FILE_APPEND);
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
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Failed to fetch login page (Internal): $errorDetails\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Failed to fetch login page', 'details' => $errorDetails];
            
    }

        // Step 2: Extract the logintoken (try multiple patterns for different LMS versions)
        $logintoken = '';
        $loginTokenPatterns = [
            '/<input[^>]*name="logintoken"[^>]*value="([^"]*)"/i',
            '/<input[^>]*value="([^"]*)"[^>]*name="logintoken"/i',
            '/name="logintoken"[^>]*value="([^"]*)"/i',
            '/value="([^"]*)"[^>]*name="logintoken"/i',
            '/"logintoken"\s*:\s*"([^"]*)"/i', // JSON format
            '/logintoken[\'"]?\s*:\s*[\'"]([^\'"]*)[\'"]/', // JS object format
        ];
        
        foreach ($loginTokenPatterns as $pattern) {

                if (preg_match($pattern, $loginPageResponse, $matches)) {

                        $logintoken = $matches[1];
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted logintoken (Internal): $logintoken\n", FILE_APPEND);
                        break;
                    
        }
            
    }
        
        if (empty($logintoken)) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Warning: Could not find logintoken in login page (Internal). Proceeding without it.\n", FILE_APPEND);
            
    }

        // Step 3: Perform the login
        $loginData = [
            'username' => $credentials['platform_username'],
            'password' => $credentials['platform_password']
            // 'anchor' => '',
        ];
        if ($logintoken) {

                $loginData['logintoken'] = $logintoken;
            
    }

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Performing login (Internal) to: $loginUrl\n", FILE_APPEND);
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // Important
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    // Use cookies
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    // Update cookie jar
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: ' . $loginUrl
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loginFinalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] cURL Error during login (Internal): $curlError\n", FILE_APPEND);
                return ['success' => false, 'error' => 'cURL Error during login', 'details' => $curlError];
            
    }

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Login response HTTP Code (Internal): $loginHttpCode, Final URL: $loginFinalUrl\n", FILE_APPEND);

        // --- Improved Success Check Logic (Same as in handleLmsSubplatformAuth) ---
        $authenticated = false;
        $loginPageUrlPattern = '/[\/\\\\]login[\/\\\\]/i';

        if ($loginHttpCode >= 200 && $loginHttpCode < 400) {

                if (!preg_match($loginPageUrlPattern, $loginFinalUrl)) {

                        $authenticated = true;
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication successful (Internal) - Redirected away from login pattern (Final URL: $loginFinalUrl)\n", FILE_APPEND);
                    
        }
        else if (preg_match($loginPageUrlPattern, $loginFinalUrl) && strpos($loginFinalUrl, 'index.php') !== false) {

                        $authenticated = false;
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication failed (Internal) - Still on login/index.php (Final URL: $loginFinalUrl)\n", FILE_APPEND);
                    
        }
        else {

                        if (stripos($loginResponse, 'invalid') !== false ||
                            stripos($loginResponse, 'incorrect') !== false ||
                            stripos($loginResponse, 'failure') !== false ||
                            (stripos($loginResponse, 'error') !== false && stripos($loginResponse, 'yui') === false && stripos($loginResponse, 'Error') === false)
                           ) {

                                $authenticated = false;
                                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication failed (Internal) - Found strong failure indicators in response body\n", FILE_APPEND);
                            
            }
            else {

                                if (stripos($loginResponse, 'dashboard') !== false ||
                                    stripos($loginResponse, 'my/') !== false ||
                                    stripos($loginResponse, 'logout') !== false ||
                                    stripos($loginResponse, 'home') !== false) {

                                         $authenticated = true;
                                         file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication successful (Internal) - Found success indicators in response body (fallback)\n", FILE_APPEND);
                                    
                }
                else {

                                         file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication status ambiguous (Internal), defaulting based on URL check (likely failure).\n", FILE_APPEND);
                                    
                }
                            
            }
                    
        }
            
    }
    else {

                $authenticated = false;
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Authentication failed (Internal) - HTTP Error $loginHttpCode\n", FILE_APPEND);
            
    }
        // --- End of Improved Success Check ---

        if ($authenticated) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Sub-platform authentication successful (Internal) for: $subplatformName, user: $username\n", FILE_APPEND);
                
                // Step 2.5: Visit dashboard to establish session properly
                $dashboardUrl = $baseUrl . '/my/';
                $ch = curl_init($dashboardUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Connection: keep-alive',
                    'Upgrade-Insecure-Requests: 1',
                    'Referer: ' . $baseUrl . '/login/index.php'
                ]);
                
                // Reduced delay to prevent timeout
                usleep(500000);
        // 0.5 second delay
                
            /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
                $dashboardResponse = curl_exec($ch);
                $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Visited dashboard for $subplatformName to establish session (HTTP: $dashboardHttpCode)\n", FILE_APPEND);
                
                // Check if cookie file exists and has content
                if (file_exists($cookieFile)) {

                        $cookieContent = file_get_contents($cookieFile);
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Cookie file exists for $subplatformName, size: " . strlen($cookieContent) . " bytes\n", FILE_APPEND);
                    
        }
        else {

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] WARNING: Cookie file does not exist for $subplatformName\n", FILE_APPEND);
                    
        }
                
                // Verify session is established by checking if we're still getting login page
                if (strpos($dashboardResponse, 'Log in to the site') !== false) {

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] WARNING: Dashboard still shows login page for $subplatformName - session not established\n", FILE_APPEND);
                    
        }
        else {

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] SUCCESS: Dashboard shows authenticated content for $subplatformName\n", FILE_APPEND);
                    
        }
                
                return ['success' => true, 'message' => 'Authentication successful', 'dashboardResponse' => $dashboardResponse];
            
    }
    else {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LMS Sub-platform authentication FAILED (Internal) for: $subplatformName, user: $username (HTTP: $loginHttpCode, Final URL: $loginFinalUrl)\n", FILE_APPEND);
                $responsePreview = substr(strip_tags($loginResponse), 0, 500);
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Login response preview (Internal): $responsePreview...\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Login failed or session check failed', 'details' => ['http_code' => $loginHttpCode, 'final_url' => $loginFinalUrl]];
            
    }
}
/**
 * Handle LMS AJAX service requests (for CORS issues)
 * This function handles AJAX service calls from LMS sub-platforms
 */
function handleAnalyzeLmsSubplatforms() {

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
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['username']) || !isset($input['password'])) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Username and password are required',
                    'message' => 'You must provide both a username and a password.'
                ]);
                return;
            
    }
}
function handleLmsAjaxService() {

        global $method, $lms_subplatforms;

        $subplatformName = isset($_GET['subplatform']) ? urldecode($_GET['subplatform']) : '';
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $path = isset($_GET['path']) ? $_GET['path'] : '';

        if (!$subplatformName || !$username || !$path) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Sub-platform name, username, and path are required',
                    'message' => 'You must provide a sub-platform name, username, and path.'
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

        $baseUrl = rtrim($targetSubplatform['url'], '/');
        
        // Debug logging - Remove after testing
        $originalPath = $path;
        
        // Remove leading LMS/ from path if baseUrl already ends with /LMS
        if (substr($baseUrl, -4) === '/LMS' && strpos($path, 'LMS/') === 0) {

                $path = substr($path, 4);
        // Remove 'LMS/' from the beginning
                error_log("LMS Ajax Service URL Fix: '$originalPath' -> '$path' for baseUrl: '$baseUrl'");
            
    }
        
        $targetUrl = $baseUrl . '/' . ltrim($path, '/');

        // Get LMS credentials
        $credentials = get_platform_credentials($username, 'LMS');
        if (!$credentials) {

                http_response_code(401);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'LMS credentials not found for user',
                    'message' => 'LMS credentials not found for this user.'
                ]);
                return;
            
    }

        // Use consistent cookie file path
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cookieFile = __DIR__ . '/../cookies/' . $username . '_' . $host . '.txt';

        // Create cookies directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../cookies')) {

                mkdir(__DIR__ . '/../cookies', 0755, true);
            
    }

        $logFile = __DIR__ . '/php_errors.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX Service request: $targetUrl\n", FILE_APPEND);

        // Set up cURL for the AJAX request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        // Handle POST data if this is a POST request
        if ($method === 'POST') {

                curl_setopt($ch, CURLOPT_POST, true);
            /* PARSED INPUT: Reads raw request body (usually JSON). Validate before using. */
                $postData = file_get_contents('php://input');
                if ($postData) {

                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/x-www-form-urlencoded',
                            'Content-Length: ' . strlen($postData)
                        ]);
                    
        }
            
    }

        // Set appropriate headers for AJAX requests
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
            curl_getinfo($ch, CURLINFO_HEADER_OUT) ? [] : [],
            [
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Cache-Control: no-cache'
            ]
        ));

        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($response === false) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX Service request failed: " . curl_error($ch) . "\n", FILE_APPEND);
                http_response_code(500);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode([
                    'error' => 'Failed to fetch from LMS sub-platform',
                    'message' => 'The request to the LMS sub-platform failed.'
                ]);
                return;
            
    }

        // Extract headers and body
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Set appropriate response code
        http_response_code($httpCode);

        // Forward content type
        if ($contentType) {

                header('Content-Type: ' . $contentType);
            
    }

        // Forward other important headers
        $headerLines = explode("\r\n", $headers);
        foreach ($headerLines as $header) {

                if (preg_match('/^(Set-Cookie|Cache-Control|Expires|Last-Modified):/i', $header)) {

                        header($header);
                    
        }
            
    }

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX Service response: HTTP $httpCode, Content-Type: $contentType\n", FILE_APPEND);

        echo $body;
}

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
function validateLmsSession($response, $subplatformName, $logFile) {

        // Check for clear login indicators across all LMS platforms
        $loginIndicators = [
            'log in to the site',
            'you are not logged in',
            'please log in',
            'authentication required',
            'invalid login',
            'login failed',
            'session expired',
            'access denied'
        ];
        
        $responseText = strtolower(strip_tags($response));
        
        foreach ($loginIndicators as $indicator) {

                if (strpos($responseText, $indicator) !== false) {

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Session invalid for $subplatformName - Found indicator: $indicator\n", FILE_APPEND);
                        return false;
                    
        }
            
    }
        
        // Check for login forms (indicates not authenticated)
        if (preg_match('/<form[^>]*[^>]*>/i', $response) && 
            (strpos($responseText, 'username') !== false || strpos($responseText, 'password') !== false) &&
            strpos($responseText, 'login') !== false) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Session invalid for $subplatformName - Found login form\n", FILE_APPEND);
                return false;
            
    }
        
        // Check for positive indicators of successful authentication
        $successIndicators = [
            'dashboard',
            'logout',
            'my courses',
            'notifications',
            'user menu',
            'profile',
            'settings'
        ];
        
        $hasSuccessIndicator = false;
        foreach ($successIndicators as $indicator) {

                if (strpos($responseText, $indicator) !== false) {

                        $hasSuccessIndicator = true;
                        break;
                    
        }
            
    }
        
        if ($hasSuccessIndicator) {

                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Session valid for $subplatformName - Found success indicators\n", FILE_APPEND);
                return true;
            
    }
        
        // If no clear indicators, assume valid but log uncertainty
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Session status uncertain for $subplatformName - Assuming valid\n", FILE_APPEND);
        return true;
}

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
        
        $credentials = get_platform_credentials($username, 'Leave and Absence');
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
function getLeavePortalJavaScript() {

        global $method;
        
        if ($method !== 'GET') {

                http_response_code(405);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode(['error' => 'Method not allowed']);
                return;
            
    }
        
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        if (!$username) {

                http_response_code(400);
            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode(['error' => 'Username is required']);
                return;
            
    }
        
        $credentials = get_platform_credentials($username, 'Leave and Absence');
        if (!$credentials) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode(['success' => false, 'message' => 'No credentials found']);
                return;
            
    }
        
        $cookieFile = __DIR__ . '/../cookies/' . $credentials['platform_username'] . '_LeavePortal.txt';
        $baseUrl = 'https://leave.final.digital';
        
        // Fetch the notifications page
        $notificationUrl = $baseUrl . '/notifications/all_notifications.php';
        $ch = curl_init($notificationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        /* CURL EXECUTION: This sends HTTP requests to remote LMS endpoints. Ensure SSL verification is enabled and responses are validated. */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {

            /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
                echo json_encode(['success' => false, 'message' => "HTTP $httpCode"]);
                return;
            
    }
        
        // Extract all <script> blocks
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $response, $scriptMatches);
        $scripts = $scriptMatches[1];
        
        // Extract inline event handlers
        /* PREG MATCHES: Used to extract scripts/event handlers from HTML responses. Ensure patterns are correct and safe. */
        preg_match_all('/on\w+\s*=\s*[\'"`]([^\'"`]+)[\'"`]/i', $response, $eventMatches);
        $eventHandlers = $eventMatches[1];
        
        /* JSON ENCODING: Ensure UTF-8 and correct headers are set (Content-Type: application/json). */
        echo json_encode([
            'success' => true,
            'javascript_blocks' => $scripts,
            'event_handlers' => $eventHandlers,
            'page_url' => $notificationUrl
        ], JSON_PRETTY_PRINT);
}
