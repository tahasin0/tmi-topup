<?php include 'common/header.php'; ?>

<div class="container mx-auto px-4 mt-4">
    <div class="relative w-full overflow-hidden h-48 md:h-72 rounded-2xl shadow-xl shadow-blue-900/10 group">
        <div id="slider" class="flex transition-transform duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] h-full">
            <?php 
            $sliders = $conn->query("SELECT * FROM sliders");
            while($slide = $sliders->fetch_assoc()): ?>
                <a href="<?php echo $slide['link'] ? $slide['link'] : '#'; ?>" class="min-w-full h-full relative">
                    <img src="<?php echo $slide['image']; ?>" class="w-full h-full object-cover transform transition duration-700 hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                </a>
            <?php endwhile; ?>
        </div>
        </div>
</div>

<script>
    let idx = 0;
    const slides = document.getElementById('slider');
    const totalSlides = slides.children.length;
    if(totalSlides > 0) {
        setInterval(() => {
            idx = (idx + 1) % totalSlides;
            slides.style.transform = `translateX(-${idx * 100}%)`;
        }, 4000);
    }
</script>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full"></span>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Popular Games</h2>
        </div>
        <a href="game.php" class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition flex items-center gap-1">
            View All <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <?php 
        $games = $conn->query("SELECT * FROM games");
        if($games->num_rows > 0):
            while($game = $games->fetch_assoc()): ?>
            <a href="game_detail.php?id=<?php echo $game['id']; ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl hover:shadow-blue-500/20 border border-gray-100 overflow-hidden transition-all duration-300 transform hover:-translate-y-2">
                <div class="relative aspect-square overflow-hidden bg-gray-100">
                    <img src="<?php echo $game['image']; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="<?php echo $game['name']; ?>">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                    <div class="absolute bottom-0 left-0 w-full p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <button class="w-full bg-white/90 backdrop-blur text-blue-600 text-xs font-bold py-2 rounded-lg shadow-lg">TOP UP NOW</button>
                    </div>
                </div>
                <div class="p-4 text-center relative z-10 bg-white">
                    <h3 class="font-bold text-gray-800 text-sm md:text-base truncate mb-1"><?php echo $game['name']; ?></h3>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider group-hover:text-blue-500 transition-colors">Instant Delivery</p>
                </div>
            </a>
        <?php endwhile; 
        else: ?>
            <div class="col-span-full flex flex-col items-center justify-center py-10 opacity-50">
                <i class="fa-solid fa-ghost text-4xl mb-2"></i>
                <p>No games found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/footer.php'; ?>
<?php include 'common/bottom.php'; ?>
