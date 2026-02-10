<?php include 'common/header.php'; 

if(isset($_GET['del'])) {
    $conn->query("DELETE FROM users WHERE id=".$_GET['del']);
    echo "<script>window.location='user.php';</script>";
}

// Balance update functionality
if(isset($_POST['update_balance'])) {
    $user_id = $_POST['user_id'];
    $new_balance = $_POST['balance'];
    
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("di", $new_balance, $user_id);
    $stmt->execute();
    
    // Redirect to prevent form resubmission
    echo "<script>window.location='user.php';</script>";
    exit();
}
?>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Name / Email</th>
                <th class="p-3">Phone</th>
                <th class="p-3">Balance</th>
                <th class="p-3">Joined</th>
                <th class="p-3 text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php 
            $users = $conn->query("SELECT * FROM users ORDER BY id DESC");
            while($u = $users->fetch_assoc()): 
            ?>
            <tr class="hover:bg-gray-50">
                <td class="p-3"><?php echo $u['id']; ?></td>
                <td class="p-3">
                    <div class="font-bold"><?php echo $u['name']; ?></div>
                    <div class="text-xs text-gray-500"><?php echo $u['email']; ?></div>
                </td>
                <td class="p-3"><?php echo $u['phone']; ?></td>
                <td class="p-3">
                    <!-- Editable Balance Form -->
                    <form method="POST" class="inline">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <div class="flex items-center gap-2">
                            <span class="text-green-600 font-bold mr-1">
                                <?php echo getSetting($conn, 'currency'); ?>
                            </span>
                            <input type="number" 
                                   name="balance" 
                                   value="<?php echo $u['balance']; ?>" 
                                   step="0.01"
                                   min="0"
                                   class="w-28 px-2 py-1 border rounded text-sm font-bold text-green-600">
                            <button type="submit" 
                                    name="update_balance" 
                                    class="bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600 ml-2">
                                Update
                            </button>
                        </div>
                    </form>
                </td>
                <td class="p-3 text-gray-400 text-xs"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td class="p-3 text-right">
                    <a href="?del=<?php echo $u['id']; ?>" onclick="return confirm('Ban/Delete User?')" class="text-white bg-red-500 px-3 py-1 rounded text-xs">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body></html>
