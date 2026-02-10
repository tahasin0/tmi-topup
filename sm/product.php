<?php include 'common/header.php'; 

// Add Product
if(isset($_POST['add'])) {
    $gid = $_POST['game_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; // নতুন
    $status = $_POST['status']; // নতুন
    $conn->query("INSERT INTO products (game_id, name, price, stock, status) VALUES ($gid, '$name', '$price', '$stock', '$status')");
}

// Delete Product
if(isset($_GET['del'])) {
    $id = $_GET['del'];
    $conn->query("DELETE FROM products WHERE id=$id");
    echo "<script>window.location='product.php';</script>";
}

// Toggle Stock Status
if(isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $product = $conn->query("SELECT status FROM products WHERE id=$id")->fetch_assoc();
    $newStatus = ($product['status'] == 1) ? 0 : 1;
    $conn->query("UPDATE products SET status=$newStatus WHERE id=$id");
    echo "<script>window.location='product.php';</script>";
}

// Update Stock Quantity
if(isset($_POST['update_stock'])) {
    $id = $_POST['product_id'];
    $newStock = $_POST['stock'];
    $conn->query("UPDATE products SET stock=$newStock WHERE id=$id");
    echo "<script>window.location='product.php';</script>";
}
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1 bg-white p-6 rounded shadow h-fit">
        <h3 class="font-bold mb-4 border-b pb-2">Add Product</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500">Select Game</label>
                <select name="game_id" class="w-full border p-2 rounded bg-gray-50" required>
                    <?php 
                    $games = $conn->query("SELECT * FROM games");
                    while($g = $games->fetch_assoc()): ?>
                        <option value="<?php echo $g['id']; ?>"><?php echo $g['name']; ?> (<?php echo $g['type']; ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500">Package Name</label>
                <input type="text" name="name" placeholder="e.g. 100 Diamonds / Weekly" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500">Selling Price</label>
                <input type="number" step="0.01" name="price" placeholder="0.00" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500">Stock Quantity</label>
                <input type="number" name="stock" value="10" min="0" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500">Status</label>
                <select name="status" class="w-full border p-2 rounded bg-gray-50">
                    <option value="1">In Stock</option>
                    <option value="0">Out of Stock</option>
                </select>
            </div>
            <button type="submit" name="add" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Add Product</button>
        </form>
    </div>

    <div class="md:col-span-2 bg-white p-6 rounded shadow">
        <h3 class="font-bold mb-4 border-b pb-2">Product List</h3>
        <div class="overflow-y-auto max-h-[500px]">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-600 sticky top-0">
                    <tr>
                        <th class="p-2">Game</th>
                        <th class="p-2">Package Name</th>
                        <th class="p-2">Price</th>
                        <th class="p-2">Stock</th>
                        <th class="p-2">Status</th>
                        <th class="p-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php 
                    $prods = $conn->query("SELECT p.*, g.name as gname FROM products p JOIN games g ON p.game_id = g.id ORDER BY p.id DESC");
                    while($p = $prods->fetch_assoc()): 
                        $status = $p['status'];
                        $stock = $p['stock'];
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo ($status == 0) ? 'bg-red-50' : ''; ?>">
                        <td class="p-2 font-bold text-gray-700"><?php echo $p['gname']; ?></td>
                        <td class="p-2"><?php echo $p['name']; ?></td>
                        <td class="p-2 text-blue-600 font-bold"><?php echo getSetting($conn, 'currency').$p['price']; ?></td>
                        
                        <td class="p-2">
                            <form method="POST" class="flex items-center gap-2">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="number" name="stock" value="<?php echo $stock; ?>" min="0" class="w-20 border p-1 rounded text-center">
                                <button type="submit" name="update_stock" class="text-xs bg-gray-200 hover:bg-gray-300 p-1 rounded">
                                    <i class="fa-solid fa-save"></i>
                                </button>
                            </form>
                        </td>
                        
                        <td class="p-2">
                            <?php if($status == 1): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check mr-1"></i> In Stock
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-800">
                                    <i class="fa-solid fa-times mr-1"></i> Out of Stock
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="p-2 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="?toggle=<?php echo $p['id']; ?>" 
                                   class="inline-flex items-center gap-1 text-xs font-bold <?php echo ($status == 1) ? 'text-red-600 bg-red-100 hover:bg-red-200' : 'text-green-600 bg-green-100 hover:bg-green-200'; ?> px-3 py-1.5 rounded transition"
                                   title="<?php echo ($status == 1) ? 'Mark as Out of Stock' : 'Mark as In Stock'; ?>">
                                    <i class="fa-solid fa-power-off"></i>
                                    <?php echo ($status == 1) ? 'Disable' : 'Enable'; ?>
                                </a>
                                
                                <a href="?del=<?php echo $p['id']; ?>" 
                                   onclick="return confirm('Delete this package?')" 
                                   class="text-red-500 bg-red-100 p-1.5 rounded hover:bg-red-200">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Stock Summary -->
        <?php 
        $totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
        $inStock = $conn->query("SELECT COUNT(*) as instock FROM products WHERE status=1")->fetch_assoc()['instock'];
        $outOfStock = $conn->query("SELECT COUNT(*) as outstock FROM products WHERE status=0")->fetch_assoc()['outstock'];
        ?>
        <div class="mt-6 grid grid-cols-3 gap-4 text-center">
            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                <div class="text-2xl font-bold text-blue-700"><?php echo $totalProducts; ?></div>
                <div class="text-xs text-blue-600 font-medium">Total Products</div>
            </div>
            <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                <div class="text-2xl font-bold text-green-700"><?php echo $inStock; ?></div>
                <div class="text-xs text-green-600 font-medium">In Stock</div>
            </div>
            <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                <div class="text-2xl font-bold text-red-700"><?php echo $outOfStock; ?></div>
                <div class="text-xs text-red-600 font-medium">Out of Stock</div>
            </div>
        </div>
    </div>
</div>

<script>
    // Quick stock update with AJAX (optional)
    document.querySelectorAll('input[name="stock"]').forEach(input => {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Show saving indicator
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            
            // Auto submit after delay
            setTimeout(() => {
                form.submit();
            }, 300);
        });
    });
    
    // Toggle button confirmation
    document.querySelectorAll('a[href*="toggle"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const status = this.innerHTML.includes('Disable') ? 'Out of Stock' : 'In Stock';
            if(!confirm(`Are you sure you want to mark this product as "${status}"?`)) {
                e.preventDefault();
            }
        });
    });
</script>

</body></html>