<?php
// ================================
// create-post.php
// ================================
error_reporting(0);
ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

function loadJSON($file) {
    if (!file_exists($file)) return null;
    $content = @file_get_contents($file);
    if ($content === false) return null;
    return json_decode($content, true);
}

function saveJSON($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return @file_put_contents($file, $json) !== false;
}

function generatePostID() {
    return 'POST' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}

function uploadFile($file, $type) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $uploadDir = __DIR__ . '/uploads/' . $type . '/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'document' => ['pdf', 'docx', 'txt', 'doc']
    ];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!isset($allowedTypes[$type]) || !in_array($ext, $allowedTypes[$type])) {
        return null;
    }
    
    if ($file['size'] > 10 * 1024 * 1024) {
        return null;
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (@move_uploaded_file($file['tmp_name'], $filepath)) {
        @chmod($filepath, 0644);
        return 'uploads/' . $type . '/' . $filename;
    }
    
    return null;
}

try {
    $userId = $_SESSION['user_id'];
    $userFile = __DIR__ . '/data/users/' . $userId . '.json';
    $user = loadJSON($userFile);
    
    if (!$user) {
        header('Location: panel.php?page=create-post&error=user_not_found');
        exit;
    }
    
    $postId = generatePostID();
    $attempts = 0;
    while (file_exists(__DIR__ . '/data/posts/' . $postId . '.json') && $attempts < 10) {
        $postId = generatePostID();
        $attempts++;
    }
    
    $files = [];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $path = uploadFile($_FILES['image'], 'image');
        if ($path) $files[] = $path;
    }
    
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $path = uploadFile($_FILES['audio'], 'audio');
        if ($path) $files[] = $path;
    }
    
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $path = uploadFile($_FILES['document'], 'document');
        if ($path) $files[] = $path;
    }
    
    $post = [
        'id' => $postId,
        'author' => $userId,
        'author_name' => $user['name'],
        'author_symbol' => isset($user['symbol']) ? $user['symbol'] : '👤',
        'title' => htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8'),
        'text' => htmlspecialchars(trim($_POST['text']), ENT_QUOTES, 'UTF-8'),
        'topic' => isset($_POST['topic']) ? $_POST['topic'] : 'general',
        'files' => $files,
        'likes' => [],
        'dislikes' => [],
        'comments' => [],
        'pinned' => false,
        'approved' => false,
        'created' => time()
    ];
    
    $postFile = __DIR__ . '/data/posts/' . $postId . '.json';
    if (saveJSON($postFile, $post)) {
        @chmod($postFile, 0644);
        header('Location: panel.php?page=my-posts&success=created');
        exit;
    } else {
        header('Location: panel.php?page=create-post&error=save_failed');
        exit;
    }
    
} catch (Exception $e) {
    header('Location: panel.php?page=create-post&error=exception');
    exit;
}
?>
<?php