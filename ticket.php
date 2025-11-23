<?php
function loadJSON($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

$ticketId = $_GET['id'] ?? '';
if (empty($ticketId)) die('Bilet ID-si tapılmadı');

$ticketFile = __DIR__ . '/data/tickets/' . $ticketId . '.json';
$ticket = loadJSON($ticketFile);
if (!$ticket) die('Bilet tapılmadı');

$ticketUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/ticket.php?id=' . $ticketId;
$qrCodeDataUrl = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($ticketUrl);
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet - <?= htmlspecialchars($ticket['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
        .ticket-border {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3px;
        }
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.5); }
            50% { box-shadow: 0 0 40px rgba(118, 75, 162, 0.8); }
        }
        .glow-animation {
            animation: glow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 min-h-screen flex items-center justify-center p-4 sm:p-6">
    
    <div class="max-w-2xl w-full">
        <!-- Ticket Card -->
        <div id="ticket-content" class="ticket-border rounded-2xl glow-animation" data-aos="zoom-in">
            <div class="bg-white rounded-2xl p-6 sm:p-8 md:p-12">
                <!-- Header -->
                <div class="text-center mb-6 sm:mb-8 border-b-2 border-dashed border-purple-300 pb-6">
                    <div class="text-5xl sm:text-6xl mb-3">🎟️</div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-purple-900 mb-2">Düşüncə Mərkəzi</h1>
                    <p class="text-purple-600 text-base sm:text-lg"><?= htmlspecialchars($ticket['event_name'] ?? 'Tədbir Bileti') ?></p>
                    <?php if (isset($ticket['event_date'])): ?>
                    <p class="text-gray-600 mt-2 text-sm sm:text-base">📅 <?= date('d.m.Y, H:i', $ticket['event_date']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- User Info -->
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <div class="bg-purple-50 rounded-xl p-4">
                        <p class="text-xs sm:text-sm text-purple-600 font-semibold mb-1">Ad Soyad</p>
                        <p class="text-base sm:text-lg font-bold text-purple-900"><?= htmlspecialchars($ticket['name']) ?></p>
                    </div>
                    
                    <div class="bg-purple-50 rounded-xl p-4">
                        <p class="text-xs sm:text-sm text-purple-600 font-semibold mb-1">Qrup</p>
                        <p class="text-base sm:text-lg font-bold text-purple-900"><?= htmlspecialchars($ticket['group']) ?></p>
                    </div>
                    
                    <div class="bg-purple-50 rounded-xl p-4">
                        <p class="text-xs sm:text-sm text-purple-600 font-semibold mb-1">Əlaqə</p>
                        <p class="text-base sm:text-lg font-bold text-purple-900"><?= htmlspecialchars($ticket['phone']) ?></p>
                    </div>
                    
                    <div class="bg-purple-50 rounded-xl p-4">
                        <p class="text-xs sm:text-sm text-purple-600 font-semibold mb-1">Bilet ID</p>
                        <p class="text-base sm:text-lg font-bold text-purple-900 font-mono"><?= htmlspecialchars($ticketId) ?></p>
                    </div>
                </div>

                <!-- Seat Info -->
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl p-6 text-white text-center mb-6 sm:mb-8">
                    <p class="text-xs sm:text-sm font-semibold mb-2">OTURACAQ</p>
                    <p class="text-3xl sm:text-4xl font-bold"><?= htmlspecialchars($ticket['seat']) ?></p>
                </div>

                <!-- QR Code -->
                <div class="text-center mb-6">
                    <div class="inline-block bg-white p-4 rounded-xl shadow-lg">
                        <img src="<?= $qrCodeDataUrl ?>" alt="QR Code" class="w-48 h-48 sm:w-64 sm:h-64 mx-auto">
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600 mt-4">QR kodu girişdə skan edin</p>
                </div>

                <!-- Status -->
                <div class="text-center">
                    <?php if ($ticket['used']): ?>
                        <div class="bg-red-100 border-2 border-red-500 text-red-700 px-4 sm:px-6 py-3 rounded-lg inline-block font-bold text-sm sm:text-base">
                            ❌ İSTİFADƏ EDİLİB
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            İstifadə tarixi: <?= date('d.m.Y H:i', $ticket['used_at']) ?>
                        </p>
                    <?php else: ?>
                        <div class="bg-green-100 border-2 border-green-500 text-green-700 px-4 sm:px-6 py-3 rounded-lg inline-block font-bold text-sm sm:text-base">
                            ✅ AKTİV
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="mt-6 sm:mt-8 pt-6 border-t border-gray-200 text-center text-xs sm:text-sm text-gray-500">
                    <p>Bu bilet şəxsidir və köçürülə bilməz</p>
                    <p class="mt-1">Tədbir gününə qədər qorunmalıdır</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex flex-wrap gap-3 sm:gap-4 justify-center no-print" data-aos="fade-up" data-aos-delay="300">
            <button onclick="downloadPDF()" class="flex-1 sm:flex-none bg-purple-600 hover:bg-purple-700 text-white px-4 sm:px-6 py-3 rounded-lg font-semibold transition transform hover:scale-105 text-sm sm:text-base">
                📥 PDF Yüklə
            </button>
            <button onclick="window.print()" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-3 rounded-lg font-semibold transition transform hover:scale-105 text-sm sm:text-base">
                🖨️ Çap Et
            </button>
            <button onclick="copyLink()" class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white px-4 sm:px-6 py-3 rounded-lg font-semibold transition transform hover:scale-105 text-sm sm:text-base">
                🔗 Kopyala
            </button>
        </div>

        <div class="mt-6 text-center no-print" data-aos="fade-up" data-aos-delay="400">
            <a href="panel.php" class="text-purple-300 hover:text-purple-100 transition text-sm sm:text-base">
                ← Panelə qayıt
            </a>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function downloadPDF() {
            const element = document.getElementById('ticket-content');
            const opt = {
                margin: 0.5,
                filename: 'bilet-<?= $ticketId ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        function copyLink() {
            const link = window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    alert('✓ Link kopyalandı!');
                });
            } else {
                const input = document.createElement('input');
                input.value = link;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('✓ Link kopyalandı!');
            }
        }
    </script>

</body>
</html>