<?php include 'common/header.php'; 

if(isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $num = $conn->real_escape_string($_POST['number']);
    $desc = $conn->real_escape_string($_POST['description']);
    $short = $conn->real_escape_string($_POST['short_desc']);
    
    $logoPath = "";
    $qrPath = "";
    $uploadDir = "../uploads/";
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    // Handle Logo Upload
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $newName = "pay_logo_" . time() . "." . $ext;
            if(move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newName)) {
                $logoPath = "uploads/" . $newName;
            }
        }
    }

    // Handle QR Upload
    if(isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $newName = "pay_qr_" . time() . "." . $ext;
            if(move_uploaded_file($_FILES['qr_image']['tmp_name'], $uploadDir . $newName)) {
                $qrPath = "uploads/" . $newName;
            }
        }
    }
    
    $conn->query("INSERT INTO payment_methods (name, number, description, short_desc, logo, qr_image) VALUES ('$name', '$num', '$desc', '$short', '$logoPath', '$qrPath')");
    echo "<script>window.location='paymentmethod.php';</script>";
}

if(isset($_GET['del'])) {
    $conn->query("DELETE FROM payment_methods WHERE id=".$_GET['del']);
    echo "<script>window.location='paymentmethod.php';</script>";
}
?>

<div class="bg-white p-6 rounded shadow mb-6">
    <h3 class="font-bold mb-4 border-b pb-2">Add Payment Method</h3>
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Method Name</label>
            <input type="text" name="name" placeholder="e.g. Bkash" class="w-full border p-2 rounded focus:outline-none" required>
        </div>
        
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Wallet Number</label>
            <input type="text" name="number" placeholder="017xxxxxxxx" class="w-full border p-2 rounded focus:outline-none" required>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Method Logo</label>
            <input type="file" name="logo" accept="image/*" class="w-full border p-2 rounded bg-gray-50 text-sm">
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">QR Code Image</label>
            <input type="file" name="qr_image" accept="image/*" class="w-full border p-2 rounded bg-gray-50 text-sm">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-1">Short Description</label>
            <input type="text" name="short_desc" placeholder="e.g. Send Money Only" class="w-full border p-2 rounded focus:outline-none" required>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-1">Full Instructions</label>
            <textarea name="description" rows="3" placeholder="Step by step payment instructions..." class="w-full border p-2 rounded focus:outline-none"></textarea>
        </div>

        <button type="submit" name="add" class="bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700 transition md:col-span-2">Save Payment Method</button>
    </form>
</div>

<div class="grid grid-cols-1 gap-4">
    <?php $methods = $conn->query("SELECT * FROM payment_methods ORDER BY id DESC");
    while($m = $methods->fetch_assoc()): ?>
    <div class="bg-white p-4 rounded shadow flex flex-col md:flex-row items-start md:items-center gap-4 hover:shadow-md transition">
        <div class="w-14 h-14 bg-gray-100 rounded flex items-center justify-center overflow-hidden border">
            <?php if($m['logo']): ?>
                <img src="../<?php echo $m['logo']; ?>" class="w-full h-full object-contain">
            <?php else: ?>
                <span class="text-xl font-bold text-gray-400"><?php echo substr($m['name'], 0, 1); ?></span>
            <?php endif; ?>
        </div>

        <div class="flex-1">
            <h4 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                <?php echo $m['name']; ?> 
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200"><?php echo $m['number']; ?></span>
            </h4>
            <p class="text-xs text-gray-500"><?php echo $m['short_desc']; ?></p>
        </div>

        <div class="flex items-center gap-3 mt-3 md:mt-0">
            <?php if($m['qr_image']): ?> 
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded flex items-center gap-1">
                    <i class="fa-solid fa-qrcode"></i> QR Active
                </span>
            <?php endif; ?>
            <a href="?del=<?php echo $m['id']; ?>" onclick="return confirm('Delete this payment method?')" class="text-red-500 hover:bg-red-50 p-2 rounded transition"><i class="fa-solid fa-trash"></i></a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</body></html>
