<?php include 'common/header.php'; 
$stats = [
    'Users' => $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0],
    'Orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
    'Revenue' => $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed'")->fetch_row()[0],
    'Pending' => $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0],
];
?>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php foreach($stats as $key => $val): ?>
    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-blue-500">
        <div class="text-gray-500 text-sm uppercase"><?php echo $key; ?></div>
        <div class="text-2xl font-bold"><?php echo ($key=='Revenue'?'৳':'').(int)$val; ?></div>
    </div>
    <?php endforeach; ?>
</div>

<h3 class="font-bold text-gray-700 mb-4">Quick Links</h3>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <a href="game.php" class="bg-purple-100 text-purple-700 p-4 rounded-xl text-center font-bold hover:bg-purple-200">Add Game</a>
    <a href="product.php" class="bg-green-100 text-green-700 p-4 rounded-xl text-center font-bold hover:bg-green-200">Add Product</a>
    <a href="paymentmethod.php" class="bg-orange-100 text-orange-700 p-4 rounded-xl text-center font-bold hover:bg-orange-200">Payment Methods</a>
    <a href="setting.php" class="bg-gray-100 text-gray-700 p-4 rounded-xl text-center font-bold hover:bg-gray-200">Settings</a>
</div>

</body></html>
