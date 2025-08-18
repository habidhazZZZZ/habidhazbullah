<?php
// File: public/proses_tambah_perangkat.php
// Deskripsi: Memproses form penambahan perangkat baru.

require_once 'auth_check.php';
require_once 'config/db.php';

$user_id = $_SESSION['user_id'];
$device_name = $_POST['device_name'] ?? '';
$host = $_POST['host'] ?? '';
$api_user = $_POST['api_user'] ?? '';
$api_pass = $_POST['api_pass'] ?? '';
$api_port = (int)($_POST['api_port'] ?? 8728);

if (empty($device_name) || empty($host) || empty($api_user)) {
    header('Location: setup.php?pesan=gagal_tambah');
    exit;
}

$query = "INSERT INTO devices (user_id, device_name, host, api_user, api_pass, api_port) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $query);
// 'i' untuk integer, 's' untuk string
mysqli_stmt_bind_param($stmt, "issssi", $user_id, $device_name, $host, $api_user, $api_pass, $api_port);

if (mysqli_stmt_execute($stmt)) {
    header('Location: setup.php?pesan=sukses');
} else {
    header('Location: setup.php?pesan=gagal_tambah');
}
exit;
?>
