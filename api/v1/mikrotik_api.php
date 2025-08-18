<?php
// File: includes/mikrotik_api.php
// Class helper untuk berinteraksi dengan RouterOS API.

// Pastikan Anda sudah menjalankan `composer require pear/net_routeros`
require_once __DIR__ . '/../vendor/autoload.php';

use \RouterOS\Client;
use \RouterOS\Query;
use \RouterOS\Exceptions\ConnectException;

class MikroTikAPI {
    private $client;

    /**
     * Konstruktor untuk inisialisasi koneksi.
     * @param string $host Alamat IP atau domain router.
     * @param string $user Username API.
     * @param string $pass Password API.
     * @param int $port Port API (default 8728).
     */
    public function __construct($host, $user, $pass, $port = 8728) {
        try {
            $this->client = new Client([
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'port' => (int)$port,
                'timeout' => 5, // Timeout koneksi 5 detik
            ]);
        } catch (ConnectException $e) {
            // Melempar exception yang lebih deskriptif
            throw new Exception("Gagal terhubung ke router: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Terjadi kesalahan saat inisialisasi: " . $e->getMessage());
        }
    }

    /**
     * Fungsi generik untuk menjalankan query.
     * @param string $path Path perintah (misal: '/system/resource/print').
     * @return array Hasil query.
     */
    private function query($path) {
        try {
            return $this->client->query($path)->read();
        } catch (Exception $e) {
            // Melempar exception jika query gagal
            throw new Exception("Query API gagal untuk path '{$path}': " . $e->getMessage());
        }
    }

    // --- Metode untuk mendapatkan data spesifik ---

    public function getSystemResource() {
        return $this->query('/system/resource/print')[0] ?? [];
    }

    public function getIdentity() {
        return $this->query('/system/identity/print')[0] ?? [];
    }

    public function getRouterboard() {
        return $this->query('/system/routerboard/print')[0] ?? [];
    }
    
    public function getClock() {
        return $this->query('/system/clock/print')[0] ?? [];
    }

    public function getHotspotActiveCount() {
        return count($this->query('/ip/hotspot/active/print'));
    }

    /**
     * Mendapatkan daftar interface beserta traffic real-time.
     * @return array Daftar interface.
     */
    public function getInterfacesWithTraffic() {
        $interfaces = $this->query('/interface/print');
        $trafficData = [];

        foreach ($interfaces as $interface) {
            $name = $interface['name'];
            // Monitor traffic untuk interface ini saja
            $monitorQuery = (new Query('/interface/monitor-traffic'))
                ->equal('interface', $name)
                ->equal('once', '');
            
            $monitorResult = $this->client->query($monitorQuery)->read()[0] ?? [];

            $trafficData[] = [
                'name' => $name,
                'type' => $interface['type'],
                'is_running' => ($interface['running'] ?? 'false') === 'true',
                'mac_address' => $interface['mac-address'] ?? 'N/A',
                'tx_mbps' => round(($monitorResult['tx-bits-per-second'] ?? 0) / 1000000, 2),
                'rx_mbps' => round(($monitorResult['rx-bits-per-second'] ?? 0) / 1000000, 2),
            ];
        }
        return $trafficData;
    }

    /**
     * Mendapatkan traffic untuk satu interface spesifik.
     * @param string $interfaceName Nama interface.
     * @return array Data traffic.
     */
    public function getSpecificInterfaceTraffic($interfaceName) {
        $monitorQuery = (new Query('/interface/monitor-traffic'))
            ->equal('interface', $interfaceName)
            ->equal('once', '');
        
        $monitorResult = $this->client->query($monitorQuery)->read()[0] ?? [];

        return [
            'name' => $interfaceName,
            'tx_mbps' => round(($monitorResult['tx-bits-per-second'] ?? 0) / 1000000, 2),
            'rx_mbps' => round(($monitorResult['rx-bits-per-second'] ?? 0) / 1000000, 2),
        ];
    }
}
