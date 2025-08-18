<?php
// File: api/process_setup.php

// Tampilkan error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Menentukan path ke root proyek secara andal
$project_root = dirname(__DIR__);

// Memuat autoloader dari Composer
$autoload_file = $project_root . '/vendor/autoload.php';
if (!file_exists($autoload_file)) {
    echo json_encode(['status' => 'error', 'message' => 'Dependensi Composer belum di-install. Jalankan `composer install` di terminal.']);
    exit;
}
require_once $autoload_file;

use \RouterOS\Client;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$config = [
    'host' => $_POST['host'] ?? '',
    'user' => $_POST['user'] ?? '',
    'pass' => $_POST['pass'] ?? '',
    'port' => (int)($_POST['port'] ?? 8728),
];

if (empty($config['host']) || empty($config['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'IP Address dan Username wajib diisi.']);
    exit;
}

try {
    $client = new Client($config);
    $client->query('/system/resource/print')->read();

    $configDir = $project_root . '/config';
    if (!is_dir($configDir)) {
        mkdir($configDir, 0755, true);
    }
    
    $configFile = $configDir . '/connection.json';
    if (file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT))) {
        echo json_encode(['status' => 'success', 'message' => 'Koneksi berhasil! Konfigurasi disimpan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Koneksi berhasil, tetapi gagal menyimpan file konfigurasi. Periksa izin folder `config`.']);
    }

} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi Gagal: ' . $e->getMessage()]);
}
?>
