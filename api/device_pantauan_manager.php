<?php
// File: api/device_pantauan_manager.php (Versi Final & Lengkap)

// Baris untuk menampilkan error, bisa diaktifkan jika masih ada masalah
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';

try {
    require_once $project_root . '/public/config/db.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memuat atau terhubung ke database.', 'detail' => $e->getMessage()]);
    exit;
}

use \RouterOS\Client;
use \RouterOS\Query;

function getMikrotikClient($koneksi) {
    if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login' || !isset($_SESSION['active_device_id'])) {
        throw new \Exception('Akses ditolak. Sesi login tidak valid atau perangkat belum dipilih.');
    }
    $deviceId = $_SESSION['active_device_id'];
    $userId = $_SESSION['user_id'];
    $query_text = "SELECT * FROM devices WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query_text);
    if (!$stmt) throw new \Exception("Gagal menyiapkan query database: " . mysqli_error($koneksi));
    mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);
    mysqli_stmt_execute($stmt);
    $device = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$device) throw new \Exception('Perangkat tidak ditemukan di database untuk user ini.');
    try {
        return new Client([
            'host' => $device['host'], 'user' => $device['api_user'],
            'pass' => $device['api_pass'], 'port' => (int)$device['api_port'],
            'timeout' => 5,
        ]);
    } catch (\Throwable $e) {
        throw new \Exception("Gagal terhubung ke Router MikroTik di host: {$device['host']}.", 0, $e);
    }
}

try {
    $action = $_GET['action'] ?? '';
    if (empty($action)) {
        throw new \Exception("Aksi tidak valid atau tidak disediakan.");
    }
    $client = getMikrotikClient($koneksi);

    if ($action === 'get_dashboard_data') {
        $data = ['status' => 'success'];
        $resource = $client->query('/system/resource/print')->read()[0];
        $routerboard = $client->query('/system/routerboard/print')->read()[0];
        $identity = $client->query('/system/identity/print')->read()[0];
        $clock = $client->query('/system/clock/print')->read()[0];
        $data['identity'] = $identity['name'];
        $data['system'] = [
            'cpu_load' => (int)$resource['cpu-load'], 'total_ram' => (int)$resource['total-memory'],
            'used_ram' => (int)$resource['total-memory'] - (int)$resource['free-memory'], 'uptime' => $resource['uptime'],
            'version' => $resource['version'], 'architecture' => $resource['architecture-name'], 'cpu' => $resource['cpu'],
            'board_name' => $resource['board-name']
        ];
        $data['routerboard'] = $routerboard;
        $data['clock'] = $clock['time'];
        $all_interfaces = $client->query('/interface/print')->read();
        $interfaceNames = array_map(fn($iface) => $iface['name'], $all_interfaces);

        $trafficQuery = (new Query('/interface/monitor-traffic'))
            ->equal('interface', implode(',', $interfaceNames))
            ->equal('once', '');

        $trafficData = $client->query($trafficQuery)->read();
        $data['interfaces'] = array_map(function($iface) use ($trafficData) {
            foreach ($trafficData as $traffic) {
                if ($traffic['name'] === $iface['name']) {
                    $iface['tx'] = (int)$traffic['tx-bits-per-second'];
                    $iface['rx'] = (int)$traffic['rx-bits-per-second'];
                    return $iface;
                }
            }
            $iface['tx'] = 0; $iface['rx'] = 0;
            return $iface;
        }, $all_interfaces);
        
        $route = $client->query('/ip/route/print', ['?dst-address' => '0.0.0.0/0', '?active' => 'true'])->read()[0] ?? null;
        $data['total_traffic'] = ['tx' => 0, 'rx' => 0];
        if ($route && isset($route['interface'])) {
            foreach ($data['interfaces'] as $iface) {
                if ($iface['name'] === $route['interface']) {
                    $data['total_traffic']['tx'] = $iface['tx'];
                    $data['total_traffic']['rx'] = $iface['rx'];
                    break;
                }
            }
        }
        $data['services'] = $client->query('/ip/service/print')->read();
        $hotspotActive = $client->query('/ip/hotspot/active/print')->read();
        $data['hotspot_active_users'] = count($hotspotActive);
        echo json_encode($data);

    } elseif ($action === 'get_devices') {
        // FUNGSI SEBELUMNYA YANG DIKEMBALIKAN
        $dhcpLeases = $client->query('/ip/dhcp-server/lease/print')->read();

        $labelsQuery = "SELECT mac_address, device_type, owner_name FROM device_labels WHERE user_id = ?";
        $stmt = mysqli_prepare($koneksi, $labelsQuery);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $labels = [];
        while($row = mysqli_fetch_assoc($result)) {
            $labels[$row['mac_address']] = $row;
        }

        $combinedData = array_map(function($lease) use ($labels) {
            $mac = $lease['mac-address'];
            $lease['device_type'] = $labels[$mac]['device_type'] ?? '';
            $lease['owner_name'] = $labels[$mac]['owner_name'] ?? '';
            return $lease;
        }, $dhcpLeases);

        echo json_encode(['status' => 'success', 'data' => $combinedData]);

    } elseif ($action === 'save_label' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // FUNGSI SEBELUMNYA YANG DIKEMBALIKAN
        $mac_address = $_POST['mac_address'] ?? null;
        $device_type = $_POST['device_type'] ?? '';
        $owner_name = $_POST['owner_name'] ?? '';
        $user_id = $_SESSION['user_id'];

        if (!$mac_address) throw new \Exception("MAC Address wajib diisi.");

        $query = "INSERT INTO device_labels (mac_address, device_type, owner_name, user_id) 
                  VALUES (?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE device_type = VALUES(device_type), owner_name = VALUES(owner_name)";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $mac_address, $device_type, $owner_name, $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Label perangkat berhasil disimpan.']);
        } else {
            throw new \Exception("Gagal menyimpan label ke database.");
        }
    } else {
        throw new \Exception("Aksi tidak valid: " . htmlspecialchars($action));
    }

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>