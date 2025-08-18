<?php
session_start();
// Mengambil koneksi dari dalam folder public
require 'public/config/db.php'; 

// Ambil data dari form
$username = $_POST['username'];
$password = $_POST['password'];

// Lindungi dari SQL Injection
$username = mysqli_real_escape_string($koneksi, $username);

// Query untuk mencari user
$query = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

    // Verifikasi password
    if (password_verify($password, $data['password'])) {
        // Buat session
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['level'] = $data['level'];
        $_SESSION['status'] = "login";

        // Alihkan ke aplikasi inti setelah sukses login
        header("location: public/");
        exit;
    } else {
        header("location: login.php?pesan=gagal");
        exit;
    }
} else {
    header("location: login.php?pesan=gagal");
    exit;
}
?>
