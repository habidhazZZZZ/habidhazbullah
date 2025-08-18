<?php
// File: public/auth_check.php
// Deskripsi: Memeriksa sesi login di setiap halaman yang dilindungi.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah ada sesi login yang valid.
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login') {
    // Jika tidak, lempar pengguna KELUAR dari folder public ke halaman login utama.
    header("Location: ../login.php?pesan=belum_login");
    exit;
}
?>
