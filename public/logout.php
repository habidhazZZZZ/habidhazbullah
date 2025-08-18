<?php
// File: public/logout.php
// Deskripsi: Menghancurkan sesi pengguna dan mengarahkan ke halaman login.

// Selalu mulai sesi untuk dapat mengakses dan menghancurkannya.
session_start();

// Hancurkan semua data yang tersimpan dalam sesi.
session_destroy();

// Setelah sesi dihancurkan, arahkan pengguna kembali ke halaman login utama
// (naik satu level folder ke root) dengan pesan sukses logout.
header("location: ../login.php?pesan=logout");

// Hentikan eksekusi skrip untuk memastikan redirect berjalan dengan baik.
exit;
?>
