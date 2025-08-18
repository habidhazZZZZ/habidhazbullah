<?php
// File: api/get_saved_devices.php
// Deskripsi: API untuk mengambil daftar perangkat yang disimpan oleh pengguna.

session_start();
header('Content-Type: application/json');

// Keamanan: Pastikan pengguna sudah login.
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Anda harus login.']);
    exit;
}

$project_root = dirname(__DIR__);
require_once $project_root . '/public/config/db.php';

try {
    $user_id = $_SESSION['user_id'];
    // Ambil ID perangkat yang sedang aktif dari sesi untuk penandaan
    $active_device_id = $_SESSION['active_device_id'] ?? null;

    // Ambil data perangkat milik pengguna yang sedang login
    $query = "SELECT id, device_name, host, api_user, api_port FROM devices WHERE user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $devices = [];
    while($row = mysqli_fetch_assoc($result)) {
        // Tambahkan penanda 'is_active' jika ID perangkat cocok dengan yang ada di sesi
        $row['is_active'] = ($row['id'] == $active_device_id);
        $devices[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $devices]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan internal pada server: ' . $e->getMessage()
    ]);
}
?>
