<?php
// common/header.php

session_start();

// Include config
$config_file = __DIR__ . '/config.php';
if(!file_exists($config_file)) {
    die("Configuration file missing!");
}
include $config_file;

// Check if IP is blocked BEFORE anything else
function checkBlockedIP($conn) {
    // Get visitor IP
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    $ips = explode(',', $ip);
    $ip = trim($ips[0]);
    
    // Skip check for localhost
    if($ip == '127.0.0.1' || $ip == '::1') {
        return;
    }
    
    // Check if IP is in blocked list
    $result = $conn->query("SELECT * FROM blocked_ips WHERE ip_address = '$ip'");
    if($result && $result->num_rows > 0) {
        // Only block if not admin
        if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            // Include visitor tracker to show blocked page
            $tracker_file = __DIR__ . '/visitor_tracker.php';
            if(file_exists($tracker_file)) {
                include $tracker_file;
                if(function_exists('showBlockedPage')) {
                    showBlockedPage($ip);
                }
            } else {
                die("Your IP ($ip) has been blocked.");
            }
        }
    }
}

// Run IP block check
checkBlockedIP($conn);

// Now do visitor tracking (for non-blocked IPs)
$current_url = $_SERVER['REQUEST_URI'] ?? '';
$is_admin_page = (strpos($current_url, '/admin/') !== false) || 
                 (strpos($current_url, '/sm/') !== false) ||
                 (strpos($current_url, 'admin') !== false);

// Only track visitors on non-admin pages
if(!$is_admin_page) {
    $tracker_file = __DIR__ . '/visitor_tracker.php';
    
    if(file_exists($tracker_file)) {
        include $tracker_file;
        
        if(function_exists('trackVisitor')) {
            trackVisitor($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo getSetting($conn, 'site_name'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; -webkit-tap-highlight-color: transparent; }
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
<?php include('loading.php'); ?>

<header class="glass-nav sticky top-0 z-[100] transition-all duration-300">
    <?php if(getSetting($conn, 'marquee_active') == '1'): ?>
    <div class="bg-gradient-to-r from-blue-700 to-indigo-600 text-white text-[11px] font-medium py-1.5 overflow-hidden relative z-[101]">
        <div class="whitespace-nowrap animate-[marquee_20s_linear_infinite]">
            <span class="px-4"><?php echo getSetting($conn, 'marquee_text'); ?></span>
        </div>
    </div>
    <style> @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } } </style>
    <?php endif; ?>

    <div class="container mx-auto px-3 py-2.5 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <button onclick="toggleUserSidebar()" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 lg:hidden active:scale-95 transition">
                <i class="fa-solid fa-bars-staggered text-lg"></i>
            </button>
            <a href="index.php" class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 tracking-tight">
                <?php echo getSetting($conn, 'site_name'); ?>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <?php if(isset($_SESSION['user_id'])): 
                $uid = $_SESSION['user_id'];
                $u_res = $conn->query("SELECT balance, name FROM users WHERE id=$uid");
                $u_data = $u_res->fetch_assoc();
            ?>
                <div class="bg-blue-50 border border-blue-100 rounded-full px-3 py-1 flex items-center gap-2 pr-1">
                    <div class="flex flex-col items-end leading-none">
                        <span class="text-[9px] text-gray-500 font-bold uppercase">Balance</span>
                        <span class="font-bold text-blue-700 text-sm"><?php echo getSetting($conn, 'currency').$u_data['balance']; ?></span>
                    </div>
                    <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-sm">
                        <i class="fa-solid fa-wallet text-xs"></i>
                    </div>
                </div>

                <a href="profile.php" class="w-9 h-9 rounded-full bg-gray-200 border-2 border-white shadow-md overflow-hidden relative ml-1">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u_data['name']); ?>&background=0D8ABC&color=fff" class="w-full h-full object-cover">
                </a>
            <?php else: ?>
                <a href="login.php" class="bg-blue-600 text-white px-5 py-2 rounded-full text-xs font-bold shadow-lg shadow-blue-200">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php include 'sidebar.php'; ?>