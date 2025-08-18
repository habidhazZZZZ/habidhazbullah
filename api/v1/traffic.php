<?php
// File: api/v1/traffic.php
// Endpoint untuk mendapatkan data traffic dari interface tertentu.

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Memuat file-file yang diperlukan
require_once __DIR__ . '/../../includes/mikrotik_api.php';
require_once __DIR__ . '/../../config/db.php'; // <-- PERBAIKAN: Menambahkan file definisi class Database

session_start();

function send_error_response($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

try {
    // 1. Validasi Sesi dan Input
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_device_id'])) {
        send_error_response('Sesi tidak valid.', 401);
    }
    if (!isset($_GET['interface']) || empty($_GET['interface'])) {
        send_error_response('Parameter "interface" dibutuhkan.', 400);
    }

    $interfaceName = $_GET['interface'];
    $userId = $_SESSION['user_id'];
    $deviceId = $_SESSION['active_device_id'];

    // 2. Dapatkan Konfigurasi Perangkat
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT host, api_user, api_pass, api_port FROM devices WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deviceId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $deviceConfig = $result->fetch_assoc();
    $stmt->close();

    if (!$deviceConfig) {
        send_error_response('Perangkat tidak ditemukan.', 404);
    }

    // 3. Inisialisasi dan Ambil Data
    $mikrotik = new MikroTikAPI(
        $deviceConfig['host'],
        $deviceConfig['api_user'],
        $deviceConfig['api_pass'],
        (int)$deviceConfig['api_port']
    );

    $trafficData = $mikrotik->getSpecificInterfaceTraffic($interfaceName);

    // 4. Kirim Respons
    echo json_encode(['status' => 'success', 'data' => $trafficData]);

} catch (Exception $e) {
    send_error_response($e->getMessage());
}
?>
