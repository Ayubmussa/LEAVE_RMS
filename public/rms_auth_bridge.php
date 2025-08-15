<?php
/**
 * RMS Authentication Bridge (Public)
 * - Fetches stored RMS credentials for the given user
 * - Performs client-side POST to RMS for both dashboard and notifications
 * - Uses JavaScript redirect for notifications after authentication
 */

require_once '../database/db_connection.php';

$username = $_GET['username'] ?? '';
$type = $_GET['type'] ?? 'dashboard';
$targetPage = $_GET['page'] ?? ($type === 'notifications' ? 'Dashboard/notifications.php' : 'Dashboard/home.php');

$logFile = __DIR__ . '/../database/php_errors.log';

if (!$username) {
    http_response_code(400);
    echo 'Username is required';
    exit;
}

$credentials = get_platform_credentials($username, 'RMS');
if (!$credentials || empty($credentials['platform_username']) || empty($credentials['platform_password'])) {
    http_response_code(404);
    echo 'No RMS credentials found';
    exit;
}

// Build RMS login endpoint
$baseUrl = 'https://rms.final.digital';
$loginUrl = $baseUrl . '/index.php';
$actionUrl = $loginUrl;

$isNotification = ($type === 'notifications');

file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] RMS Bridge (client POST) for user: $username, type: $type, action: $actionUrl", FILE_APPEND);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Authenticating to RMS...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 48px; background: #f5f5f5; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 16px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .card { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .hint { color: #666; font-size: 14px; margin-top: 8px; }
        .button { display: inline-block; margin-top: 16px; padding: 10px 16px; background: #3498db; color: #fff; border-radius: 6px; text-decoration: none; }
        .button:hover { background: #2d83bd; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Authenticating to RMS...</h2>
        <div class="spinner"></div>
        <p class="hint">We are securely signing you in to RMS.</p>
        <noscript>
            <p class="hint">JavaScript is required. Click the button below to continue.</p>
        </noscript>
        <form id="rmsLoginForm" method="POST" action="<?php echo htmlspecialchars($actionUrl, ENT_QUOTES); ?>">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($credentials['platform_username'], ENT_QUOTES); ?>" />
            <input type="hidden" name="password_hash" value="<?php echo htmlspecialchars($credentials['platform_password'], ENT_QUOTES); ?>" />
            <button class="button" type="submit">Continue</button>
        </form>
        <p class="hint">If you're not redirected automatically, click Continue.</p>
    </div>
    <script>
        <?php if ($isNotification): ?>
        // For notifications: Use iframe for authentication, then redirect main window
        (function(){
            // Create hidden iframe for authentication
            var iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.name = 'authFrame';
            document.body.appendChild(iframe);
            
            // Set form target to iframe
            document.getElementById('rmsLoginForm').target = 'authFrame';
            
            // Submit form to iframe
            document.getElementById('rmsLoginForm').submit();
            
            // After a delay, redirect main window to notifications
            setTimeout(function() {
                window.location.href = '<?php echo $baseUrl . '/' . $targetPage; ?>';
            }, 1500);
        })();
        <?php else: ?>
        // For dashboard: Direct form submission
        (function(){
            try {
                document.getElementById('rmsLoginForm').submit();
            } catch (e) {
                // Ignore; manual submission fallback via button
            }
        })();
        <?php endif; ?>
    </script>
    

</body>
</html>
