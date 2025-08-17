<?php
// Mulai session untuk menangani pesan error dan status login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika admin sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tes Papikostik</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php
        // Tampilkan pesan error jika ada
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            // Hapus pesan error setelah ditampilkan agar tidak muncul lagi saat refresh
            unset($_SESSION['error_message']);
        }
        ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
