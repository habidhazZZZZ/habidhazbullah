<?php
// 1. Sertakan auth_check.php untuk memastikan hanya admin yang login bisa mengakses
require_once 'auth_check.php';

// 2. Sertakan file konfigurasi
require_once '../config/config.php';

// Ambil username admin dari session untuk ditampilkan
$admin_username = htmlspecialchars($_SESSION['admin_username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tes Papikostik</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        /* Override beberapa style khusus untuk dashboard */
        body {
            display: block; /* Override flex-box dari login */
            height: auto;
            background-color: #f4f7f6;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Dashboard</h1>
        <div>
            <span>Selamat datang, <strong><?php echo $admin_username; ?></strong>!</span>
            <a href="logout" class="btn-logout">Logout</a>
        </div>
    </header>

    <div class="admin-container">
        <h2>Menu Utama</h2>
        <p>Silakan pilih salah satu menu di bawah ini untuk mengelola website.</p>

        <div class="dashboard-menu">
            <a href="tambah_peserta" class="menu-item">
                <h3>Tambah Peserta</h3>
                <p>Mendaftarkan peserta baru untuk mengikuti tes.</p>
            </a>
            <a href="lihat_hasil" class="menu-item">
                <h3>Lihat Hasil Tes</h3>
                <p>Melihat dan mengelola hasil tes dari semua peserta.</p>
            </a>
            <a href="kelola_konten" class="menu-item">
                <h3>Kelola Soal & Deskripsi</h3>
                <p>Mengedit daftar soal Papikostik dan deskripsi aspek.</p>
            </a>
            <a href="daftar_peserta" class="menu-item">
                <h3>Daftar Peserta</h3>
                <p>Melihat dan mengelola data peserta yang terdaftar.</p>
            </a>
        </div>
    </div>

</body>
</html>
