<?php
session_start();

// Proteksi: hanya siswa yang bisa submit tugas
if (!isset($_SESSION['loggedin']) || $_SESSION['role_id'] != 2) { // 2 = student
    header("Location: login.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/Submission.php');

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_submission'])) {

    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['user_id'];

    // --- Cek Duplikasi Submission ---
    $database = new Database();
    $db = $database->connect();
    $submission_check = new Submission($db);
    $submission_check->assignment_id = $assignment_id;
    $submission_check->student_id = $student_id;

    if ($submission_check->hasSubmitted()) {
        $_SESSION['message'] = "Anda sudah pernah mengumpulkan tugas ini.";
        $_SESSION['message_type'] = 'error';
        header("Location: assignment.php?id=" . $assignment_id);
        exit();
    }

    // --- Proses Upload File ---
    $upload_dir = $root_path . '/public/uploads/';
    $file = $_FILES['file_submission'];

    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = "Terjadi error saat mengupload file.";
        $_SESSION['message_type'] = 'error';
        header("Location: assignment.php?id=" . $assignment_id);
        exit();
    }

    // Validasi ukuran file (misal, 25MB)
    $max_size = 25 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        $_SESSION['message'] = "Ukuran file terlalu besar. Maksimal 25MB.";
        $_SESSION['message_type'] = 'error';
        header("Location: assignment.php?id=" . $assignment_id);
        exit();
    }

    // Validasi tipe file
    $allowed_types = ['doc', 'docx', 'pdf', 'jpg', 'jpeg', 'png', 'mp4', 'xls', 'xlsx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        $_SESSION['message'] = "Tipe file tidak diizinkan. Hanya: " . implode(', ', $allowed_types);
        $_SESSION['message_type'] = 'error';
        header("Location: assignment.php?id=" . $assignment_id);
        exit();
    }

    // Buat nama file yang unik
    $unique_name = $student_id . '_' . $assignment_id . '_' . time() . '.' . $file_ext;
    $target_file = $upload_dir . $unique_name;

    // Pindahkan file ke direktori uploads
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // --- Simpan ke Database ---
        $submission = new Submission($db);
        $submission->assignment_id = $assignment_id;
        $submission->student_id = $student_id;
        $submission->file_path = 'uploads/' . $unique_name; // Simpan path relatif

        if ($submission->create()) {
            $_SESSION['message'] = "Tugas berhasil dikumpulkan!";
            $_SESSION['message_type'] = 'success';
            header("Location: dashboard.php");
            exit();
        } else {
            // Hapus file jika gagal simpan ke DB
            unlink($target_file);
            $_SESSION['message'] = "Gagal menyimpan data submission ke database.";
            $_SESSION['message_type'] = 'error';
            header("Location: assignment.php?id=" . $assignment_id);
            exit();
        }
    } else {
        $_SESSION['message'] = "Gagal memindahkan file yang diupload.";
        $_SESSION['message_type'] = 'error';
        header("Location: assignment.php?id=" . $assignment_id);
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
