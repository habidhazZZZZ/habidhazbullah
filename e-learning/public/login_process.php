<?php
session_start();

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/User.php');

// Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Buat koneksi database
    $database = new Database();
    $db = $database->connect();

    // Buat instance dari model User
    $user = new User($db);

    // Ambil data dari form
    $user->username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi sederhana
    if (empty($user->username) || empty($password)) {
        $_SESSION['message'] = "Username dan password harus diisi!";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }

    // Dapatkan user berdasarkan username
    $stmt = $user->getByUsername();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $hashed_password = $row['password'];

        // Verifikasi password
        if (password_verify($password, $hashed_password)) {
            // Password benar, mulai session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role_id'] = $row['role_id'];

            // Arahkan ke dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Password salah
            $_SESSION['message'] = "Username atau password salah.";
            $_SESSION['message_type'] = 'error';
            header("Location: login.php");
            exit();
        }
    } else {
        // Username tidak ditemukan
        $_SESSION['message'] = "Username atau password salah.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }
} else {
    // Jika halaman diakses langsung, arahkan ke halaman login
    header("Location: login.php");
    exit();
}
?>
