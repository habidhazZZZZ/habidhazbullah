<?php
/**
 * File: public/config/db.php
 * Deskripsi: Konfigurasi dan inisialisasi koneksi ke database.
 * File ini sekarang juga membuat objek koneksi $koneksi.
 */

// 1. Definisikan detail koneksi database Anda
define('DB_HOST', '127.0.0.1'); // atau 'localhost'
define('DB_NAME', 'mikrotik_manager');
define('DB_USER', 'root');
define('DB_PASS', '');

// 2. Buat objek koneksi mysqli
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. Periksa koneksi dan hentikan skrip jika gagal
if (!$koneksi) {
    // Jangan tampilkan error detail di production, cukup log saja.
    // Untuk sekarang, die() cukup untuk debugging.
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set charset ke utf8mb4 untuk mendukung berbagai karakter
mysqli_set_charset($koneksi, "utf8mb4");

?>
