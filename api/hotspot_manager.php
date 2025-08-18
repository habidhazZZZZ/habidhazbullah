<?php
// File: api/hotspot_manager.php (Versi Final Lengkap)
// Deskripsi: Backend terpusat untuk manajemen hotspot, user, IP binding, dan pemantauan.

session_start();
header('Content-Type: application/json');
// ini_set('display_errors', 1); error_reporting(E_ALL); // Aktifkan hanya untuk debug

// Memuat semua dependensi yang diperlukan
$project_root = dirname(__DIR__);
require_once $project_root . '/vendor/autoload.php';
require_once $project_root . '/public/config/db.php';

// Menggunakan library yang diperlukan
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \RouterOS\Client;
use \RouterOS\Query;

try {
    /**
     * Fungsi untuk mendapatkan koneksi ke router yang aktif berdasarkan sesi.
     * @param mysqli $koneksi Objek koneksi database mysqli.
     * @return Client Objek klien RouterOS.
     */
    function getMikrotikClient($koneksi) {
        if (!isset($_SESSION['active_device_id'])) {
            throw new \Exception('Sesi perangkat aktif tidak ditemukan. Silakan pilih perangkat terlebih dahulu.');
        }
        $deviceId = $_SESSION['active_device_id'];
        $userId = $_SESSION['user_id'];
        
        $query = "SELECT * FROM devices WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) throw new \Exception('Gagal menyiapkan query database.');
        
        mysqli_stmt_bind_param($stmt, "ii", $deviceId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $device = mysqli_fetch_assoc($result);
        
        if (!$device) throw new \Exception('Perangkat tidak ditemukan atau Anda tidak memiliki akses.');
        
        return new Client([
            'host' => $device['host'],
            'user' => $device['api_user'],
            'pass' => $device['api_pass'],
            'port' => (int)$device['api_port'],
        ]);
    }

    $action = $_GET['action'] ?? '';
    $client = getMikrotikClient($koneksi); // Inisialisasi client di awal

    switch ($action) {
        // =================================================================================
        // == FUNGSI MANAJEMEN USER HOTSPOT (DENGAN INTEGRASI DATABASE)
        // =================================================================================
        case 'get_all_users':
            // 1. Ambil semua user dari MikroTik
            $mikrotikUsers = $client->query('/ip/hotspot/user/print')->read();
            $app_user_id = $_SESSION['user_id'];

            // 2. Ambil detail dari tabel 'hotspot_users' berdasarkan user_id yang login
            $db_query = "SELECT username, nama_lengkap, jurusan, kelas FROM hotspot_users WHERE user_id = ?";
            $stmt = mysqli_prepare($koneksi, $db_query);
            mysqli_stmt_bind_param($stmt, "i", $app_user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $localUserDetails = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $localUserDetails[$row['username']] = $row;
            }

            // 3. Gabungkan data MikroTik dengan data dari database lokal
            $combinedData = array_map(function($mikrotikUser) use ($localUserDetails) {
                $username = $mikrotikUser['name'];
                if (isset($localUserDetails[$username])) {
                    // Gabungkan semua data dari database ke data mikrotik
                    $mikrotikUser = array_merge($mikrotikUser, $localUserDetails[$username]);
                }
                return $mikrotikUser;
            }, $mikrotikUsers);

            echo json_encode(['status' => 'success', 'data' => $combinedData]);
            break;

        case 'add_user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new \Exception('Metode request salah.');
            
            // Ambil semua data dari form
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;
            $profile = $_POST['profile'] ?? null;
            $nama_lengkap = $_POST['nama_lengkap'] ?? '';
            $jurusan = $_POST['jurusan'] ?? '';
            $kelas = $_POST['kelas'] ?? '';
            $app_user_id = $_SESSION['user_id']; 

            if (!$username || !$password || !$profile) {
                throw new \Exception("Username, Password, dan Profile wajib diisi.");
            }

            // Tambahkan user ke MikroTik
            try {
                $addUserQuery = (new Query('/ip/hotspot/user/add'))
                    ->equal('name', $username)->equal('password', $password)
                    ->equal('profile', $profile)->equal('comment', $nama_lengkap); // Comment diisi nama lengkap
                $client->query($addUserQuery)->read();
            } catch (\Exception $e) {
                throw new \Exception("Gagal menambahkan user ke MikroTik: " . $e->getMessage());
            }

            // Simpan detail ke database lokal (tabel hotspot_users)
            $db_query = "INSERT INTO hotspot_users (username, nama_lengkap, jurusan, kelas, user_id) 
                         VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE 
                         nama_lengkap = VALUES(nama_lengkap), 
                         jurusan = VALUES(jurusan), 
                         kelas = VALUES(kelas)";
            
            $stmt = mysqli_prepare($koneksi, $db_query);
            if (!$stmt) throw new \Exception('Gagal menyiapkan query database lokal.');
            
            mysqli_stmt_bind_param($stmt, "ssssi", $username, $nama_lengkap, $jurusan, $kelas, $app_user_id);
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new \Exception("User ditambahkan ke MikroTik, tapi gagal menyimpan detail ke database.");
            }

            echo json_encode(['status' => 'success', 'message' => "User '$username' berhasil ditambahkan."]);
            break;
            
        case 'get_profiles':
            $profiles = $client->query('/ip/hotspot/user/profile/print')->read();
            echo json_encode(['status' => 'success', 'data' => $profiles]);
            break;

        // =================================================================================
        // == FUNGSI TAMBAH MASSAL (EXCEL)
        // =================================================================================
        case 'download_template':
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template User Hotspot');
            
            $sheet->setCellValue('A1', 'username');
            $sheet->setCellValue('B1', 'password');
            $sheet->setCellValue('C1', 'profile');
            $sheet->setCellValue('D1', 'nama_lengkap');
            $sheet->setCellValue('E1', 'jurusan');
            $sheet->setCellValue('F1', 'kelas');
            
            $headerStyle = ['font' => ['bold' => true]];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            foreach(range('A','F') as $columnID) { $sheet->getColumnDimension($columnID)->setAutoSize(true); }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_user_hotspot.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        case 'upload_bulk':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new \Exception('Metode request salah.');
            if (!isset($_FILES['user_file']) || $_FILES['user_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Gagal mengunggah file.');
            }

            $file_tmp_path = $_FILES['user_file']['tmp_name'];
            $spreadsheet = IOFactory::load($file_tmp_path);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            $success_count = 0; $error_count = 0;
            $error_details = []; $total_rows = 0;
            $app_user_id = $_SESSION['user_id'];

            // Mulai transaksi database
            mysqli_begin_transaction($koneksi);

            try {
                $db_query = "INSERT INTO hotspot_users (username, nama_lengkap, jurusan, kelas, user_id) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($koneksi, $db_query);

                foreach ($sheetData as $row_num => $row) {
                    if ($row_num == 1) continue; // Lewati header
                    $total_rows++;

                    $username = trim($row['A'] ?? ''); $password = trim($row['B'] ?? '');
                    $profile  = trim($row['C'] ?? ''); $nama_lengkap = trim($row['D'] ?? '');
                    $jurusan  = trim($row['E'] ?? ''); $kelas = trim($row['F'] ?? '');
                    
                    if (empty($username) || empty($password) || empty($profile)) {
                        $error_count++; $error_details[] = "Baris $row_num: Data username/password/profile kosong.";
                        continue;
                    }
                    
                    // Tambahkan ke MikroTik
                    $addUserQuery = (new Query('/ip/hotspot/user/add'))
                        ->equal('name', $username)->equal('password', $password)
                        ->equal('profile', $profile)->equal('comment', $nama_lengkap);
                    $client->query($addUserQuery)->read();

                    // Tambahkan ke antrian database
                    mysqli_stmt_bind_param($stmt, "ssssi", $username, $nama_lengkap, $jurusan, $kelas, $app_user_id);
                    mysqli_stmt_execute($stmt);
                    
                    $success_count++;
                }
                // Commit semua data ke database jika semua berhasil
                mysqli_commit($koneksi);

            } catch (\Exception $e) {
                // Jika ada error, batalkan semua perubahan di database
                mysqli_rollback($koneksi);
                throw new \Exception("Terjadi error saat proses massal: " . $e->getMessage());
            }

            $message = "Proses selesai.\nTotal baris data: $total_rows\nBerhasil: $success_count\nGagal: $error_count\n";
            if (!empty($error_details)) {
                $message .= "\nDetail Kegagalan:\n" . implode("\n", $error_details);
            }
            echo json_encode(['status' => 'success', 'message' => $message]);
            break;

        // =================================================================================
        // == FUNGSI MANAJEMEN IP BINDING
        // =================================================================================
        case 'get_ip_bindings':
            $bindings = $client->query('/ip/hotspot/ip-binding/print')->read();
            echo json_encode(['status' => 'success', 'data' => $bindings]);
            break;

        case 'add_ip_binding':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new \Exception('Metode request salah.');
            
            $mac_address = $_POST['mac-address'] ?? null;
            $to_address = $_POST['to-address'] ?? null;
            $server = $_POST['server'] ?? 'all';
            $type = $_POST['type'] ?? 'bypassed';
            $comment = $_POST['comment'] ?? '';

            if (!$mac_address) throw new \Exception("MAC Address wajib diisi.");

            $query = (new Query('/ip/hotspot/ip-binding/add'))
                ->equal('mac-address', strtoupper($mac_address))
                ->equal('type', $type)->equal('comment', $comment);
            
            if ($to_address) $query->equal('to-address', $to_address);
            if ($server) $query->equal('server', $server);

            $client->query($query)->read();
            echo json_encode(['status' => 'success', 'message' => 'IP Binding berhasil ditambahkan.']);
            break;

        case 'remove_ip_binding': 
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new \Exception('Metode request salah.');
            $id = $_POST['id'] ?? null;
            if (!$id) throw new \Exception("ID IP Binding tidak ditemukan.");
            
            $query = (new Query('/ip/hotspot/ip-binding/remove'))->equal('.id', $id);
            $client->query($query)->read();
            
            echo json_encode(['status' => 'success', 'message' => 'IP Binding berhasil dihapus.']);
            break;

        // =================================================================================
        // == FUNGSI PEMANTAUAN PERANGKAT (DHCP LEASES) - Tidak diubah
        // =================================================================================
        case 'get_dhcp_devices':
            // ... (kode untuk get_dhcp_devices dan save_device_label tetap sama) ...
            break;
        case 'save_device_label':
            // ... (kode untuk get_dhcp_devices dan save_device_label tetap sama) ...
            break;

        default:
            http_response_code(404); 
            throw new \Exception("Aksi tidak valid atau tidak diizinkan.");
    }

} catch (\Throwable $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>
