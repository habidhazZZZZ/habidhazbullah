<?php
// File: api/check_env.php
// Tujuan: Mendiagnosis masalah fundamental pada lingkungan server.

header('Content-Type: application/json');

$checks = [];
$errors = [];

// 1. Cek versi PHP
$checks['php_version'] = PHP_VERSION;
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = "Versi PHP Anda (" . PHP_VERSION . ") sudah sangat tua. Harap upgrade ke versi 7.4 atau lebih baru.";
}

// 2. Cek path root proyek
$project_root = dirname(__DIR__);
$checks['project_root_path'] = $project_root;

// 3. Cek keberadaan file autoloader Composer
$autoload_path = $project_root . '/vendor/autoload.php';
$checks['autoload_path'] = $autoload_path;
if (file_exists($autoload_path)) {
    $checks['autoload_status'] = 'Ditemukan';
} else {
    $checks['autoload_status'] = 'TIDAK DITEMUKAN';
    $errors[] = 'File vendor/autoload.php tidak ditemukan. Pastikan Anda sudah menjalankan "composer install" di direktori proyek yang benar.';
}

// 4. Cek keberadaan file konfigurasi koneksi
$config_path = $project_root . '/config/connection.json';
$checks['config_path'] = $config_path;
if (file_exists($config_path)) {
    $checks['config_status'] = 'Ditemukan';
    // Coba baca isinya
    $config_content = file_get_contents($config_path);
    json_decode($config_content);
    if (json_last_error() === JSON_ERROR_NONE) {
        $checks['config_json_valid'] = 'Ya';
    } else {
        $checks['config_json_valid'] = 'Tidak (Format JSON Rusak)';
        $errors[] = 'File config/connection.json ditemukan, tetapi format JSON di dalamnya tidak valid.';
    }
} else {
    $checks['config_status'] = 'TIDAK DITEMUKAN';
    $errors[] = 'File config/connection.json tidak ditemukan. Pastikan Anda sudah menjalankan setup awal.';
}


// Siapkan respons
$response = [
    'status' => empty($errors) ? 'success' : 'error',
    'message' => empty($errors) ? 'Lingkungan server terlihat baik.' : 'Ditemukan masalah pada lingkungan server.',
    'diagnostics' => $checks,
    'errors' => $errors
];

// Set kode status HTTP berdasarkan hasil
http_response_code(empty($errors) ? 200 : 500);

echo json_encode($response, JSON_PRETTY_PRINT);

?>
