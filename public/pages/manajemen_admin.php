<?php
// File: public/pages/manajemen_admin.php
if ($_SESSION['level'] !== 'admin') {
    die("Akses ditolak. Anda harus menjadi admin untuk mengakses halaman ini.");
}
$query_admins = "SELECT id, nama_lengkap, username, level FROM users";
$result_admins = mysqli_query($koneksi, $query_admins);
?>

<!-- Library untuk notifikasi modern -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Kolom Kiri: Daftar Admin -->
    <div class="lg:col-span-2 bg-gray-800 p-6 rounded-lg">
        <h3 class="text-xl font-semibold mb-4">Daftar Pengguna Aplikasi</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-400">
                <thead class="text-xs text-gray-300 uppercase bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nama Lengkap</th>
                        <th scope="col" class="px-6 py-3">Username</th>
                        <th scope="col" class="px-6 py-3">Level</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_admins) > 0): ?>
                        <?php while($admin = mysqli_fetch_assoc($result_admins)): ?>
                        <tr class="bg-gray-800 border-b border-gray-700">
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap"><?= htmlspecialchars($admin['nama_lengkap']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($admin['username']) ?></td>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= $admin['level'] == 'admin' ? 'bg-green-500/20 text-green-300' : 'bg-yellow-500/20 text-yellow-300' ?>"><?= htmlspecialchars($admin['level']) ?></span></td>
                            <td class="px-6 py-4">
                                <!-- Tombol hapus sekarang memanggil fungsi JavaScript -->
                                <a href="#" 
                                   onclick="confirmDelete(event, '<?= $admin['id'] ?>', '<?= htmlspecialchars(addslashes($admin['nama_lengkap']), ENT_QUOTES) ?>')"
                                   class="font-medium text-red-500 hover:underline">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">Tidak ada data admin.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kolom Kanan: Form Tambah Admin -->
    <div class="lg:col-span-1 bg-gray-800 p-6 rounded-lg">
        <h3 class="text-xl font-semibold mb-4">Tambah Pengguna Baru</h3>
        <form action="pages/proses_tambah_admin.php" method="POST" class="space-y-4">
            <div>
                <label for="nama_lengkap" class="block mb-2 text-sm font-medium text-gray-300">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            </div>
            <div>
                <label for="username" class="block mb-2 text-sm font-medium text-gray-300">Username</label>
                <input type="text" name="username" id="username" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            </div>
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            </div>
            <div>
                <label for="level" class="block mb-2 text-sm font-medium text-gray-300">Level</label>
                <select name="level" id="level" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                <i class="bi bi-plus-circle-fill mr-2"></i>Tambah Pengguna
            </button>
        </form>
    </div>
</div>

<script>
// Fungsi untuk menampilkan dialog konfirmasi hapus yang modern
function confirmDelete(event, id, name) {
    event.preventDefault(); // Mencegah link berjalan secara langsung
    
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        html: `Anda akan menghapus pengguna "<b>${name}</b>".<br>Tindakan ini tidak dapat dibatalkan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1f2937', // Background gelap
        color: '#e5e7eb' // Teks putih
    }).then((result) => {
        // Jika pengguna mengklik "Ya, Hapus!"
        if (result.isConfirmed) {
            // Arahkan ke skrip penghapusan
            window.location.href = `pages/proses_hapus_admin.php?id=${id}`;
        }
    });
}

// Fungsi untuk menampilkan notifikasi toast setelah aksi
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');

    if (status) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            background: '#1f2937',
            color: '#e5e7eb'
        });

        let title, icon;
        switch(status) {
            case 'sukses':
                title = 'Pengguna baru berhasil ditambahkan!';
                icon = 'success';
                break;
            case 'hapus_sukses':
                title = 'Pengguna berhasil dihapus.';
                icon = 'success';
                break;
            case 'hapus_sendiri':
                title = 'Anda tidak dapat menghapus akun Anda sendiri.';
                icon = 'error';
                break;
            default:
                title = 'Operasi gagal dilakukan.';
                icon = 'error';
                break;
        }

        Toast.fire({
            icon: icon,
            title: title
        });

        // Membersihkan parameter 'status' dari URL agar notifikasi tidak muncul lagi saat refresh
        const url = new URL(window.location);
        url.searchParams.delete('status');
        window.history.replaceState({}, document.title, url);
    }
});
</script>
