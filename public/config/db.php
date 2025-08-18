<?php
// File: public/config/db.php

// 1. Definisikan detail koneksi
define('DB_HOST', '127.0.0.1'); // atau 'localhost'
define('DB_NAME', 'mikrotik_manager'); // Ganti jika nama database Anda berbeda
define('DB_USER', 'root');
define('DB_PASS', ''); // Default di Laragon kosong

// 2. Buat koneksi menggunakan konstanta di atas
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. Periksa apakah koneksi berhasil
if (!$koneksi) {
    // Hentikan skrip dan tampilkan pesan error yang jelas
    die("KONEKSI DATABASE GAGAL: " . mysqli_connect_error());
}
?>
