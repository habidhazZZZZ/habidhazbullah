<?php
session_start();

// Proteksi: hanya guru yang bisa membuat tugas
if (!isset($_SESSION['loggedin']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/Assignment.php');

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    if (empty(trim($_POST['title'])) || empty($_POST['subject_id']) || empty($_POST['due_date'])) {
        $_SESSION['message'] = "Judul, mata pelajaran, dan tenggat waktu tidak boleh kosong.";
        $_SESSION['message_type'] = 'error';
        header("Location: create_assignment.php");
        exit();
    }

    // Buat koneksi database
    $database = new Database();
    $db = $database->connect();

    // Buat instance dari model Assignment
    $assignment = new Assignment($db);

    // Ambil data dari form
    $assignment->title = trim($_POST['title']);
    $assignment->description = trim($_POST['description']);
    $assignment->subject_id = $_POST['subject_id'];
    $assignment->due_date = $_POST['due_date'];

    // Buat tugas baru
    if ($assignment->create()) {
        $_SESSION['message'] = "Tugas baru berhasil dibuat!";
        $_SESSION['message_type'] = 'success';
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Terjadi kesalahan. Tugas gagal dibuat.";
        $_SESSION['message_type'] = 'error';
        header("Location: create_assignment.php");
        exit();
    }
} else {
    // Jika halaman diakses langsung, arahkan ke halaman form
    header("Location: create_assignment.php");
    exit();
}
?>
