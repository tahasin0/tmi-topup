<?php
// common/visitor_tracker.php

function getVisitorIP() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    // Handle multiple IPs (if proxied)
    $ips = explode(',', $ip);
    return trim($ips[0]);
}

function isIPBlocked($conn, $ip) {
    // Check in blocked_ips table
    $result = $conn->query("SELECT * FROM blocked_ips WHERE ip_address = '$ip'");
    return ($result && $result->num_rows > 0);
}

function blockIP($conn, $ip, $reason = '') {
    $admin = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
    
    // Insert into blocked_ips
    $conn->query("INSERT INTO blocked_ips (ip_address, reason, blocked_by) 
                  VALUES ('$ip', '$reason', '$admin') 
                  ON DUPLICATE KEY UPDATE reason='$reason', blocked_by='$admin'");
    
    // Update visitors table
    $conn->query("UPDATE visitors SET is_blocked=1 WHERE ip_address='$ip'");
    
    return true;
}

function unblockIP($conn, $ip) {
    $conn->query("DELETE FROM blocked_ips WHERE ip_address='$ip'");
    $conn->query("UPDATE visitors SET is_blocked=0 WHERE ip_address='$ip'");
    return true;
}

function trackVisitor($conn) {
    $ip = getVisitorIP();
    
    // Skip localhost
    if($ip == '127.0.0.1' || $ip == '::1') {
        return;
    }
    
    // Check if IP is blocked
    if(isIPBlocked($conn, $ip)) {
        // Redirect to blocked page or show message
        if(!isset($_SESSION['admin_logged_in'])) {
            showBlockedPage($ip);
            exit();
        }
        return;
    }
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
    $pageUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    // Device detection
    $device = 'Desktop';
    $browser = 'Unknown';
    $platform = 'Unknown';
    
    if(preg_match('/(android|iphone|ipad)/i', $userAgent)) {
        $device = 'Mobile';
    }
    
    if(preg_match('/chrome/i', $userAgent)) {
        $browser = 'Chrome';
    } elseif(preg_match('/firefox/i', $userAgent)) {
        $browser = 'Firefox';
    } elseif(preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
        $browser = 'Safari';
    } elseif(preg_match('/edge/i', $userAgent)) {
        $browser = 'Edge';
    } elseif(preg_match('/opera/i', $userAgent)) {
        $browser = 'Opera';
    }
    
    if(preg_match('/windows/i', $userAgent)) {
        $platform = 'Windows';
    } elseif(preg_match('/macintosh|mac os/i', $userAgent)) {
        $platform = 'Mac';
    } elseif(preg_match('/linux/i', $userAgent)) {
        $platform = 'Linux';
    } elseif(preg_match('/android/i', $userAgent)) {
        $platform = 'Android';
    } elseif(preg_match('/iphone|ipad/i', $userAgent)) {
        $platform = 'iOS';
    }
    
    // Check existing visitor
    $existing = $conn->query("SELECT * FROM visitors WHERE ip_address='$ip'");
    
    if($existing && $existing->num_rows > 0) {
        $visitor = $existing->fetch_assoc();
        $visitCount = $visitor['visit_count'] + 1;
        
        $conn->query("UPDATE visitors SET 
            visit_count = $visitCount,
            last_visit = NOW(),
            user_agent = '$userAgent',
            referrer = '$referrer',
            page_url = '$pageUrl',
            browser = '$browser',
            device_type = '$device',
            platform = '$platform'
            WHERE ip_address='$ip'");
    } else {
        $conn->query("INSERT INTO visitors 
            (ip_address, user_agent, referrer, page_url, browser, device_type, platform) 
            VALUES 
            ('$ip', '$userAgent', '$referrer', '$pageUrl', '$browser', '$device', '$platform')");
    }
}

function showBlockedPage($ip) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        </style>
    </head>
    <body class="flex items-center justify-center p-4">
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-ban text-3xl text-red-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Access Denied</h1>
            <p class="text-gray-600 mb-6">
                Your IP address <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($ip); ?></code> 
                has been blocked from accessing this website.
            </p>
            <div class="bg-red-50 border border-red-100 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-700">
                    <i class="fa-solid fa-circle-info mr-2"></i>
                    If you believe this is a mistake, please contact the website administrator.
                </p>
            </div>
            <a href="javascript:history.back()" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Go Back
            </a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>