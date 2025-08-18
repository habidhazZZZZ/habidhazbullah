<?php
// 1. Panggil Satpam Utama: Pastikan sudah login.
require_once 'auth_check.php';

// 2. Panggil Satpam Kedua: Pastikan sudah memilih perangkat.
require_once 'device_check.php';

// 3. Logika untuk memuat halaman dari menu
// Menggabungkan semua halaman yang diizinkan dari kedua versi
$allowed_pages = [
    'selamat_datang', 'manajemen_admin', 'manajemen_antrian', 
    'pantauan_perangkat', 'perangkat_tersimpan', 'filter_rules',
    'tambah_user', 'user_aktif', 'user_profiles', 'cetak_kartu'
];
$page = $_GET['page'] ?? 'selamat_datang';
if (!in_array($page, $allowed_pages)) {
    $page = 'selamat_datang';
}
$page_file = "pages/{$page}.php";

// 4. Panggil koneksi database
require_once 'config/db.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Manajemen Mikrotik</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #1f2937; }
    ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
    .nav-link.active { background-color: #4f46e5; }
  </style>
</head>
<body class="bg-gray-900 text-gray-100">
  <div id="app" class="h-screen w-screen flex">
    <!-- Sidebar Navigasi -->
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
      <div class="h-16 flex items-center justify-center bg-gray-900">
        <h1 id="router-identity" class="text-xl font-bold">MikroTik</h1>
      </div>
      <nav class="flex-1 px-4 py-4 space-y-2">
        <a href="index.php?page=selamat_datang" class="nav-link <?php if($page === 'selamat_datang') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-house-door-fill mr-3"></i> Selamat Datang</a>
        
        <?php if ($_SESSION['level'] === 'admin'): ?>
        <a href="index.php?page=manajemen_admin" class="nav-link <?php if($page === 'manajemen_admin') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-people-fill mr-3"></i> Manajemen Admin</a>
        <?php endif; ?>

        <a href="index.php?page=manajemen_antrian" class="nav-link <?php if($page === 'manajemen_antrian') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-speedometer2 mr-3"></i> Manajemen Antrian</a>
        <a href="index.php?page=pantauan_perangkat" class="nav-link <?php if($page === 'pantauan_perangkat') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-router-fill mr-3"></i> Pantauan Perangkat</a>
        <a href="index.php?page=perangkat_tersimpan" class="nav-link <?php if($page === 'perangkat_tersimpan') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-hdd-rack-fill mr-3"></i> Perangkat Tersimpan</a>
        <a href="index.php?page=filter_rules" class="nav-link <?php if($page === 'filter_rules') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-shield-fill-check mr-3"></i> Filter Rules</a>

        <div>
            <h3 class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen Hotspot</h3>
            <div class="space-y-2">
                <a href="index.php?page=tambah_user" class="nav-link <?php if($page === 'tambah_user') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-person-plus-fill mr-3"></i> Tambah User</a>
                <a href="index.php?page=user_aktif" class="nav-link <?php if($page === 'user_aktif') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-person-check-fill mr-3"></i> User Aktif</a>
                <a href="index.php?page=cetak_kartu" class="nav-link <?php if($page === 'cetak_kartu') echo 'active'; ?> flex items-center px-4 py-2 rounded-md hover:bg-indigo-700 transition"><i class="bi bi-printer-fill mr-3"></i> Cetak Kartu</a>
            </div>
        </div>
      </nav>
      <div class="p-4 border-t border-gray-700">
          <a href="logout.php" class="flex items-center justify-center w-full px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 transition"><i class="bi bi-box-arrow-right mr-3"></i> Logout</a>
      </div>
    </aside>

    <!-- Konten Utama -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <div class="p-8 flex-grow">
            <div class="flex justify-between items-center mb-6">
                <h2 id="page-title" class="text-3xl font-bold"></h2>
                <div class="flex items-center gap-4">
                    <!-- Info Perangkat Aktif -->
                    <div id="active-device-info" class="text-right">
                        <p class="text-sm font-bold text-white">-</p>
                        <p class="text-xs text-gray-400 font-mono">-</p>
                    </div>
                    <!-- Tombol Ganti Perangkat -->
                    <a href="setup.php" class="bg-gray-700 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <i class="bi bi-hdd-stack-fill"></i> Ganti Perangkat
                    </a>
                    <a href="dashboard.php" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <i class="bi bi-display"></i> Monitoring
                    </a>
                </div>
            </div>
            <?php 
                define('IS_INCLUDED', true);
                if (file_exists($page_file)) {
                    include($page_file); 
                } else {
                    echo "<div class='bg-red-500/20 text-red-300 p-4 rounded-lg'>Error: File halaman <strong>".htmlspecialchars($page_file)."</strong> tidak ditemukan.</div>";
                }
            ?>
        </div>
    </main>
  </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pageTitle = document.getElementById('page-title');
    const activeLink = document.querySelector('.nav-link.active');
    if(activeLink) {
        pageTitle.textContent = activeLink.textContent.trim();
    }

    // Ambil info perangkat aktif
    fetch('api/get_device_info.php')
        .then(res => res.ok ? res.json() : Promise.reject('Gagal memuat info perangkat'))
        .then(result => {
            if(result.status === 'success' && result.data) {
                const infoContainer = document.getElementById('active-device-info');
                infoContainer.querySelector('p:first-child').textContent = result.data.device_name;
                infoContainer.querySelector('p:last-child').textContent = result.data.host;
                // Juga set nama router di sidebar
                document.getElementById('router-identity').textContent = result.data.device_name;
            } else {
                // Jika status tidak sukses, tampilkan pesan error dari API
                throw new Error(result.message || 'Data perangkat tidak valid');
            }
        })
        .catch(err => {
            console.error("Gagal memuat info perangkat:", err);
            const infoContainer = document.getElementById('active-device-info');
            infoContainer.querySelector('p:first-child').textContent = 'Error';
            infoContainer.querySelector('p:last-child').textContent = 'Gagal memuat';
        });
    
    // Script notifikasi
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');
    if (status) {
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3500,
            timerProgressBar: true, didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }, background: '#1f2937', color: '#e5e7eb'
        });
        let title, icon;
        switch(status) {
            case 'sukses': title = 'Operasi berhasil!'; icon = 'success'; break;
            case 'hapus_sukses': title = 'Data berhasil dihapus.'; icon = 'success'; break;
            case 'hapus_sendiri': title = 'Anda tidak dapat menghapus akun Anda sendiri.'; icon = 'warning'; break;
            case 'gagal': case 'hapus_gagal': title = 'Operasi gagal dilakukan.'; icon = 'error'; break;
        }
        if (title && icon) {
            Toast.fire({ icon: icon, title: title });
        }
        const url = new URL(window.location);
        url.searchParams.delete('status');
        window.history.replaceState({}, document.title, url);
    }
});
</script>
</body>
</html>
