<?php
// common/config.php
ob_start();
session_start();

$host = "sql312.infinityfree.com";
$user = "if0_41044916";
$pass = "zp4FruXDP7HxkYW"; 
$db   = "if0_41044916_t";

// ✅ প্রথমে connection তৈরি করুন
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    if (strpos($_SERVER['SCRIPT_NAME'], 'install.php') === false) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// ✅ তারপর timezone সেট করুন
date_default_timezone_set('Asia/Dhaka');

// ✅ এখন Database timezone সেট করুন
$conn->query("SET time_zone = '+06:00'");

// config.php তে:
define('TRACK_VISITORS', true);

// Helper: Get Setting
function getSetting($conn, $key) {
    $res = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
    if($res && $res->num_rows > 0) return $res->fetch_assoc()['value'];
    return "";
}

// বাংলাদেশী সময় ফরম্যাট
function bd_time($format = 'd M, Y h:i A') {
    return date($format);
}

// এখনকার সময় MySQL ফরম্যাটে
function mysql_now() {
    return date('Y-m-d H:i:s');
}
?>