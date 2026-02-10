<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];

// --- HANDLE FORM SUBMISSION ---
if(isset($_POST['action']) && $_POST['action'] == 'create_order') {
    // Receive Data
    $gid = (int)$_POST['game_id'];
    $pid = (int)$_POST['product_id'];
    $amt = $_POST['amount'];
    $ply = isset($_POST['player_id']) ? $conn->real_escape_string($_POST['player_id']) : '';
    $met = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : '';
    $trx = isset($_POST['trx_id']) ? $conn->real_escape_string($_POST['trx_id']) : '';
    $wal = isset($_POST['wallet_number']) ? $conn->real_escape_string($_POST['wallet_number']) : '';
    
    // Check Wallet Balance if payment method is wallet
    if($met == 'wallet') {
        $wallet_check = $conn->query("SELECT balance FROM users WHERE id=$uid")->fetch_assoc();
        
        if($wallet_check['balance'] < $amt) {
            echo "<script>
                alert('Insufficient wallet balance! Please recharge your wallet.');
                window.history.back();
            </script>";
            exit;
        }
        
        // Auto generate transaction ID for wallet payment
        $trx = 'WALLET_' . time() . '_' . rand(1000,9999);
    }

    // Check Game Type
    $gameType = 'uid'; // default
    if($gid > 0) {
        $gRes = $conn->query("SELECT type FROM games WHERE id=$gid");
        if($gRes && $gRes->num_rows > 0) {
            $gameType = $gRes->fetch_assoc()['type'];
        }
    }

    if($gid == 0) {
        // [CASE A] Add Money Request - Wallet payment not allowed here
        if($met == 'wallet') {
            echo "<script>
                alert('Cannot use wallet to add money to wallet!');
                window.history.back();
            </script>";
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, method, wallet_number, trx_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idsss", $uid, $amt, $met, $wal, $trx);
        
        if($stmt->execute()){
            echo "<script>
                alert('Add Money Request Submitted! Check Payment History.'); 
                window.location.href='payment_history.php';
            </script>";
            exit;
        }
    } else {
        // [CASE B] Game Order
        $conn->begin_transaction();
        
        try {
            // Step 1: Insert Order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, game_id, product_id, amount, player_id, transaction_id, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiidsss", $uid, $gid, $pid, $amt, $ply, $trx, $met);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            
            // Step 2: If payment method is wallet, deduct balance and update order status
            if($met == 'wallet') {
                // Deduct from wallet
                $conn->query("UPDATE users SET balance = balance - $amt WHERE id=$uid");
                
                // Add transaction history
                $conn->query("INSERT INTO wallet_transactions (user_id, order_id, amount, type, description) 
                             VALUES ($uid, $order_id, $amt, 'debit', 'Game order payment')");
                
               // Update order status to pending
$conn->query("UPDATE orders SET status='pending' WHERE id=$order_id");

$conn->commit();
                
                // Redirect based on game type
                if($gameType == 'voucher') {
                    echo "<script>
                        alert('Payment Successful! Check My Codes for your voucher.'); 
                        window.location.href='mycode.php';
                    </script>";
                } else {
                    echo "<script>
                        alert('Payment Successful! Order placed.'); 
                        window.location.href='order.php';
                    </script>";
                }
                exit;
            } else {
                // For other payment methods, keep as pending
                $conn->commit();
                
                if($gameType == 'voucher') {
                    echo "<script>
                        alert('Order Placed! Check My Codes for your voucher.'); 
                        window.location.href='mycode.php';
                    </script>";
                } else {
                    echo "<script>window.location.href='order.php';</script>";
                }
                exit;
            }
            
        } catch(Exception $e) {
            $conn->rollback();
            echo "<script>
                alert('Payment failed: " . $e->getMessage() . "');
                window.history.back();
            </script>";
            exit;
        }
    }
}
?>

<div class="container mx-auto px-4 py-6 mb-20">
    <h2 class="text-xl font-bold mb-4 border-l-4 border-blue-600 pl-3">My Orders</h2>
    
    <div class="space-y-4">
        <?php 
        $sql = "SELECT o.*, g.name as gname, g.image as gimg, p.name as pname 
                FROM orders o 
                JOIN games g ON o.game_id = g.id 
                JOIN products p ON o.product_id = p.id 
                WHERE o.user_id=$uid ORDER BY o.id DESC";
        $res = $conn->query($sql);
        
        if($res && $res->num_rows > 0):
        while($row = $res->fetch_assoc()): 
            $statusColor = 'yellow';
            if($row['status'] == 'completed') $statusColor = 'green';
            if($row['status'] == 'cancelled') $statusColor = 'red';
        ?>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex gap-4">
                <img src="<?php echo $row['gimg']; ?>" class="w-16 h-16 rounded-lg object-cover">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800"><?php echo $row['gname']; ?></h3>
                    <p class="text-sm text-gray-500"><?php echo $row['pname']; ?></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="font-bold text-blue-600"><?php echo getSetting($conn, 'currency').$row['amount']; ?></span>
                        <span class="text-xs px-2 py-1 rounded bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-700 font-bold uppercase"><?php echo $row['status']; ?></span>
                    </div>
                </div>
            </div>
            
            <details class="mt-2 text-xs text-gray-500 border-t pt-2">
                <summary class="cursor-pointer text-blue-500 mb-1">View Details</summary>
                <p>Order ID: #<?php echo $row['id']; ?></p>
                <p>Player ID: <?php echo $row['player_id']; ?></p>
                <p>TrxID: <?php echo $row['transaction_id']; ?></p>
                <p>Date: <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></p>
            </details>
        </div>
        <?php endwhile; 
        else: echo "<div class='text-center text-gray-500 mt-10'>No orders found.</div>";
        endif; ?>
    </div>
    
    <div class="text-center py-4 text-xs text-gray-400 mt-6">
        <p>&copy; 2025 <?php echo getSetting($conn, 'site_name'); ?>.</p>
        <a href="https://t.me/mraiprime" target="_blank" class="text-blue-400 hover:text-blue-600 transition decoration-none">Developed by Mr Ai Prime</a>
    </div>
</div>
<?php include 'common/bottom.php'; ?>