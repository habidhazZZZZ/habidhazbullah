<?php
/*
 * Skrip Bantuan untuk Membuat Admin Pertama
 * ==========================================
 * CARA PENGGUNAAN:
 * 1. Buka file ini di browser Anda (misal: http://localhost/papikostik/config/create_admin.php).
 * 2. Isi username dan password yang Anda inginkan.
 * 3. Klik "Buat Admin".
 * 4. Skrip ini akan memasukkan data admin baru dengan password yang sudah di-hash ke database.
 * 5. PENTING: HAPUS FILE INI SETELAH SELESAI DIGUNAKAN UNTUK ALASAN KEAMANAN.
 */

require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password tidak boleh kosong.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah username sudah ada
        $check_sql = "SELECT id FROM admins WHERE username = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_stmt->store_result();
            if ($check_stmt->num_rows > 0) {
                $error = "Username '{$username}' sudah ada. Silakan gunakan username lain.";
            } else {
                // Masukkan admin baru ke database
                $insert_sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
                if ($stmt = $conn->prepare($insert_sql)) {
                    $stmt->bind_param("ss", $username, $hashed_password);
                    if ($stmt->execute()) {
                        $message = "Admin '{$username}' berhasil dibuat! Anda sekarang bisa login di halaman admin. <strong>JANGAN LUPA HAPUS FILE INI.</strong>";
                    } else {
                        $error = "Gagal membuat admin: " . $conn->error;
                    }
                    $stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Admin Awal</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f0f0; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; width: 100%; }
        .message, .error { padding: 15px; border-radius: 5px; margin: 20px 0; }
        .message { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Buat Akun Admin Pertama</h2>
        <p>Gunakan form ini untuk membuat akun admin awal Anda.</p>
        <p style="color: red; font-weight: bold;">PERINGATAN: Hapus file ini dari server setelah Anda selesai membuat admin.</p>

        <?php if ($message) echo "<div class='message'>{$message}</div>"; ?>
        <?php if ($error) echo "<div class='error'>{$error}</div>"; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username Admin Baru:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password Admin Baru:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Buat Admin</button>
        </form>
    </div>
</body>
</html>
