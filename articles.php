<?php
session_start();

function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

$topic = $_GET['topic'] ?? null;
$search = $_GET['search'] ?? '';

// Load all approved posts
$posts = [];
$postsDir = __DIR__ . '/data/posts/';
if (is_dir($postsDir)) {
    foreach (array_diff(scandir($postsDir), ['.', '..']) as $file) {
        $post = loadJSON($postsDir . $file);
        if ($post && ($post['approved'] ?? false)) {
            if ($topic && $post['topic'] !== $topic) continue;
            if ($search && stripos($post['title'], $search) === false && stripos($post['text'], $search) === false) continue;
            $posts[] = $post;
        }
    }
    usort($posts, fn($a, $b) => ($b['created'] ?? 0) - ($a['created'] ?? 0));
}

$topics = [
    'philosophy' => ['Fəlsəfə', '🧠'],
    'religion' => ['Din & Mənəviyyat', '🕌'],
    'art' => ['İncəsənət & Simvolika', '🎨'],
    'multiculturalism' => ['Multikulturalizm', '🌍'],
    'personal' => ['Şəxsiyyət İnkişafı', '💡']
];
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Məqalələr - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen text-white">
    
    <nav class="glass-effect p-4 sm:p-6 mb-8">
        <div class="container mx-auto flex flex-wrap items-center justify-between gap-4">
            <a href="index.php" class="text-xl sm:text-2xl font-bold flex items-center">
                <span class="mr-2">🌙</span> <span class="hidden sm:inline">Düşüncə Mərkəzi</span>
            </a>
            <form method="GET" class="flex-1 max-w-md">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Axtar..." class="w-full bg-white bg-opacity-10 px-4 py-2 rounded-lg border border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </form>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="panel.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base whitespace-nowrap">Panel</a>
            <?php else: ?>
                <a href="login.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base whitespace-nowrap">Giriş</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 pb-20">
        <!-- Topics Filter -->
        <div class="mb-8" data-aos="fade-down">
            <div class="flex flex-wrap gap-3">
                <a href="articles.php" class="<?= !$topic ? 'bg-purple-600' : 'bg-white bg-opacity-10 hover:bg-opacity-20' ?> px-4 py-2 rounded-lg transition text-sm sm:text-base">
                    📚 Hamısı
                </a>
                <?php foreach ($topics as $key => $info): ?>
                <a href="?topic=<?= $key ?>" class="<?= $topic === $key ? 'bg-purple-600' : 'bg-white bg-opacity-10 hover:bg-opacity-20' ?> px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap">
                    <?= $info[1] ?> <?= $info[0] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <h1 class="text-3xl sm:text-5xl font-bold mb-8" data-aos="fade-up">
            <?= $topic ? $topics[$topic][1] . ' ' . $topics[$topic][0] : '📝 Bütün Məqalələr' ?>
        </h1>

        <?php if (empty($posts)): ?>
        <div class="glass-effect p-12 rounded-xl text-center" data-aos="fade-up">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-xl text-purple-300">Məqalə tapılmadı</p>
        </div>
        <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($posts as $i => $post): ?>
            <a href="article.php?id=<?= $post['id'] ?>" class="glass-effect p-6 rounded-xl hover:scale-105 transition transform block group" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                <div class="flex items-center mb-4">
                    <div class="text-3xl mr-3 group-hover:scale-110 transition"><?= $post['author_symbol'] ?? '👤' ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold truncate"><?= htmlspecialchars($post['author_name'] ?? 'Anonim') ?></div>
                        <div class="text-xs sm:text-sm text-purple-300"><?= date('d.m.Y', $post['created']) ?></div>
                    </div>
                    <div class="text-2xl"><?= $topics[$post['topic']][1] ?? '📝' ?></div>
                </div>
                
                <?php if (!empty($post['files'])): ?>
                    <?php foreach ($post['files'] as $file): ?>
                        <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)): ?>
                            <div class="mb-4 rounded-lg overflow-hidden">
                                <img src="<?= htmlspecialchars($file) ?>" alt="" class="w-full h-48 object-cover group-hover:scale-110 transition duration-500">
                            </div>
                        <?php break; endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <h3 class="text-lg sm:text-xl font-bold mb-3 line-clamp-2"><?= htmlspecialchars($post['title']) ?></h3>
                <p class="text-purple-200 text-sm line-clamp-3 mb-4"><?= htmlspecialchars(substr(strip_tags($post['text']), 0, 150)) ?>...</p>
                
                <div class="flex items-center justify-between text-xs sm:text-sm text-purple-300">
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center">
                            <span class="mr-1">👍</span> <?= count($post['likes'] ?? []) ?>
                        </span>
                        <span class="flex items-center">
                            <span class="mr-1">💬</span> <?= count($post['comments'] ?? []) ?>
                        </span>
                    </div>
                    <span class="text-purple-400">Oxu →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>

</body>
</html>