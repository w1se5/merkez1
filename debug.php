<?php
/**
 * debug.php - Sistem Yoxlama
 * Bu faylı açın xətaları görmək üçün
 * Sonra silin!
 */

echo "<!DOCTYPE html>
<html lang='az'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Debug - Sistem Yoxlama</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-900 text-white p-6'>
<div class='max-w-4xl mx-auto'>
<h1 class='text-3xl font-bold mb-6'>🔍 Sistem Yoxlama</h1>";

// 1. PHP Version
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>PHP Versiya</h2>";
echo "<p>Versiya: <b>" . phpversion() . "</b></p>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p class='text-green-400'>✓ PHP versiyası uyğundur</p>";
} else {
    echo "<p class='text-red-400'>✗ PHP 7.4+ lazımdır</p>";
}
echo "</div>";

// 2. Directory Permissions
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Qovluq İcazələri</h2>";

$dirs = [
    'data' => __DIR__ . '/data',
    'data/users' => __DIR__ . '/data/users',
    'data/posts' => __DIR__ . '/data/posts',
    'data/tickets' => __DIR__ . '/data/tickets',
    'data/events' => __DIR__ . '/data/events',
    'data/polls' => __DIR__ . '/data/polls',
    'uploads' => __DIR__ . '/uploads',
    'uploads/images' => __DIR__ . '/uploads/images',
];

foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<p class='text-green-400'>✓ {$name} - Yazıla bilir</p>";
        } else {
            echo "<p class='text-red-400'>✗ {$name} - Yazıla bilmir (chmod 755 lazımdır)</p>";
        }
    } else {
        echo "<p class='text-yellow-400'>⚠ {$name} - Mövcud deyil</p>";
    }
}
echo "</div>";

// 3. File Test
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Fayl Yazma Testi</h2>";

$testFile = __DIR__ . '/data/test.txt';
$testData = 'Test ' . time();

if (@file_put_contents($testFile, $testData)) {
    echo "<p class='text-green-400'>✓ Fayl yazıldı</p>";
    
    if (file_exists($testFile)) {
        $content = @file_get_contents($testFile);
        if ($content === $testData) {
            echo "<p class='text-green-400'>✓ Fayl oxundu</p>";
        } else {
            echo "<p class='text-red-400'>✗ Fayl oxuna bilmədi</p>";
        }
        @unlink($testFile);
    }
} else {
    echo "<p class='text-red-400'>✗ Fayl yazıla bilmədi</p>";
    echo "<p class='text-sm text-gray-400'>Səbəb: İcazə yoxdur və ya qovluq mövcud deyil</p>";
}
echo "</div>";

// 4. JSON Test
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>JSON Əməliyyatları</h2>";

$testData = ['test' => 'value', 'time' => time()];
$testJson = json_encode($testData, JSON_PRETTY_PRINT);

if ($testJson !== false) {
    echo "<p class='text-green-400'>✓ JSON encode işləyir</p>";
    
    $decoded = json_decode($testJson, true);
    if ($decoded !== null) {
        echo "<p class='text-green-400'>✓ JSON decode işləyir</p>";
    } else {
        echo "<p class='text-red-400'>✗ JSON decode xətası</p>";
    }
} else {
    echo "<p class='text-red-400'>✗ JSON encode xətası</p>";
}
echo "</div>";

// 5. Session Test
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Session</h2>";

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='text-green-400'>✓ Session aktiv</p>";
    $_SESSION['test'] = time();
    echo "<p class='text-green-400'>✓ Session yazma işləyir</p>";
} else {
    echo "<p class='text-red-400'>✗ Session başlatıla bilmədi</p>";
}
echo "</div>";

// 6. User Files Check
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>İstifadəçi Faylları</h2>";

$userFiles = [
    '10000.json' => 'Admin',
    '12345.json' => 'İstifadəçi 1',
    '12346.json' => 'İstifadəçi 2'
];

foreach ($userFiles as $file => $name) {
    $path = __DIR__ . '/data/users/' . $file;
    if (file_exists($path)) {
        echo "<p class='text-green-400'>✓ {$name} ({$file}) - Mövcuddur</p>";
        $content = @file_get_contents($path);
        if ($content !== false) {
            $json = json_decode($content, true);
            if ($json !== null) {
                echo "<p class='text-sm text-gray-400 ml-4'>Ad: {$json['name']}, ID: {$json['id']}</p>";
            } else {
                echo "<p class='text-red-400 ml-4'>✗ JSON parse xətası</p>";
            }
        }
    } else {
        echo "<p class='text-red-400'>✗ {$name} ({$file}) - Tapılmadı</p>";
    }
}
echo "</div>";

// 7. Create Post Test
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Məqalə Yaratma Testi</h2>";

$testPost = [
    'id' => 'TEST9999',
    'author' => '12345',
    'author_name' => 'Test User',
    'author_symbol' => '🔬',
    'title' => 'Test Məqalə',
    'text' => 'Bu bir test məqaləsidir.',
    'topic' => 'philosophy',
    'files' => [],
    'likes' => [],
    'dislikes' => [],
    'comments' => [],
    'approved' => false,
    'created' => time()
];

$testPostFile = __DIR__ . '/data/posts/TEST9999.json';
$testPostJson = json_encode($testPost, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (@file_put_contents($testPostFile, $testPostJson)) {
    echo "<p class='text-green-400'>✓ Test məqalə yaradıldı</p>";
    @chmod($testPostFile, 0644);
    
    // Verify
    if (file_exists($testPostFile)) {
        $verify = json_decode(@file_get_contents($testPostFile), true);
        if ($verify && $verify['id'] === 'TEST9999') {
            echo "<p class='text-green-400'>✓ Test məqalə yoxlandı</p>";
        }
    }
    
    // Clean up
    @unlink($testPostFile);
    echo "<p class='text-gray-400'>Test məqalə silindi</p>";
} else {
    echo "<p class='text-red-400'>✗ Test məqalə yaradıla bilmədi</p>";
}
echo "</div>";

// 8. Upload Directory Test
echo "<div class='bg-gray-800 rounded-lg p-4 mb-4'>";
echo "<h2 class='text-xl font-bold mb-2'>Upload Qovluqları</h2>";

$uploadDirs = ['images', 'audio', 'documents'];
foreach ($uploadDirs as $dir) {
    $path = __DIR__ . '/uploads/' . $dir;
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<p class='text-green-400'>✓ uploads/{$dir} - Yazıla bilir</p>";
        } else {
            echo "<p class='text-red-400'>✗ uploads/{$dir} - Yazıla bilmir</p>";
        }
    } else {
        echo "<p class='text-yellow-400'>⚠ uploads/{$dir} - Mövcud deyil</p>";
        if (@mkdir($path, 0755, true)) {
            echo "<p class='text-green-400 ml-4'>✓ Yaradıldı</p>";
        }
    }
}
echo "</div>";

echo "<div class='bg-blue-900 rounded-lg p-6 mb-4'>
<h2 class='text-xl font-bold mb-2'>📝 Tövsiyələr</h2>
<ul class='list-disc list-inside space-y-2'>
<li>Əgər qırmızı xəta varsa, chmod 755 icazəsi verin</li>
<li>InfinityFree-də bəzi icazələr məhdud ola bilər</li>
<li>Əgər fayl yazıla bilmirsə, File Manager-dən icazələri yoxlayın</li>
<li>Test tamamlandıqdan sonra bu faylı SİLİN</li>
</ul>
</div>";

echo "<div class='text-center'>
<a href='index.php' class='inline-block bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-bold transition'>
← Ana Səhifə
</a>
<a href='setup.php' class='inline-block bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg font-bold transition ml-2'>
Setup İşə Sal
</a>
</div>";

echo "</div></body></html>";
?>