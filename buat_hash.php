<?php
// File: buat_hash.php
// Ganti password di bawah ini dengan password yang Anda inginkan.
$password_baru = 'habidhaz123';

// Membuat hash dari password di atas.
$hash_hasil = password_hash($password_baru, PASSWORD_DEFAULT);

echo "<h2>Generator Hash Password</h2>";
echo "<p>Gunakan hash di bawah ini untuk dimasukkan ke kolom 'password' di database.</p>";
echo "<p><b>Password:</b> " . htmlspecialchars($password_baru) . "</p>";
echo "<p><b>Hash yang dihasilkan:</b></p>";
echo "<textarea readonly style='width: 100%; height: 60px; font-family: monospace;'>" . htmlspecialchars($hash_hasil) . "</textarea>";
?>
