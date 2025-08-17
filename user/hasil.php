<?php
// Memulai session karena mungkin diakses oleh admin yang sudah login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

$peserta_id = null;
$error = '';

// --- Otentikasi & Otorisasi ---
// Cek jika diakses oleh peserta dengan token
$token = $_GET['token'] ?? null;
if ($token) {
    $sql = "SELECT id FROM peserta WHERE token = ? AND status_tes = 'Selesai'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $peserta_id = $row['id'];
    } else {
        $error = "Token tidak valid atau tes belum diselesaikan.";
    }
    $stmt->close();
} else {
    // Cek jika diakses oleh admin yang sudah login
    $id_from_admin = $_GET['id'] ?? null;
    if ($id_from_admin && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $peserta_id = $id_from_admin;
    } else {
        $error = "Akses tidak sah. Silakan login sebagai admin atau gunakan link tes yang valid.";
    }
}

// --- Pengambilan Data ---
$peserta = null;
$hasil_tes = null;
$deskripsi_aspek = [];

if (!$error && $peserta_id) {
    // Ambil data peserta
    $stmt_p = $conn->prepare("SELECT nama_lengkap, email FROM peserta WHERE id = ?");
    $stmt_p->bind_param("i", $peserta_id);
    $stmt_p->execute();
    $result_p = $stmt_p->get_result();
    $peserta = $result_p->fetch_assoc();
    $stmt_p->close();

    // Ambil hasil tes
    $stmt_h = $conn->prepare("SELECT * FROM hasil_tes WHERE peserta_id = ?");
    $stmt_h->bind_param("i", $peserta_id);
    $stmt_h->execute();
    $result_h = $stmt_h->get_result();
    $hasil_tes = $result_h->fetch_assoc();
    $stmt_h->close();

    // Ambil deskripsi aspek
    $result_d = $conn->query("SELECT aspek, nama_aspek, deskripsi FROM deskripsi_aspek");
    while($row = $result_d->fetch_assoc()){
        $deskripsi_aspek[$row['aspek']] = $row;
    }

    if (!$peserta || !$hasil_tes) {
        $error = "Data hasil tes untuk peserta ini tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tes Papikostik</title>
    <link rel="stylesheet" href="../assets/css/user_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .result-header { text-align: center; margin-bottom: 30px; }
        .chart-container { max-width: 700px; margin: 0 auto 40px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; font-weight: 600; }
        .score-badge {
            background-color: #007bff; color: white; padding: 5px 10px;
            border-radius: 12px; font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="error-box"><h2>Error</h2><p><?php echo htmlspecialchars($error); ?></p></div>
        <?php elseif ($peserta && $hasil_tes): ?>
            <div class="result-header">
                <h1>Hasil Tes Papikostik</h1>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($peserta['nama_lengkap']); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($peserta['email']); ?></p>
            </div>

            <div class="chart-container">
                <canvas id="papiKostickChart"></canvas>
            </div>

            <h2>Detail Skor dan Interpretasi Aspek</h2>
            <table>
                <thead>
                    <tr>
                        <th>Aspek</th>
                        <th>Nama Aspek</th>
                        <th>Skor</th>
                        <th>Deskripsi Umum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $aspek_labels = ['G','L','I','T','V','S','R','D','C','E','N','A','P','X','B','O','K','F','W','Z'];
                    foreach ($aspek_labels as $aspek):
                        $skor = $hasil_tes['skor_' . strtolower($aspek)];
                        $deskripsi_info = $deskripsi_aspek[$aspek] ?? ['nama_aspek' => 'N/A', 'deskripsi' => 'N/A'];
                    ?>
                    <tr>
                        <td><strong><?php echo $aspek; ?></strong></td>
                        <td><?php echo htmlspecialchars($deskripsi_info['nama_aspek']); ?></td>
                        <td><span class="score-badge"><?php echo $skor; ?></span></td>
                        <td><?php echo htmlspecialchars($deskripsi_info['deskripsi']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="info-box"><p>Memuat data hasil tes...</p></div>
        <?php endif; ?>
    </div>

<script>
<?php if ($hasil_tes): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('papiKostickChart').getContext('2d');

    const labels = [
        'G (Pekerja Keras)', 'L (Pemimpin)', 'I (Pengambil Keputusan)', 'T (Tipe Sibuk)',
        'V (Bersemangat)', 'S (Peran Sosial)', 'R (Teoritis)', 'D (Detail)',
        'C (Terorganisir)', 'E (Emosi Terkendali)', 'N (Menyelesaikan Tugas)', 'A (Prestasi)',
        'P (Mengatur Orang Lain)', 'X (Ingin Dikenali)', 'B (Bagian dari Kelompok)', 'O (Butuh Kasih Sayang)',
        'K (Agresif)', 'F (Butuh Dukungan Atasan)', 'W (Butuh Aturan)', 'Z (Hubungan Erat)'
    ];

    const data = {
        labels: labels,
        datasets: [{
            label: 'Skor Papikostik',
            data: [
                <?php echo $hasil_tes['skor_g']; ?>, <?php echo $hasil_tes['skor_l']; ?>,
                <?php echo $hasil_tes['skor_i']; ?>, <?php echo $hasil_tes['skor_t']; ?>,
                <?php echo $hasil_tes['skor_v']; ?>, <?php echo $hasil_tes['skor_s']; ?>,
                <?php echo $hasil_tes['skor_r']; ?>, <?php echo $hasil_tes['skor_d']; ?>,
                <?php echo $hasil_tes['skor_c']; ?>, <?php echo $hasil_tes['skor_e']; ?>,
                <?php echo $hasil_tes['skor_n']; ?>, <?php echo $hasil_tes['skor_a']; ?>,
                <?php echo $hasil_tes['skor_p']; ?>, <?php echo $hasil_tes['skor_x']; ?>,
                <?php echo $hasil_tes['skor_b']; ?>, <?php echo $hasil_tes['skor_o']; ?>,
                <?php echo $hasil_tes['skor_k']; ?>, <?php echo $hasil_tes['skor_f']; ?>,
                <?php echo $hasil_tes['skor_w']; ?>, <?php echo $hasil_tes['skor_z']; ?>
            ],
            fill: true,
            backgroundColor: 'rgba(0, 123, 255, 0.2)',
            borderColor: 'rgba(0, 123, 255, 1)',
            pointBackgroundColor: 'rgba(0, 123, 255, 1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(0, 123, 255, 1)'
        }]
    };

    const config = {
        type: 'radar',
        data: data,
        options: {
            elements: {
                line: {
                    borderWidth: 3
                }
            },
            scales: {
                r: {
                    angleLines: {
                        display: false
                    },
                    suggestedMin: 0,
                    suggestedMax: 9
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        },
    };

    new Chart(ctx, config);
});
<?php endif; ?>
</script>
</body>
</html>
