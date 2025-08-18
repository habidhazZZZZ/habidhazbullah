<?php
// File: api/v1/status.php
// Endpoint utama untuk mendapatkan status keseluruhan perangkat MikroTik.

// Header wajib untuk respons JSON
header('Content-Type: application/json');
ini_set('display_errors', 0); // Nonaktifkan tampilan error di produksi
error_reporting(E_ALL);

// Memuat file-file yang diperlukan
require_once __DIR__ . '/../../includes/mikrotik_api.php';
require_once __DIR__ . '/../../config/db.php'; // Asumsi file ini berisi koneksi DB

session_start();

// Fungsi untuk mengirim respons error standar
function send_error_response($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

try {
    // 1. Validasi Sesi Pengguna
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_device_id'])) {
        send_error_response('Sesi tidak valid. Silakan login kembali.', 401);
    }

    $userId = $_SESSION['user_id'];
    $deviceId = $_SESSION['active_device_id'];

    // 2. Dapatkan Konfigurasi Perangkat dari Database
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT host, api_user, api_pass, api_port FROM devices WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        send_error_response("Gagal menyiapkan query: " . $conn->error);
    }
    $stmt->bind_param("ii", $deviceId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $deviceConfig = $result->fetch_assoc();
    $stmt->close();

    if (!$deviceConfig) {
        send_error_response('Konfigurasi perangkat tidak ditemukan atau akses ditolak.', 404);
    }

    // 3. Inisialisasi dan Koneksi ke MikroTik
    $mikrotik = new MikroTikAPI(
        $deviceConfig['host'],
        $deviceConfig['api_user'],
        $deviceConfig['api_pass'],
        (int)$deviceConfig['api_port']
    );

    // 4. Ambil semua data yang dibutuhkan dalam satu koneksi
    $systemData = $mikrotik->getSystemResource();
    $identityData = $mikrotik->getIdentity();
    $routerboardData = $mikrotik->getRouterboard();
    $clockData = $mikrotik->getClock();
    $interfaces = $mikrotik->getInterfacesWithTraffic();
    $hotspotActiveUsers = $mikrotik->getHotspotActiveCount();

    // 5. Olah dan Gabungkan Data
    $ramTotal = $systemData['total-memory'] ?? 0;
    $ramUsed = $ramTotal - ($systemData['free-memory'] ?? 0);

    $response = [
        'status' => 'success',
        'identity' => $identityData['name'] ?? 'MikroTik',
        'system' => [
            'cpu_load' => (int)($systemData['cpu-load'] ?? 0),
            'uptime' => $systemData['uptime'] ?? 'N/A',
            'version' => $systemData['version'] ?? 'N/A',
            'board_name' => $systemData['board-name'] ?? 'N/A',
            'architecture' => $systemData['architecture-name'] ?? 'N/A',
            'serial_number' => $routerboardData['serial-number'] ?? 'N/A',
            'ram_usage' => [
                'total_mb' => round($ramTotal / 1024 / 1024),
                'used_mb' => round($ramUsed / 1024 / 1024),
                'percentage' => ($ramTotal > 0) ? round(($ramUsed / $ramTotal) * 100) : 0,
            ],
        ],
        'clock' => [
            'time' => $clockData['time'] ?? 'N/A',
            'date' => $clockData['date'] ?? 'N/A',
        ],
        'network' => [
            'interfaces' => $interfaces,
            'hotspot_active' => $hotspotActiveUsers,
        ],
    ];

    // 6. Kirim Respons
    echo json_encode($response);

} catch (Exception $e) {
    // Tangani semua jenis error (koneksi, query, dll)
    send_error_response($e->getMessage());
}
?>
