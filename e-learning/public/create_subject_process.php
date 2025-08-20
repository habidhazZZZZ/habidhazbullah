<?php
session_start();

// Proteksi: hanya guru yang bisa membuat mata pelajaran
if (!isset($_SESSION['loggedin']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/Subject.php');

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    if (empty(trim($_POST['name']))) {
        $_SESSION['message'] = "Nama mata pelajaran tidak boleh kosong.";
        $_SESSION['message_type'] = 'error';
        header("Location: create_subject.php");
        exit();
    }

    // Buat koneksi database
    $database = new Database();
    $db = $database->connect();

    // Buat instance dari model Subject
    $subject = new Subject($db);

    // Ambil data dari form
    $subject->name = trim($_POST['name']);
    $subject->description = trim($_POST['description']);
    $subject->teacher_id = $_SESSION['user_id']; // Ambil ID guru dari session

    // Buat mata pelajaran baru
    if ($subject->create()) {
        $_SESSION['message'] = "Mata pelajaran baru berhasil dibuat!";
        $_SESSION['message_type'] = 'success';
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Terjadi kesalahan. Mata pelajaran gagal dibuat.";
        $_SESSION['message_type'] = 'error';
        header("Location: create_subject.php");
        exit();
    }
} else {
    // Jika halaman diakses langsung, arahkan ke halaman form
    header("Location: create_subject.php");
    exit();
}
?>
