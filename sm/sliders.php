<?php include 'common/header.php'; 

if(isset($_POST['add'])) {
    $link = $conn->real_escape_string($_POST['link']);
    $imagePath = "";

    // Image Upload Logic
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newFilename = "slider_" . time() . "." . $ext;
            $uploadDir = "../uploads/";
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                $imagePath = "uploads/" . $newFilename;
            }
        }
    }

    if(!empty($imagePath)) {
        $conn->query("INSERT INTO sliders (image, link) VALUES ('$imagePath', '$link')");
        echo "<script>window.location='sliders.php';</script>";
    } else {
        echo "<script>alert('Failed to upload image. Please check format.');</script>";
    }
}

if(isset($_GET['del'])) {
    $conn->query("DELETE FROM sliders WHERE id=".$_GET['del']);
    echo "<script>window.location='sliders.php';</script>";
}
?>

<div class="bg-white p-6 rounded shadow mb-6">
    <h3 class="font-bold mb-4 border-b pb-2">Add New Slider</h3>
    <form method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-sm font-bold text-gray-700 mb-1">Slider Image</label>
            <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded bg-gray-50 focus:outline-none" required>
        </div>
        <div class="flex-1 w-full">
            <label class="block text-sm font-bold text-gray-700 mb-1">Direct Link (Optional)</label>
            <input type="text" name="link" placeholder="https://..." class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" name="add" class="bg-green-600 text-white px-6 py-2.5 rounded font-bold hover:bg-green-700 transition w-full md:w-auto">Add Slider</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php $sliders = $conn->query("SELECT * FROM sliders ORDER BY id DESC");
    while($s = $sliders->fetch_assoc()): ?>
    <div class="group relative rounded-xl overflow-hidden shadow-lg h-40 bg-gray-100">
        <img src="../<?php echo $s['image']; ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-4 backdrop-blur-sm">
            <?php if($s['link']): ?>
                <a href="<?php echo $s['link']; ?>" target="_blank" class="bg-white text-blue-600 w-10 h-10 rounded-full flex items-center justify-center shadow hover:scale-110 transition"><i class="fa-solid fa-link"></i></a>
            <?php endif; ?>
            <a href="?del=<?php echo $s['id']; ?>" onclick="return confirm('Delete this slider?')" class="bg-white text-red-500 w-10 h-10 rounded-full flex items-center justify-center shadow hover:scale-110 transition"><i class="fa-solid fa-trash"></i></a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</body></html>
