<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$uid = $_SESSION['user_id'];
$msg = "";
$msgType = "";

// Handle Profile Update
if(isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("UPDATE users SET name='$name', email='$email' WHERE id=$uid");
    $msg = "Profile Updated Successfully!";
    $msgType = "success";
}

// Handle Password Change
if(isset($_POST['change_pass'])) {
    $curr = $_POST['current_pass'];
    $new = $_POST['new_pass'];
    $u = $conn->query("SELECT password FROM users WHERE id=$uid")->fetch_assoc();
    if(password_verify($curr, $u['password'])) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash' WHERE id=$uid");
        $msg = "Password Changed Successfully!";
        $msgType = "success";
    } else {
        $msg = "Current Password Incorrect!";
        $msgType = "error";
    }
}

// Fetch User Data
$u = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
// Stats
$orders = $conn->query("SELECT COUNT(*) as cnt, SUM(amount) as spent FROM orders WHERE user_id=$uid AND status='completed'")->fetch_assoc();
?>

<div class="container mx-auto px-4 py-6 mb-24">
    
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-6 text-white shadow-xl mb-6 relative overflow-hidden">
        <div class="absolute -right-6 -top-6 bg-white/10 w-32 h-32 rounded-full"></div>
        <div class="flex items-center gap-4 relative z-10">
            <div class="w-16 h-16 bg-white text-blue-600 rounded-full flex items-center justify-center text-3xl font-bold shadow">
                <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
            </div>
            <div>
                <h2 class="font-bold text-xl"><?php echo $u['name']; ?></h2>
                <p class="text-blue-100 text-sm"><?php echo $u['phone']; ?></p>
                <div class="mt-2 bg-blue-900/30 inline-block px-3 py-1 rounded-lg border border-blue-400/30 text-xs">
                    Member since <?php echo date('M Y', strtotime($u['created_at'])); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-b-4 border-green-500">
            <p class="text-xs text-gray-400 uppercase font-bold">Balance</p>
            <h3 class="font-bold text-green-600 text-xl"><?php echo getSetting($conn, 'currency').$u['balance']; ?></h3>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-b-4 border-blue-500">
            <p class="text-xs text-gray-400 uppercase font-bold">Total Spent</p>
            <h3 class="font-bold text-gray-700 text-xl"><?php echo getSetting($conn, 'currency').(float)$orders['spent']; ?></h3>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-b-4 border-orange-500">
            <p class="text-xs text-gray-400 uppercase font-bold">Orders</p>
            <h3 class="font-bold text-gray-700 text-xl"><?php echo (int)$orders['cnt']; ?></h3>
        </div>
        <a href="<?php echo getSetting($conn, 'fab_link'); ?>" class="bg-gray-800 p-4 rounded-xl shadow-sm text-center flex flex-col justify-center items-center text-white border-b-4 border-gray-600">
            <i class="fa-solid fa-headset text-xl mb-1"></i>
            <span class="text-xs font-bold">Get Support</span>
        </a>
    </div>

    <div class="flex bg-gray-200 rounded-lg p-1 mb-6">
        <button onclick="showSection('info')" id="btn-info" class="flex-1 py-2 text-sm font-bold rounded-md bg-white shadow text-blue-600 transition">Profile</button>
        <button onclick="showSection('edit')" id="btn-edit" class="flex-1 py-2 text-sm font-bold rounded-md text-gray-500 transition">Edit</button>
        <button onclick="showSection('security')" id="btn-security" class="flex-1 py-2 text-sm font-bold rounded-md text-gray-500 transition">Security</button>
    </div>

    <div id="sec-info" class="space-y-4">
        <div class="space-y-3">
            <a href="order.php" class="bg-white p-4 rounded-xl shadow-sm flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fa-solid fa-list"></i></div>
                    <span class="font-bold text-gray-700">Order History</span>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300"></i>
            </a>

            <a href="payment_history.php" class="bg-white p-4 rounded-xl shadow-sm flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <span class="font-bold text-gray-700">Payment History</span>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300"></i>
            </a>

            <a href="mycode.php" class="bg-white p-4 rounded-xl shadow-sm flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center"><i class="fa-solid fa-ticket"></i></div>
                    <span class="font-bold text-gray-700">My Vouchers</span>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300"></i>
            </a>
        </div>

        <a href="logout.php" class="bg-white p-4 rounded-xl shadow-sm flex justify-between items-center hover:bg-red-50 group mt-4 transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center"><i class="fa-solid fa-power-off"></i></div>
                <span class="font-bold text-gray-700 group-hover:text-red-600">Log Out</span>
            </div>
        </a>
    </div>

    <div id="sec-edit" class="hidden">
        <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="font-bold text-lg mb-4">Edit Details</h3>
            <form method="POST" class="space-y-4 ajax-form"> 
                <input type="hidden" name="update_profile" value="1">
                <div>
                    <label class="text-xs font-bold text-gray-500">Full Name</label>
                    <input type="text" name="name" value="<?php echo $u['name']; ?>" class="w-full border p-3 rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500">Email Address</label>
                    <input type="email" name="email" value="<?php echo $u['email']; ?>" class="w-full border p-3 rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500">Phone Number</label>
                    <input type="text" value="<?php echo $u['phone']; ?>" readonly class="w-full border p-3 rounded-lg bg-gray-200 text-gray-500 cursor-not-allowed">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow hover:bg-blue-700">Save Changes</button>
            </form>
        </div>
    </div>

    <div id="sec-security" class="hidden">
        <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="font-bold text-lg mb-4">Change Password</h3>
            <form method="POST" class="space-y-4 ajax-form">
                <input type="hidden" name="change_pass" value="1">
                <div>
                    <label class="text-xs font-bold text-gray-500">Current Password</label>
                    <input type="password" name="current_pass" placeholder="••••••" required class="w-full border p-3 rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500">New Password</label>
                    <input type="password" name="new_pass" placeholder="••••••" required class="w-full border p-3 rounded-lg bg-gray-50">
                </div>
                <button type="submit" class="w-full bg-gray-800 text-white py-3 rounded-xl font-bold shadow hover:bg-black">Update Password</button>
            </form>
        </div>
    </div>

    <div class="text-center mt-8 py-4 text-xs text-gray-400">
        <p>&copy; 2025 <?php echo getSetting($conn, 'site_name'); ?>.</p>
        <a href="https://t.me/mraiprime" target="_blank" class="text-blue-400 hover:text-blue-600 transition decoration-none">Developed by SM Tahasin</a>
    </div>
</div>

<script>
    <?php if($msg): ?>
        document.addEventListener('DOMContentLoaded', () => {
            showNotif('<?php echo $msgType; ?>', '<?php echo ucfirst($msgType); ?>', '<?php echo $msg; ?>');
        });
    <?php endif; ?>

    function showSection(sec) {
        ['info', 'edit', 'security'].forEach(s => {
            document.getElementById('sec-'+s).classList.add('hidden');
            document.getElementById('btn-'+s).classList.remove('bg-white', 'shadow', 'text-blue-600');
            document.getElementById('btn-'+s).classList.add('text-gray-500');
        });
        document.getElementById('sec-'+sec).classList.remove('hidden');
        document.getElementById('btn-'+sec).classList.add('bg-white', 'shadow', 'text-blue-600');
        document.getElementById('btn-'+sec).classList.remove('text-gray-500');
    }
</script>

<?php include 'common/bottom.php'; ?>
