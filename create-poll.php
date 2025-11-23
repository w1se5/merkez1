<?php
// ================================
// create-poll.php
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

function generatePollID() {
    return 'POLL' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}

try {
    $userId = $_SESSION['user_id'];
    $userFile = __DIR__ . '/data/users/' . $userId . '.json';
    $user = loadJSON($userFile);
    
    if (!$user) {
        header('Location: panel.php?page=create-poll&error=user_not_found');
        exit;
    }
    
    $pollId = generatePollID();
    $attempts = 0;
    while (file_exists(__DIR__ . '/data/polls/' . $pollId . '.json') && $attempts < 10) {
        $pollId = generatePollID();
        $attempts++;
    }
    
    $options = [];
    if (isset($_POST['options']) && is_array($_POST['options'])) {
        foreach ($_POST['options'] as $option) {
            $trimmed = trim($option);
            if (!empty($trimmed)) {
                $options[] = $trimmed;
            }
        }
    }
    
    if (count($options) < 2) {
        header('Location: panel.php?page=create-poll&error=min_options');
        exit;
    }
    
    if (count($options) > 10) {
        header('Location: panel.php?page=create-poll&error=max_options');
        exit;
    }
    
    $poll = [
        'id' => $pollId,
        'author' => $userId,
        'author_name' => $user['name'],
        'title' => htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8'),
        'options' => $options,
        'votes' => [],
        'created' => time()
    ];
    
    $pollFile = __DIR__ . '/data/polls/' . $pollId . '.json';
    if (saveJSON($pollFile, $poll)) {
        @chmod($pollFile, 0644);
        header('Location: polls.php?success=created');
        exit;
    } else {
        header('Location: panel.php?page=create-poll&error=save_failed');
        exit;
    }
    
} catch (Exception $e) {
    header('Location: panel.php?page=create-poll&error=exception');
    exit;
}
?>