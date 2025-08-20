<?php
session_start();

// Hancurkan semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Arahkan ke halaman login dengan pesan
session_start();
$_SESSION['message'] = "Anda telah berhasil logout.";
$_SESSION['message_type'] = 'success';
header("Location: login.php");
exit;
?>
