<?php
// Memulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah admin sudah login.
// Jika tidak ada session 'admin_logged_in' atau nilainya bukan true,
// maka arahkan kembali ke halaman login.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Simpan pesan untuk memberitahu pengguna mengapa mereka dialihkan
    $_SESSION['error_message'] = "Anda harus login untuk mengakses halaman ini.";
    header("Location: index.php");
    exit;
}

// Opsional: perbarui waktu aktivitas terakhir untuk auto-logout
// $_SESSION['last_activity'] = time();
?>
