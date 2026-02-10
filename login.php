<?php
include 'common/config.php';
if(isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
// ... (PHP Logic remains same) ...
$msg = "";
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (Keep existing PHP logic) ...
    $type = $_POST['type'];
    if($type == 'login') {
        $email = $conn->real_escape_string($_POST['email']);
        $pass = $_POST['password'];
        $sql = "SELECT * FROM users WHERE email='$email' OR phone='$email'";
        $result = $conn->query($sql);
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if(password_verify($pass, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                header("Location: index.php"); exit;
            } else { $msg = "Invalid Password"; }
        } else { $msg = "User not found"; }
    } elseif($type == 'signup') {
         // ... Keep Signup logic
         $name = $conn->real_escape_string($_POST['name']);
         $phone = $conn->real_escape_string($_POST['phone']);
         $email = $conn->real_escape_string($_POST['email']);
         $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
         $sql = "INSERT INTO users (name, phone, email, password) VALUES ('$name', '$phone', '$email', '$pass')";
         if($conn->query($sql)) { $msg = "Account created! Please login."; } 
         else { $msg = "Error: Email or Phone already exists."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Join the Game</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Animated Background */
        .bg-animate {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    </style>
</head>
<body class="bg-animate flex items-center justify-center min-h-screen p-4">

<div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden border border-white/50 relative">
    
    <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500 rounded-full blur-3xl opacity-20"></div>
    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-purple-500 rounded-full blur-3xl opacity-20"></div>

    <div class="p-8 text-center pb-2">
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Welcome Back</h1>
        <p class="text-xs text-gray-500 mt-1">Enter your credentials to access your account.</p>
    </div>

    <div class="px-6 pb-2">
        <div class="flex bg-gray-100 p-1 rounded-xl">
            <button onclick="switchTab('login')" id="tab-login" class="w-1/2 py-2.5 text-center text-sm font-bold rounded-lg shadow-sm bg-white text-blue-600 transition-all">Log In</button>
            <button onclick="switchTab('signup')" id="tab-signup" class="w-1/2 py-2.5 text-center text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700 transition-all">Sign Up</button>
        </div>
    </div>

    <div class="p-6 pt-4">
        <?php if($msg): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-xl mb-4 text-center text-xs font-bold animate-pulse"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form id="form-login" method="POST" class="space-y-4">
            <input type="hidden" name="type" value="login">
            <div class="relative group">
                <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" name="email" placeholder="Email or Phone" required class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 bg-gray-50 focus:bg-white transition-all text-sm font-medium">
            </div>
            <div class="relative group">
                <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="password" name="password" placeholder="Password" required class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 bg-gray-50 focus:bg-white transition-all text-sm font-medium">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:scale-[1.02] active:scale-95 transition-all">
                Log In Securely
            </button>
        </form>

        <form id="form-signup" method="POST" class="space-y-4 hidden">
            <input type="hidden" name="type" value="signup">
            <div class="relative group">
                <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="name" placeholder="Full Name" required class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all text-sm">
            </div>
            <div class="relative group">
                <i class="fa-solid fa-phone absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="phone" placeholder="Phone Number" required class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all text-sm">
            </div>
            <div class="relative group">
                <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                <input type="email" name="email" placeholder="Email Address" required class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all text-sm">
            </div>
            <div class="relative group">
                <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400"></i>
                <input type="password" name="password" placeholder="Password" required class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all text-sm">
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-3.5 rounded-xl font-bold shadow-lg hover:bg-black hover:scale-[1.02] active:scale-95 transition-all">
                Create Account
            </button>
        </form>
    </div>
    
    <div class="bg-gray-50 py-3 text-center text-[10px] text-gray-400 border-t">
        Developed by <span class="font-bold text-blue-500">SM Tahasin</span>
    </div>
</div>

<script>
    function switchTab(tab) {
        const loginBtn = document.getElementById('tab-login');
        const signupBtn = document.getElementById('tab-signup');
        
        if(tab === 'login') {
            document.getElementById('form-login').classList.remove('hidden');
            document.getElementById('form-signup').classList.add('hidden');
            
            loginBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            loginBtn.classList.remove('text-gray-500');
            
            signupBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            signupBtn.classList.add('text-gray-500');
        } else {
            document.getElementById('form-login').classList.add('hidden');
            document.getElementById('form-signup').classList.remove('hidden');
            
            signupBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            signupBtn.classList.remove('text-gray-500');
            
            loginBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            loginBtn.classList.add('text-gray-500');
        }
    }
</script>
</body>
</html>
