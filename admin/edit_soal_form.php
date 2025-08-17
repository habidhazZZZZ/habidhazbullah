<?php
require_once 'auth_check.php';
require_once '../config/config.php';

$soal_id = $_GET['id'] ?? null;
if (!$soal_id) {
    header("Location: kelola_konten.php?view=soal");
    exit;
}

$soal = null;
$message = '';
$error = '';

// Logika untuk memproses form saat disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data
    $nomor_soal = filter_input(INPUT_POST, 'nomor_soal', FILTER_SANITIZE_NUMBER_INT);
    $pernyataan_a = trim($_POST['pernyataan_a']);
    $aspek_a = strtoupper(trim($_POST['aspek_a']));
    $pernyataan_b = trim($_POST['pernyataan_b']);
    $aspek_b = strtoupper(trim($_POST['aspek_b']));

    if (empty($pernyataan_a) || empty($aspek_a) || empty($pernyataan_b) || empty($aspek_b)) {
        $error = "Semua field harus diisi.";
    } else {
        $sql = "UPDATE soal_papikostik SET nomor_soal = ?, pernyataan_a = ?, aspek_a = ?, pernyataan_b = ?, aspek_b = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssi", $nomor_soal, $pernyataan_a, $aspek_a, $pernyataan_b, $aspek_b, $soal_id);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Soal berhasil diperbarui!";
                header("Location: kelola_konten.php?view=soal");
                exit;
            } else {
                $error = "Gagal memperbarui soal. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }
}

// Logika untuk mengambil data soal saat halaman dimuat (GET)
$sql_get = "SELECT nomor_soal, pernyataan_a, aspek_a, pernyataan_b, aspek_b FROM soal_papikostik WHERE id = ?";
if ($stmt_get = $conn->prepare($sql_get)) {
    $stmt_get->bind_param("i", $soal_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result->num_rows === 1) {
        $soal = $result->fetch_assoc();
    } else {
        $error = "Soal tidak ditemukan.";
    }
    $stmt_get->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Soal - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        body { display: block; height: auto; }
        .form-container { max-width: 800px; margin: 20px auto; }
        .message, .error { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .message { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        textarea { width: 100%; min-height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="dashboard" style="text-decoration:none; color:white;">Admin Dashboard</a></h1>
        <a href="logout">Logout</a>
    </header>

    <div class="admin-container form-container">
        <h2>Edit Soal Papikostik</h2>
        <a href="kelola_konten?view=soal">&larr; Kembali ke Daftar Soal</a>

        <?php if ($error): ?>
            <div class="error" style="margin-top: 20px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($soal): ?>
            <form action="edit_soal_form?id=<?php echo $soal_id; ?>" method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label for="nomor_soal">Nomor Soal</label>
                    <input type="number" id="nomor_soal" name="nomor_soal" value="<?php echo htmlspecialchars($soal['nomor_soal']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="pernyataan_a">Pernyataan A</label>
                    <textarea id="pernyataan_a" name="pernyataan_a" required><?php echo htmlspecialchars($soal['pernyataan_a']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="aspek_a">Aspek A (Satu Huruf Kapital)</label>
                    <input type="text" id="aspek_a" name="aspek_a" value="<?php echo htmlspecialchars($soal['aspek_a']); ?>" maxlength="1" required>
                </div>

                <hr style="margin: 30px 0;">

                <div class="form-group">
                    <label for="pernyataan_b">Pernyataan B</label>
                    <textarea id="pernyataan_b" name="pernyataan_b" required><?php echo htmlspecialchars($soal['pernyataan_b']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="aspek_b">Aspek B (Satu Huruf Kapital)</label>
                    <input type="text" id="aspek_b" name="aspek_b" value="<?php echo htmlspecialchars($soal['aspek_b']); ?>" maxlength="1" required>
                </div>

                <button type="submit" class="btn">Simpan Perubahan</button>
            </form>
        <?php else: ?>
            <p style="margin-top: 20px;">Data soal tidak dapat dimuat.</p>
        <?php endif; ?>
    </div>
</body>
</html>
