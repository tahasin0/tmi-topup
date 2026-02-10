<?php include 'common/header.php'; 

// Update Status Logic (Fixed)
if(isset($_POST['update_status']) || isset($_POST['status'])) {
    $oid = $_POST['order_id'];
    
    // Check where the status is coming from
    if(isset($_POST['status'])) {
        // From Dropdown (this page)
        $st = $_POST['status'];
    } else {
        // From Buttons (order_detail.php)
        $st = $_POST['update_status'];
    }

    $conn->query("UPDATE orders SET status='$st' WHERE id=$oid");
    
    // Optional: Logic for Voucher auto-assign can be added here
    
    // Redirect to avoid form resubmission on refresh
    echo "<script>window.location.href='order.php';</script>";
}
?>

<div class="overflow-x-auto bg-white rounded shadow">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-700 uppercase">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">User/Player</th>
                <th class="p-3">Item</th>
                <th class="p-3">Amount</th>
                <th class="p-3">TrxID</th>
                <th class="p-3">Status</th>
                <th class="p-3">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Added error handling in case of empty tables
            $sql = "SELECT o.*, u.name as uname, p.name as pname, g.name as gname 
                    FROM orders o 
                    JOIN users u ON o.user_id=u.id 
                    JOIN products p ON o.product_id=p.id 
                    JOIN games g ON o.game_id=g.id 
                    ORDER BY o.id DESC";
            $orders = $conn->query($sql);

            if($orders && $orders->num_rows > 0):
                while($o = $orders->fetch_assoc()): 
                    // Color coding for status
                    $statusColor = 'text-gray-600';
                    if($o['status'] == 'completed') $statusColor = 'text-green-600 font-bold';
                    if($o['status'] == 'cancelled') $statusColor = 'text-red-600 font-bold';
                    if($o['status'] == 'pending') $statusColor = 'text-yellow-600 font-bold';
            ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3">#<?php echo $o['id']; ?></td>
                <td class="p-3">
                    <div class="font-bold"><?php echo $o['uname']; ?></div>
                    <div class="text-xs text-gray-500"><?php echo $o['player_id']; ?></div>
                </td>
                <td class="p-3"><?php echo $o['gname'] . " - " . $o['pname']; ?></td>
                <td class="p-3 font-bold"><?php echo $o['amount']; ?></td>
                <td class="p-3 font-mono text-xs">
                    <?php echo $o['transaction_id']; ?><br>
                    <span class="text-gray-400"><?php echo $o['payment_method']; ?></span>
                </td>
                <td class="p-3">
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                        <input type="hidden" name="update_status" value="1"> 
                        
                        <select name="status" onchange="this.form.submit()" class="border rounded p-1 text-xs <?php echo $statusColor; ?> cursor-pointer focus:outline-none">
                            <option value="pending" <?php if($o['status']=='pending') echo 'selected'; ?>>Pending</option>
                            <option value="completed" <?php if($o['status']=='completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if($o['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </form>
                </td>
                <td class="p-3">
                    <a href="order_detail.php?id=<?php echo $o['id']; ?>" class="text-blue-600 hover:text-blue-800"><i class="fa-solid fa-eye"></i></a>
                </td>
            </tr>
            <?php endwhile; 
            else: ?>
                <tr><td colspan="7" class="p-5 text-center text-gray-500">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body></html>
