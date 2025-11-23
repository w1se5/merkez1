<?php
session_start();

function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

// Load all approved posts
$posts = [];
$postsDir = __DIR__ . '/data/posts/';
if (is_dir($postsDir)) {
    foreach (array_diff(scandir($postsDir), ['.', '..']) as $file) {
        $post = loadJSON($postsDir . $file);
        if ($post && ($post['approved'] ?? false)) {
            $posts[] = $post;
        }
    }
}

$topics = [
    'philosophy' => [
        'name' => 'Fəlsəfə',
        'icon' => '🧠',
        'description' => 'Həyatın mənası, varlıq, bilik və dəyərlər haqqında dərin düşüncələr',
        'color' => 'from-blue-600 to-purple-600'
    ],
    'religion' => [
        'name' => 'Din & Mənəviyyat',
        'icon' => '🕌',
        'description' => 'İnanc, mənəvi dəyərlər və dinlərarası dialoq',
        'color' => 'from-green-600 to-teal-600'
    ],
    'art' => [
        'name' => 'İncəsənət & Simvolika',
        'icon' => '🎨',
        'description' => 'Sənət, mədəniyyət, simvollar və estetika',
        'color' => 'from-pink-600 to-rose-600'
    ],
    'multiculturalism' => [
        'name' => 'Multikulturalizm',
        'icon' => '🌍',
        'description' => 'Fərqli mədəniyyətlərin harmoniyası və birgəyaşayış',
        'color' => 'from-orange-600 to-yellow-600'
    ],
    'personal' => [
        'name' => 'Şəxsiyyət İnkişafı',
        'icon' => '💡',
        'description' => 'Özünütanıma, inkişaf və şəxsi böyümə',
        'color' => 'from-indigo-600 to-blue-600'
    ]
];

// Count posts per topic
$topicCounts = [];
foreach ($topics as $key => $topic) {
    $topicCounts[$key] = count(array_filter($posts, fn($p) => $p['topic'] === $key));
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bölmələr - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .topic-card {
            transition: all 0.3s ease;
        }
        .topic-card:hover {
            transform: translateY(-10px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen text-white">
    
    <nav class="glass-effect p-4 sm:p-6 mb-8">
        <div class="container mx-auto flex items-center justify-between">
            <a href="index.php" class="text-xl sm:text-2xl font-bold flex items-center">
                <span class="mr-2">🌙</span> <span class="hidden sm:inline">Düşüncə Mərkəzi</span>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="panel.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base">Panel</a>
            <?php else: ?>
                <a href="login.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base">Giriş</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 pb-20">
        <h1 class="text-3xl sm:text-5xl font-bold mb-4 sm:mb-8" data-aos="fade-up">📚 Bölmələr</h1>
        <p class="text-purple-200 mb-12 text-sm sm:text-base" data-aos="fade-up" data-aos-delay="100">
            Maraqlandığınız bölməni seçin və məqalələri kəşf edin
        </p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($topics as $key => $topic): ?>
            <a href="articles.php?topic=<?= $key ?>" class="topic-card glass-effect rounded-xl p-6 sm:p-8 block" data-aos="fade-up" data-aos-delay="<?= array_search($key, array_keys($topics)) * 100 ?>">
                <div class="text-5xl sm:text-6xl mb-4"><?= $topic['icon'] ?></div>
                <h2 class="text-xl sm:text-2xl font-bold mb-3"><?= $topic['name'] ?></h2>
                <p class="text-purple-200 text-sm sm:text-base mb-6"><?= $topic['description'] ?></p>
                
                <div class="flex items-center justify-between">
                    <div class="bg-gradient-to-r <?= $topic['color'] ?> px-4 py-2 rounded-full text-sm sm:text-base font-semibold">
                        <?= $topicCounts[$key] ?> məqalə
                    </div>
                    <div class="text-purple-300 text-xl">→</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Recent from all topics -->
        <div class="mt-16" data-aos="fade-up">
            <h2 class="text-2xl sm:text-3xl font-bold mb-8">Son Məqalələr</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <?php 
                usort($posts, fn($a, $b) => ($b['created'] ?? 0) - ($a['created'] ?? 0));
                $recentPosts = array_slice($posts, 0, 6);
                foreach ($recentPosts as $i => $post): 
                ?>
                <a href="article.php?id=<?= $post['id'] ?>" class="glass-effect p-6 rounded-xl hover:scale-105 transition transform block" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                    <div class="flex items-center mb-4">
                        <div class="text-3xl mr-3"><?= $post['author_symbol'] ?? '👤' ?></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold truncate text-sm"><?= htmlspecialchars($post['author_name'] ?? 'Anonim') ?></div>
                            <div class="text-xs text-purple-300"><?= date('d.m.Y', $post['created']) ?></div>
                        </div>
                        <div class="text-2xl"><?= $topics[$post['topic']]['icon'] ?? '📝' ?></div>
                    </div>
                    <h3 class="text-lg font-bold mb-3 line-clamp-2"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="text-purple-200 text-sm line-clamp-3"><?= htmlspecialchars(substr($post['text'], 0, 100)) ?>...</p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>

</body>
</html>