<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['user_id'] ?? '');
    $userKey = trim($_POST['user_key'] ?? '');
    
    if (strlen($userId) === 5 && strlen($userKey) >= 6) {
        $userFile = __DIR__ . '/data/users/' . $userId . '.json';
        
        if (file_exists($userFile)) {
            $user = json_decode(file_get_contents($userFile), true);
            
            if ($user && $user['key'] === $userKey) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_symbol'] = $user['symbol'] ?? '👤';
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } elseif ($user['role'] === 'moderator') {
                    header('Location: moderator/index.php');
                } else {
                    header('Location: panel.php');
                }
                exit;
            } else {
                $error = 'ID və ya KEY yanlışdır';
            }
        } else {
            $error = 'Belə istifadəçi tapılmadı';
        }
    } else {
        $error = 'ID 5 rəqəm, KEY minimum 6 simvol olmalıdır';
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen flex items-center justify-center px-4 sm:px-6">
    
    <div class="glass-effect rounded-2xl p-6 sm:p-8 md:p-12 max-w-md w-full" data-aos="zoom-in">
        <div class="text-center mb-8 float-animation">
            <div class="text-5xl sm:text-6xl mb-4">🌙</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Düşüncə Mərkəzi</h1>
            <p class="text-purple-200 text-sm sm:text-base">Panelə daxil olun</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6" data-aos="shake">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-purple-200 mb-2 font-semibold text-sm">İstifadəçi ID</label>
                <input 
                    type="text" 
                    name="user_id" 
                    maxlength="5" 
                    required
                    placeholder="Məs: 12345"
                    autofocus
                    class="w-full bg-white bg-opacity-10 border border-purple-400 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder-purple-300 text-base"
                >
                <p class="text-xs sm:text-sm text-purple-300 mt-1">5 rəqəmli ID nömrənizi daxil edin</p>
            </div>

            <div>
                <label class="block text-purple-200 mb-2 font-semibold text-sm">Giriş KEY</label>
                <input 
                    type="password" 
                    name="user_key" 
                    minlength="6"
                    required
                    placeholder="******"
                    class="w-full bg-white bg-opacity-10 border border-purple-400 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder-purple-300 text-base"
                >
                <p class="text-xs sm:text-sm text-purple-300 mt-1">Şəxsi KEY kodunuzu daxil edin</p>
            </div>

            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 sm:py-4 rounded-lg transition transform hover:scale-105 text-base sm:text-lg"
            >
                🔐 Daxil Ol
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="index.php" class="text-purple-300 hover:text-purple-100 transition text-sm sm:text-base">
                ← Ana səhifəyə qayıt
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-purple-400 border-opacity-30">
            <p class="text-xs sm:text-sm text-purple-300 text-center">
                ID və KEY-iniz yoxdursa? Admin ilə əlaqə saxlayın.
            </p>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>

</body>
</html>