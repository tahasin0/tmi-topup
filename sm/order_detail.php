<?php include 'common/header.php'; 
if(!isset($_GET['id'])) header("Location: order.php");
$oid = $_GET['id'];
$order = $conn->query("SELECT o.*, u.name as uname, u.phone as uphone, g.name as gname, p.name as pname 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       JOIN games g ON o.game_id = g.id 
                       JOIN products p ON o.product_id = p.id 
                       WHERE o.id=$oid")->fetch_assoc();
?>

<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-700">Order #<?php echo $order['id']; ?></h2>
        <span class="px-3 py-1 rounded text-sm font-bold uppercase 
            <?php echo $order['status']=='completed'?'bg-green-100 text-green-700':($order['status']=='pending'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700'); ?>">
            <?php echo $order['status']; ?>
        </span>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-1">User Details</h4>
            <p class="font-bold"><?php echo $order['uname']; ?></p>
            <p class="text-sm text-gray-500"><?php echo $order['uphone']; ?></p>
        </div>
        <div>
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-1">Game Info</h4>
            <p class="font-bold"><?php echo $order['gname']; ?></p>
            <p class="text-sm text-gray-500"><?php echo $order['pname']; ?></p>
        </div>
        <div>
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-1">Target Account</h4>
            <p class="font-mono bg-gray-100 p-2 rounded text-center select-all"><?php echo $order['player_id']; ?></p>
        </div>
        <div>
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-1">Payment Info</h4>
            <p class="text-sm">Method: <b><?php echo $order['payment_method']; ?></b></p>
            <p class="text-sm">TrxID: <b class="font-mono text-blue-600"><?php echo $order['transaction_id']; ?></b></p>
            <p class="text-xl font-bold text-gray-800 mt-1"><?php echo getSetting($conn, 'currency').$order['amount']; ?></p>
        </div>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-bold mb-2">Update Status</h4>
        <form action="order.php" method="POST" class="flex gap-2">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            <button type="submit" name="update_status" value="pending" class="flex-1 py-2 rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200">Pending</button>
            <button type="submit" name="update_status" value="completed" class="flex-1 py-2 rounded bg-green-100 text-green-700 hover:bg-green-200">Complete</button>
            <button type="submit" name="update_status" value="cancelled" class="flex-1 py-2 rounded bg-red-100 text-red-700 hover:bg-red-200">Cancel</button>
        </form>
    </div>
</div>
</body></html>
