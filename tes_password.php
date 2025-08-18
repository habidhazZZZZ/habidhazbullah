<?php
// File: tes_password.php

// Ganti hash ini dengan hash dari user 'habidhaz' di database Anda.
// Salin-tempel langsung dari kolom 'password' di phpMyAdmin.
$hash_dari_db = '$2y$10$Y5b5d7v5j.5E5f9B6c8h3u.G5f6h8c9d4e7b2a1e0f3g2h1i0j9k8l';

// Masukkan password yang Anda coba saat login.
$password_yang_diketik = 'password123'; // Ganti ini jika passwordnya berbeda

echo "<h2>Mencoba Memverifikasi Password</h2>";
echo "<p><strong>Password yang diketik:</strong> " . htmlspecialchars($password_yang_diketik) . "</p>";
echo "<p><strong>Hash dari Database:</strong> " . htmlspecialchars($hash_dari_db) . "</p>";

if (password_verify($password_yang_diketik, $hash_dari_db)) {
    echo '<h3 style="color: green;">SUKSES: Password cocok dengan hash!</h3>';
    echo "<p>Ini berarti fungsi verifikasi berjalan normal. Masalahnya ada di alur `proses_login.php`.</p>";
} else {
    echo '<h3 style="color: red;">GAGAL: Password TIDAK cocok dengan hash!</h3>';
    echo "<p>Ini berarti salah satu dari dua hal:</p>";
    echo "<ul>";
    echo "<li>Password yang Anda ketik ('" . htmlspecialchars($password_yang_diketik) . "') salah.</li>";
    echo "<li>Hash di database Anda tidak dibuat dari password tersebut.</li>";
    echo "</ul>";
    echo "<p><b>Solusi:</b> Coba buat ulang user dengan hash baru.</p>";
}
?>
