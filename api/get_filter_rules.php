<?php
// File: api/get_filter_rules.php
// Deskripsi: API khusus untuk mengambil data Firewall Filter Rules.

session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);

$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

use \RouterOS\Client;
use \RouterOS\Query;

try {
    if (!isset($_SESSION['active_device_id'])) {
        throw new Exception('Sesi perangkat aktif tidak ditemukan.');
    }

    /**
     * Fungsi untuk mendapatkan koneksi ke router dengan penanganan error yang lebih baik.
     * @param mysqli $koneksi Objek koneksi database.
     * @return Client Objek klien RouterOS.
     */
    function getMikrotikClient($koneksi) {
        if (!$koneksi) {
            throw new \Exception('Koneksi database tidak valid.');
        }
        $deviceId = $_SESSION['active_device_id'];
        $userId = $_SESSION['user_id'];
        
        // Pilih hanya kolom yang dibutuhkan untuk efisiensi
        $query = "SELECT host, api_user, api_pass, api_port FROM devices WHERE id = ? AND user_id = ?";
        
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            throw new \Exception('Gagal menyiapkan query database: ' . mysqli_error($koneksi));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new \Exception('Gagal mengeksekusi query: ' . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            throw new \Exception('Gagal mendapatkan hasil dari query: ' . mysqli_stmt_error($stmt));
        }

        $device = mysqli_fetch_assoc($result);
        
        if (!$device) {
            throw new \Exception('Perangkat tidak ditemukan di database untuk user ini. Pastikan Anda telah memilih perangkat yang benar.');
        }
        
        return new Client([
            'host' => $device['host'],
            'user' => $device['api_user'],
            'pass' => $device['api_pass'],
            'port' => (int)$device['api_port'],
        ]);
    }

    // Panggil fungsi dengan menyertakan variabel $koneksi
    $client = getMikrotikClient($koneksi);
    $filter_rules = $client->query('/ip/firewall/filter/print')->read();

    echo json_encode(['status' => 'success', 'data' => $filter_rules]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
