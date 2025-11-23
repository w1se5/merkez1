<?php
session_start();

$archiveDir = __DIR__ . '/data/archive/';
$files = [];

if (is_dir($archiveDir)) {
    foreach (array_diff(scandir($archiveDir), ['.', '..']) as $file) {
        $filepath = $archiveDir . $file;
        if (is_file($filepath)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $type = 'other';
            if (in_array($ext, ['pdf'])) $type = 'document';
            elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $type = 'image';
            elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) $type = 'audio';
            
            $files[] = [
                'name' => $file,
                'path' => '/data/archive/' . $file,
                'size' => filesize($filepath),
                'type' => $type,
                'ext' => $ext,
                'modified' => filemtime($filepath)
            ];
        }
    }
    usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

if ($search) {
    $files = array_filter($files, fn($f) => stripos($f['name'], $search) !== false);
}
if ($filter !== 'all') {
    $files = array_filter($files, fn($f) => $f['type'] === $filter);
}

function formatBytes($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arxiv - Düşüncə Mərkəzi</title>
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
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Faylda axtar..." class="w-full bg-white bg-opacity-10 px-4 py-2 rounded-lg border border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm sm:text-base">
            </form>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="panel.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base whitespace-nowrap">Panel</a>
            <?php else: ?>
                <a href="login.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base whitespace-nowrap">Giriş</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 pb-20">
        <!-- Filter -->
        <div class="mb-8" data-aos="fade-down">
            <div class="flex flex-wrap gap-3">
                <a href="?filter=all" class="<?= $filter === 'all' ? 'bg-purple-600' : 'bg-white bg-opacity-10 hover:bg-opacity-20' ?> px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap">
                    📂 Hamısı
                </a>
                <a href="?filter=document" class="<?= $filter === 'document' ? 'bg-purple-600' : 'bg-white bg-opacity-10 hover:bg-opacity-20' ?> px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap">
                    📄 Sənədlər
                </a>
                <a href="?filter=audio" class="<?= $filter === 'audio' ? 'bg-purple-600' : 'bg-white bg-opacity-10 hover:bg-opacity-20' ?> px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap">
                    🎵 Audio
                </a>
                <a href="?filter=image" class="<?= $filter === 'image' ? 'bg-purple-600' : 'bg-white bg-opacity-10 hover:bg-opacity-20' ?> px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap">
                    🖼️ Şəkillər
                </a>
            </div>
        </div>

        <h1 class="text-3xl sm:text-5xl font-bold mb-4" data-aos="fade-up">📚 Arxiv</h1>
        <p class="text-purple-200 mb-8 text-sm sm:text-base" data-aos="fade-up" data-aos-delay="100">
            Kitablar, audio materiallar və digər faydalı resurslar
        </p>

        <?php if (empty($files)): ?>
        <div class="glass-effect p-12 rounded-xl text-center" data-aos="fade-up">
            <div class="text-6xl mb-4">📂</div>
            <p class="text-xl text-purple-300">
                <?= $search ? 'Axtarışa uyğun fayl tapılmadı' : 'Arxivdə hələ fayl yoxdur' ?>
            </p>
        </div>
        <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($files as $i => $file): 
                $icons = [
                    'document' => '📄',
                    'audio' => '🎵',
                    'image' => '🖼️',
                    'other' => '📎'
                ];
                $icon = $icons[$file['type']];
            ?>
            <div class="glass-effect p-6 rounded-xl hover:scale-105 transition transform" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                <div class="text-5xl mb-4"><?= $icon ?></div>
                <h3 class="text-base sm:text-lg font-bold mb-2 line-clamp-2"><?= htmlspecialchars($file['name']) ?></h3>
                <div class="text-xs sm:text-sm text-purple-300 space-y-1 mb-4">
                    <div class="flex items-center">
                        <span class="mr-2">📏</span>
                        <span><?= formatBytes($file['size']) ?></span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2">📅</span>
                        <span><?= date('d.m.Y', $file['modified']) ?></span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2">📋</span>
                        <span class="uppercase"><?= $file['ext'] ?></span>
                    </div>
                </div>

                <?php if ($file['type'] === 'audio'): ?>
                    <audio controls class="w-full mb-3">
                        <source src="<?= htmlspecialchars($file['path']) ?>">
                    </audio>
                <?php elseif ($file['type'] === 'image'): ?>
                    <a href="<?= htmlspecialchars($file['path']) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($file['path']) ?>" alt="" class="w-full h-32 object-cover rounded-lg mb-3">
                    </a>
                <?php endif; ?>

                <a href="<?= htmlspecialchars($file['path']) ?>" target="_blank" class="block w-full text-center bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg transition text-sm sm:text-base">
                    <?= $file['type'] === 'document' ? 'Oxu' : ($file['type'] === 'audio' ? 'Dinlə' : 'Aç') ?> →
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <div class="mt-12 glass-effect p-6 rounded-xl text-center" data-aos="fade-up">
            <p class="text-purple-300 mb-4">Admin olaraq arxivə fayl əlavə edə bilərsiniz</p>
            <a href="panel.php?page=upload-archive" class="inline-block bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-lg transition">
                Fayl Yüklə
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>

</body>
</html>