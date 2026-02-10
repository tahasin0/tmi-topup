<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>

<div class="container mx-auto px-4 py-6 mb-20">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold border-l-4 border-blue-600 pl-3">My Vouchers</h2>
        <a href="https://shop.garena.my/app" target="_blank" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-sm shadow hover:bg-red-600 transition flex items-center gap-2">
            <i class="fa-solid fa-up-right-from-square"></i> Redeem Site
        </a>
    </div>

    <div class="space-y-4">
        <?php 
        $uid = $_SESSION['user_id'];
        // Fetch Completed Orders that are Vouchers (have redeem code)
        $sql = "SELECT rc.code, p.name as pname, g.name as gname, g.image, o.id as order_id, o.amount, o.transaction_id, o.created_at 
                FROM redeem_codes rc 
                JOIN orders o ON rc.order_id = o.id 
                JOIN products p ON o.product_id = p.id
                JOIN games g ON o.game_id = g.id
                WHERE o.user_id = $uid AND o.status='completed'
                ORDER BY o.id DESC";
        
        $res = $conn->query($sql);
        if($res && $res->num_rows > 0):
        while($row = $res->fetch_assoc()): ?>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group">
                <div class="flex gap-4 items-center mb-3">
                    <img src="<?php echo $row['image']; ?>" class="w-14 h-14 rounded-lg object-cover border">
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 text-sm"><?php echo $row['gname']; ?></h3>
                        <p class="text-xs text-gray-500"><?php echo $row['pname']; ?></p>
                        <p class="text-[10px] text-gray-400 mt-1">Order #<?php echo $row['order_id']; ?> • <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                         <span class="font-bold text-blue-600"><?php echo getSetting($conn, 'currency').$row['amount']; ?></span>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg flex justify-between items-center gap-3">
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Redeem Code</p>
                        <code class="text-blue-700 font-mono font-bold text-sm select-all break-all"><?php echo $row['code']; ?></code>
                    </div>
                    <button type="button" onclick="copyToClipboard('<?php echo $row['code']; ?>')" class="bg-white text-gray-600 w-10 h-10 rounded-full shadow hover:text-blue-600 hover:shadow-md transition flex items-center justify-center active:scale-95">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>

                <div class="mt-3 text-[10px] text-gray-400 text-center">
                    TrxID: <span class="font-mono"><?php echo $row['transaction_id']; ?></span>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="text-center py-10">
                <i class="fa-solid fa-ticket text-gray-300 text-5xl mb-3"></i>
                <p class="text-gray-500">No vouchers purchased yet.</p>
                <a href="index.php" class="text-blue-500 text-sm font-bold mt-2 inline-block">Buy Now</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center py-4 text-xs text-gray-400 mt-6">
        <p>&copy; 2025 <?php echo getSetting($conn, 'site_name'); ?>.</p>
        <a href="https://t.me/mraiprime" target="_blank" class="text-blue-400 hover:text-blue-600 transition decoration-none">Developed by Mr Ai Prime</a>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        // Fallback for non-secure contexts (like local IP)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showNotif('success', 'Copied!', 'Code copied to clipboard.');
            }).catch(err => {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed"; 
        textArea.style.left = "-9999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showNotif('success', 'Copied!', 'Code copied to clipboard.');
        } catch (err) {
            showNotif('error', 'Error', 'Failed to copy code.');
        }
        document.body.removeChild(textArea);
    }
</script>

<?php include 'common/bottom.php'; ?>
