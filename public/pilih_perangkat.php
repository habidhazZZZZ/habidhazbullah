<?php
// File: public/pilih_perangkat.php
// Deskripsi: Memproses pemilihan perangkat dari halaman setup.php

require_once 'auth_check.php';
require_once 'config/db.php';

$device_id = $_GET['device_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$device_id) {
    header('Location: setup.php?pesan=error');
    exit;
}

// Verifikasi bahwa perangkat ini milik pengguna yang sedang login untuk keamanan
$query = "SELECT id FROM devices WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ii", $device_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // Jika verifikasi berhasil, simpan ID perangkat ke sesi
    $_SESSION['active_device_id'] = $device_id;
    
    // --- PERUBAHAN DI SINI ---
    // Arahkan ke halaman menu utama, bukan langsung ke dashboard.
    header('Location: index.php');
    exit;

} else {
    // Jika perangkat bukan milik user, kembalikan ke setup dengan pesan error
    header('Location: setup.php?pesan=akses_ditolak');
    exit;
}
?>
