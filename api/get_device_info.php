<?php
// File: api/get_device_info.php
// Deskripsi: API ringan untuk mengambil nama dan host perangkat yang aktif.

session_start();
header('Content-Type: application/json');

// Keamanan: Pastikan pengguna sudah login dan memilih perangkat.
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login' || !isset($_SESSION['active_device_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

$project_root = dirname(__DIR__);
require_once $project_root . '/public/config/db.php';

try {
    $device_id = $_SESSION['active_device_id'];
    $user_id = $_SESSION['user_id'];

    // Ambil hanya kolom yang dibutuhkan dari database
    $query = "SELECT device_name, host FROM devices WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $device_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $device = mysqli_fetch_assoc($result);

    if ($device) {
        echo json_encode(['status' => 'success', 'data' => $device]);
    } else {
        throw new Exception('Perangkat aktif tidak ditemukan di database.');
    }

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
