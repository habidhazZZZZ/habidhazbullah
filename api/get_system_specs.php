<?php
// File: api/get_system_specs.php
// Deskripsi: API khusus untuk halaman Selamat Datang.

session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);

$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

use \RouterOS\Client;
use \RouterOS\Query;

try {
    if (!isset($_SESSION['active_device_id'])) {
        throw new Exception('Sesi perangkat aktif tidak ditemukan.');
    }

    // Fungsi untuk mendapatkan koneksi ke router
    function getMikrotikClient($koneksi) {
        $deviceId = $_SESSION['active_device_id'];
        $userId = $_SESSION['user_id'];
        $query = "SELECT * FROM devices WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);
        mysqli_stmt_execute($stmt);
        $device = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$device) throw new \Exception('Perangkat tidak ditemukan.');
        return new Client(['host' => $device['host'], 'user' => $device['api_user'], 'pass' => $device['api_pass'], 'port' => (int)$device['api_port']]);
    }

    $client = getMikrotikClient($koneksi);

    // Ambil data yang dibutuhkan
    $resource = $client->query('/system/resource/print')->read()[0] ?? [];
    $routerboard = $client->query('/system/routerboard/print')->read()[0] ?? [];
    $clock = $client->query('/system/clock/print')->read()[0] ?? [];
    $interfaces = $client->query('/interface/print')->read();

    // Olah data sistem
    $system = [
        'cpu_model' => $resource['board-name'] ?? '-',
        'version' => $resource['version'] ?? '-',
        'serial_number' => $routerboard['serial-number'] ?? '-',
        'arch' => $resource['architecture-name'] ?? '-',
        'cpu_frequency' => $resource['cpu-frequency'] ?? '0',
        'uptime' => $resource['uptime'] ?? '-',
        'router_time' => ($clock['time'] ?? '') . ' ' . ($clock['date'] ?? ''),
        'ram_total' => round((floatval($resource['total-memory'] ?? 0)) / 1024 / 1024),
    ];

    // Olah data port
    $ports = [];
    foreach ($interfaces as $int) {
        $monitor = $client->query((new Query('/interface/monitor-traffic'))->equal('interface', $int['name'])->equal('once', ''))->read()[0] ?? [];
        $ip_addr_query = (new Query('/ip/address/print'))->where('interface', $int['name']);
        $ip_addrs = $client->query($ip_addr_query)->read();
        $ip_address = !empty($ip_addrs) ? $ip_addrs[0]['address'] : null;
        
        $ports[] = [
            'name' => $int['name'],
            'status' => $int['running'] ?? 'false',
            'ip_address' => $ip_address,
            'tx_mbps' => round(floatval($monitor['tx-bits-per-second'] ?? 0) / 1024 / 1024, 2),
            'rx_mbps' => round(floatval($monitor['rx-bits-per-second'] ?? 0) / 1024 / 1024, 2)
        ];
    }

    echo json_encode(['status' => 'success', 'system' => $system, 'ports' => $ports]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
