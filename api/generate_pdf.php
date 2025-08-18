<?php
/**
 * File: api/generate_pdf.php (Versi Final - Diperbaiki dengan JOIN yang Benar)
 * Deskripsi: Skrip untuk menghasilkan PDF kartu login.
 * Versi ini memperbaiki query JOIN untuk mencocokkan struktur database yang benar.
 */

// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Keamanan dasar: Pastikan pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login') {
    http_response_code(403);
    die('Akses ditolak. Anda harus login terlebih dahulu.');
}

// Memuat file-file yang diperlukan
$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php'; // Untuk TCPDF
require_once $project_root . '/public/config/db.php';   // Untuk koneksi database $koneksi

// --- Logika Utama ---
try {
    // 1. Validasi Input dari GET request
    $jurusan = $_GET['jurusan'] ?? '';
    $kelas = $_GET['class'] ?? '';

    if (empty($jurusan) || empty($kelas)) {
        header("Content-Type: text/plain");
        http_response_code(400);
        die("Error: Parameter 'jurusan' dan 'class' wajib diisi.");
    }
    
    // 2. Ambil Data Pengguna Menggunakan JOIN yang Sudah Diperbaiki
    // --- PERBAIKAN UTAMA DI SINI ---
    // Mengubah kondisi JOIN dari 'u.name' menjadi 'u.username'
    $query = "
        SELECT 
            hu.nama_lengkap, 
            hu.username AS name, 
            u.password 
        FROM 
            hotspot_users AS hu
        JOIN 
            users AS u ON hu.username = u.username
        WHERE 
            hu.jurusan = ? AND hu.kelas = ? 
        ORDER BY 
            hu.nama_lengkap ASC";

    $stmt = mysqli_prepare($koneksi, $query);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan query database: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "ss", $jurusan, $kelas);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (empty($users)) {
        header("Content-Type: text/plain");
        http_response_code(404);
        die("Tidak ada pengguna yang ditemukan untuk Jurusan: " . htmlspecialchars($jurusan) . " dan Kelas: " . htmlspecialchars($kelas));
    }

    // 3. Konfigurasi dan Inisialisasi PDF (TCPDF)
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); // Perbaikan: Menambahkan backslash
    $cardWidth = 86;
    $cardHeight = 54;
    $marginX = 10;
    $marginY = 10;
    $gapX = (210 - (2 * $cardWidth) - (2 * $marginX));
    $gapY = 5;
    
    $pdf->SetCreator('Aplikasi Hotspot Manager');
    $pdf->SetAuthor('Admin Sekolah');
    $pdf->SetTitle('Kartu Hotspot - ' . $jurusan . ' - ' . $kelas);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins($marginX, $marginY, $marginX);
    $pdf->SetAutoPageBreak(true, $marginY);
    $pdf->AddPage();

    $col = 0; $row = 0; $userCount = count($users);

    // 4. Loop Melalui Pengguna dan Gambar Setiap Kartu
    foreach ($users as $index => $user) {
        $x = $marginX + ($col * ($cardWidth + $gapX));
        $y = $marginY + ($row * ($cardHeight + $gapY));
        
        $pdf->SetLineStyle(['width' => 0.2, 'color' => [150, 150, 150]]);
        $pdf->RoundedRect($x, $y, $cardWidth, $cardHeight, 3.5, '1111', 'S');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($x + 5, $y + 5);
        $pdf->Cell($cardWidth - 10, 5, 'KARTU LOGIN INTERNET', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY($x + 5, $y + 12);
        $displayName = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : $user['name'];
        $pdf->Cell($cardWidth - 10, 5, $displayName, 0, 1, 'C');
        
        $pdf->Line($x + 5, $y + 19, $x + $cardWidth - 5, $y + 19);

        $textBlockX = $x + 5;
        $textBlockY = $y + 22;
        
        $pdf->SetFont('courier', 'B', 9);
        $pdf->SetXY($textBlockX, $textBlockY);
        $pdf->Cell(20, 5, 'Username:', 0, 1, 'L');
        
        $pdf->SetFont('courier', '', 9);
        $pdf->SetXY($textBlockX, $textBlockY + 4);
        $pdf->Cell(40, 5, $user['name'], 0, 1, 'L');

        $pdf->SetXY($textBlockX, $textBlockY + 10);
        $pdf->SetFont('courier', 'B', 9);
        $pdf->Cell(20, 5, 'Password:', 0, 1, 'L');
        
        $pdf->SetFont('courier', '', 9);
        $pdf->SetXY($textBlockX, $textBlockY + 14);
        $pdf->Cell(40, 5, $user['password'], 0, 1, 'L');

        $qrCodeSize = 27;
        $qrCodeX = $x + $cardWidth - $qrCodeSize - 5;
        $qrCodeY = $y + 21;
        
        $barcodeStyle = ['border' => false, 'padding' => 1, 'fgcolor' => [0,0,0], 'bgcolor' => [255,255,255]];
        $qrCodeData = json_encode(['user' => $user['name'], 'pass' => $user['password']]);
        $pdf->write2DBarcode($qrCodeData, 'QRCODE,M', $qrCodeX, $qrCodeY, $qrCodeSize, $qrCodeSize, $barcodeStyle, 'N');
        
        $pdf->SetFont('helvetica', 'I', 6);
        $pdf->SetXY($x + 5, $y + $cardHeight - 9);
        $pdf->MultiCell($cardWidth - 10, 6, "Gunakan username & password ini untuk login ke jaringan WiFi sekolah.", 0, 'C', 0, 1);

        $col++;
        if ($col >= 2) {
            $col = 0;
            $row++;
        }
        
        if ($row >= 5 && ($index + 1) < $userCount) {
            $pdf->AddPage();
            $row = 0;
            $col = 0;
        }
    }

    // 5. Output PDF ke Browser
    ob_end_clean();
    $pdf->Output('Kartu_Hotspot_' . str_replace(' ', '_', $kelas) . '.pdf', 'I');

} catch (Throwable $e) {
    header("Content-Type: text/plain");
    http_response_code(500);
    echo "Terjadi Kesalahan Server:\n\n";
    echo "Pesan: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Baris: " . $e->getLine() . "\n";
}
