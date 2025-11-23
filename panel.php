<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

function saveJSON($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$userId = $_SESSION['user_id'];
$userFile = __DIR__ . '/data/users/' . $userId . '.json';
$user = loadJSON($userFile);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle symbol change
if (isset($_POST['change_symbol'])) {
    $user['symbol'] = $_POST['symbol'];
    saveJSON($userFile, $user);
    $_SESSION['user_symbol'] = $user['symbol'];
    header('Location: panel.php?page=profile&success=symbol');
    exit;
}

// Handle KEY change
if (isset($_POST['change_key'])) {
    $newKey = trim($_POST['new_key']);
    if (strlen($newKey) >= 6) {
        $user['key'] = $newKey;
        saveJSON($userFile, $user);
        header('Location: panel.php?page=profile&success=key');
        exit;
    }
}

// Handle theme change
if (isset($_POST['change_theme'])) {
    $user['theme'] = $_POST['theme'];
    saveJSON($userFile, $user);
    header('Location: panel.php?page=profile&success=theme');
    exit;
}

$currentPage = $_GET['page'] ?? 'profile';
$themes = [
    'purple' => 'from-purple-900 via-indigo-900 to-blue-900',
    'blue' => 'from-blue-900 via-cyan-900 to-teal-900',
    'green' => 'from-green-900 via-emerald-900 to-teal-900',
    'red' => 'from-red-900 via-pink-900 to-purple-900'
];
$userTheme = $themes[$user['theme'] ?? 'purple'];
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s;
        }
        .sidebar.active {
            transform: translateX(0);
        }
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br <?= $userTheme ?> min-h-screen text-white">
    
    <!-- Mobile Header -->
    <div class="md:hidden glass-effect p-4 flex items-center justify-between fixed top-0 w-full z-50">
        <button id="menuBtn" class="text-2xl">☰</button>
        <span class="font-bold">Panel</span>
        <a href="index.php" class="text-2xl">🏠</a>
    </div>

    <div class="flex min-h-screen pt-16 md:pt-0">
        <!-- Sidebar -->
        <aside class="sidebar fixed md:static inset-y-0 left-0 w-64 glass-effect p-6 z-40 overflow-y-auto">
            <div class="mb-8 text-center" data-aos="fade-down">
                <div class="text-6xl mb-2"><?= htmlspecialchars($user['symbol'] ?? '👤') ?></div>
                <h2 class="text-xl font-bold"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-sm text-purple-300"><?= htmlspecialchars($user['group'] ?? 'İstifadəçi') ?></p>
                <span class="inline-block mt-2 px-3 py-1 bg-purple-600 rounded-full text-xs">
                    ID: <?= htmlspecialchars($user['id']) ?>
                </span>
            </div>

            <nav class="space-y-2">
                <a href="?page=profile" class="block px-4 py-3 rounded-lg <?= $currentPage === 'profile' ? 'bg-purple-600' : 'hover:bg-purple-600 hover:bg-opacity-50' ?> transition">
                    👤 Profilim
                </a>
                <a href="?page=create-post" class="block px-4 py-3 rounded-lg <?= $currentPage === 'create-post' ? 'bg-purple-600' : 'hover:bg-purple-600 hover:bg-opacity-50' ?> transition">
                    ✍️ Məqalə Yarat
                </a>
                <a href="?page=my-posts" class="block px-4 py-3 rounded-lg <?= $currentPage === 'my-posts' ? 'bg-purple-600' : 'hover:bg-purple-600 hover:bg-opacity-50' ?> transition">
                    📝 Məqalələrim
                </a>
                <a href="?page=create-poll" class="block px-4 py-3 rounded-lg <?= $currentPage === 'create-poll' ? 'bg-purple-600' : 'hover:bg-purple-600 hover:bg-opacity-50' ?> transition">
                    📊 Anket Yarat
                </a>
                <a href="?page=ticket" class="block px-4 py-3 rounded-lg <?= $currentPage === 'ticket' ? 'bg-purple-600' : 'hover:bg-purple-600 hover:bg-opacity-50' ?> transition">
                    🎫 Biletim
                </a>
            </nav>

            <div class="mt-8 pt-6 border-t border-purple-400 border-opacity-30">
                <a href="index.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 hover:bg-opacity-50 transition mb-2">
                    🏠 Ana Səhifə
                </a>
                <?php if ($user['role'] === 'moderator'): ?>
                <a href="moderator/index.php" class="block px-4 py-3 rounded-lg bg-yellow-600 hover:bg-yellow-700 transition mb-2">
                    👮 Moderator Panel
                </a>
                <?php endif; ?>
                <a href="?logout=1" class="block px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 transition text-center">
                    🚪 Çıxış
                </a>
            </div>
        </aside>

        <!-- Overlay -->
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                
                <?php if (isset($_GET['success'])): ?>
                <div class="glass-effect border-2 border-green-400 p-4 rounded-xl mb-6 animate-pulse" data-aos="fade-down">
                    <p class="text-green-300 font-semibold">✓ Dəyişikliklər yadda saxlanıldı!</p>
                </div>
                <?php endif; ?>

                <?php if ($currentPage === 'profile'): ?>
                    <!-- Profile Page -->
                    <div class="glass-effect rounded-xl p-6 sm:p-8" data-aos="fade-up">
                        <h1 class="text-2xl sm:text-3xl font-bold mb-8">👤 Profilim</h1>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-purple-200 mb-2 text-sm">ID</label>
                                <input type="text" value="<?= htmlspecialchars($user['id']) ?>" readonly class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg cursor-not-allowed text-sm sm:text-base">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 text-sm">Ad Soyad</label>
                                <input type="text" value="<?= htmlspecialchars($user['name']) ?>" readonly class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg cursor-not-allowed text-sm sm:text-base">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 text-sm">Əlaqə</label>
                                <input type="text" value="<?= htmlspecialchars($user['phone']) ?>" readonly class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg cursor-not-allowed text-sm sm:text-base">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 text-sm">Qrup</label>
                                <input type="text" value="<?= htmlspecialchars($user['group'] ?? 'Ümumi') ?>" readonly class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg cursor-not-allowed text-sm sm:text-base">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-3 text-sm font-semibold">Profil Simvolu</label>
                                <form method="POST" class="flex flex-wrap gap-3">
                                    <?php 
                                    $symbols = ['🌙', '⭐', '🔮', '🧠', '💡', '🎭', '📚', '🎨', '🌟', '✨', '🦋', '🌸'];
                                    foreach ($symbols as $symbol): 
                                    ?>
                                        <button type="submit" name="change_symbol" value="<?= $symbol ?>" class="text-4xl p-3 rounded-lg hover:bg-purple-600 transition transform hover:scale-110 <?= $user['symbol'] === $symbol ? 'bg-purple-600 ring-2 ring-purple-400' : 'bg-white bg-opacity-10' ?>">
                                            <?= $symbol ?>
                                        </button>
                                    <?php endforeach; ?>
                                </form>
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-3 text-sm font-semibold">Rəng Teması</label>
                                <form method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <?php foreach ($themes as $key => $gradient): ?>
                                    <button type="submit" name="change_theme" value="<?= $key ?>" class="h-20 rounded-lg bg-gradient-to-br <?= $gradient ?> hover:scale-105 transition transform <?= ($user['theme'] ?? 'purple') === $key ? 'ring-4 ring-white' : '' ?>">
                                        <?= ucfirst($key) ?>
                                    </button>
                                    <?php endforeach; ?>
                                </form>
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 text-sm font-semibold">KEY Dəyiş</label>
                                <form method="POST" class="flex flex-col sm:flex-row gap-2">
                                    <input type="password" name="new_key" minlength="6" placeholder="Yeni KEY (min 6 simvol)" class="flex-1 bg-white bg-opacity-10 px-4 py-3 rounded-lg text-sm sm:text-base border border-purple-400">
                                    <button type="submit" name="change_key" class="bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-lg transition transform hover:scale-105 text-sm sm:text-base whitespace-nowrap">
                                        Yenilə
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif ($currentPage === 'ticket'): ?>
                    <!-- Ticket Page - INLINE -->
                    <div class="glass-effect rounded-xl p-6 sm:p-8" data-aos="fade-up">
                        <h1 class="text-2xl sm:text-3xl font-bold mb-8">🎫 Biletlərim</h1>
                        
                        <?php if (!empty($user['tickets'])): ?>
                            <div class="space-y-6">
                                <?php foreach ($user['tickets'] as $ticketId): 
                                    $ticket = loadJSON(__DIR__ . '/data/tickets/' . $ticketId . '.json');
                                    if ($ticket):
                                ?>
                                <div class="bg-white bg-opacity-10 rounded-xl p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg sm:text-xl font-bold"><?= htmlspecialchars($ticket['event_name'] ?? 'Tədbir') ?></h3>
                                        <span class="text-2xl">🎭</span>
                                    </div>
                                    
                                    <div class="grid sm:grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <p class="text-xs text-purple-300">Ad Soyad</p>
                                            <p class="font-bold"><?= htmlspecialchars($ticket['name']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-purple-300">Qrup</p>
                                            <p class="font-bold"><?= htmlspecialchars($ticket['group']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-purple-300">Oturacaq</p>
                                            <p class="font-bold text-xl text-green-400"><?= htmlspecialchars($ticket['seat']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-purple-300">Status</p>
                                            <p class="font-bold <?= $ticket['used'] ? 'text-red-400' : 'text-green-400' ?>">
                                                <?= $ticket['used'] ? '❌ İstifadə edilib' : '✅ Aktiv' ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <a href="ticket.php?id=<?= $ticketId ?>" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-3 rounded-lg transition transform hover:scale-105 text-sm sm:text-base" target="_blank">
                                        Bileti Aç →
                                    </a>
                                </div>
                                <?php endif; endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4">🎫</div>
                                <p class="text-xl text-purple-300 mb-4">Hələ biletiniz yoxdur</p>
                                <p class="text-purple-400 mb-6">Tədbirlər səhifəsindən bilet ala bilərsiniz</p>
                                <a href="events.php" class="inline-block bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-lg transition">
                                    Tədbirlərə Bax →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($currentPage === 'create-post'): ?>
                    <!-- Create Post - INLINE -->
                    <div class="glass-effect rounded-xl p-6 sm:p-8" data-aos="fade-up">
                        <h1 class="text-2xl sm:text-3xl font-bold mb-8">✍️ Yeni Məqalə Yarat</h1>
                        
                        <form method="POST" action="create-post.php" enctype="multipart/form-data" class="space-y-6">
                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Başlıq *</label>
                                <input type="text" name="title" required maxlength="200" placeholder="Məqalənin başlığı..." class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm sm:text-base text-white">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Bölmə *</label>
                                <select name="topic" required class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm sm:text-base text-white">
                                    <option value="philosophy">🧠 Fəlsəfə</option>
                                    <option value="religion">🕌 Din & Mənəviyyat</option>
                                    <option value="art">🎨 İncəsənət & Simvolika</option>
                                    <option value="multiculturalism">🌍 Multikulturalizm</option>
                                    <option value="personal">💡 Şəxsiyyət İnkişafı</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Mətn *</label>
                                <textarea name="text" rows="12" required placeholder="Məqalənizin mətni..." class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm sm:text-base text-white"></textarea>
                                <p class="text-xs text-purple-300 mt-1">Fikirlərinizi ətraflı və aydın ifadə edin</p>
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Şəkil (opsional)</label>
                                <input type="file" name="image" accept="image/*" class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 text-sm text-white">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Audio (opsional)</label>
                                <input type="file" name="audio" accept="audio/*" class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 text-sm text-white">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Sənəd (PDF/DOCX/TXT)</label>
                                <input type="file" name="document" accept=".pdf,.docx,.txt" class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 text-sm text-white">
                            </div>

                            <div class="bg-blue-900 bg-opacity-30 p-4 rounded-lg">
                                <p class="text-sm text-blue-200">
                                    ℹ️ Məqaləniz moderator tərəfindən təsdiq edildikdən sonra yayımlanacaq
                                </p>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105">
                                📤 Paylaş
                            </button>
                        </form>
                    </div>

                <?php elseif ($currentPage === 'my-posts'): ?>
                    <!-- My Posts - INLINE -->
                    <?php
                    $myPosts = [];
                    $postsDir = __DIR__ . '/data/posts/';
                    if (is_dir($postsDir)) {
                        foreach (array_diff(scandir($postsDir), ['.', '..']) as $file) {
                            $post = loadJSON($postsDir . $file);
                            if ($post && $post['author'] === $userId) {
                                $myPosts[] = $post;
                            }
                        }
                        usort($myPosts, fn($a, $b) => ($b['created'] ?? 0) - ($a['created'] ?? 0));
                    }
                    ?>
                    <div class="glass-effect rounded-xl p-6 sm:p-8" data-aos="fade-up">
                        <h1 class="text-2xl sm:text-3xl font-bold mb-8">📝 Məqalələrim (<?= count($myPosts) ?>)</h1>
                        
                        <?php if (empty($myPosts)): ?>
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4">📝</div>
                                <p class="text-xl text-purple-300 mb-6">Hələ məqalə yazmamısınız</p>
                                <a href="?page=create-post" class="inline-block bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-lg transition">
                                    İlk Məqaləni Yaz →
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-6">
                                <?php foreach ($myPosts as $post): ?>
                                <div class="bg-white bg-opacity-10 rounded-xl p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg sm:text-xl font-bold mb-2"><?= htmlspecialchars($post['title']) ?></h3>
                                            <p class="text-xs sm:text-sm text-purple-300"><?= date('d.m.Y, H:i', $post['created']) ?></p>
                                        </div>
                                        <div>
                                            <?php if ($post['approved'] ?? false): ?>
                                                <span class="px-3 py-1 bg-green-600 rounded-full text-xs">✓ Təsdiqlənib</span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-yellow-600 rounded-full text-xs">⏳ Gözləyir</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <p class="text-sm text-purple-200 mb-4 line-clamp-3"><?= htmlspecialchars(substr($post['text'], 0, 200)) ?>...</p>
                                    
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center space-x-4 text-purple-300">
                                            <span>👍 <?= count($post['likes'] ?? []) ?></span>
                                            <span>💬 <?= count($post['comments'] ?? []) ?></span>
                                        </div>
                                        <?php if ($post['approved'] ?? false): ?>
                                            <a href="article.php?id=<?= $post['id'] ?>" class="text-blue-300 hover:text-blue-200">Bax →</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($currentPage === 'create-poll'): ?>
                    <!-- Create Poll - INLINE -->
                    <div class="glass-effect rounded-xl p-6 sm:p-8" data-aos="fade-up">
                        <h1 class="text-2xl sm:text-3xl font-bold mb-8">📊 Yeni Anket Yarat</h1>
                        
                        <form method="POST" action="create-poll.php" class="space-y-6">
                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Anket Başlığı *</label>
                                <input type="text" name="title" required maxlength="200" placeholder="Məs: Sizə görə ən vacib dəyər hansıdır?" class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm sm:text-base text-white">
                            </div>

                            <div>
                                <label class="block text-purple-200 mb-2 font-semibold text-sm">Cavab Variantları (min 2, max 10)</label>
                                <div id="options" class="space-y-3">
                                    <input type="text" name="options[]" required placeholder="Variant 1" class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 text-sm sm:text-base text-white">
                                    <input type="text" name="options[]" required placeholder="Variant 2" class="w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 text-sm sm:text-base text-white">
                                </div>
                                <button type="button" onclick="addOption()" class="mt-3 bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition text-sm">
                                    + Variant Əlavə Et
                                </button>
                            </div>

                            <div class="bg-blue-900 bg-opacity-30 p-4 rounded-lg">
                                <p class="text-sm text-blue-200">
                                    ℹ️ Anket yaradıldıqdan sonra digər istifadəçilər cavab verə biləcək
                                </p>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105">
                                📤 Anketi Yarat
                            </button>
                        </form>
                    </div>

                    <script>
                    let optionCount = 2;
                    function addOption() {
                        if (optionCount >= 10) {
                            alert('Maksimum 10 variant əlavə edə bilərsiniz');
                            return;
                        }
                        optionCount++;
                        const div = document.getElementById('options');
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'options[]';
                        input.placeholder = 'Variant ' + optionCount;
                        input.className = 'w-full bg-white bg-opacity-10 px-4 py-3 rounded-lg border border-purple-400 text-sm sm:text-base text-white';
                        div.appendChild(input);
                    }
                    </script>

                <?php else: ?>
                    <div class="glass-effect rounded-xl p-8" data-aos="fade-up">
                        <h1 class="text-3xl font-bold mb-4"><?= ucfirst($currentPage) ?></h1>
                        <p class="text-purple-300">Bu bölmə hazırlanır...</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('overlay');

        menuBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
        });
    </script>

</body>
</html>