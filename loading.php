<!-- lottie-loading.php -->
<div id="loading" class="lottie-loading-screen">
  <div id="lottie-loader"></div>
  <p class="loading-text">Please wait...</p>
</div>

<!-- Lottie Player JS লাইব্রেরি -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<style>
.lottie-loading-screen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.4s ease, visibility 0.4s;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.lottie-loading-screen.active {
  opacity: 1;
  visibility: visible;
}

#lottie-loader {
  width: 200px;
  height: 200px;
  margin-bottom: 20px;
}

.loading-text {
  font-size: 18px;
  font-weight: 300;
  letter-spacing: 1px;
  color: #64b5f6;
  margin-top: -10px;
  animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
  0% { opacity: 0.6; }
  100% { opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const loadingScreen = document.getElementById('loading');
  const lottieContainer = document.getElementById('lottie-loader');
  
  // লট্টি অ্যানিমেশন তৈরি
  const lottiePlayer = document.createElement('lottie-player');
  lottiePlayer.setAttribute('src', 'https://assets3.lottiefiles.com/packages/lf20_usmfx6bp.json');
  lottiePlayer.setAttribute('background', 'transparent');
  lottiePlayer.setAttribute('speed', '1');
  lottiePlayer.setAttribute('style', 'width: 200px; height: 200px;');
  lottiePlayer.setAttribute('loop', '');
  lottiePlayer.setAttribute('autoplay', '');
  
  lottieContainer.appendChild(lottiePlayer);

  // সব লিঙ্কে ইভেন্ট যোগ
  document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function(e) {
      // শুধু আপনার সাইটের internal links-এর জন্য
      const href = this.getAttribute('href');
      const isExternal = this.target === '_blank' || 
                         this.hasAttribute('download') ||
                         href.startsWith('http') && !href.includes(window.location.hostname) ||
                         href.startsWith('#') ||
                         href.startsWith('javascript:') ||
                         href.startsWith('mailto:') ||
                         href.startsWith('tel:');
      
      if (!isExternal && href && !href.includes('#')) {
        // লোডিং দেখান
        loadingScreen.classList.add('active');
        lottiePlayer.play();
        
        // 2 সেকেন্ড পর অটো হাইড (সিকিউরিটি)
        setTimeout(() => {
          loadingScreen.classList.remove('active');
        }, 2500);
      }
    });
  });

  // পেজ লোড হলে লোডিং হাইড
  window.addEventListener('load', function() {
    setTimeout(() => {
      loadingScreen.classList.remove('active');
    }, 500);
  });

  // AJAX রিকুয়েস্টের জন্য (যদি থাকে)
  document.addEventListener('ajaxStart', function() {
    loadingScreen.classList.add('active');
  });
  
  document.addEventListener('ajaxStop', function() {
    loadingScreen.classList.remove('active');
  });
});
</script>