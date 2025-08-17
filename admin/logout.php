<?php
// Mulai session untuk mengaksesnya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Hapus semua variabel session
$_SESSION = array();

// 2. Hancurkan session
session_destroy();

// 3. Arahkan kembali ke halaman login
//    Pesan bisa ditambahkan jika diinginkan, tapi biasanya tidak perlu untuk logout.
header("Location: index.php");
exit;
?>
