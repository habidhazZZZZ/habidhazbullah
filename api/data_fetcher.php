<?php
// File: api/data_fetcher.php

// =================================================================
// == DIAGNOSTIK KRITIS ==
// =================================================================
$project_root = dirname(__DIR__);
$autoload_path = $project_root . '/vendor/autoload.php';

if (!file_exists($autoload_path)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'CRITICAL ERROR: File autoload.php tidak ditemukan. Pastikan Anda sudah menjalankan "composer install".',
        'expected_path' => $autoload_path
    ]);
    exit();
}
require_once $autoload_path;
// =================================================================

// Impor Class yang dibutuhkan
use \RouterOS\Client;
use \RouterOS\Query;

session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if (!isset($_SESSION['active_device_id'])) {
        throw new Exception('Sesi perangkat aktif tidak ditemukan. Silakan pilih perangkat dari halaman setup terlebih dahulu.');
    }

    require_once $project_root . '/public/config/db.php';
    
    $device_id = $_SESSION['active_device_id'];
    $user_id = $_SESSION['user_id'];

    $query_device = "SELECT host, api_user, api_pass, api_port FROM devices WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query_device);
    if (!$stmt) throw new Exception("Gagal menyiapkan query database: " . mysqli_error($koneksi));
    
    mysqli_stmt_bind_param($stmt, "ii", $device_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $device_config = mysqli_fetch_assoc($result);

    if (!$device_config) {
        throw new Exception('Perangkat tidak ditemukan atau Anda tidak memiliki akses.');
    }
    
    $config = [
        'host' => $device_config['host'],
        'user' => $device_config['api_user'],
        'pass' => $device_config['api_pass'],
        'port' => (int)$device_config['api_port'],
    ];

    $client = new Client($config);

    // Ambil semua data dalam satu koneksi
    $resource = $client->query('/system/resource/print')->read()[0] ?? [];
    $interfaces = $client->query('/interface/print')->read();
    $identity = $client->query('/system/identity/print')->read()[0] ?? [];
    $routerboard_data = $client->query('/system/routerboard/print')->read()[0] ?? [];
    $clock = $client->query('/system/clock/print')->read()[0] ?? [];
    $hotspot_active_count = count($client->query('/ip/hotspot/active/print')->read());
    
    // --- PENAMBAHAN BARU: Ambil data Simple Queues ---
    $queues = $client->query('/queue/simple/print')->read();

    // Olah data port
    $ports = [];
    foreach ($interfaces as $int) {
        $monitor = $client->query((new Query('/interface/monitor-traffic'))->equal('interface', $int['name'])->equal('once', ''))->read()[0] ?? [];
        $ports[] = [
            'name' => $int['name'],
            'type' => $int['type'],
            'status' => $int['running'] ?? 'false',
            'tx_mbps' => round(floatval($monitor['tx-bits-per-second'] ?? 0) / 1024 / 1024, 2),
            'rx_mbps' => round(floatval($monitor['rx-bits-per-second'] ?? 0) / 1024 / 1024, 2)
        ];
    }
    
    $ram_total_bytes = floatval($resource['total-memory'] ?? 0);
    $ram_free_bytes = floatval($resource['free-memory'] ?? 0);
    $ram_used_bytes = $ram_total_bytes - $ram_free_bytes;
    
    $system = [
        'identity' => $identity['name'] ?? 'MikroTik',
        'version' => $resource['version'] ?? '-',
        'uptime' => $resource['uptime'] ?? '-',
        'cpu_model' => $resource['board-name'] ?? 'MikroTik',
        'cpu_frequency' => $resource['cpu-frequency'] ?? '-',
        'cpu_load_percent' => floatval($resource['cpu-load'] ?? 0),
        'ram_total' => round($ram_total_bytes / 1024 / 1024),
        'ram_used' => round($ram_used_bytes / 1024 / 1024),
        'ram_percent' => ($ram_total_bytes > 0) ? round(($ram_used_bytes / $ram_total_bytes) * 100, 1) : 0,
        'arch' => $resource['architecture-name'] ?? '-',
        'serial_number' => $routerboard_data['serial-number'] ?? '-',
        'router_time' => ($clock['time'] ?? '') . ' ' . ($clock['date'] ?? ''),
    ];

    // Kirim respons sukses dalam format JSON
    echo json_encode([
        'status' => 'success',
        'ports' => $ports,
        'system' => $system,
        'hotspot' => ['active_users' => $hotspot_active_count],
        'queues' => $queues, // <-- PENAMBAHAN BARU
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
