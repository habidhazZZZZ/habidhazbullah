<?php
/**
 * File: api/debug_test.php
 * Deskripsi: Skrip khusus untuk mendiagnosis masalah koneksi dan data
 * antara database dan router MikroTik.
 */

// Tampilkan semua error untuk diagnosis
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Atur header agar outputnya rapi
header('Content-Type: text/plain; charset=utf-8');

session_start();

// Memuat file-file yang diperlukan
$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

use \RouterOS\Client;

echo "=========================================\n";
echo "=== SKRIP DIAGNOSIS CETAK KARTU ===\n";
echo "=========================================\n\n";

// 1. Validasi Input
$jurusan = $_GET['jurusan'] ?? null;
$kelas = $_GET['class'] ?? null;

if (!$jurusan || !$kelas) {
    die("GAGAL: Harap sertakan ?jurusan=NAMAJURUSAN&class=NAMAKELAS di URL.");
}

echo "Filter yang diuji: Jurusan = '$jurusan', Kelas = '$kelas'\n\n";

// 2. Tes Koneksi Database
echo "--- LANGKAH 1: TES KONEKSI & QUERY DATABASE ---\n";
if (!$koneksi) {
    die("GAGAL: Tidak bisa terhubung ke database. Periksa file config/db.php\n");
}
echo "Koneksi Database: BERHASIL.\n";

// 3. Tes Query JOIN
$query = "
    SELECT 
        hu.nama_lengkap, 
        hu.username,
        u.password AS db_password
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
    die("GAGAL: Gagal menyiapkan query database. Error: " . mysqli_error($koneksi) . "\n");
}
echo "Persiapan Query JOIN: BERHASIL.\n";

mysqli_stmt_bind_param($stmt, "ss", $jurusan, $kelas);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dbUsers = mysqli_fetch_all($result, MYSQLI_ASSOC);

$jumlahUserDB = count($dbUsers);
echo "Jumlah pengguna ditemukan di Database untuk filter ini: $jumlahUserDB\n";

if ($jumlahUserDB > 0) {
    echo "Daftar Username dari Database:\n";
    foreach ($dbUsers as $user) {
        echo "- " . $user['username'] . "\n";
    }
}
echo "\n";

// 4. Tes Koneksi MikroTik
echo "--- LANGKAH 2: TES KONEKSI MIKROTIK ---\n";
try {
    if (!isset($_SESSION['active_device_id']) || !isset($_SESSION['user_id'])) {
        throw new \Exception('Sesi tidak valid. Harap login dulu ke dashboard.');
    }
    
    $deviceId = $_SESSION['active_device_id'];
    $userId = $_SESSION['user_id'];
    
    $deviceQuery = "SELECT host, api_user, api_pass, api_port FROM devices WHERE id = ? AND user_id = ?";
    $deviceStmt = mysqli_prepare($koneksi, $deviceQuery);
    mysqli_stmt_bind_param($deviceStmt, "ii", $deviceId, $userId);
    mysqli_stmt_execute($deviceStmt);
    $deviceResult = mysqli_stmt_get_result($deviceStmt);
    $device = mysqli_fetch_assoc($deviceResult);
    
    if (!$device) {
        throw new \Exception('Perangkat aktif tidak ditemukan di database.');
    }

    echo "Mencoba terhubung ke router: {$device['host']}:{$device['api_port']}...\n";
    
    $client = new Client([
        'host' => $device['host'],
        'user' => $device['api_user'],
        'pass' => $device['api_pass'],
        'port' => (int)$device['api_port'],
    ]);
    
    echo "Koneksi MikroTik: BERHASIL.\n";

    // 5. Ambil data dari MikroTik
    $mikrotikUsersRaw = $client->query('/ip/hotspot/user/print')->read();
    $jumlahUserMikroTik = count($mikrotikUsersRaw);
    echo "Jumlah total pengguna yang ditemukan di MikroTik: $jumlahUserMikroTik\n\n";

    // 6. Analisis Hasil
    echo "--- LANGKAH 3: ANALISIS & KESIMPULAN ---\n";
    if ($jumlahUserDB == 0) {
        echo "KESIMPULAN: Masalah ada di database. Tidak ada pengguna yang cocok dengan filter Jurusan '$jurusan' dan Kelas '$kelas'. Pastikan data di tabel 'hotspot_users' sudah benar.\n";
    } elseif ($jumlahUserMikroTik == 0) {
        echo "KESIMPULAN: Masalah ada di MikroTik. Tidak ada satupun pengguna hotspot yang terdaftar di router.\n";
    } else {
        // Cek kecocokan
        $mikrotikUsernames = [];
        foreach ($mikrotikUsersRaw as $user) {
            if (isset($user['name'])) {
                $mikrotikUsernames[] = trim(strtolower($user['name']));
            }
        }

        $cocok = 0;
        echo "Mencocokkan pengguna dari Database dengan pengguna di MikroTik...\n";
        foreach ($dbUsers as $dbUser) {
            if (in_array(trim(strtolower($dbUser['username'])), $mikrotikUsernames)) {
                echo "- Pengguna '{$dbUser['username']}' DITEMUKAN di MikroTik.\n";
                $cocok++;
            } else {
                echo "- Pengguna '{$dbUser['username']}' TIDAK DITEMUKAN di MikroTik.\n";
            }
        }

        echo "\nTotal Pengguna yang Cocok: $cocok\n";
        if ($cocok == 0) {
            echo "KESIMPULAN AKHIR: Data pengguna ada di database, tetapi tidak ada satupun dari mereka yang terdaftar di router MikroTik. Inilah penyebab preview kosong. Pastikan username di database sama persis dengan di MikroTik.\n";
        } else {
            echo "KESIMPULAN AKHIR: Seharusnya ada $cocok pengguna yang tampil. Jika preview masih kosong, kemungkinan ada masalah lain di file 'cetak_kartu.php' (JavaScript).\n";
        }
    }

} catch (Throwable $e) {
    echo "GAGAL: Terjadi error saat proses koneksi MikroTik.\n";
    echo "Pesan Error: " . $e->getMessage() . "\n";
    echo "KESIMPULAN: Masalah ada pada koneksi ke router. Periksa detail perangkat (IP, user, pass, port API) di halaman dashboard Anda.\n";
}
