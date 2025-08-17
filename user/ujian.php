<?php
require_once '../config/config.php';

$token = $_GET['token'] ?? '';
$peserta = null;
$soal_list = [];

if (empty($token)) {
    header("Location: mulai_tes.php"); // Arahkan jika tidak ada token
    exit;
}

// 1. Validasi token dan ambil data peserta
$sql_peserta = "SELECT id, nama_lengkap, status_tes FROM peserta WHERE token = ? LIMIT 1";
if ($stmt_peserta = $conn->prepare($sql_peserta)) {
    $stmt_peserta->bind_param("s", $token);
    $stmt_peserta->execute();
    $result_peserta = $stmt_peserta->get_result();
    if ($result_peserta->num_rows === 1) {
        $peserta = $result_peserta->fetch_assoc();
        if ($peserta['status_tes'] === 'Selesai') {
            // Jika sudah selesai, arahkan ke halaman hasil
            header("Location: hasil.php?token=" . $token);
            exit;
        }
    } else {
        header("Location: mulai_tes.php?error=invalid_token");
        exit;
    }
    $stmt_peserta->close();
}

// 2. Ambil semua soal dari database
$sql_soal = "SELECT id, nomor_soal, pernyataan_a, pernyataan_b FROM soal_papikostik ORDER BY nomor_soal ASC";
$result_soal = $conn->query($sql_soal);
if ($result_soal && $result_soal->num_rows > 0) {
    while ($row = $result_soal->fetch_assoc()) {
        $soal_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes Papikostik - Pengerjaan Soal</title>
    <link rel="stylesheet" href="../assets/css/user_style.css">
</head>
<body>
    <div class="container">
        <div class="test-header">
            <h1>Tes Papikostik</h1>
            <p>Pilih salah satu pernyataan yang paling menggambarkan diri Anda untuk setiap nomor.</p>

            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <div style="text-align: right;">
                <span id="progressText">0</span> dari 90 soal terjawab
            </div>
        </div>

        <?php if (empty($soal_list)): ?>
            <div class="error-box">
                <p>Gagal memuat soal. Silakan hubungi administrator.</p>
            </div>
        <?php else: ?>
            <form id="testForm" action="proses_tes.php" method="POST">
                <input type="hidden" name="peserta_id" value="<?php echo htmlspecialchars($peserta['id']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <?php foreach ($soal_list as $soal): ?>
                    <div class="question-pair">
                        <div class="question-number">Soal Nomor <?php echo htmlspecialchars($soal['nomor_soal']); ?></div>

                        <input type="radio" id="q<?php echo $soal['id']; ?>_a" name="jawaban[<?php echo $soal['id']; ?>]" value="A" required>
                        <label for="q<?php echo $soal['id']; ?>_a" class="choice-label">
                            <?php echo htmlspecialchars($soal['pernyataan_a']); ?>
                        </label>

                        <input type="radio" id="q<?php echo $soal['id']; ?>_b" name="jawaban[<?php echo $soal['id']; ?>]" value="B">
                        <label for="q<?php echo $soal['id']; ?>_b" class="choice-label">
                            <?php echo htmlspecialchars($soal['pernyataan_b']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 18px;">Selesai & Kirim Jawaban</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('testForm');
            const totalQuestions = <?php echo count($soal_list); ?>;
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            function updateProgress() {
                const answeredQuestions = form.querySelectorAll('input[type="radio"]:checked').length;
                const progressPercentage = (answeredQuestions / totalQuestions) * 100;

                progressBar.style.width = progressPercentage + '%';
                progressText.textContent = answeredQuestions;
            }

            // Update progress on every change
            form.addEventListener('change', updateProgress);

            // Validate before submitting
            form.addEventListener('submit', function(event) {
                const answeredQuestions = form.querySelectorAll('input[type="radio"]:checked').length;
                if (answeredQuestions < totalQuestions) {
                    event.preventDefault(); // Stop form submission
                    alert('Harap jawab semua ' + totalQuestions + ' soal sebelum mengirimkan jawaban Anda.');
                } else {
                    if (!confirm('Apakah Anda yakin ingin mengirimkan jawaban Anda? Anda tidak dapat mengubahnya lagi.')) {
                        event.preventDefault();
                    }
                }
            });

            // Initial progress check
            updateProgress();
        });
    </script>
</body>
</html>
