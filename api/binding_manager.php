<?php
// File: api/binding_manager.php
// Deskripsi: Backend khusus untuk mengelola IP Binding Hotspot.

header('Content-Type: application/json');
// ini_set('display_errors', 1); error_reporting(E_ALL); // Aktifkan hanya untuk debug

$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';

use \RouterOS\Client;
use \RouterOS\Query;

try {
    function getMikrotikClient() {
        $configFile = dirname(__DIR__) . '/config/connection.json';
        if (!file_exists($configFile)) { throw new \Exception('File konfigurasi router tidak ditemukan.'); }
        $config = json_decode(file_get_contents($configFile), true);
        return new Client($config);
    }

    $action = $_GET['action'] ?? '';

    if ($action === 'get_ip_bindings') {
        $client = getMikrotikClient();
        $bindings = $client->query('/ip/hotspot/ip-binding/print')->read();
        echo json_encode(['status' => 'success', 'data' => $bindings]);

    } elseif ($action === 'add_ip_binding' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $client = getMikrotikClient();
        $mac_address = $_POST['mac_address'] ?? null;
        $binding_type = $_POST['binding_type'] ?? 'bypassed';
        $comment = $_POST['comment'] ?? '';

        if (!$mac_address) {
            throw new \Exception("MAC Address wajib diisi.");
        }

        $query = (new Query('/ip/hotspot/ip-binding/add'))
            ->equal('mac-address', strtoupper($mac_address))
            ->equal('type', $binding_type)
            ->equal('comment', $comment);
        $client->query($query)->read();
        
        echo json_encode(['status' => 'success', 'message' => 'IP Binding berhasil ditambahkan.']);

    } elseif ($action === 'delete_ip_binding' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        if (!$id) { throw new \Exception("ID IP Binding tidak ditemukan."); }
        $client = getMikrotikClient();
        $query = (new Query('/ip/hotspot/ip-binding/remove'))->equal('.id', $id);
        $client->query($query)->read();
        echo json_encode(['status' => 'success', 'message' => 'IP Binding berhasil dihapus.']);
    
    } else {
        throw new \Exception("Aksi tidak valid atau metode request salah.");
    }

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
}
?>
