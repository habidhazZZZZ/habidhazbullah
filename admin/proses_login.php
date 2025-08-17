<?php
// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file konfigurasi database
require_once '../config/config.php';

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Username dan password tidak boleh kosong.";
        header("Location: index.php");
        exit;
    }

    // Siapkan statement untuk mencegah SQL Injection
    $sql = "SELECT id, username, password FROM admins WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variabel ke statement
        $stmt->bind_param("s", $param_username);

        // Set parameter
        $param_username = $username;

        // Eksekusi statement
        if ($stmt->execute()) {
            // Simpan hasil
            $stmt->store_result();

            // Cek jika username ada, lalu verifikasi password
            if ($stmt->num_rows == 1) {
                // Bind hasil ke variabel
                $stmt->bind_result($id, $username_db, $hashed_password);
                if ($stmt->fetch()) {
                    // Verifikasi password
                    if (password_verify($password, $hashed_password)) {
                        // Password benar, mulai session baru

                        // Hapus session lama dan buat yang baru untuk mencegah session fixation
                        session_regenerate_id(true);

                        // Simpan data di session
                        $_SESSION["admin_logged_in"] = true;
                        $_SESSION["admin_id"] = $id;
                        $_SESSION["admin_username"] = $username_db;

                        // Arahkan ke dashboard
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        // Password salah
                        $_SESSION['error_message'] = "Username atau password salah.";
                        header("Location: index.php");
                        exit;
                    }
                }
            } else {
                // Username tidak ditemukan
                $_SESSION['error_message'] = "Username atau password salah.";
                header("Location: index.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan. Silakan coba lagi nanti.";
            header("Location: index.php");
            exit;
        }

        // Tutup statement
        $stmt->close();
    }

    // Tutup koneksi
    $conn->close();
} else {
    // Jika bukan request POST, arahkan ke halaman login
    header("Location: index.php");
    exit;
}
?>
