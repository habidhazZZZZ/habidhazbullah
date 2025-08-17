<?php
require_once 'auth_check.php';
require_once '../config/config.php';

// Tentukan tampilan default adalah 'soal'
$view = $_GET['view'] ?? 'soal';

$soal_list = [];
$deskripsi_list = [];
$error_message = '';

if ($view === 'soal') {
    $sql = "SELECT id, nomor_soal, pernyataan_a, aspek_a, pernyataan_b, aspek_b FROM soal_papikostik ORDER BY nomor_soal ASC";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $soal_list[] = $row;
            }
        } else {
            $error_message = "Tabel soal masih kosong. Silakan impor file <code>config/seed_soal.sql</code> ke database Anda untuk mengisi 90 soal standar.";
        }
    }
} elseif ($view === 'deskripsi') {
    $sql = "SELECT id, aspek, nama_aspek, deskripsi FROM deskripsi_aspek ORDER BY aspek ASC";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $deskripsi_list[] = $row;
            }
        } else {
            $error_message = "Tabel deskripsi aspek masih kosong. Silakan impor file <code>config/seed_deskripsi.sql</code> ke database Anda untuk mengisi data awal.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Konten - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        body { display: block; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background-color: #f2f2f2; }
        .nav-tabs { border-bottom: 2px solid #dee2e6; margin-bottom: 20px; }
        .nav-tabs a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            color: #007bff;
            border: 1px solid transparent;
            margin-bottom: -1px;
        }
        .nav-tabs a.active {
            font-weight: bold;
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            border-top-left-radius: .25rem;
            border-top-right-radius: .25rem;
        }
        .error-note { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
        .action-btn {
            background-color: #ffc107; color: #212529; padding: 5px 10px; border-radius: 4px; text-decoration: none;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="dashboard" style="text-decoration:none; color:white;">Admin Dashboard</a></h1>
        <a href="logout">Logout</a>
    </header>

    <div class="admin-container">
        <h2>Kelola Konten Tes</h2>

        <div class="nav-tabs">
            <a href="kelola_konten?view=soal" class="<?php echo $view === 'soal' ? 'active' : ''; ?>">Kelola Soal</a>
            <a href="kelola_konten?view=deskripsi" class="<?php echo $view === 'deskripsi' ? 'active' : ''; ?>">Kelola Deskripsi Aspek</a>
        </div>

        <?php if ($error_message): ?>
            <div class="error-note"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($view === 'soal' && !empty($soal_list)): ?>
            <h3>Daftar Soal Papikostik (90 Soal)</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pernyataan A (Aspek)</th>
                        <th>Pernyataan B (Aspek)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($soal_list as $soal): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($soal['nomor_soal']); ?></td>
                            <td><?php echo htmlspecialchars($soal['pernyataan_a']) . ' <strong>(' . htmlspecialchars($soal['aspek_a']) . ')</strong>'; ?></td>
                            <td><?php echo htmlspecialchars($soal['pernyataan_b']) . ' <strong>(' . htmlspecialchars($soal['aspek_b']) . ')</strong>'; ?></td>
                            <td><a href="edit_soal_form?id=<?php echo $soal['id']; ?>" class="action-btn">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($view === 'deskripsi' && !empty($deskripsi_list)): ?>
            <h3>Daftar Deskripsi Aspek (20 Aspek)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Aspek</th>
                        <th>Nama Aspek</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deskripsi_list as $deskripsi): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($deskripsi['aspek']); ?></strong></td>
                            <td><?php echo htmlspecialchars($deskripsi['nama_aspek']); ?></td>
                            <td width="60%"><?php echo htmlspecialchars($deskripsi['deskripsi']); ?></td>
                            <td><a href="edit_deskripsi_form?id=<?php echo $deskripsi['id']; ?>" class="action-btn">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
