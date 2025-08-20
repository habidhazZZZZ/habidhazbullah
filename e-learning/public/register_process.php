<?php
session_start();

// Atur path root dan inklusi file database
$root_path = realpath(dirname(__FILE__) . '/..');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/User.php'); // Kita akan buat model User nanti

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Buat koneksi database
    $database = new Database();
    $db = $database->connect();

    // Buat instance dari model User
    $user = new User($db);

    // Ambil data dari form
    $user->username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user->role_id = $_POST['role_id'];

    // Validasi sederhana
    if (empty($user->username) || empty($password) || empty($user->role_id)) {
        $_SESSION['message'] = "Semua field harus diisi!";
        $_SESSION['message_type'] = 'error';
        header("Location: register.php");
        exit();
    }

    // Cek apakah username sudah ada
    if ($user->isUsernameExists()) {
        $_SESSION['message'] = "Username sudah digunakan. Silakan pilih username lain.";
        $_SESSION['message_type'] = 'error';
        header("Location: register.php");
        exit();
    }

    // Hash password
    $user->password = password_hash($password, PASSWORD_BCRYPT);

    // Buat user baru
    if ($user->create()) {
        $_SESSION['message'] = "Registrasi berhasil! Silakan login.";
        $_SESSION['message_type'] = 'success';
        header("Location: login.php"); // Arahkan ke halaman login setelah sukses
        exit();
    } else {
        $_SESSION['message'] = "Terjadi kesalahan. Registrasi gagal.";
        $_SESSION['message_type'] = 'error';
        header("Location: register.php");
        exit();
    }
} else {
    // Jika halaman diakses langsung, arahkan ke halaman registrasi
    header("Location: register.php");
    exit();
}
?>
