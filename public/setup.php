<?php
// Panggil Satpam Utama: harus login untuk ke sini.
require_once 'auth_check.php';
require_once 'config/db.php'; // Butuh koneksi DB

// Ambil daftar perangkat milik pengguna yang sedang login
$user_id = $_SESSION['user_id'];
$query_devices = "SELECT * FROM devices WHERE user_id = ?";
$stmt = mysqli_prepare($koneksi, $query_devices);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_devices = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perangkat - MikroTik Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-900 text-gray-200 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-7xl bg-gray-800 p-8 rounded-lg shadow-lg">
        
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-700">
            <h1 class="text-3xl font-bold">Manajemen Perangkat MikroTik</h1>
            <a href="logout.php" class="text-sm text-red-400 hover:text-red-300">Logout <i class="bi bi-box-arrow-right"></i></a>
        </div>

        <?php if(isset($_GET['pesan'])): ?>
        <div class="mb-6 p-3 rounded-md text-white text-sm 
            <?php 
                $pesan = $_GET['pesan'];
                if($pesan == 'pilih_dulu') echo 'bg-yellow-600';
                if($pesan == 'sukses' || $pesan == 'sukses_hapus') echo 'bg-green-500';
                if($pesan == 'gagal_tambah' || $pesan == 'akses_ditolak' || $pesan == 'gagal' || $pesan == 'gagal_hapus') echo 'bg-red-500';
            ?>">
            <?php
                if($pesan == 'pilih_dulu') echo 'Silakan pilih perangkat yang ingin Anda monitor terlebih dahulu.';
                if($pesan == 'sukses') echo 'Perangkat baru berhasil ditambahkan!';
                if($pesan == 'gagal_tambah') echo 'Gagal menambahkan perangkat. Pastikan semua kolom terisi dengan benar.';
                if($pesan == 'akses_ditolak') echo 'Anda tidak memiliki hak akses ke perangkat tersebut.';
                if($pesan == 'gagal') echo 'Terjadi kesalahan. Gagal memproses permintaan.';
                if($pesan == 'sukses_hapus') echo 'Perangkat berhasil dihapus.';
                if($pesan == 'gagal_hapus') echo 'Gagal menghapus perangkat.';
            ?>
        </div>
        <?php endif; ?>


        <div class="flex flex-col md:flex-row gap-8">

            <div class="w-full md:w-1/3">
                <h2 class="text-2xl font-semibold mb-4">Tambah Perangkat</h2>
                <div class="bg-gray-700/50 p-6 rounded-lg">
                    <form action="proses_tambah_perangkat.php" method="POST" class="space-y-4">
                        <div>
                            <label for="device_name" class="block text-sm font-medium text-gray-300">Nama Perangkat</label>
                            <input type="text" id="device_name" name="device_name" placeholder="Contoh: Router Utama" class="mt-1 block w-full bg-gray-800 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="host" class="block text-sm font-medium text-gray-300">IP Address</label>
                            <input type="text" id="host" name="host" placeholder="Contoh: 192.168.88.1" class="mt-1 block w-full bg-gray-800 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="api_user" class="block text-sm font-medium text-gray-300">Username API</label>
                            <input type="text" id="api_user" name="api_user" value="admin" class="mt-1 block w-full bg-gray-800 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="api_pass" class="block text-sm font-medium text-gray-300">Password API</label>
                            <input type="password" id="api_pass" name="api_pass" placeholder="Kosongkan jika tidak ada" class="mt-1 block w-full bg-gray-800 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="api_port" class="block text-sm font-medium text-gray-300">Port API</label>
                            <input type="number" id="api_port" name="api_port" value="8728" class="mt-1 block w-full bg-gray-800 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border rounded-md font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500">
                                <i class="bi bi-plus-circle-fill mr-2"></i> Simpan Perangkat
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="w-full md:w-2/3">
                <h2 class="text-2xl font-semibold mb-4">Pilih Perangkat untuk Dimonitor</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <?php if(mysqli_num_rows($result_devices) > 0): ?>
                        <?php while($device = mysqli_fetch_assoc($result_devices)): ?>
                        <div class="bg-gray-700 p-4 rounded-lg flex flex-col justify-between hover:bg-gray-600/70 transition-colors duration-200">
                            <div>
                                <h3 class="font-bold text-lg text-indigo-300"><?= htmlspecialchars($device['device_name']) ?></h3>
                                <p class="text-sm text-gray-400 font-mono"><i class="bi bi-hdd-stack-fill"></i> <?= htmlspecialchars($device['host']) ?></p>
                            </div>
                            <!-- Tombol Aksi -->
                            <div class="mt-4 flex items-center gap-2">
                                <a href="pilih_perangkat.php?device_id=<?= $device['id'] ?>" class="text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md transition-colors duration-200">
                                    <i class="bi bi-display"></i> Monitor
                                </a>
                                <!-- FORM UNTUK HAPUS PERANGKAT -->
                                <form action="proses_hapus_perangkat.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus perangkat ini? Tindakan ini tidak dapat diurungkan.');">
                                    <input type="hidden" name="device_id" value="<?= $device['id'] ?>">
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition-colors duration-200">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full bg-gray-700/50 p-6 rounded-lg text-center">
                            <p class="text-gray-400">Anda belum memiliki perangkat tersimpan.</p>
                            <p class="text-gray-500 text-sm mt-2">Silakan tambahkan perangkat baru menggunakan form di sebelah kiri.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
