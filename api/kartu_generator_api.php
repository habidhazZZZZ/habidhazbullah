<?php
/**
 * File: api/kartu_generator_api.php (Versi Final - Gabungan)
 * Deskripsi: API untuk Cetak Kartu. Menggabungkan logika pengecekan ke MikroTik
 * dengan query JOIN ke database lokal untuk hasil yang paling akurat.
 */

session_start();
header('Content-Type: application/json');

// Memuat file-file yang diperlukan
$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

use \RouterOS\Client;
use \RouterOS\Query;

/**
 * Fungsi untuk mendapatkan koneksi ke router yang aktif.
 */
function getMikrotikClient($koneksi) {
    if (!isset($_SESSION['active_device_id'])) {
        throw new \Exception('Sesi perangkat aktif tidak ditemukan. Silakan pilih perangkat dari dashboard.');
    }
    if (!isset($_SESSION['user_id'])) {
        throw new \Exception('Sesi pengguna tidak valid. Silakan login kembali.');
    }
    
    $deviceId = $_SESSION['active_device_id'];
    $userId = $_SESSION['user_id'];
    
    $query = "SELECT host, api_user, api_pass, api_port FROM devices WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    if (!$stmt) throw new \Exception('Gagal menyiapkan query untuk mengambil detail perangkat.');
    
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

/**
 * Fungsi helper untuk mengirim respons JSON yang konsisten.
 */
function json_response($status, $dataOrMessage) {
    if ($status === 'success') {
        echo json_encode(['status' => 'success', 'data' => $dataOrMessage]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $dataOrMessage]);
    }
    exit;
}

try {
    // Keamanan: Pastikan pengguna sudah login
    if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login') {
        throw new \Exception('Akses ditolak. Anda harus login terlebih dahulu.');
    }
    
    if (!$koneksi) {
        throw new Exception("Gagal terhubung ke database.");
    }

    $action = $_GET['action'] ?? '';

    switch ($action) {
        
        case 'get_all_jurusan':
            $query = "SELECT DISTINCT jurusan FROM hotspot_users WHERE jurusan IS NOT NULL AND jurusan != '' ORDER BY jurusan ASC";
            $result = mysqli_query($koneksi, $query);
            if (!$result) throw new Exception("Query untuk mengambil jurusan gagal: " . mysqli_error($koneksi));
            
            $jurusanList = array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'jurusan');
            json_response('success', $jurusanList);
            break;

        case 'get_classes_by_jurusan':
            $jurusan = $_GET['jurusan'] ?? null;
            if (!$jurusan) throw new Exception("Parameter 'jurusan' wajib diisi.");

            $query = "SELECT DISTINCT kelas FROM hotspot_users WHERE jurusan = ? AND kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC";
            $stmt = mysqli_prepare($koneksi, $query);
            if (!$stmt) throw new Exception("Gagal menyiapkan query untuk mengambil kelas.");
            
            mysqli_stmt_bind_param($stmt, "s", $jurusan);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $kelasList = array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'kelas');
            json_response('success', $kelasList);
            break;

        case 'get_users_by_class':
            $jurusan = $_GET['jurusan'] ?? null;
            $kelas = $_GET['class'] ?? null;
            if (!$jurusan || !$kelas) throw new Exception("Parameter 'jurusan' dan 'class' wajib diisi.");

            // --- GABUNGAN LOGIKA LAMA DAN BARU ---

            // 1. Ambil data dari MikroTik (Logika Lama)
            $client = getMikrotikClient($koneksi);
            $mikrotikUsersRaw = $client->query('/ip/hotspot/user/print')->read();
            
            $mikrotikUsersMap = [];
            foreach ($mikrotikUsersRaw as $user) {
                if (isset($user['name'])) {
                    $clean_name = trim(strtolower($user['name']));
                    $mikrotikUsersMap[$clean_name] = $user;
                }
            }

            // 2. Ambil data dari Database Lokal dengan JOIN (Logika Baru)
            $query = "
                SELECT 
                    hu.nama_lengkap, 
                    hu.username,
                    u.password AS db_password
                FROM 
                    hotspot_users AS hu
                JOIN 
                    users AS u ON hu.username = u.username
                WHERE 
                    hu.jurusan = ? AND hu.kelas = ? 
                ORDER BY 
                    hu.nama_lengkap ASC";
            
            $stmt = mysqli_prepare($koneksi, $query);
            if (!$stmt) {
                throw new Exception("Gagal menyiapkan query database: " . mysqli_error($koneksi));
            }
            
            mysqli_stmt_bind_param($stmt, "ss", $jurusan, $kelas);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $dbUsers = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 3. Gabungkan dan filter: hanya tampilkan user yang ada di DB dan MikroTik
            $integratedUsers = [];
            foreach ($dbUsers as $dbUser) {
                $clean_username = trim(strtolower($dbUser['username']));
                if (isset($mikrotikUsersMap[$clean_username])) {
                    $mikrotikUser = $mikrotikUsersMap[$clean_username];
                    
                    $mergedUser = [
                        'nama_lengkap' => $dbUser['nama_lengkap'],
                        'name'         => $dbUser['username'], // 'name' untuk frontend JS
                        'password'     => $mikrotikUser['password'] ?? $dbUser['db_password'], // Prioritaskan password dari Mikrotik
                        'jurusan'      => $jurusan,
                        'kelas'        => $kelas,
                    ];
                    $integratedUsers[] = $mergedUser;
                }
            }

            json_response('success', $integratedUsers);
            break;

        default:
            throw new Exception("Aksi tidak valid atau tidak diizinkan.");
    }

} catch (Throwable $e) {
    // --- PERBAIKAN DI SINI ---
    json_response('error', $e->getMessage());
}
