<?php
// File: public/pages/proses_tambah_admin.php
// Deskripsi: Memproses data dari form tambah admin dan menyimpannya ke database.

// Mulai sesi untuk memeriksa level pengguna dan koneksi ke database.
session_start();
require_once '../config/db.php';

// Keamanan: Pastikan hanya admin yang bisa menjalankan skrip ini.
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    // Jika bukan admin, tendang keluar ke halaman login.
    header("Location: ../../login.php?pesan=akses_ditolak");
    exit;
}

// Ambil data dari form dengan aman.
$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$level = $_POST['level'] ?? 'user';

// Validasi dasar: Pastikan input penting tidak kosong.
if (empty($nama_lengkap) || empty($username) || empty($password)) {
    // Jika ada yang kosong, kembalikan ke halaman sebelumnya dengan pesan gagal.
    header("Location: ../index.php?page=manajemen_admin&status=gagal");
    exit;
}

// Enkripsi password menggunakan metode hashing yang aman. Ini wajib!
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Siapkan query SQL menggunakan prepared statements untuk mencegah SQL Injection.
$query = "INSERT INTO users (nama_lengkap, username, password, level) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $query);

// Jika statement berhasil disiapkan, bind parameternya.
if ($stmt) {
    // 'ssss' berarti keempat variabel adalah string.
    mysqli_stmt_bind_param($stmt, "ssss", $nama_lengkap, $username, $hashed_password, $level);

    // Eksekusi statement dan periksa hasilnya.
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, arahkan kembali dengan status sukses.
        header("Location: ../index.php?page=manajemen_admin&status=sukses");
    } else {
        // Jika gagal (misalnya username duplikat), arahkan kembali dengan status gagal.
        header("Location: ../index.php?page=manajemen_admin&status=gagal");
    }
    // Tutup statement.
    mysqli_stmt_close($stmt);
} else {
    // Jika query gagal disiapkan, ini biasanya masalah pada SQL atau koneksi.
    header("Location: ../index.php?page=manajemen_admin&status=gagal");
}

// Tutup koneksi database.
mysqli_close($koneksi);
exit;
?>
