<?php
require_once 'auth_check.php';
require_once '../config/config.php';

$deskripsi_id = $_GET['id'] ?? null;
if (!$deskripsi_id) {
    header("Location: kelola_konten.php?view=deskripsi");
    exit;
}

$deskripsi_item = null;
$error = '';

// Logika untuk memproses form saat disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_aspek = trim($_POST['nama_aspek']);
    $deskripsi_text = trim($_POST['deskripsi']);

    if (empty($nama_aspek) || empty($deskripsi_text)) {
        $error = "Semua field harus diisi.";
    } else {
        $sql = "UPDATE deskripsi_aspek SET nama_aspek = ?, deskripsi = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $nama_aspek, $deskripsi_text, $deskripsi_id);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Deskripsi aspek berhasil diperbarui!";
                header("Location: kelola_konten.php?view=deskripsi");
                exit;
            } else {
                $error = "Gagal memperbarui deskripsi. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }
}

// Logika untuk mengambil data deskripsi saat halaman dimuat (GET)
$sql_get = "SELECT aspek, nama_aspek, deskripsi FROM deskripsi_aspek WHERE id = ?";
if ($stmt_get = $conn->prepare($sql_get)) {
    $stmt_get->bind_param("i", $deskripsi_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result->num_rows === 1) {
        $deskripsi_item = $result->fetch_assoc();
    } else {
        $error = "Deskripsi aspek tidak ditemukan.";
    }
    $stmt_get->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Deskripsi Aspek - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        body { display: block; height: auto; }
        .form-container { max-width: 800px; margin: 20px auto; }
        .error { padding: 15px; border-radius: 5px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; }
        textarea { width: 100%; min-height: 120px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="dashboard" style="text-decoration:none; color:white;">Admin Dashboard</a></h1>
        <a href="logout">Logout</a>
    </header>

    <div class="admin-container form-container">
        <h2>Edit Deskripsi Aspek</h2>
        <a href="kelola_konten?view=deskripsi">&larr; Kembali ke Daftar Deskripsi</a>

        <?php if ($error): ?>
            <div class="error" style="margin-top: 20px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($deskripsi_item): ?>
            <form action="edit_deskripsi_form?id=<?php echo $deskripsi_id; ?>" method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Aspek</label>
                    <input type="text" value="<?php echo htmlspecialchars($deskripsi_item['aspek']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="nama_aspek">Nama Aspek</label>
                    <input type="text" id="nama_aspek" name="nama_aspek" value="<?php echo htmlspecialchars($deskripsi_item['nama_aspek']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($deskripsi_item['deskripsi']); ?></textarea>
                </div>

                <button type="submit" class="btn">Simpan Perubahan</button>
            </form>
        <?php else: ?>
            <p style="margin-top: 20px;">Data deskripsi tidak dapat dimuat.</p>
        <?php endif; ?>
    </div>
</body>
</html>
