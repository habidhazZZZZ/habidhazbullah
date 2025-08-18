<?php
// File: public/proses_hapus_perangkat.php
// Deskripsi: Memproses permintaan penghapusan perangkat.

require_once 'auth_check.php';
require_once 'config/db.php';

// Pastikan request adalah POST untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: setup.php?pesan=gagal');
    exit;
}

$device_id = $_POST['device_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (empty($device_id)) {
    header('Location: setup.php?pesan=gagal');
    exit;
}

// Query untuk menghapus perangkat.
// KLAUSA "AND user_id = ?" SANGAT PENTING untuk memastikan pengguna
// hanya bisa menghapus perangkat miliknya sendiri.
$query = "DELETE FROM devices WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($koneksi, $query);

if ($stmt) {
    // 'i' untuk integer
    mysqli_stmt_bind_param($stmt, "ii", $device_id, $user_id);
    mysqli_stmt_execute($stmt);

    // Cek apakah ada baris yang terpengaruh (terhapus)
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Jika perangkat yang dihapus adalah perangkat yang sedang aktif, hapus dari sesi
        if (isset($_SESSION['active_device_id']) && $_SESSION['active_device_id'] == $device_id) {
            unset($_SESSION['active_device_id']);
        }
        header('Location: setup.php?pesan=sukses_hapus');
    } else {
        // Tidak ada baris yang terhapus, kemungkinan ID tidak cocok atau bukan milik user
        header('Location: setup.php?pesan=gagal_hapus');
    }
    mysqli_stmt_close($stmt);
} else {
    // Gagal menyiapkan statement
    header('Location: setup.php?pesan=gagal');
}

exit;
?>
