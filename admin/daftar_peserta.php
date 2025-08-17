<?php
require_once 'auth_check.php';
require_once '../config/config.php';

// Ambil semua data peserta dari database
$peserta_list = [];
$sql = "SELECT id, nama_lengkap, email, status_tes, created_at FROM peserta ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $peserta_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
        body { display: block; height: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .status-selesai {
            color: green;
            font-weight: bold;
        }
        .status-belum {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="dashboard" style="text-decoration:none; color:white;">Admin Dashboard</a></h1>
        <a href="logout">Logout</a>
    </header>

    <div class="admin-container">
        <h2>Daftar Peserta</h2>
        <p>Berikut adalah daftar semua peserta yang terdaftar di sistem.</p>

        <?php if (empty($peserta_list)): ?>
            <p>Belum ada peserta yang ditambahkan. Silakan <a href="tambah_peserta">tambah peserta baru</a>.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Status Tes</th>
                        <th>Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta_list as $index => $peserta): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($peserta['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($peserta['email']); ?></td>
                            <td>
                                <?php
                                    $status_class = $peserta['status_tes'] == 'Selesai' ? 'status-selesai' : 'status-belum';
                                    echo '<span class="' . $status_class . '">' . htmlspecialchars($peserta['status_tes']) . '</span>';
                                ?>
                            </td>
                            <td><?php echo date('d M Y, H:i', strtotime($peserta['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <br>
        <a href="tambah_peserta" class="btn" style="display: inline-block; width: auto;">Tambah Peserta Baru</a>
    </div>
</body>
</html>
