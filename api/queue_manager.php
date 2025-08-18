<?php
// File: api/queue_manager.php
// Deskripsi: API untuk mengelola Simple Queues, seperti enable/disable.

session_start();
header('Content-Type: application/json');
// ini_set('display_errors', 1); error_reporting(E_ALL); // Aktifkan untuk debug

$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

use \RouterOS\Client;
use \RouterOS\Query;

try {
    // Keamanan: Pastikan pengguna sudah login.
    if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login') {
        throw new \Exception('Akses ditolak. Anda harus login.');
    }

    // Fungsi untuk mendapatkan koneksi ke router yang aktif
    function getMikrotikClient($koneksi) {
        if (!isset($_SESSION['active_device_id'])) {
            throw new \Exception('Sesi perangkat aktif tidak ditemukan.');
        }
        $deviceId = $_SESSION['active_device_id'];
        $userId = $_SESSION['user_id'];
        
        $query = "SELECT * FROM devices WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);
        mysqli_stmt_execute($stmt);
        $device = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if (!$device) throw new \Exception('Perangkat tidak ditemukan.');
        
        return new Client([
            'host' => $device['host'],
            'user' => $device['api_user'],
            'pass' => $device['api_pass'],
            'port' => (int)$device['api_port'],
        ]);
    }

    $action = $_GET['action'] ?? '';

    if ($action === 'toggle_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        $current_status = $_POST['status'] ?? null;

        if (!$id || $current_status === null) {
            throw new \Exception("ID atau status antrian tidak lengkap.");
        }

        $client = getMikrotikClient($koneksi);
        
        // Tentukan status baru (kebalikan dari status saat ini)
        $new_disabled_value = ($current_status === 'true') ? 'false' : 'true'; // 'true' for disabled, 'false' for enabled

        $query = (new Query('/queue/simple/set'))
            ->equal('.id', $id)
            ->equal('disabled', $new_disabled_value);
            
        $client->query($query)->read();

        $new_status_text = ($new_disabled_value === 'false') ? 'diaktifkan' : 'dinonaktifkan';
        echo json_encode(['status' => 'success', 'message' => "Antrian berhasil {$new_status_text}."]);

    } else {
        throw new \Exception("Aksi tidak valid.");
    }

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
