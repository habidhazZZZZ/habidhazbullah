<?php
require_once 'auth_check.php';
require_once '../config/config.php';

// Ambil semua data peserta yang sudah selesai tes
$peserta_selesai_list = [];
$sql = "SELECT p.id, p.nama_lengkap, p.email, h.tanggal_tes
        FROM peserta p
        JOIN hasil_tes h ON p.id = h.peserta_id
        WHERE p.status_tes = 'Selesai'
        ORDER BY h.tanggal_tes DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $peserta_selesai_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tes Peserta - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        body { display: block; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-btn {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover { background-color: #138496; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="dashboard.php" style="text-decoration:none; color:white;">Admin Dashboard</a></h1>
        <a href="logout.php">Logout</a>
    </header>

    <div class="admin-container">
        <h2>Hasil Tes Peserta</h2>
        <p>Berikut adalah daftar peserta yang telah menyelesaikan tes Papikostik.</p>

        <?php if (empty($peserta_selesai_list)): ?>
            <p>Belum ada peserta yang menyelesaikan tes.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Tanggal Tes</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta_selesai_list as $index => $peserta): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($peserta['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($peserta['email']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($peserta['tanggal_tes'])); ?></td>
                            <td>
                                <a href="../user/hasil.php?id=<?php echo $peserta['id']; ?>" class="action-btn" target="_blank">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
