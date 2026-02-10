<?php
// sm/visitors.php

session_start();

include '../common/config.php';

// Database connection
if(!isset($conn)) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}



// First, let's define the necessary functions if they don't exist
if(!function_exists('blockIP')) {
    function blockIP($conn, $ip, $reason = '') {
        $admin = $_SESSION['username'] ?? 'System';
        $conn->query("INSERT INTO blocked_ips (ip_address, reason, blocked_by) 
                      VALUES ('$ip', '$reason', '$admin') 
                      ON DUPLICATE KEY UPDATE reason='$reason', blocked_by='$admin'");
        
        $conn->query("UPDATE visitors SET is_blocked=1 WHERE ip_address='$ip'");
        return true;
    }
}

if(!function_exists('unblockIP')) {
    function unblockIP($conn, $ip) {
        $conn->query("DELETE FROM blocked_ips WHERE ip_address='$ip'");
        $conn->query("UPDATE visitors SET is_blocked=0 WHERE ip_address='$ip'");
        return true;
    }
}

// Process actions
if(isset($_GET['block'])) {
    $ip = $_GET['block'];
    $reason = $_GET['reason'] ?? 'Manual block by admin';
    
    blockIP($conn, $ip, $reason);
    echo "<script>alert('IP blocked successfully'); window.location='visitors.php';</script>";
}

if(isset($_GET['unblock'])) {
    $ip = $_GET['unblock'];
    unblockIP($conn, $ip);
    echo "<script>alert('IP unblocked successfully'); window.location='visitors.php';</script>";
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM visitors WHERE id=$id");
    echo "<script>alert('Record deleted'); window.location='visitors.php';</script>";
}

// Search functionality
$search = '';
$where = '1=1';
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where = "(ip_address LIKE '%$search%' OR country LIKE '%$search%' OR city LIKE '%$search%' OR isp LIKE '%$search%')";
}

// Get statistics
$totalVisitors = $conn->query("SELECT COUNT(*) as total FROM visitors")->fetch_assoc()['total'];
$uniqueVisitors = $conn->query("SELECT COUNT(DISTINCT ip_address) as unique_visits FROM visitors")->fetch_assoc()['unique_visits'];
$blockedCount = $conn->query("SELECT COUNT(*) as blocked FROM visitors WHERE is_blocked=1")->fetch_assoc()['blocked'];
$todayVisitors = $conn->query("SELECT COUNT(*) as today FROM visitors WHERE DATE(last_visit) = CURDATE()")->fetch_assoc()['today'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get visitors
$visitors = $conn->query("SELECT * FROM visitors WHERE $where ORDER BY last_visit DESC LIMIT $limit OFFSET $offset");
$totalRows = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE $where")->fetch_assoc()['count'];
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f3f4f6; font-family: 'Segoe UI', system-ui, sans-serif; }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .ip-cell { font-family: 'Courier New', monospace; }
        .table-row:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen">
    
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Visitor Management</h1>
            <p class="text-gray-600 mt-1">Track and manage website visitors</p>
        </div>
        <div class="flex gap-3">
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-blue-600"><?php echo $totalVisitors; ?></div>
                    <div class="text-sm text-gray-600 mt-1">Total Visits</div>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-green-600"><?php echo $uniqueVisitors; ?></div>
                    <div class="text-sm text-gray-600 mt-1">Unique Visitors</div>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-yellow-600"><?php echo $todayVisitors; ?></div>
                    <div class="text-sm text-gray-600 mt-1">Today's Visits</div>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-red-600"><?php echo $blockedCount; ?></div>
                    <div class="text-sm text-gray-600 mt-1">Blocked IPs</div>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-ban text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Action Bar -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <!-- Search Box -->
            <div class="flex-1">
                <form method="GET" class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="search" placeholder="Search by IP, Country, City, ISP..." 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                        Search
                    </button>
                    <?php if($search): ?>
                    <a href="visitors.php" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-medium">
                        Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Manual Block Form -->
            <div class="lg:w-2/3">
                <form method="GET" class="flex gap-2">
                    <div class="flex-1">
                        <input type="text" name="ip" placeholder="Enter IP address to block (e.g., 192.168.1.1)" 
                               class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               required>
                    </div>
                    <input type="text" name="reason" placeholder="Reason (optional)" 
                           class="w-1/3 border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <button type="submit" name="block" value="1" 
                            class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i> Block IP
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Visitors Table -->
    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Visitor Records</h2>
            <p class="text-sm text-gray-600">Total <?php echo $totalRows; ?> records found</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">IP Address</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Device Info</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Visits</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">First Visit</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Visit</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if($visitors->num_rows > 0): ?>
                        <?php while($visitor = $visitors->fetch_assoc()): 
                            $isBlocked = $visitor['is_blocked'] == 1;
                            $country = $visitor['country'] ?? 'Unknown';
                            $city = $visitor['city'] ?? 'Unknown';
                        ?>
                        <tr class="table-row <?php echo $isBlocked ? 'bg-red-50' : ''; ?>">
                            <td class="p-4">
                                <div class="ip-cell bg-gray-100 px-3 py-2 rounded text-sm font-medium <?php echo $isBlocked ? 'text-red-600' : 'text-gray-800'; ?>">
                                    <?php echo htmlspecialchars($visitor['ip_address']); ?>
                                </div>
                                <?php if(!empty($visitor['isp']) && $visitor['isp'] != 'Unknown'): ?>
                                <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                    <i class="fa-solid fa-network-wired mr-1"></i> <?php echo htmlspecialchars($visitor['isp']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <?php if($country != 'Unknown'): ?>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-4 bg-gray-200 rounded overflow-hidden">
                                        <!-- Country flag would go here -->
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($country); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($city); ?></div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-gray-400 italic">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-<?php echo strtolower($visitor['device_type'] == 'Mobile' ? 'mobile-screen' : 'desktop'); ?> text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($visitor['browser']); ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($visitor['device_type']); ?> • <?php echo htmlspecialchars($visitor['platform']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                                        <?php echo $visitor['visit_count']; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-gray-600">
                                <?php echo date('d M, Y', strtotime($visitor['first_visit'])); ?><br>
                                <span class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($visitor['first_visit'])); ?></span>
                            </td>
                            <td class="p-4 text-sm text-gray-600">
                                <?php echo date('d M, Y', strtotime($visitor['last_visit'])); ?><br>
                                <span class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($visitor['last_visit'])); ?></span>
                            </td>
                            <td class="p-4">
                                <?php if($isBlocked): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    <i class="fa-solid fa-ban mr-1"></i> BLOCKED
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check mr-1"></i> ACTIVE
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    <?php if(!$isBlocked): ?>
                                    <a href="?block=<?php echo urlencode($visitor['ip_address']); ?>&reason=Manual%20Block" 
                                       onclick="return confirm('Block IP address <?php echo $visitor['ip_address']; ?>?')"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition text-xs font-medium">
                                        <i class="fa-solid fa-ban"></i> Block
                                    </a>
                                    <?php else: ?>
                                    <a href="?unblock=<?php echo urlencode($visitor['ip_address']); ?>" 
                                       onclick="return confirm('Unblock IP address <?php echo $visitor['ip_address']; ?>?')"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition text-xs font-medium">
                                        <i class="fa-solid fa-check"></i> Unblock
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="?delete=<?php echo $visitor['id']; ?>" 
                                       onclick="return confirm('Delete this visitor record?')"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-xs font-medium">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    
                                    <button onclick="showVisitorDetails(<?php echo htmlspecialchars(json_encode($visitor), ENT_QUOTES, 'UTF-8'); ?>)" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-xs font-medium">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-users-slash text-5xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">No visitors found</h3>
                                    <p class="text-gray-500 max-w-md">
                                        <?php echo $search ? 'No visitors match your search criteria.' : 'Visitor tracking data will appear here once users visit your site.'; ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-600">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?> • 
                Showing <?php echo min($limit, $visitors->num_rows); ?> of <?php echo $totalRows; ?> records
            </div>
            <div class="flex items-center gap-1">
                <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    <i class="fa-solid fa-chevron-left"></i> Previous
                </a>
                <?php endif; ?>
                
                <div class="flex items-center gap-1">
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    if($start > 1): ?>
                    <span class="px-3 py-2 text-gray-500">...</span>
                    <?php endif; ?>
                    
                    <?php for($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if($end < $totalPages): ?>
                    <span class="px-3 py-2 text-gray-500">...</span>
                    <?php endif; ?>
                </div>
                
                <?php if($page < $totalPages): ?>
                <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Next <i class="fa-solid fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Visitor Details</h3>
            <button onclick="closeDetails()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]" id="modalContent">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<script>
function showVisitorDetails(visitor) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    
    // Format dates
    const firstVisit = new Date(visitor.first_visit).toLocaleString();
    const lastVisit = new Date(visitor.last_visit).toLocaleString();
    
    const html = `
        <div class="space-y-6">
            <!-- IP Section -->
            <div>
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">IP Information</h4>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="font-mono text-xl font-bold text-gray-800">${visitor.ip_address}</div>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600 text-sm">ISP:</span>
                            <div class="font-medium">${visitor.isp || 'Unknown'}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 text-sm">Status:</span>
                            <div>
                                ${visitor.is_blocked == 1 ? 
                                    '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-800">BLOCKED</span>' : 
                                    '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800">ACTIVE</span>'
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Location Section -->
            <div>
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Location</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">Country</span>
                        <div class="font-medium">${visitor.country || 'Unknown'}</div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">City</span>
                        <div class="font-medium">${visitor.city || 'Unknown'}</div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">Region</span>
                        <div class="font-medium">${visitor.region || 'Unknown'}</div>
                    </div>
                </div>
            </div>
            
            <!-- Device Section -->
            <div>
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Device Information</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">Device Type</span>
                        <div class="font-medium flex items-center gap-2">
                            <i class="fa-solid fa-${visitor.device_type == 'Mobile' ? 'mobile-screen' : 'desktop'} text-blue-500"></i>
                            ${visitor.device_type}
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">Browser</span>
                        <div class="font-medium">${visitor.browser}</div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">Platform</span>
                        <div class="font-medium">${visitor.platform}</div>
                    </div>
                </div>
            </div>
            
            <!-- Visit Stats -->
            <div>
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Visit Statistics</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">${visitor.visit_count}</div>
                        <div class="text-sm text-gray-600">Total Visits</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-sm font-bold text-green-600">${firstVisit}</div>
                        <div class="text-sm text-gray-600">First Visit</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <div class="text-sm font-bold text-purple-600">${lastVisit}</div>
                        <div class="text-sm text-gray-600">Last Visit</div>
                    </div>
                </div>
            </div>
            
            <!-- Technical Details -->
            <div>
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Technical Details</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-600 text-sm block mb-1">User Agent</span>
                        <div class="bg-gray-100 p-3 rounded text-xs font-mono overflow-x-auto">
                            ${visitor.user_agent}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600 text-sm block mb-1">Referrer</span>
                            <div class="font-medium truncate">${visitor.referrer}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 text-sm block mb-1">Last Page</span>
                            <div class="font-medium truncate">${visitor.page_url}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="pt-4 border-t border-gray-200 flex gap-3">
                ${visitor.is_blocked == 0 ? 
                    `<a href="?block=${visitor.ip_address}&reason=Blocked+from+details" 
                       class="flex-1 bg-red-600 text-white py-3 rounded-lg text-center font-medium hover:bg-red-700 transition">
                        <i class="fa-solid fa-ban mr-2"></i> Block This IP
                    </a>` :
                    `<a href="?unblock=${visitor.ip_address}" 
                       class="flex-1 bg-green-600 text-white py-3 rounded-lg text-center font-medium hover:bg-green-700 transition">
                        <i class="fa-solid fa-check mr-2"></i> Unblock This IP
                    </a>`
                }
                <a href="?delete=${visitor.id}" 
                   class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg text-center font-medium hover:bg-gray-300 transition">
                    <i class="fa-solid fa-trash mr-2"></i> Delete Record
                </a>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDetails() {
    const modal = document.getElementById('detailsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('detailsModal').addEventListener('click', function(e) {
    if(e.target === this) {
        closeDetails();
    }
});

// Auto-refresh page every 60 seconds
setTimeout(function() {
    if(confirm('Refresh visitor data?')) {
        window.location.reload();
    }
}, 60000);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') {
        closeDetails();
    }
});
</script>

</body>
</html>