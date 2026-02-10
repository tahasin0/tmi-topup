<?php 
// 1. Header include করা হয়েছে (এখানে header.php তে কোনো হাত দেওয়ার দরকার নেই)
include 'common/header.php'; 

if(!isset($_GET['id'])) header("Location: index.php");
$id = (int)$_GET['id'];
$game = $conn->query("SELECT * FROM games WHERE id=$id")->fetch_assoc();
if(!$game) header("Location: index.php");

$products = $conn->query("SELECT * FROM products WHERE game_id=$id ORDER BY price ASC");
?>

<style>
    /* কমন সাপোর্ট বাটন ক্লাসের নামগুলো এখানে দেওয়া হলো */
    .support-fab, .floating-contact, .whatsapp-float, #support-btn, .fab-wrapper {
        display: none !important;
    }
    
    /* পেজের নিচে সাদা অংশ কমানোর জন্য */
    body {
        padding-bottom: 0 !important;
    }

    /* এনিমেশন স্টাইল */
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; opacity: 0; transform: translateY(20px); }
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
</style>

<div class="relative w-full h-48 bg-gray-900 overflow-hidden">
    <img src="<?php echo $game['image']; ?>" class="w-full h-full object-cover opacity-50 blur-sm scale-110">
    <div class="absolute inset-0 bg-gradient-to-t from-gray-50 to-transparent"></div>
</div>

<div class="container mx-auto px-4 relative z-10 -mt-20 pb-32">
    
    <div class="bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-lg border border-white/50 flex items-center gap-4 mb-6 animate-fade-in-up">
        <img src="<?php echo $game['image']; ?>" class="w-20 h-20 rounded-xl shadow-md border-2 border-white ring-2 ring-blue-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 leading-none mb-1"><?php echo $game['name']; ?></h1>
            <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-1 rounded-md tracking-wider">
                <i class="fa-solid fa-bolt text-yellow-500"></i> INSTANT
            </span>
            <span class="ml-1 text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded-md uppercase">
                <?php echo $game['type']; ?>
            </span>
        </div>
    </div>

    <form action="instantpay.php" method="POST" class="space-y-5">
        <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
        <input type="hidden" name="game_name" value="<?php echo $game['name']; ?>">
        <input type="hidden" name="game_type" value="<?php echo $game['type']; ?>">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-gem text-blue-500"></i> Select Recharge
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <?php while($prod = $products->fetch_assoc()): ?>
                <label class="cursor-pointer group relative">
                    <input type="radio" name="product_id" value="<?php echo $prod['id']; ?>" data-price="<?php echo $prod['price']; ?>" class="peer sr-only" required onchange="updateTotal()">
                    
                    <div class="border border-gray-200 rounded-xl p-3 text-center transition-all duration-300 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-blue-50 group-hover:border-blue-300 hover:shadow-md h-full flex flex-col justify-between bg-white">
                        <div class="absolute top-2 right-2 text-blue-600 opacity-0 transform scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        
                        <div class="font-bold text-gray-700 text-sm peer-checked:text-blue-700"><?php echo $prod['name']; ?></div>
                        <div class="mt-2 text-xs font-bold text-gray-500 peer-checked:text-blue-600 bg-gray-50 peer-checked:bg-white p-1 rounded">
                            <?php echo getSetting($conn, 'currency').$prod['price']; ?>
                        </div>
                    </div>
                </label>
                <?php endwhile; ?>
            </div>
        </div>

        <?php if($game['type'] == 'uid'): ?>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-purple-500"></i> Player ID
            </h3>
            <div class="relative">
                <i class="fa-solid fa-user-tag absolute left-4 top-4 text-gray-400"></i>
                <input type="text" name="player_id" placeholder="Enter UID here..." class="w-full border border-gray-200 p-3 pl-10 rounded-xl bg-gray-50 focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all font-mono" required>
            </div>
            <p class="text-[10px] text-gray-400 mt-2 ml-1">* Make sure UID is correct.</p>
        </div>
        <?php else: ?>
            <input type="hidden" name="player_id" value="Voucher Request">
        <?php endif; ?>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
             <h3 class="font-bold text-gray-700 text-sm">Quantity</h3>
             <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                 <button type="button" onclick="changeQty(-1)" class="w-8 h-8 bg-white rounded shadow text-gray-600 hover:text-red-500 transition"><i class="fa-solid fa-minus"></i></button>
                 <input type="number" name="quantity" id="qty" value="1" readonly class="w-10 text-center bg-transparent font-bold text-gray-800 outline-none">
                 <button type="button" onclick="changeQty(1)" class="w-8 h-8 bg-white rounded shadow text-gray-600 hover:text-green-500 transition"><i class="fa-solid fa-plus"></i></button>
             </div>
        </div>

        <div class="fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-md border-t border-gray-200 p-4 z-40 pb-6 shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex flex-col">
                    <span class="text-xs text-gray-500">Total Payable</span>
                    <div class="font-black text-2xl text-blue-600 leading-none" id="totalDisplay"><?php echo getSetting($conn, 'currency'); ?>0</div>
                    <input type="hidden" name="total_amount" id="totalInput" value="0">
                </div>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transform hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    Buy Now <i class="fa-solid fa-bag-shopping"></i>
                </button>
            </div>
        </div>
    </form>
    
    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 mt-4 mb-20">
        <h3 class="font-bold text-blue-800 text-sm mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Description</h3>
        <p class="text-xs text-gray-600 leading-relaxed"><?php echo nl2br($game['description']); ?></p>
    </div>
</div>

<script>
    let currentPrice = 0;
    function changeQty(n) {
        let q = document.getElementById('qty');
        let val = parseInt(q.value) + n;
        if(val < 1) val = 1;
        q.value = val;
        updateTotal();
    }
    function updateTotal() {
        const radios = document.getElementsByName('product_id');
        for (let r of radios) {
            if (r.checked) {
                currentPrice = parseFloat(r.getAttribute('data-price'));
                break;
            }
        }
        let qty = parseInt(document.getElementById('qty').value);
        let total = (currentPrice * qty).toFixed(2);
        document.getElementById('totalDisplay').innerText = "<?php echo getSetting($conn, 'currency'); ?>" + total;
        document.getElementById('totalInput').value = total;
    }
</script>

</body>
</html>
