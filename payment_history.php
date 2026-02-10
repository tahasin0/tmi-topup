<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];
?>

<div class="container mx-auto px-4 py-6 mb-20">
    <div class="flex items-center gap-2 mb-6 border-b pb-2">
        <a href="profile.php" class="text-gray-500 hover:text-blue-600"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-xl font-bold text-gray-800">Payment History</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3">TrxID</th>
                        <th class="px-4 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    $deposits = $conn->query("SELECT * FROM deposits WHERE user_id=$uid ORDER BY id DESC");
                    if($deposits && $deposits->num_rows > 0):
                        while($d = $deposits->fetch_assoc()): 
                            $stColor = 'bg-yellow-100 text-yellow-700';
                            if($d['status'] == 'approved') $stColor = 'bg-green-100 text-green-700';
                            if($d['status'] == 'rejected') $stColor = 'bg-red-100 text-red-700';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            <?php echo date('d M Y', strtotime($d['created_at'])); ?><br>
                            <?php echo date('h:i A', strtotime($d['created_at'])); ?>
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-800"><?php echo getSetting($conn, 'currency').$d['amount']; ?></td>
                        <td class="px-4 py-3 text-xs"><?php echo $d['method']; ?></td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-500 select-all"><?php echo $d['trx_id']; ?></td>
                        <td class="px-4 py-3 text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo $stColor; ?>">
                                <?php echo $d['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">No payment records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center py-4 text-xs text-gray-400 mt-6">
        <p>&copy; 2025 <?php echo getSetting($conn, 'site_name'); ?>.</p>
        <a href="https://t.me/mraiprime" target="_blank" class="text-blue-400 hover:text-blue-600 transition decoration-none">Developed by SM Tahasiin</a>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
