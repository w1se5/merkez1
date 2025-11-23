<?php
session_start();

function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

// Load recent posts
$posts = [];
$postsDir = __DIR__ . '/data/posts/';
if (is_dir($postsDir)) {
    foreach (array_diff(scandir($postsDir), ['.', '..']) as $file) {
        $post = loadJSON($postsDir . $file);
        if ($post && ($post['approved'] ?? false)) $posts[] = $post;
    }
    usort($posts, fn($a, $b) => ($b['created'] ?? 0) - ($a['created'] ?? 0));
}
$recentPosts = array_slice($posts, 0, 6);

// Load next event
$nextEvent = null;
$eventsDir = __DIR__ . '/data/events/';
if (is_dir($eventsDir)) {
    foreach (array_diff(scandir($eventsDir), ['.', '..']) as $file) {
        $event = loadJSON($eventsDir . $file);
        if ($event && ($event['date'] ?? 0) > time()) {
            $nextEvent = $event;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation { animation: float 6s ease-in-out infinite; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.active {
            transform: translateX(0);
        }
        .menu-icon span {
            display: block;
            width: 25px;
            height: 3px;
            background: white;
            margin: 5px 0;
            transition: 0.3s;
        }
        .menu-icon.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        .menu-icon.active span:nth-child(2) {
            opacity: 0;
        }
        .menu-icon.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen text-white">
    
    <!-- Navigation -->
    <nav class="glass-effect fixed w-full top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-xl sm:text-2xl font-bold gradient-text flex items-center">
                    <span class="text-2xl sm:text-3xl mr-2">🌙</span>
                    <span class="hidden sm:inline">Düşüncə Mərkəzi</span>
                    <span class="sm:hidden">DM</span>
                </div>
                
                <!-- Mobile menu button -->
                <button class="md:hidden menu-icon" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Desktop menu -->
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="hover:text-purple-300 transition">Ana Səhifə</a>
                    <a href="topics.php" class="hover:text-purple-300 transition">Bölmələr</a>
                    <a href="articles.php" class="hover:text-purple-300 transition">Məqalələr</a>
                    <a href="events.php" class="hover:text-purple-300 transition">Tədbirlər</a>
                    <a href="polls.php" class="hover:text-purple-300 transition">Anketlər</a>
                    <a href="archive.php" class="hover:text-purple-300 transition">Arxiv</a>
                </div>
                
                <div class="hidden md:block">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="panel.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg transition transform hover:scale-105">
                            Panel
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-4 py-2 rounded-lg transition transform hover:scale-105">
                            Giriş
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu fixed inset-y-0 left-0 w-64 glass-effect z-40 p-6 md:hidden" id="mobileMenu">
        <div class="mt-20 space-y-4">
            <a href="index.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 transition">🏠 Ana Səhifə</a>
            <a href="topics.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 transition">📚 Bölmələr</a>
            <a href="articles.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 transition">📝 Məqalələr</a>
            <a href="events.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 transition">🎫 Tədbirlər</a>
            <a href="polls.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 transition">📊 Anketlər</a>
            <a href="archive.php" class="block px-4 py-3 rounded-lg hover:bg-purple-600 transition">📂 Arxiv</a>
            <div class="pt-4 border-t border-purple-400">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="panel.php" class="block bg-purple-600 px-4 py-3 rounded-lg text-center">👤 Panel</a>
                <?php else: ?>
                    <a href="login.php" class="block bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-3 rounded-lg text-center">🔐 Giriş</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden" id="overlay"></div>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6" data-aos="fade-up">
        <div class="container mx-auto text-center">
            <div class="float-animation mb-8">
                <h1 class="text-4xl sm:text-5xl md:text-7xl font-bold mb-6">
                    Düşüncə Mərkəzi
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-purple-200 max-w-3xl mx-auto">
                    Azad düşüncə, hörmət və elm prinsipləri ilə birgə inkişaf edirik
                </p>
            </div>
            
            <?php if ($nextEvent): ?>
            <a href="events.php" class="inline-block mt-8 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 sm:px-8 py-3 sm:py-4 rounded-full text-base sm:text-lg font-semibold transition transform hover:scale-105" data-aos="zoom-in" data-aos-delay="200">
                📅 Növbəti Tədbir: <?= htmlspecialchars($nextEvent['title']) ?>
            </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-20 px-4 sm:px-6">
        <div class="container mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center mb-16" data-aos="fade-up">Əsas Prinsiplərimiz</h2>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
                <div class="glass-effect p-6 sm:p-8 rounded-xl hover:scale-105 transition transform" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl sm:text-5xl mb-4">🧠</div>
                    <h3 class="text-xl sm:text-2xl font-bold mb-4">Azad Düşüncə</h3>
                    <p class="text-purple-200">Hər kəs öz fikirlərini sərbəst ifadə edə bilir. Fərqli baxış bucaqlarına hörmət edirik.</p>
                </div>
                <div class="glass-effect p-6 sm:p-8 rounded-xl hover:scale-105 transition transform" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl sm:text-5xl mb-4">🤝</div>
                    <h3 class="text-xl sm:text-2xl font-bold mb-4">Hörmət</h3>
                    <p class="text-purple-200">Qarşılıqlı hörmət və anlayış bizim inkişafın təməlidir.</p>
                </div>
                <div class="glass-effect p-6 sm:p-8 rounded-xl hover:scale-105 transition transform" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl sm:text-5xl mb-4">🔬</div>
                    <h3 class="text-xl sm:text-2xl font-bold mb-4">Elm</h3>
                    <p class="text-purple-200">Bilik və elm əsasında düşünür, araşdırır və paylaşırıq.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Articles -->
    <section class="py-20 px-4 sm:px-6 bg-black bg-opacity-20">
        <div class="container mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-center mb-16" data-aos="fade-up">Son Məqalələr</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <?php foreach ($recentPosts as $i => $post): ?>
                <a href="article.php?id=<?= $post['id'] ?>" class="glass-effect p-6 rounded-xl hover:scale-105 transition transform block" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                    <div class="flex items-center mb-4">
                        <div class="text-2xl sm:text-3xl mr-3"><?= $post['author_symbol'] ?? '👤' ?></div>
                        <div>
                            <div class="font-semibold text-sm sm:text-base"><?= htmlspecialchars($post['author_name'] ?? 'Anonim') ?></div>
                            <div class="text-xs sm:text-sm text-purple-300"><?= date('d.m.Y', $post['created']) ?></div>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-3"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="text-purple-200 text-sm sm:text-base line-clamp-3"><?= htmlspecialchars(substr($post['text'], 0, 150)) ?>...</p>
                    <div class="mt-4 flex items-center text-xs sm:text-sm text-purple-300">
                        <span class="mr-4">👍 <?= count($post['likes'] ?? []) ?></span>
                        <span>💬 <?= count($post['comments'] ?? []) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12" data-aos="fade-up">
                <a href="articles.php" class="inline-block bg-purple-600 hover:bg-purple-700 px-8 py-3 rounded-lg transition transform hover:scale-105">
                    Bütün Məqalələr →
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-4 sm:px-6 text-center text-purple-300">
        <div class="container mx-auto">
            <div class="text-4xl mb-4">🌙</div>
            <p class="mb-2">&copy; 2025 Düşüncə Mərkəzi Bütün hüquqlar qorunur </p>
            <p class="text-sm">Azad düşüncə, hörmət və elm</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('overlay');

        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            mobileMenu.classList.remove('active');
            overlay.classList.add('hidden');
        });
    </script>

</body>
</html>