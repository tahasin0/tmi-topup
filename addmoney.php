<?php 
include 'common/header.php'; 

// Helper Function to convert ANY YouTube link to Embed link
function getYoutubeEmbedUrl($url) {
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
    $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

    if (preg_match($longUrlRegex, $url, $matches)) {
        $youtube_id = $matches[count($matches) - 1];
    }
    if (preg_match($shortUrlRegex, $url, $matches)) {
        $youtube_id = $matches[1];
    }
    return isset($youtube_id) ? 'https://www.youtube.com/embed/' . $youtube_id : $url;
}

$videoLink = getSetting($conn, 'add_money_video');
$embedLink = getYoutubeEmbedUrl($videoLink);
?>

<div class="container mx-auto px-4 py-6 mb-20">
    <h2 class="font-bold text-xl mb-4">Add Money</h2>
    
    <div class="bg-white p-6 rounded-xl shadow-sm text-center mb-6">
        <p class="text-gray-500 mb-2">Enter Amount to Add</p>
        
        <form action="instantpay.php" method="POST"> 
            <input type="hidden" name="game_id" value="0">
            <input type="hidden" name="product_id" value="0">
            <input type="hidden" name="game_name" value="Wallet Deposit">
            <input type="hidden" name="game_type" value="deposit">
            <input type="hidden" name="player_id" value="Wallet Balance">

            <input type="number" class="text-3xl font-bold text-center w-full border-b-2 border-blue-500 focus:outline-none mb-4 pb-2" placeholder="500" name="total_amount" required>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold shadow hover:bg-blue-700 transition">Click Here To Add Money</button>
        </form>
    </div>

    <h3 class="font-bold mb-3 border-l-4 border-green-500 pl-3">How to Add Money</h3>
    
    <?php if($videoLink): ?>
    <div class="aspect-video bg-black rounded-xl overflow-hidden shadow-lg relative">
        <iframe class="w-full h-full" src="<?php echo $embedLink; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    <?php else: ?>
        <p class="text-gray-400 text-sm">No video tutorial available.</p>
    <?php endif; ?>

    <div class="text-center py-4 text-xs text-gray-400 mt-6">
        <p>&copy; 2025 <?php echo getSetting($conn, 'site_name'); ?>.</p>
        <a href="https://t.me/mraiprime" target="_blank" class="text-blue-400 hover:text-blue-600 transition decoration-none">
            Developed by Mr Ai Prime
        </a>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
