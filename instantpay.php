<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if($_SERVER['REQUEST_METHOD'] != 'POST') { header("Location: index.php"); exit; }

// Receive Data
$total = $_POST['total_amount'];
$game_id = $_POST['game_id'];
$product_id = $_POST['product_id'];
$player_id = $_POST['player_id'];
$game_name = isset($_POST['game_name']) ? $_POST['game_name'] : '';

// Resolve Product Name
$product_name = "";
if($product_id != 0) {
    $prod = $conn->query("SELECT name FROM products WHERE id=$product_id")->fetch_assoc();
    $product_name = $prod ? $prod['name'] : 'Unknown Product';
} else {
    $product_name = "Balance Add Request";
    if(empty($game_name)) $game_name = "Wallet";
}

// 👉 Wallet Balance
$wallet = $conn->query("SELECT balance FROM users WHERE id=".$_SESSION['user_id'])->fetch_assoc();
?>

<div class="container mx-auto px-4 py-6 mb-20">
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6 text-center border-t-4 border-blue-600">
        <p class="text-gray-500 mb-1 text-sm uppercase font-bold">Total Payable Amount</p>
        <h1 class="text-4xl font-bold text-blue-600 my-2"><?php echo getSetting($conn, 'currency').$total; ?></h1>
        <div class="inline-block bg-gray-100 px-3 py-1 rounded-full text-xs text-gray-600 font-medium">
            <?php echo $game_name; ?> • <?php echo $product_name; ?>
        </div>
    </div>

    <form action="order.php" method="POST" id="payForm">
        <input type="hidden" name="action" value="create_order">
        <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="amount" value="<?php echo $total; ?>">
        <input type="hidden" name="player_id" value="<?php echo $player_id; ?>">

        <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-wallet text-blue-500"></i> Select Payment Method
        </h3>

        <div class="grid grid-cols-2 gap-4 mb-6">

            <!-- ✅ WALLET PAYMENT OPTION -->
            <label class="cursor-pointer group">
                <input type="radio" name="payment_method" value="wallet" 
                       class="peer sr-only" required 
                       <?php echo ($wallet['balance'] < $total) ? 'disabled' : ''; ?>
                       onchange="handleWalletPayment(this)">
                <div class="border-2 border-gray-100 rounded-xl p-4 flex flex-col items-center gap-2 hover:bg-gray-50 peer-checked:border-green-600 peer-checked:bg-green-50 transition <?php echo ($wallet['balance'] < $total) ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                    <i class="fa-solid fa-wallet text-2xl text-green-600"></i>
                    <span class="font-bold text-gray-700">
                        Wallet (৳<?php echo number_format($wallet['balance'],2); ?>)
                    </span>
                    <?php if($wallet['balance'] < $total): ?>
                        <span class="text-xs text-red-500 font-bold">Insufficient Balance</span>
                    <?php endif; ?>
                </div>
            </label>

            <!-- EXISTING PAYMENT METHODS -->
            <?php 
            $methods = $conn->query("SELECT * FROM payment_methods");
            while($m = $methods->fetch_assoc()): ?>
            <label class="cursor-pointer group">
                <input type="radio" name="payment_method" value="<?php echo $m['name']; ?>" 
                       data-number="<?php echo $m['number']; ?>" 
                       data-qr="<?php echo $m['qr_image']; ?>" 
                       data-desc="<?php echo $m['short_desc']; ?>"
                       class="peer sr-only" required onchange="showPaymentDetails(this)">
                
                <div class="border-2 border-gray-100 rounded-xl p-4 flex flex-col items-center gap-2 hover:bg-gray-50 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition">
                    <?php if($m['logo']): ?>
                        <img src="<?php echo $m['logo']; ?>" class="h-10 object-contain">
                    <?php else: ?>
                        <span class="font-bold text-gray-700"><?php echo $m['name']; ?></span>
                    <?php endif; ?>
                </div>
            </label>
            <?php endwhile; ?>
        </div>
        
        <!-- MANUAL PAYMENT DETAILS -->
        <div id="paymentDetails" class="hidden bg-white p-6 rounded-xl shadow-lg border border-blue-100">
            <div class="text-center mb-6">
                <img id="qrImg" src="" class="w-32 h-32 mx-auto mb-3 hidden rounded-lg border p-1">
                
                <p class="text-xs text-gray-500 mb-1 font-bold uppercase">Send Money To</p>
                <div class="bg-gray-100 p-3 rounded-lg relative group">
                    <span id="payNumber" class="font-bold text-xl"></span>
                    <!-- COPY BUTTON -->
                    <button type="button" onclick="copyPaymentNumber()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition opacity-0 group-hover:opacity-100">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
                
                <!-- SMALL NOTE -->
                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-info-circle text-yellow-600"></i>
                        <p id="payDesc" class="text-xs text-gray-700 font-medium"></p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Click the copy button to copy the number</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your Wallet Number</label>
                    <input type="text" name="wallet_number" placeholder="Enter your wallet number" class="w-full border p-3 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                    <input type="text" name="trx_id" placeholder="Enter transaction ID" class="w-full border p-3 rounded-lg">
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold transition">
                    <i class="fa-solid fa-check mr-2"></i> Confirm Payment
                </button>
            </div>
        </div>

        <!-- WALLET CONFIRM BUTTON -->
        <div id="walletConfirm" class="hidden">
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold transition">
                <i class="fa-solid fa-wallet mr-2"></i> Pay from Wallet
            </button>
        </div>
    </form>
    
    <!-- COPY SUCCESS MESSAGE -->
    <div id="copySuccess" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg hidden transition transform translate-x-full">
        <i class="fa-solid fa-check mr-2"></i> Number copied to clipboard!
    </div>
</div>

<script>
function handleWalletPayment(input){
    if(<?php echo ($wallet['balance'] < $total) ? 'true' : 'false'; ?>){
        alert('Insufficient wallet balance! Please choose another payment method.');
        input.checked = false;
        return;
    }
    
    document.getElementById('paymentDetails').classList.add('hidden');
    document.getElementById('walletConfirm').classList.remove('hidden');
}

function showPaymentDetails(input){
    document.getElementById('paymentDetails').classList.remove('hidden');
    document.getElementById('walletConfirm').classList.add('hidden');

    const number = input.dataset.number;
    document.getElementById('payNumber').innerText = number;
    document.getElementById('payDesc').innerText = input.dataset.desc;

    const qrImg = document.getElementById('qrImg');
    if(input.dataset.qr){
        qrImg.src = input.dataset.qr;
        qrImg.classList.remove('hidden');
    }else{
        qrImg.classList.add('hidden');
    }
}

// COPY PAYMENT NUMBER FUNCTION
function copyPaymentNumber() {
    const numberElement = document.getElementById('payNumber');
    const numberText = numberElement.innerText;
    
    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = numberText;
    document.body.appendChild(textarea);
    
    // Select and copy the text
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // Remove the textarea
    document.body.removeChild(textarea);
    
    // Show success message
    const successMsg = document.getElementById('copySuccess');
    successMsg.classList.remove('hidden', 'translate-x-full');
    successMsg.classList.add('translate-x-0');
    
    // Hide message after 3 seconds
    setTimeout(() => {
        successMsg.classList.remove('translate-x-0');
        successMsg.classList.add('translate-x-full');
        setTimeout(() => {
            successMsg.classList.add('hidden');
        }, 300);
    }, 3000);
}
</script>

<style>
#copySuccess {
    transition: all 0.3s ease-in-out;
    z-index: 9999;
}
</style>

<?php include 'common/bottom.php'; ?>