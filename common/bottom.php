<nav class="fixed bottom-0 left-0 w-full bg-white border-t flex justify-around py-2 z-40 text-xs text-gray-600">
    <a href="index.php" class="flex flex-col items-center gap-1 hover:text-blue-600 <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'text-blue-600':''; ?>">
        <i class="fa-solid fa-home text-lg"></i> Home
    </a>
    <a href="addmoney.php" class="flex flex-col items-center gap-1 hover:text-blue-600 <?php echo basename($_SERVER['PHP_SELF'])=='addmoney.php'?'text-blue-600':''; ?>">
        <i class="fa-solid fa-wallet text-lg"></i> Deposit 
    </a>
    <a href="order.php" class="flex flex-col items-center gap-1 hover:text-blue-600 <?php echo basename($_SERVER['PHP_SELF'])=='order.php'?'text-blue-600':''; ?>">
        <i class="fa-solid fa-box-open text-lg"></i> Orders
    </a>
    <a href="mycode.php" class="flex flex-col items-center gap-1 hover:text-blue-600 <?php echo basename($_SERVER['PHP_SELF'])=='mycode.php'?'text-blue-600':''; ?>">
        <i class="fa-solid fa-ticket text-lg"></i> Codes
    </a>
    <a href="profile.php" class="flex flex-col items-center gap-1 hover:text-blue-600 <?php echo basename($_SERVER['PHP_SELF'])=='profile.php'?'text-blue-600':''; ?>">
        <i class="fa-solid fa-user text-lg"></i> Profile
    </a>
</nav>

<a href="<?php echo getSetting($conn, 'fab_link'); ?>" target="_blank" class="fixed bottom-20 right-4 w-14 h-14 bg-green-500 rounded-full shadow-lg flex items-center justify-center text-white text-2xl z-50 fab-bounce transition hover:scale-110">
    <i class="fa-brands fa-whatsapp"></i>
</a>

<div id="loadingModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 hidden backdrop-blur-sm">
    <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center justify-center w-32 h-32 animate-fade-in">
        <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
        <span class="text-xs font-bold text-gray-500 tracking-wider">LOADING</span>
    </div>
</div>

<div id="notifModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 hidden">
    <div class="bg-white w-80 rounded-2xl shadow-2xl p-6 text-center transform scale-95 transition-all duration-300" id="notifContent">
        <div id="notifIcon" class="text-5xl mb-4"></div>
        <h3 id="notifTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
        <p id="notifMsg" class="text-sm text-gray-500 mb-6"></p>
        <button onclick="closeNotif()" class="bg-gray-900 text-white w-full py-3 rounded-xl font-bold hover:bg-gray-800">Okay</button>
    </div>
</div>

<script>
    // Security
    document.addEventListener('contextmenu', event => event.preventDefault());
    
    // UI Helpers
    const loader = document.getElementById('loadingModal');
    const notif = document.getElementById('notifModal');
    
    function showLoader() { loader.classList.remove('hidden'); }
    function hideLoader() { loader.classList.add('hidden'); }
    
    function showNotif(type, title, msg) {
        const iconEl = document.getElementById('notifIcon');
        document.getElementById('notifTitle').innerText = title;
        document.getElementById('notifMsg').innerText = msg;
        
        if(type === 'success') {
            iconEl.innerHTML = '<i class="fa-solid fa-circle-check text-green-500 animate-bounce"></i>';
        } else if(type === 'error') {
            iconEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500 animate-pulse"></i>';
        } else {
            iconEl.innerHTML = '<i class="fa-solid fa-circle-info text-blue-500"></i>';
        }
        
        notif.classList.remove('hidden');
        document.getElementById('notifContent').classList.remove('scale-95');
        document.getElementById('notifContent').classList.add('scale-100');
    }

    function closeNotif() {
        notif.classList.add('hidden');
    }

    // Generic AJAX Form Handler
    document.addEventListener('DOMContentLoaded', () => {
        const forms = document.querySelectorAll('form.ajax-form');
        forms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                showLoader();
                
                const formData = new FormData(form);
                
                try {
                    // Post to current URL or action attribute
                    const response = await fetch(form.action || window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const text = await response.text();
                    hideLoader();

                    // Try parsing JSON response if your PHP returns JSON
                    try {
                        const data = JSON.parse(text);
                        if(data.status) {
                            showNotif(data.status, data.title, data.message);
                            if(data.redirect) {
                                setTimeout(() => window.location.href = data.redirect, 1500);
                            }
                        } else {
                            // Fallback for non-JSON responses (like page reload logic)
                            // If the PHP echoed a script or HTML, we might just reload
                            window.location.reload();
                        }
                    } catch(err) {
                        // If PHP returned HTML (standard post-redirect-get), just reload to show changes
                         window.location.reload(); 
                    }

                } catch (error) {
                    hideLoader();
                    showNotif('error', 'Network Error', 'Something went wrong.');
                }
            });
        });
    });
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .fab-bounce { animation: bounce 2s infinite; }
    @keyframes bounce { 0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 40% {transform: translateY(-10px);} 60% {transform: translateY(-5px);} }
</style>
</body>
</html>
