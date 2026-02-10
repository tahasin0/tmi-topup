<?php include 'common/header.php'; 

if(isset($_POST['add_code'])) {
    $pid = $_POST['product_id'];
    $code = $_POST['code'];
    $conn->query("INSERT INTO redeem_codes (product_id, code) VALUES ($pid, '$code')");
}

if(isset($_POST['assign_order'])) {
    $cid = $_POST['code_id'];
    $oid = $_POST['order_id'];
    $conn->query("UPDATE redeem_codes SET order_id=$oid, status='used' WHERE id=$cid");
    $conn->query("UPDATE orders SET status='completed' WHERE id=$oid"); // Auto complete order
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-bold mb-4">Add Redeem Code</h3>
        <form method="POST" class="space-y-3">
            <select name="product_id" class="w-full border p-2 rounded" required>
                <option value="">Select Product</option>
                <?php $prods = $conn->query("SELECT p.id, p.name, g.name as gname FROM products p JOIN games g ON p.game_id=g.id WHERE g.type='voucher'");
                while($p = $prods->fetch_assoc()) echo "<option value='{$p['id']}'>{$p['gname']} - {$p['name']}</option>"; ?>
            </select>
            <textarea name="code" placeholder="Code (e.g., UP-12345)" class="w-full border p-2 rounded" required></textarea>
            <button type="submit" name="add_code" class="bg-green-600 text-white px-4 py-2 rounded">Add Code</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-bold mb-4">Available Codes</h3>
        <ul class="space-y-2">
            <?php 
            $codes = $conn->query("SELECT r.*, p.name FROM redeem_codes r JOIN products p ON r.product_id=p.id WHERE r.status='active'");
            while($c = $codes->fetch_assoc()): ?>
            <li class="flex justify-between border-b pb-1">
                <span><?php echo $c['name']; ?></span>
                <code class="text-blue-600"><?php echo $c['code']; ?></code>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<div class="bg-white p-6 rounded shadow mt-6">
    <h3 class="font-bold mb-4">Assign Code to Pending Order</h3>
    <form method="POST" class="flex gap-4">
        <input type="text" name="order_id" placeholder="Order ID" class="border p-2 rounded" required>
        <select name="code_id" class="border p-2 rounded" required>
            <?php 
            $codes = $conn->query("SELECT r.*, p.name FROM redeem_codes r JOIN products p ON r.product_id=p.id WHERE r.status='active'");
            while($c = $codes->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['name']} - {$c['code']}</option>"; 
            ?>
        </select>
        <button type="submit" name="assign_order" class="bg-blue-600 text-white px-4 py-2 rounded">Assign & Complete</button>
    </form>
</div>
</body></html>
