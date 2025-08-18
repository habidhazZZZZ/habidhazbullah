<?php
// File: public/device_check.php
// Deskripsi: Memeriksa apakah pengguna sudah memilih perangkat aktif.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah ada sesi 'active_device_id'.
if (!isset($_SESSION['active_device_id'])) {
    // Jika pengguna belum memilih perangkat...
    header("Location: setup.php?pesan=pilih_dulu"); // Arahkan ke halaman setup
    exit; // Hentikan eksekusi skrip.
}
?>
