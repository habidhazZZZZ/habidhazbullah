<?php
/*
 * File Konfigurasi Database
 * Ganti nilai-nilai di bawah ini dengan kredensial database Anda.
 */

// Aktifkan pelaporan error untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pengaturan Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Kredensial Database
define('DB_HOST', 'localhost'); // Biasanya 'localhost' atau alamat IP server database
define('DB_USERNAME', 'root');      // Username database Anda
define('DB_PASSWORD', '');          // Password database Anda
define('DB_NAME', 'papikostik_db'); // Nama database yang telah Anda buat

// Membuat Koneksi ke Database menggunakan mysqli
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Memeriksa Koneksi
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan error.
    // Sebaiknya jangan tampilkan error detail di lingkungan produksi.
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Mengatur charset koneksi ke utf8mb4 untuk mendukung karakter yang beragam
$conn->set_charset("utf8mb4");

// Opsi untuk memulai session, akan sering digunakan
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
