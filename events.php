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

function getNextAvailableSeat($eventId) {
    $seatsFile = __DIR__ . '/data/events/' . $eventId . '/seats.json';
    $seats = loadJSON($seatsFile);
    if (!$seats) {
        $seats = [];
        for ($row = 1; $row <= 16; $row++) {
            for ($seat = 1; $seat <= 12; $seat++) {
                $seats[$row][$seat] = null;
            }
        }
        saveJSON($seatsFile, $seats);
    }
    for ($row = 1; $row <= 16; $row++) {
        for ($seat = 1; $seat <= 12; $seat++) {
            if (($seats[$row][$seat] ?? null) === null) {
                return ['row' => $row, 'seat' => $seat];
            }
        }
    }
    return null;
}

function generateTicketID() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $id = '';
    for ($i = 0; $i < 5; $i++) {
        $id .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $id;
}

// Handle ticket purchase
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_ticket'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Bilet almaq üçün giriş etməlisiniz';
    } else {
        $eventId = $_POST['event_id'];
        $userId = $_SESSION['user_id'];
        $userFile = __DIR__ . '/data/users/' . $userId . '.json';
        $user = loadJSON($userFile);
        $event = loadJSON(__DIR__ . '/data/events/' . $eventId . '.json');
        
        if (!$event) {
            $error = 'Tədbir tapılmadı';
        } else {
            // Check if user already has ticket
            $hasTicket = false;
            if (isset($user['tickets'])) {
                foreach ($user['tickets'] as $ticketId) {
                    $ticket = loadJSON(__DIR__ . '/data/tickets/' . $ticketId . '.json');
                    if ($ticket && $ticket['event_id'] === $eventId) {
                        $hasTicket = true;
                        break;
                    }
                }
            }
            
            if ($hasTicket) {
                $error = 'Bu tədbir üçün artıq biletiniz var';
            } else {
                $seatInfo = getNextAvailableSeat($eventId);
                if (!$seatInfo) {
                    $error = 'Təəssüf ki, bütün yerlər doludur';
                } else {
                    $ticketId = generateTicketID();
                    while (file_exists(__DIR__ . '/data/tickets/' . $ticketId . '.json')) {
                        $ticketId = generateTicketID();
                    }
                    
                    $ticket = [
                        'id' => $ticketId,
                        'userid' => $userId,
                        'name' => $user['name'],
                        'phone' => $user['phone'],
                        'group' => $user['group'] ?? 'İstifadəçi',
                        'event_id' => $eventId,
                        'event_name' => $event['title'],
                        'event_date' => $event['date'] ?? null,
                        'seat' => "Sıra {$seatInfo['row']}, Yer {$seatInfo['seat']}",
                        'used' => false,
                        'created' => time()
                    ];
                    
                    saveJSON(__DIR__ . '/data/tickets/' . $ticketId . '.json', $ticket);
                    
                    $seats = loadJSON(__DIR__ . '/data/events/' . $eventId . '/seats.json');
                    $seats[$seatInfo['row']][$seatInfo['seat']] = $userId;
                    saveJSON(__DIR__ . '/data/events/' . $eventId . '/seats.json', $seats);
                    
                    if (!isset($user['tickets'])) $user['tickets'] = [];
                    $user['tickets'][] = $ticketId;
                    saveJSON($userFile, $user);
                    
                    $success = 'Biletiniz uğurla yaradıldı! Paneldən görə bilərsiniz.';
                }
            }
        }
    }
}

// Load all events
$events = [];
$eventsDir = __DIR__ . '/data/events/';
if (is_dir($eventsDir)) {
    foreach (array_diff(scandir($eventsDir), ['.', '..']) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $event = loadJSON($eventsDir . $file);
            if ($event) $events[] = $event;
        }
    }
    usort($events, fn($a, $b) => ($a['date'] ?? 0) - ($b['date'] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tədbirlər - Düşüncə Mərkəzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .modal {
            display: none;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .modal.active {
            display: flex;
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen text-white">
    
    <nav class="glass-effect p-4 sm:p-6 mb-8">
        <div class="container mx-auto flex items-center justify-between">
            <a href="index.php" class="text-xl sm:text-2xl font-bold flex items-center">
                <span class="mr-2">🌙</span> Düşüncə Mərkəzi
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="panel.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base">Panel</a>
            <?php else: ?>
                <a href="login.php" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm sm:text-base">Giriş</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 pb-20">
        <?php if ($success): ?>
        <div class="glass-effect border-2 border-green-400 p-4 sm:p-6 rounded-xl mb-8 animate-pulse" data-aos="fade-down">
            <div class="flex items-center">
                <span class="text-3xl sm:text-4xl mr-4">✅</span>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-green-300 mb-1">Uğurlu!</h3>
                    <p class="text-sm sm:text-base"><?= htmlspecialchars($success) ?></p>
                    <a href="panel.php?page=ticket" class="inline-block mt-3 bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm transition">
                        Bileti Gör →
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="glass-effect border-2 border-red-400 p-4 sm:p-6 rounded-xl mb-8" data-aos="fade-down">
            <div class="flex items-center">
                <span class="text-3xl sm:text-4xl mr-4">⚠️</span>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-red-300 mb-1">Xəta</h3>
                    <p class="text-sm sm:text-base"><?= htmlspecialchars($error) ?></p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="inline-block mt-3 bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition">
                        İndi Giriş Et →
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <h1 class="text-3xl sm:text-5xl font-bold mb-4 sm:mb-8" data-aos="fade-up">🎫 Tədbirlər</h1>
        <p class="text-purple-200 mb-12 text-sm sm:text-base" data-aos="fade-up" data-aos-delay="100">
            Tədbiri seçin və biletinizi alın. Oturacaq yerləri avtomatik təyin olunur.
        </p>

        <?php if (empty($events)): ?>
        <div class="glass-effect p-12 rounded-xl text-center" data-aos="fade-up">
            <div class="text-6xl mb-4">📅</div>
            <p class="text-xl text-purple-300">Hazırda aktiv tədbir yoxdur</p>
        </div>
        <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($events as $i => $event): 
                $eventId = $event['id'];
                $seatsFile = __DIR__ . '/data/events/' . $eventId . '/seats.json';
                $seats = loadJSON($seatsFile) ?? [];
                $totalSeats = 192;
                $takenSeats = 0;
                foreach ($seats as $row) {
                    foreach ($row as $seat) {
                        if ($seat !== null) $takenSeats++;
                    }
                }
                $availableSeats = $totalSeats - $takenSeats;
                $isPast = ($event['date'] ?? 0) < time();
            ?>
            <div class="glass-effect p-6 rounded-xl hover:scale-105 transition transform" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="text-5xl mb-4">🎭</div>
                <h3 class="text-xl sm:text-2xl font-bold mb-3"><?= htmlspecialchars($event['title']) ?></h3>
                <p class="text-purple-200 mb-4 text-sm"><?= htmlspecialchars($event['description'] ?? '') ?></p>
                
                <div class="space-y-2 mb-6 text-sm">
                    <div class="flex items-center">
                        <span class="mr-2">📅</span>
                        <span><?= date('d.m.Y, H:i', $event['date'] ?? time()) ?></span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2">📍</span>
                        <span><?= htmlspecialchars($event['location'] ?? 'TBA') ?></span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2">🪑</span>
                        <span class="<?= $availableSeats > 20 ? 'text-green-400' : ($availableSeats > 0 ? 'text-yellow-400' : 'text-red-400') ?>">
                            <?= $availableSeats ?> boş yer
                        </span>
                    </div>
                </div>

                <?php if ($isPast): ?>
                    <button disabled class="w-full bg-gray-600 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        Tədbir Keçib
                    </button>
                <?php elseif ($availableSeats <= 0): ?>
                    <button disabled class="w-full bg-red-600 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        Yer Yoxdur
                    </button>
                <?php else: ?>
                    <button onclick="openModal('<?= $eventId ?>', '<?= htmlspecialchars($event['title']) ?>')" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-3 rounded-lg font-semibold transition transform hover:scale-105">
                        Bilet Al
                    </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div class="modal fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center p-4" id="ticketModal">
        <div class="glass-effect max-w-md w-full p-6 sm:p-8 rounded-xl" data-aos="zoom-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl sm:text-2xl font-bold">🎫 Bilet Al</h2>
                <button onclick="closeModal()" class="text-3xl hover:text-red-400 transition">&times;</button>
            </div>
            
            <div id="modalContent">
                <div class="text-5xl text-center mb-4">🎭</div>
                <h3 class="text-lg sm:text-xl font-bold text-center mb-6" id="eventTitle"></h3>
                
                <?php if (isset($_SESSION['user_id'])): 
                    $user = loadJSON(__DIR__ . '/data/users/' . $_SESSION['user_id'] . '.json');
                ?>
                    <div class="bg-purple-900 bg-opacity-50 p-4 rounded-lg mb-6">
                        <p class="text-sm text-purple-300 mb-2">Bilet sahibi:</p>
                        <p class="font-bold"><?= htmlspecialchars($user['name']) ?></p>
                        <p class="text-sm text-purple-300"><?= htmlspecialchars($user['phone']) ?></p>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="event_id" id="modalEventId">
                        <button type="submit" name="buy_ticket" class="w-full bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 px-6 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105">
                            ✓ Təsdiq Et və Bilet Al
                        </button>
                    </form>
                    
                    <p class="text-xs text-purple-300 text-center mt-4">
                        Oturacaq avtomatik təyin olunacaq
                    </p>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-purple-200 mb-4">Bilet almaq üçün giriş etməlisiniz</p>
                        <a href="login.php" class="inline-block bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-lg font-semibold transition">
                            Giriş Et →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function openModal(eventId, eventTitle) {
            document.getElementById('eventTitle').textContent = eventTitle;
            document.getElementById('modalEventId').value = eventId;
            document.getElementById('ticketModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('ticketModal').classList.remove('active');
        }

        document.getElementById('ticketModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>

</body>
</html>