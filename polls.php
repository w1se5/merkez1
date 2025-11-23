<?php
session_start();

function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

function saveJSON($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Handle poll vote
if (isset($_POST['vote']) && isset($_SESSION['user_id'])) {
    $pollId = $_POST['poll_id'];
    $answer = $_POST['answer'];
    $pollFile = __DIR__ . '/data/polls/' . $pollId . '.json';
    $poll = loadJSON($pollFile);
    
    if ($poll) {
        $userId = $_SESSION['user_id'];
        if (!isset($poll['votes'][$userId])) {
            $poll['votes'][$userId] = $answer;
            saveJSON($pollFile, $poll);
        }
    }
    header('Location: polls.php');
    exit;
}

// Load all polls
$polls = [];
$pollsDir = __DIR__ . '/data/polls/';
if (is_dir($pollsDir)) {
    foreach (array_diff(scandir($pollsDir), ['.', '..']) as $file) {
        $poll = loadJSON($pollsDir . $file);
        if ($poll) $polls[] = $poll;
    }
    usort($polls, fn($a, $b) => ($b['created'] ?? 0) - ($a['created'] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anketlər - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
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
        <h1 class="text-3xl sm:text-5xl font-bold mb-4" data-aos="fade-up">📊 Anketlər</h1>
        <p class="text-purple-200 mb-12 text-sm sm:text-base" data-aos="fade-up" data-aos-delay="100">
            Fikirlərinizi paylaşın və digərlərinin cavablarını görün
        </p>

        <?php if (empty($polls)): ?>
        <div class="glass-effect p-12 rounded-xl text-center" data-aos="fade-up">
            <div class="text-6xl mb-4">📋</div>
            <p class="text-xl text-purple-300">Hazırda aktiv anket yoxdur</p>
        </div>
        <?php else: ?>
        <div class="space-y-8">
            <?php foreach ($polls as $i => $poll): 
                $totalVotes = count($poll['votes'] ?? []);
                $userVoted = isset($_SESSION['user_id']) && isset($poll['votes'][$_SESSION['user_id']]);
            ?>
            <div class="glass-effect p-6 sm:p-8 rounded-xl" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h2 class="text-xl sm:text-2xl font-bold mb-2"><?= htmlspecialchars($poll['title']) ?></h2>
                        <p class="text-sm text-purple-300">
                            <span class="mr-4">👤 <?= htmlspecialchars($poll['author_name'] ?? 'Admin') ?></span>
                            <span>📅 <?= date('d.m.Y', $poll['created'] ?? time()) ?></span>
                        </p>
                    </div>
                    <div class="bg-purple-900 bg-opacity-50 px-3 py-1 rounded-full text-sm whitespace-nowrap ml-4">
                        <?= $totalVotes ?> cavab
                    </div>
                </div>

                <?php if (!$userVoted && isset($_SESSION['user_id'])): ?>
                    <!-- Vote Form -->
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">
                        <?php foreach ($poll['options'] as $j => $option): ?>
                        <label class="flex items-center bg-white bg-opacity-10 hover:bg-opacity-20 p-4 rounded-lg cursor-pointer transition">
                            <input type="radio" name="answer" value="<?= $j ?>" required class="mr-3 w-5 h-5">
                            <span class="text-sm sm:text-base"><?= htmlspecialchars($option) ?></span>
                        </label>
                        <?php endforeach; ?>
                        <button type="submit" name="vote" class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-8 py-3 rounded-lg font-semibold transition transform hover:scale-105">
                            Cavabla
                        </button>
                    </form>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <!-- Not logged in -->
                    <div class="bg-purple-900 bg-opacity-50 p-6 rounded-lg text-center">
                        <p class="mb-4">Cavab vermək üçün giriş etməlisiniz</p>
                        <a href="login.php" class="inline-block bg-purple-600 hover:bg-purple-700 px-6 py-2 rounded-lg transition">
                            Giriş Et →
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Results -->
                    <div class="space-y-4">
                        <?php 
                        $results = [];
                        foreach ($poll['options'] as $j => $option) {
                            $count = 0;
                            foreach ($poll['votes'] as $vote) {
                                if ($vote == $j) $count++;
                            }
                            $results[$j] = $count;
                        }
                        ?>
                        <?php foreach ($poll['options'] as $j => $option): 
                            $count = $results[$j];
                            $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
                            $isUserChoice = $poll['votes'][$_SESSION['user_id']] == $j;
                        ?>
                        <div class="bg-white bg-opacity-5 p-4 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm sm:text-base font-semibold <?= $isUserChoice ? 'text-green-400' : '' ?>">
                                    <?= $isUserChoice ? '✓ ' : '' ?><?= htmlspecialchars($option) ?>
                                </span>
                                <span class="text-sm font-bold"><?= $percentage ?>%</span>
                            </div>
                            <div class="w-full bg-white bg-opacity-10 rounded-full h-3 overflow-hidden">
                                <div class="progress-bar bg-gradient-to-r from-purple-600 to-pink-600 h-full rounded-full" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <p class="text-xs text-purple-300 mt-1"><?= $count ?> cavab</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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