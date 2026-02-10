<div id="userSidebar" class="fixed inset-y-0 left-0 w-72 bg-white/95 backdrop-blur-xl shadow-2xl transform -translate-x-full transition-transform duration-300 z-[200] lg:hidden flex flex-col border-r border-gray-100">
    
    <div class="h-32 bg-gradient-to-br from-blue-600 to-purple-700 p-6 flex flex-col justify-end relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10 blur-2xl"></div>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <h2 class="text-white font-bold text-xl relative z-10">Hi, <?php echo $u_data['name']; ?> 👋</h2>
            <p class="text-blue-100 text-xs relative z-10">Welcome back!</p>
        <?php else: ?>
            <h2 class="text-white font-bold text-xl relative z-10">Guest User</h2>
            <a href="login.php" class="text-white/80 text-xs underline relative z-10">Tap to Login</a>
        <?php endif; ?>
        
        <button onclick="toggleUserSidebar()" class="absolute top-4 right-4 text-white/70 hover:text-white bg-white/10 w-8 h-8 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>

    <nav class="p-4 space-y-1 overflow-y-auto flex-1">
        <?php 
        $menuItems = [
            ['link'=>'index.php', 'icon'=>'fa-house', 'text'=>'Home'],
            ['link'=>'addmoney.php', 'icon'=>'fa-wallet', 'text'=>'Add Money'],
            ['link'=>'order.php', 'icon'=>'fa-box-open', 'text'=>'My Orders'],
            ['link'=>'mycode.php', 'icon'=>'fa-ticket', 'text'=>'My Codes'],
            ['link'=>'profile.php', 'icon'=>'fa-user-gear', 'text'=>'Profile Settings'],
            ['link'=>'logout.php', 'icon'=>'fa-right-from-bracket', 'text'=>'Logout', 'color'=>'text-red-500']
        ];
        
        foreach($menuItems as $item): 
            $active = basename($_SERVER['PHP_SELF']) == $item['link'] ? 'bg-blue-50 text-blue-600 border-blue-200' : 'hover:bg-gray-50 text-gray-600 border-transparent';
            $iconColor = isset($item['color']) ? $item['color'] : 'text-gray-400';
        ?>
        <a href="<?php echo $item['link']; ?>" class="flex items-center gap-4 p-3.5 rounded-xl border transition-all duration-200 <?php echo $active; ?>">
            <i class="fa-solid <?php echo $item['icon']; ?> w-6 text-center <?php echo $active ? 'text-blue-600' : $iconColor; ?>"></i>
            <span class="font-medium text-sm"><?php echo $item['text']; ?></span>
            <i class="fa-solid fa-chevron-right ml-auto text-xs text-gray-300"></i>
        </a>
        <?php endforeach; ?>
    </nav>
    
    <div class="p-5 border-t text-center">
        <p class="text-[10px] text-gray-400">App Version 2.0.1 (Beta)</p>
    </div>
</div>

<div id="sidebarOverlay" onclick="toggleUserSidebar()" class="fixed inset-0 bg-black/40 z-[150] hidden backdrop-blur-sm transition-opacity"></div>

<script>
    function toggleUserSidebar() {
        const sb = document.getElementById('userSidebar');
        const ov = document.getElementById('sidebarOverlay');
        const isOpen = !sb.classList.contains('-translate-x-full');
        
        if (isOpen) {
            sb.classList.add('-translate-x-full');
            ov.classList.add('hidden');
        } else {
            sb.classList.remove('-translate-x-full');
            ov.classList.remove('hidden');
        }
    }
</script>
