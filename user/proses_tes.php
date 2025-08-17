<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Tolak akses jika bukan metode POST
    header("Location: mulai_tes.php");
    exit;
}

// 1. Ambil data dari form
$peserta_id = filter_input(INPUT_POST, 'peserta_id', FILTER_VALIDATE_INT);
$token = $_POST['token'] ?? '';
$jawaban_array = $_POST['jawaban'] ?? [];

// 2. Validasi data
if (!$peserta_id || empty($token) || count($jawaban_array) < 90) {
    // Seharusnya validasi di sisi client sudah mencegah ini, tapi sebagai pengaman
    die("Data tidak lengkap. Harap jawab semua soal.");
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // 3. Ambil kunci jawaban (aspek) dari semua soal
    $soal_aspek_map = [];
    $sql_soal = "SELECT id, aspek_a, aspek_b FROM soal_papikostik";
    $result_soal = $conn->query($sql_soal);
    while ($row = $result_soal->fetch_assoc()) {
        $soal_aspek_map[$row['id']] = [
            'A' => $row['aspek_a'],
            'B' => $row['aspek_b']
        ];
    }

    // 4. Inisialisasi skor
    $skor = array_fill_keys(['G','L','I','T','V','S','R','D','C','E','N','A','P','X','B','O','K','F','W','Z'], 0);

    // 5. Simpan jawaban mentah dan hitung skor
    $sql_insert_jawaban = "INSERT INTO jawaban (peserta_id, soal_id, jawaban_dipilih, aspek_terpilih) VALUES (?, ?, ?, ?)";
    $stmt_jawaban = $conn->prepare($sql_insert_jawaban);

    foreach ($jawaban_array as $soal_id => $jawaban_dipilih) {
        $aspek_terpilih = $soal_aspek_map[$soal_id][$jawaban_dipilih];

        // Simpan jawaban individu
        $stmt_jawaban->bind_param("iiss", $peserta_id, $soal_id, $jawaban_dipilih, $aspek_terpilih);
        $stmt_jawaban->execute();

        // Tambah skor untuk aspek yang terpilih
        if (isset($skor[$aspek_terpilih])) {
            $skor[$aspek_terpilih]++;
        }
    }
    $stmt_jawaban->close();

    // 6. Simpan skor akhir ke tabel hasil_tes
    $sql_insert_hasil = "INSERT INTO hasil_tes (peserta_id, skor_g, skor_l, skor_i, skor_t, skor_v, skor_s, skor_r, skor_d, skor_c, skor_e, skor_n, skor_a, skor_p, skor_x, skor_b, skor_o, skor_k, skor_f, skor_w, skor_z) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_hasil = $conn->prepare($sql_insert_hasil);
    $stmt_hasil->bind_param("iiiiiiiiiiiiiiiiiiiii",
        $peserta_id, $skor['G'], $skor['L'], $skor['I'], $skor['T'], $skor['V'], $skor['S'], $skor['R'], $skor['D'],
        $skor['C'], $skor['E'], $skor['N'], $skor['A'], $skor['P'], $skor['X'], $skor['B'], $skor['O'], $skor['K'],
        $skor['F'], $skor['W'], $skor['Z']
    );
    $stmt_hasil->execute();
    $stmt_hasil->close();

    // 7. Update status peserta menjadi 'Selesai'
    $sql_update_peserta = "UPDATE peserta SET status_tes = 'Selesai' WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update_peserta);
    $stmt_update->bind_param("i", $peserta_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Jika semua query berhasil, commit transaksi
    $conn->commit();

    // 8. Arahkan ke halaman hasil dengan URL cantik
    $base_url = "http://" . $_SERVER['HTTP_HOST'] . rtrim(str_replace('/user', '', dirname($_SERVER['PHP_SELF'])), '/\\');
    $redirect_url = $base_url . "/hasil/" . $token;
    header("Location: " . $redirect_url);
    exit;

} catch (mysqli_sql_exception $exception) {
    // Jika ada error, batalkan semua perubahan (rollback)
    $conn->rollback();

    // Tampilkan pesan error atau log error
    // Sebaiknya jangan tampilkan detail error ke pengguna
    error_log("Gagal memproses tes: " . $exception->getMessage());
    die("Terjadi kesalahan saat memproses jawaban Anda. Silakan hubungi administrator.");
} finally {
    $conn->close();
}
?>
