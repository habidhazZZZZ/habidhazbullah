<?php
require_once 'auth_check.php';
require_once '../config/config.php';

$message = '';
$error = '';
$new_token = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);

    if (empty($nama_lengkap) || empty($email)) {
        $error = "Nama lengkap dan email tidak boleh kosong.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek apakah email sudah terdaftar
        $sql_check = "SELECT id FROM peserta WHERE email = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error = "Email ini sudah terdaftar.";
            } else {
                // Buat token unik
                $token = bin2hex(random_bytes(16)); // 32 karakter hex

                // Simpan ke database
                $sql_insert = "INSERT INTO peserta (nama_lengkap, email, token) VALUES (?, ?, ?)";
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("sss", $nama_lengkap, $email, $token);
                    if ($stmt_insert->execute()) {
                        $message = "Peserta berhasil ditambahkan!";
                        $new_token = $token;
                    } else {
                        $error = "Gagal menambahkan peserta. Silakan coba lagi.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peserta - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        body { display: block; height: auto; }
        .form-container { max-width: 600px; margin: 20px auto; }
        .message, .error { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .message { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .token-info { background-color: #e2e3e5; padding: 15px; border-radius: 5px; margin-top: 20px; word-break: break-all; }
        .token-info a { color: #007bff; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="dashboard.php" style="text-decoration:none; color:white;">Admin Dashboard</a></h1>
        <a href="logout.php">Logout</a>
    </header>

    <div class="admin-container form-container">
        <h2>Tambah Peserta Baru</h2>
        <p>Isi form di bawah ini untuk mendaftarkan peserta yang akan mengikuti tes.</p>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php if ($new_token):
                $test_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $test_url = str_replace('/admin', '/user', $test_url);
                $full_url = "http://" . $_SERVER['HTTP_HOST'] . $test_url . "/ujian.php?token=" . $new_token;
            ?>
            <div class="token-info">
                <strong>Link Tes untuk Peserta:</strong><br>
                <p>Bagikan link berikut kepada peserta:</p>
                <a href="<?php echo htmlspecialchars($full_url); ?>" target="_blank"><?php echo htmlspecialchars($full_url); ?></a>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="tambah_peserta.php" method="POST">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn">Tambah Peserta</button>
        </form>
    </div>
</body>
</html>
