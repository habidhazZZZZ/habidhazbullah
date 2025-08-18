<?php
/**
 * File: api/active_users_api.php (Versi Gabungan)
 * Deskripsi: API mandiri untuk halaman "User Aktif".
 * - Menerima GET request untuk mengambil daftar pengguna.
 * - Menerima POST request untuk melakukan aksi (enable, disable, delete).
 */

session_start();
header('Content-Type: application/json');

$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

use \RouterOS\Client;
use \RouterOS\Query;

try {
    // Keamanan: Pastikan pengguna sudah login
    if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login') {
        throw new \Exception('Akses ditolak. Anda harus login terlebih dahulu.');
    }

    // Fungsi untuk mendapatkan koneksi ke router yang aktif
    function getMikrotikClient($koneksi) {
        if (!isset($_SESSION['active_device_id'])) {
            throw new \Exception('Sesi perangkat aktif tidak ditemukan. Silakan pilih perangkat.');
        }
        $deviceId = $_SESSION['active_device_id'];
        $userId = $_SESSION['user_id'];
        
        $query = "SELECT * FROM devices WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) throw new \Exception('Gagal menyiapkan query database.');
        
        mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $device = mysqli_fetch_assoc($result);
        
        if (!$device) throw new \Exception('Perangkat tidak ditemukan atau Anda tidak memiliki akses.');
        
        return new Client([
            'host' => $device['host'],
            'user' => $device['api_user'],
            'pass' => $device['api_pass'],
            'port' => (int)$device['api_port'],
        ]);
    }

    // Fungsi untuk memformat byte
    function formatBytes($bytes, $precision = 2) {
        $bytes = (int)$bytes;
        if ($bytes == 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    $client = getMikrotikClient($koneksi);

    // ==================================================================
    // == BAGIAN UNTUK AKSI PENGGUNA (ENABLE, DISABLE, DELETE) - POST
    // ==================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ids = $_POST['ids'] ?? [];
        $action = $_POST['action'] ?? null;

        if (empty($ids) || !$action) {
            throw new \Exception('ID pengguna atau aksi tidak lengkap.');
        }

        $endpoint = '';
        $messageVerb = '';

        switch ($action) {
            case 'enable':
                $endpoint = '/ip/hotspot/user/enable';
                $messageVerb = 'diaktifkan';
                break;
            case 'disable':
                $endpoint = '/ip/hotspot/user/disable';
                $messageVerb = 'dinonaktifkan';
                break;
            case 'delete':
                $endpoint = '/ip/hotspot/user/remove';
                $messageVerb = 'dihapus';
                break;
            default:
                throw new \Exception('Aksi bulk tidak valid.');
        }

        $query = (new Query($endpoint))->equal('numbers', implode(',', $ids));
        $client->query($query)->read();

        echo json_encode(['status' => 'success', 'message' => count($ids) . " pengguna berhasil {$messageVerb}."]);
        exit; // Hentikan skrip setelah aksi POST selesai
    }

    // ==================================================================
    // == BAGIAN UNTUK MENGAMBIL DATA PENGGUNA - GET (Default)
    // ==================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $mikrotikUsers = $client->query('/ip/hotspot/user/print')->read();
        $activeUsersRaw = $client->query('/ip/hotspot/active/print')->read();
        $activeUsersMap = [];
        foreach ($activeUsersRaw as $activeUser) {
            $activeUsersMap[$activeUser['user']] = $activeUser;
        }

        $dbUsersRaw = mysqli_query($koneksi, "SELECT username, nama_lengkap, jurusan, kelas FROM hotspot_users");
        $dbUsersMap = [];
        while ($row = mysqli_fetch_assoc($dbUsersRaw)) {
            $dbUsersMap[$row['username']] = $row;
        }

        $combinedUsers = [];
        foreach ($mikrotikUsers as $user) {
            $username = $user['name'];
            
            $dbData = $dbUsersMap[$username] ?? [];
            $user['nama_lengkap'] = $dbData['nama_lengkap'] ?? null;
            $user['jurusan'] = $dbData['jurusan'] ?? null;
            $user['kelas'] = $dbData['kelas'] ?? null;

            if (isset($activeUsersMap[$username])) {
                $activeData = $activeUsersMap[$username];
                $user['logged_in'] = true;
                $user['address'] = $activeData['address'] ?? 'N/A';
                $user['mac-address'] = $activeData['mac-address'] ?? 'N/A';
                $user['uptime'] = $activeData['uptime'] ?? 'N/A';
                $user['bytes_in'] = formatBytes($activeData['bytes-in'] ?? 0);
                $user['bytes_out'] = formatBytes($activeData['bytes-out'] ?? 0);
            } else {
                $user['logged_in'] = false;
                $user['bytes_in'] = formatBytes($user['bytes-in'] ?? 0);
                $user['bytes_out'] = formatBytes($user['bytes-out'] ?? 0);
            }
            
            $combinedUsers[] = $user;
        }

        echo json_encode(['status' => 'success', 'data' => $combinedUsers]);
        exit;
    }

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
