<?php
session_start();

function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

function saveJSON($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$postId = $_GET['id'] ?? '';
$postFile = __DIR__ . '/data/posts/' . $postId . '.json';
$post = loadJSON($postFile);

if (!$post) {
    die('Məqalə tapılmadı');
}

// Handle like
if (isset($_POST['like']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    if (!isset($post['likes'])) $post['likes'] = [];
    if (!isset($post['dislikes'])) $post['dislikes'] = [];
    
    // Remove from dislikes
    $post['dislikes'] = array_values(array_diff($post['dislikes'], [$userId]));
    
    // Toggle like
    if (in_array($userId, $post['likes'])) {
        $post['likes'] = array_values(array_diff($post['likes'], [$userId]));
    } else {
        $post['likes'][] = $userId;
    }
    
    saveJSON($postFile, $post);
    header('Location: article.php?id=' . $postId);
    exit;
}

// Handle dislike
if (isset($_POST['dislike']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    if (!isset($post['likes'])) $post['likes'] = [];
    if (!isset($post['dislikes'])) $post['dislikes'] = [];
    
    // Remove from likes
    $post['likes'] = array_values(array_diff($post['likes'], [$userId]));
    
    // Toggle dislike
    if (in_array($userId, $post['dislikes'])) {
        $post['dislikes'] = array_values(array_diff($post['dislikes'], [$userId]));
    } else {
        $post['dislikes'][] = $userId;
    }
    
    saveJSON($postFile, $post);
    header('Location: article.php?id=' . $postId);
    exit;
}

// Handle comment
if (isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $commentText = trim($_POST['comment_text']);
    if (!empty($commentText)) {
        $user = loadJSON(__DIR__ . '/data/users/' . $_SESSION['user_id'] . '.json');
        if (!isset($post['comments'])) $post['comments'] = [];
        
        $post['comments'][] = [
            'user' => $_SESSION['user_id'],
            'user_name' => $user['name'] ?? 'Anonim',
            'user_symbol' => $user['symbol'] ?? '👤',
            'text' => htmlspecialchars($commentText),
            'time' => time()
        ];
        
        saveJSON($postFile, $post);
        header('Location: article.php?id=' . $postId . '#comments');
        exit;
    }
}

$userLiked = isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $post['likes'] ?? []);
$userDisliked = isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $post['dislikes'] ?? []);

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
    <title><?= htmlspecialchars($post['title']) ?> - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .reaction-btn {
            transition: all 0.3s;
        }
        .reaction-btn:hover {
            transform: scale(1.1);
        }
        .reaction-btn.active {
            transform: scale(1.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen text-white">
    
    <nav class="glass-effect p-4 sm:p-6 mb-8">
        <div class="container mx-auto flex items-center justify-between">
            <a href="articles.php" class="text-purple-300 hover:text-purple-100 flex items-center text-sm sm:text-base">
                ← Geri
            </a>
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

    <div class="container mx-auto px-4 sm:px-6 pb-20 max-w-4xl">
        <!-- Article Header -->
        <div class="glass-effect p-6 sm:p-8 rounded-xl mb-8" data-aos="fade-up">
            <div class="flex items-center mb-6">
                <div class="text-4xl sm:text-5xl mr-4"><?= $post['author_symbol'] ?? '👤' ?></div>
                <div class="flex-1">
                    <h2 class="text-lg sm:text-xl font-bold"><?= htmlspecialchars($post['author_name'] ?? 'Anonim') ?></h2>
                    <p class="text-sm text-purple-300"><?= date('d.m.Y, H:i', $post['created']) ?></p>
                </div>
                <div class="text-3xl sm:text-4xl"><?= $topics[$post['topic']][1] ?? '📝' ?></div>
            </div>
            
            <h1 class="text-2xl sm:text-4xl font-bold mb-6"><?= htmlspecialchars($post['title']) ?></h1>
            
            <!-- Files -->
            <?php if (!empty($post['files'])): ?>
                <div class="mb-6 space-y-4">
                    <?php foreach ($post['files'] as $file): ?>
                        <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)): ?>
                            <img src="<?= htmlspecialchars($file) ?>" alt="" class="w-full rounded-lg shadow-lg">
                        <?php elseif (preg_match('/\.(mp3|wav|ogg|m4a)$/i', $file)): ?>
                            <audio controls class="w-full">
                                <source src="<?= htmlspecialchars($file) ?>">
                            </audio>
                        <?php elseif (preg_match('/\.(pdf|docx|txt)$/i', $file)): ?>
                            <a href="<?= htmlspecialchars($file) ?>" target="_blank" class="flex items-center bg-purple-900 bg-opacity-50 p-4 rounded-lg hover:bg-opacity-70 transition">
                                <span class="text-3xl mr-3">📄</span>
                                <span>Əlavə fayl: <?= basename($file) ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Text -->
            <div class="prose prose-invert max-w-none">
                <p class="text-base sm:text-lg leading-relaxed whitespace-pre-wrap"><?= nl2br(htmlspecialchars($post['text'])) ?></p>
            </div>
        </div>

        <!-- Reactions -->
        <div class="glass-effect p-6 rounded-xl mb-8" data-aos="fade-up">
            <div class="flex items-center justify-center space-x-8">
                <form method="POST" class="inline-block">
                    <button type="submit" name="like" class="reaction-btn flex flex-col items-center <?= $userLiked ? 'active' : '' ?>">
                        <span class="text-3xl sm:text-4xl mb-1"><?= $userLiked ? '👍🏻' : '👍' ?></span>
                        <span class="text-sm sm:text-base font-bold"><?= count($post['likes'] ?? []) ?></span>
                    </button>
                </form>
                
                <form method="POST" class="inline-block">
                    <button type="submit" name="dislike" class="reaction-btn flex flex-col items-center <?= $userDisliked ? 'active' : '' ?>">
                        <span class="text-3xl sm:text-4xl mb-1"><?= $userDisliked ? '👎🏻' : '👎' ?></span>
                        <span class="text-sm sm:text-base font-bold"><?= count($post['dislikes'] ?? []) ?></span>
                    </button>
                </form>
            </div>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
            <p class="text-center text-sm text-purple-300 mt-4">
                <a href="login.php" class="underline">Giriş edin</a> reaksiya vermək üçün
            </p>
            <?php endif; ?>
        </div>

        <!-- Comments -->
        <div class="glass-effect p-6 sm:p-8 rounded-xl" data-aos="fade-up" id="comments">
            <h2 class="text-2xl font-bold mb-6">💬 Rəylər (<?= count($post['comments'] ?? []) ?>)</h2>
            
            <!-- Comment Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" class="mb-8">
                <textarea name="comment_text" rows="3" required placeholder="Rəyinizi yazın..." class="w-full bg-white bg-opacity-10 border border-purple-400 px-4 py-3 rounded-lg text-white resize-none focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                <button type="submit" name="comment" class="mt-3 bg-purple-600 hover:bg-purple-700 px-6 py-2 rounded-lg transition transform hover:scale-105">
                    Göndər
                </button>
            </form>
            <?php else: ?>
            <div class="bg-purple-900 bg-opacity-50 p-4 rounded-lg mb-8 text-center">
                <p class="text-purple-300">Rəy yazmaq üçün <a href="login.php" class="underline font-bold">giriş edin</a></p>
            </div>
            <?php endif; ?>
            
            <!-- Comments List -->
            <?php if (!empty($post['comments'])): ?>
                <div class="space-y-4">
                    <?php foreach (array_reverse($post['comments']) as $comment): ?>
                    <div class="bg-white bg-opacity-5 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-2"><?= $comment['user_symbol'] ?? '👤' ?></span>
                            <div>
                                <span class="font-bold"><?= htmlspecialchars($comment['user_name']) ?></span>
                                <span class="text-xs text-purple-300 ml-2"><?= date('d.m.Y, H:i', $comment['time']) ?></span>
                            </div>
                        </div>
                        <p class="text-sm sm:text-base"><?= nl2br($comment['text']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-purple-300">Hələ rəy yoxdur. İlk rəyi siz yazın! 💭</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>

</body>
</html>