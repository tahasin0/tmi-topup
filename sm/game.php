<?php include 'common/header.php'; 

// Handle Game Add with Image Upload
if(isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $type = $_POST['type'];
    $desc = $conn->real_escape_string($_POST['description']);
    
    $imagePath = "";
    
    // Image Upload Logic
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = $_FILES['image']['type'];
        $filesize = $_FILES['image']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Generate unique name
            $newFilename = "game_" . time() . "." . $ext;
            $uploadDir = "../uploads/";
            $dest = $uploadDir . $newFilename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imagePath = "uploads/" . $newFilename; // Path to store in DB
            } else {
                echo "<script>alert('Failed to move uploaded file.');</script>";
            }
        } else {
            echo "<script>alert('Invalid file format. Only JPG, PNG, WEBP allowed.');</script>";
        }
    }

    if(!empty($imagePath)) {
        $conn->query("INSERT INTO games (name, type, description, image) VALUES ('$name', '$type', '$desc', '$imagePath')");
        echo "<script>window.location='game.php';</script>";
    } else {
        echo "<script>alert('Please upload an image.');</script>";
    }
}

// Delete Logic
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    // Optional: Delete physical file logic could be added here
    $conn->query("DELETE FROM games WHERE id=$id");
    echo "<script>window.location='game.php';</script>";
}
?>

<div class="bg-white p-6 rounded shadow mb-6">
    <h3 class="font-bold mb-4 border-b pb-2">Add New Game</h3>
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Game Name</label>
            <input type="text" name="name" placeholder="e.g. Free Fire" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Type</label>
            <select name="type" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                <option value="uid">UID Top Up</option>
                <option value="voucher">Unipin/Voucher</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-1">Game Cover Image</label>
            <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded bg-gray-50 focus:outline-none" required>
            <p class="text-xs text-gray-500 mt-1">Supported: JPG, PNG, WEBP. Max size: 2MB.</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-1">Description / Rules</label>
            <textarea name="description" rows="3" placeholder="Enter rules or conditions..." class="w-full border p-2 rounded focus:outline-none focus:border-blue-500"></textarea>
        </div>

        <button type="submit" name="add" class="bg-blue-600 text-white p-2 rounded font-bold hover:bg-blue-700 transition md:col-span-2">Add Game</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php $games = $conn->query("SELECT * FROM games ORDER BY id DESC"); 
    while($g = $games->fetch_assoc()): ?>
    <div class="bg-white p-4 rounded shadow flex items-center gap-4 hover:shadow-md transition">
        <img src="../<?php echo $g['image']; ?>" class="w-16 h-16 object-cover rounded border">
        <div class="flex-1">
            <h4 class="font-bold text-gray-800"><?php echo $g['name']; ?></h4>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase font-bold"><?php echo $g['type']; ?></span>
        </div>
        <a href="?del=<?php echo $g['id']; ?>" class="text-red-500 bg-red-50 p-2 rounded hover:bg-red-100 transition" onclick="return confirm('Are you sure you want to delete this game?')"><i class="fa-solid fa-trash"></i></a>
    </div>
    <?php endwhile; ?>
</div>
</body></html>
