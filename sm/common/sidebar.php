  <aside id="adminSidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50 md:static md:block flex flex-col flex-shrink-0">
    <div class="p-6 font-bold text-2xl border-b border-gray-700 flex justify-between items-center">
        <span class="flex items-center gap-2">
            <i class="fa-solid fa-user-secret text-blue-500"></i> Admin
        </span>
        <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white focus:outline-none">
            <i class="fa-solid fa-times text-xl"></i>
        </button>
    </div>
    
    <nav class="flex-1 overflow-y-auto p-4 space-y-2">
        <p class="text-xs font-bold text-gray-500 uppercase px-3 mb-1">Main</p>
        <a href="index.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-blue-600 transition <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'bg-blue-600':''; ?>">
            <i class="fa-solid fa-chart-pie w-5"></i> Dashboard
        </a>
        <a href="order.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-blue-600 transition <?php echo basename($_SERVER['PHP_SELF'])=='order.php'?'bg-blue-600':''; ?>">
            <i class="fa-solid fa-cart-shopping w-5"></i> Orders
        </a>
        
        <a href="addmoney_request.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-blue-600 transition <?php echo basename($_SERVER['PHP_SELF'])=='addmoney_request.php'?'bg-blue-600':''; ?>">
            <i class="fa-solid fa-money-bill-transfer w-5"></i> Add Money Req.
        </a>

        <p class="text-xs font-bold text-gray-500 uppercase px-3 mt-4 mb-1">Management</p>
        <a href="game.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='game.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-gamepad w-5"></i> Games
        </a>
        <a href="product.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='product.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-tags w-5"></i> Products
        </a>
        <a href="sliders.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='sliders.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-images w-5"></i> Sliders
        </a>
        <a href="redeemcode.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='redeemcode.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-ticket w-5"></i> Vouchers
        </a>

        <p class="text-xs font-bold text-gray-500 uppercase px-3 mt-4 mb-1">System</p>
        <a href="user.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='user.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-users w-5"></i> Users
        </a>
        <a href="paymentmethod.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='paymentmethod.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-wallet w-5"></i> Payments
        </a>
        <a href="setting.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-800 transition <?php echo basename($_SERVER['PHP_SELF'])=='setting.php'?'bg-slate-700':''; ?>">
            <i class="fa-solid fa-gears w-5"></i> Settings
        </a>
    </nav>
   <!-- admin sidebar/menu তে: -->
<li>
    <a href="visitors.php" class="flex items-center gap-3 p-3 rounded hover:bg-gray-100">
        <i class="fa-solid fa-users"></i>
        <span>Visitors</span>
        <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
            <?php 
            $newToday = $conn->query("SELECT COUNT(*) as new FROM visitors WHERE DATE(last_visit) = CURDATE()")->fetch_assoc()['new'];
            echo $newToday;
            ?>
        </span>
    </a>
</li>
    <div class="p-4 border-t border-gray-700">
        <a href="../index.php" target="_blank" class="block bg-slate-800 text-center py-2 rounded text-sm hover:bg-slate-700 text-gray-300">Visit Site</a>
    </div>
</aside>

<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

<script>
    function toggleSidebar() {
        const sb = document.getElementById('adminSidebar');
        const ov = document.getElementById('sidebarOverlay');
        
        if (sb.classList.contains('-translate-x-full')) {
            sb.classList.remove('-translate-x-full');
            ov.classList.remove('hidden');
        } else {
            sb.classList.add('-translate-x-full');
            ov.classList.add('hidden');
        }
    }
</script>
