<?php
require_once '../config/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$peserta = null;

if (empty($token)) {
    $error = "Token tidak ditemukan. Pastikan Anda menggunakan link yang benar.";
} else {
    // Cari peserta berdasarkan token
    $sql = "SELECT id, nama_lengkap, email, status_tes FROM peserta WHERE token = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $peserta = $result->fetch_assoc();
            // Cek apakah tes sudah selesai
            if ($peserta['status_tes'] === 'Selesai') {
                $error = "Anda sudah menyelesaikan tes ini. Anda tidak dapat mengerjakannya kembali.";
                // Opsi: Arahkan ke halaman hasil jika sudah selesai
                // header("Location: hasil.php?token=" . $token);
                // exit;
            }
        } else {
            $error = "Token tidak valid. Pastikan Anda menggunakan link yang benar.";
        }
        $stmt->close();
    } else {
        $error = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Tes Papikostik</title>
    <link rel="stylesheet" href="../assets/css/user_style.css">
</head>
<body>
    <div class="container">
        <h1>Tes Kepribadian Papikostik</h1>

        <?php if ($error): ?>
            <div class="error-box">
                <h2>Akses Ditolak</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php elseif ($peserta): ?>
            <div class="info-box">
                <p>Selamat datang, <strong><?php echo htmlspecialchars($peserta['nama_lengkap']); ?></strong>!</p>
            </div>

            <h2>Petunjuk Pengerjaan</h2>
            <p>Tes ini terdiri dari 90 pasang pernyataan singkat yang berhubungan dengan situasi kerja atau minat pribadi. Tugas Anda adalah memilih salah satu dari dua pernyataan yang paling menggambarkan diri Anda.</p>
            <ul>
                <li>Pilih pernyataan (A atau B) yang paling sesuai dengan diri Anda, bukan yang Anda anggap paling ideal.</li>
                <li>Tidak ada jawaban yang benar atau salah. Kejujuran Anda dalam menjawab akan menghasilkan gambaran kepribadian yang akurat.</li>
                <li>Bekerjalah dengan cepat dan jangan terlalu lama memikirkan satu soal. Kesan pertama biasanya adalah yang paling tepat.</li>
                <li>Pastikan Anda menjawab semua 90 soal.</li>
            </ul>
            <p>Tes ini tidak memiliki batasan waktu, namun usahakan untuk menyelesaikannya dalam satu sesi agar hasilnya konsisten.</p>

            <br>
            <a href="../ujian/<?php echo htmlspecialchars($token); ?>" class="btn">Mulai Mengerjakan Tes</a>
        <?php endif; ?>
    </div>
</body>
</html>
