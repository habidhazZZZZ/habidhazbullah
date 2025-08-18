<?php
// File: public/pages/proses_hapus_admin.php
// Deskripsi: Memproses permintaan penghapusan pengguna dari database.

session_start();
require_once '../config/db.php';

// Keamanan: Pastikan hanya admin yang bisa menjalankan skrip ini.
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php?pesan=akses_ditolak");
    exit;
}

// Ambil ID pengguna yang akan dihapus dari URL.
$id_to_delete = $_GET['id'] ?? null;

// Validasi: Pastikan ID ada dan merupakan angka.
if (!$id_to_delete || !is_numeric($id_to_delete)) {
    header("Location: ../index.php?page=manajemen_admin&status=hapus_gagal");
    exit;
}

// Keamanan Tambahan: Admin tidak bisa menghapus akunnya sendiri.
if ($id_to_delete == $_SESSION['user_id']) {
    header("Location: ../index.php?page=manajemen_admin&status=hapus_sendiri");
    exit;
}

// Siapkan query SQL untuk menghapus pengguna berdasarkan ID.
$query = "DELETE FROM users WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);

if ($stmt) {
    // Bind ID ke query. 'i' berarti integer.
    mysqli_stmt_bind_param($stmt, "i", $id_to_delete);

    // Eksekusi query.
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, arahkan kembali dengan status sukses.
        header("Location: ../index.php?page=manajemen_admin&status=hapus_sukses");
    } else {
        // Jika gagal, arahkan kembali dengan status gagal.
        header("Location: ../index.php?page=manajemen_admin&status=hapus_gagal");
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: ../index.php?page=manajemen_admin&status=hapus_gagal");
}

mysqli_close($koneksi);
exit;
?>
