<?php include 'common/header.php'; 

if(isset($_POST['update'])) {
    foreach($_POST as $key => $val) {
        if($key == 'update') continue;
        $val = $conn->real_escape_string($val);
        $check = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($check->num_rows > 0) {
            $conn->query("UPDATE settings SET value='$val' WHERE name='$key'");
        } else {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
        }
    }
    
    // Handle admin password change if filled
    if(!empty($_POST['new_pass'])) {
        $np = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $aid = $_SESSION['admin_id'];
        $conn->query("UPDATE admins SET password='$np' WHERE id=$aid");
    }
    echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Settings Updated!</div>";
}
?>

<form method="POST" class="bg-white p-6 rounded shadow grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <div class="md:col-span-2 font-bold text-lg border-b pb-2">General Settings</div>
    
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Site Name</label>
        <input type="text" name="site_name" value="<?php echo getSetting($conn, 'site_name'); ?>" class="w-full border p-2 rounded">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Currency Symbol</label>
        <input type="text" name="currency" value="<?php echo getSetting($conn, 'currency'); ?>" class="w-full border p-2 rounded">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 mb-1">Meta Description</label>
        <textarea name="site_desc" class="w-full border p-2 rounded h-20"><?php echo getSetting($conn, 'site_desc'); ?></textarea>
    </div>

    <div class="md:col-span-2 font-bold text-lg border-b pb-2 mt-4">Links & Video</div>
    
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">FAB (Support) Link</label>
        <input type="text" name="fab_link" value="<?php echo getSetting($conn, 'fab_link'); ?>" class="w-full border p-2 rounded">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Add Money Youtube Embed URL</label>
        <input type="text" name="add_money_video" value="<?php echo getSetting($conn, 'add_money_video'); ?>" class="w-full border p-2 rounded">
    </div>

    <div class="md:col-span-2 font-bold text-lg border-b pb-2 mt-4">Marquee Notification</div>
    
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Marquee Status (1=On, 0=Off)</label>
        <select name="marquee_active" class="w-full border p-2 rounded">
            <option value="1" <?php echo getSetting($conn, 'marquee_active')=='1'?'selected':''; ?>>Active</option>
            <option value="0" <?php echo getSetting($conn, 'marquee_active')=='0'?'selected':''; ?>>Inactive</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Marquee Text</label>
        <input type="text" name="marquee_text" value="<?php echo getSetting($conn, 'marquee_text'); ?>" class="w-full border p-2 rounded">
    </div>

    <div class="md:col-span-2 font-bold text-lg border-b pb-2 mt-4 text-red-500">Admin Security</div>
    
    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1">Change Admin Password</label>
        <input type="password" name="new_pass" placeholder="Leave empty to keep current" class="w-full border p-2 rounded bg-red-50">
    </div>

    <div class="md:col-span-2">
        <button type="submit" name="update" class="bg-blue-600 text-white px-8 py-3 rounded shadow font-bold hover:bg-blue-700">Update Settings</button>
    </div>
</form>
</body></html>
