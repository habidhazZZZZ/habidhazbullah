<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tambah User & IP Binding</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #111827; color: #d1d5db; }
        .card { background-color: #1f2937; }
        .btn { transition: all 0.2s; }
        .tab-btn { border-bottom: 2px solid transparent; }
        .tab-btn.active { border-color: #4f46e5; color: #ffffff; }
        .file-input-label { border: 2px dashed #4b5563; }
        .file-input-label:hover { border-color: #6b7280; background-color: #374151; }
        .table-auto th, .table-auto td { padding: 0.75rem; text-align: left; }
    </style>
</head>
<body class="p-4 md:p-8">

<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Manajemen User & IP Binding</h1>

    <!-- Navigasi Tab -->
    <div class="mb-6 border-b border-gray-700">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button id="tab-single" class="tab-btn active whitespace-nowrap py-4 px-1 text-sm font-medium">Tambah Satu User</button>
            <button id="tab-bulk" class="tab-btn whitespace-nowrap py-4 px-1 text-sm font-medium text-gray-400 hover:text-white">Tambah Massal (Excel)</button>
            <button id="tab-binding" class="tab-btn whitespace-nowrap py-4 px-1 text-sm font-medium text-gray-400 hover:text-white">IP Binding</button>
        </nav>
    </div>

    <!-- Konten untuk Tambah Satu User -->
    <div id="content-single">
        <!-- ... (Konten form tambah satu user Anda yang sudah ada, tidak perlu diubah) ... -->
        <div class="card p-6 rounded-lg shadow-lg">
            <div id="single-user-alert" class="mb-4"></div>
            <form id="add-user-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label for="username" class="block text-sm font-medium text-gray-300">Username</label><input type="text" name="username" id="username" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required></div>
                <div><label for="password" class="block text-sm font-medium text-gray-300">Password</label><input type="password" name="password" id="password" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required></div>
                <div><label for="profile" class="block text-sm font-medium text-gray-300">User Profile</label><select name="profile" id="profile" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required><option>Memuat profil...</option></select></div>
                <div><label for="nama_lengkap" class="block text-sm font-medium text-gray-300">Nama Lengkap</label><input type="text" name="nama_lengkap" id="nama_lengkap" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white"></div>
                <div><label for="jurusan" class="block text-sm font-medium text-gray-300">Jurusan</label><input type="text" name="jurusan" id="jurusan" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white"></div>
                <div><label for="kelas" class="block text-sm font-medium text-gray-300">Kelas</label><input type="text" name="kelas" id="kelas" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white"></div>
                <div class="md:col-span-2"><button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded btn">Tambah Pengguna</button></div>
            </form>
        </div>
    </div>

    <!-- Konten untuk Tambah Massal (Excel) -->
    <div id="content-bulk" class="hidden">
        <!-- ... (Konten form tambah massal Anda yang sudah ada, tidak perlu diubah) ... -->
        <div class="card p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-semibold text-white mb-3">Langkah 1: Unduh Template</h2>
            <p class="text-gray-400 mb-4">Unduh template Excel untuk memastikan format data sesuai. Isi data pengguna pada file ini, lalu unggah pada Langkah 2.</p>
            <a href="../api/hotspot_manager.php?action=download_template" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg btn">📥 Unduh Template Excel</a>
        </div>
        <div class="card p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-white mb-3">Langkah 2: Unggah File</h2>
            <div id="bulk-upload-alert" class="mb-4"></div>
            <form id="bulk-upload-form" enctype="multipart/form-data">
                <label for="user_file" class="file-input-label block w-full p-8 text-center rounded-lg cursor-pointer">
                    <span id="file-name-display" class="text-gray-400">Klik untuk memilih file (.xlsx, .xls)</span>
                    <input type="file" name="user_file" id="user_file" class="hidden" accept=".xlsx, .xls" required>
                </label>
                <div class="mt-6">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg btn disabled:opacity-50 disabled:cursor-wait">🚀 Unggah dan Proses Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Konten untuk IP Binding -->
    <div id="content-binding" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah IP Binding -->
            <div class="lg:col-span-1">
                <div class="card p-6 rounded-lg shadow-lg">
                    <h2 class="text-xl font-semibold text-white mb-4">Tambah IP Binding</h2>
                    <div id="binding-alert" class="mb-4"></div>
                    <form id="add-binding-form" class="space-y-4">
                        <div>
                            <label for="mac-address" class="block text-sm font-medium text-gray-300">MAC Address</label>
                            <input type="text" name="mac-address" id="mac-address" placeholder="Contoh: 1A:2B:3C:4D:5E:6F" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required>
                        </div>
                        <div>
                            <label for="to-address" class="block text-sm font-medium text-gray-300">Alamat IP Tujuan</label>
                            <input type="text" name="to-address" id="to-address" placeholder="Contoh: 192.168.88.10" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required>
                        </div>
                        <div>
                            <label for="server" class="block text-sm font-medium text-gray-300">Server Hotspot</label>
                            <select name="server" id="server" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required>
                                <option value="all">all</option>
                                <!-- Opsi server lain bisa ditambahkan jika perlu -->
                            </select>
                        </div>
                         <div>
                            <label for="type" class="block text-sm font-medium text-gray-300">Tipe</label>
                            <select name="type" id="type" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white" required>
                                <option value="bypassed">Bypassed (Lewati Login)</option>
                                <option value="regular">Regular (Tetap Login)</option>
                            </select>
                        </div>
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-300">Komentar (Opsional)</label>
                            <input type="text" name="comment" id="comment" placeholder="Contoh: CCTV Gudang" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white">
                        </div>
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded btn">Tambah Binding</button>
                    </form>
                </div>
            </div>
            <!-- Tabel Daftar IP Binding -->
            <div class="lg:col-span-2">
                <div class="card p-6 rounded-lg shadow-lg">
                    <h2 class="text-xl font-semibold text-white mb-4">Daftar IP Binding</h2>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm text-gray-300">
                            <thead class="bg-gray-700 text-gray-200">
                                <tr>
                                    <th>MAC Address</th>
                                    <th>IP Address</th>
                                    <th>Tipe</th>
                                    <th>Komentar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="binding-table-body">
                                <tr><td colspan="5" class="text-center p-4">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Elemen Umum ---
    const tabSingle = document.getElementById('tab-single');
    const tabBulk = document.getElementById('tab-bulk');
    const tabBinding = document.getElementById('tab-binding'); // Baru
    const contentSingle = document.getElementById('content-single');
    const contentBulk = document.getElementById('content-bulk');
    const contentBinding = document.getElementById('content-binding'); // Baru

    // --- Logika untuk Tab ---
    function switchTab(activeTab) {
        // Sembunyikan semua konten
        contentSingle.classList.add('hidden');
        contentBulk.classList.add('hidden');
        contentBinding.classList.add('hidden');
        // Nonaktifkan semua tombol tab
        tabSingle.classList.remove('active', 'text-white');
        tabBulk.classList.remove('active', 'text-white');
        tabBinding.classList.remove('active', 'text-white');

        if (activeTab === 'single') {
            contentSingle.classList.remove('hidden');
            tabSingle.classList.add('active', 'text-white');
        } else if (activeTab === 'bulk') {
            contentBulk.classList.remove('hidden');
            tabBulk.classList.add('active', 'text-white');
        } else if (activeTab === 'binding') { // Baru
            contentBinding.classList.remove('hidden');
            tabBinding.classList.add('active', 'text-white');
            loadIpBindings(); // Muat data saat tab aktif
        }
    }
    tabSingle.addEventListener('click', () => switchTab('single'));
    tabBulk.addEventListener('click', () => switchTab('bulk'));
    tabBinding.addEventListener('click', () => switchTab('binding')); // Baru

    // --- Logika untuk Form Tambah Satu User (Tidak Berubah) ---
    // ... (Kode JavaScript Anda untuk tambah user tunggal dan massal tetap di sini) ...
    const singleUserForm = document.getElementById('add-user-form');
    const singleUserAlert = document.getElementById('single-user-alert');
    const profileSelect = document.getElementById('profile');

    function showSingleAlert(message, isSuccess = false) {
        const bgColor = isSuccess ? 'bg-green-600' : 'bg-red-600';
        singleUserAlert.innerHTML = `<div class="${bgColor} text-white p-4 rounded-md text-sm"><pre class="whitespace-pre-wrap font-sans">${message}</pre></div>`;
    }

    async function loadHotspotProfiles() {
        try {
            const response = await fetch('../api/hotspot_manager.php?action=get_profiles');
            if (!response.ok) throw new Error('Gagal mengambil data profil dari server.');
            const result = await response.json();
            if (result.status === 'success') {
                profileSelect.innerHTML = '<option value="" disabled selected>-- Pilih Profil --</option>';
                result.data.forEach(profile => {
                    profileSelect.innerHTML += `<option value="${profile.name}">${profile.name}</option>`;
                });
            } else { throw new Error(result.message); }
        } catch (error) {
            showSingleAlert(error.message);
        }
    }
    
    singleUserForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(singleUserForm);
        showSingleAlert('');
        try {
            const response = await fetch('../api/hotspot_manager.php?action=add_user', { method: 'POST', body: formData });
            if (!response.ok) throw new Error('Terjadi kesalahan pada server saat menambah user.');
            const result = await response.json();
            if (result.status === 'success') {
                showSingleAlert(result.message, true);
                singleUserForm.reset();
            } else { throw new Error(result.message); }
        } catch (error) {
            showSingleAlert(error.message);
        }
    });

    const bulkUploadForm = document.getElementById('bulk-upload-form');
    const userFileInput = document.getElementById('user_file');
    const fileNameDisplay = document.getElementById('file-name-display');
    const bulkUploadAlert = document.getElementById('bulk-upload-alert');
    const bulkSubmitButton = bulkUploadForm.querySelector('button[type="submit"]');

    function showBulkAlert(message, isSuccess = false) {
        const bgColor = isSuccess ? 'bg-green-600' : 'bg-red-600';
        bulkUploadAlert.innerHTML = `<div class="${bgColor} text-white p-4 rounded-md text-sm"><pre class="whitespace-pre-wrap font-sans">${message}</pre></div>`;
    }

    userFileInput.addEventListener('change', () => {
        if (userFileInput.files.length > 0) {
            fileNameDisplay.textContent = userFileInput.files[0].name;
        } else {
            fileNameDisplay.textContent = 'Klik untuk memilih file (.xlsx, .xls)';
        }
    });

    bulkUploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!userFileInput.files.length) {
            showBulkAlert('Silakan pilih file Excel terlebih dahulu.');
            return;
        }
        const formData = new FormData(bulkUploadForm);
        showBulkAlert('');
        bulkSubmitButton.disabled = true;
        bulkSubmitButton.textContent = 'Memproses...';

        try {
            const response = await fetch('../api/hotspot_manager.php?action=upload_bulk', { method: 'POST', body: formData });
            if (!response.ok) {
                 const errorText = await response.text();
                 throw new Error(`Error ${response.status}: Terjadi error fatal di server. Respons:\n${errorText}`);
            }
            const result = await response.json();
            if (result.status === 'success') {
                showBulkAlert(result.message, true);
                bulkUploadForm.reset();
                fileNameDisplay.textContent = 'Klik untuk memilih file (.xlsx, .xls)';
            } else { throw new Error(result.message); }
        } catch (error) {
            showBulkAlert(error.message);
        } finally {
            bulkSubmitButton.disabled = false;
            bulkSubmitButton.textContent = '🚀 Unggah dan Proses Data';
        }
    });


    // --- Logika untuk IP Binding (Baru) ---
    const addBindingForm = document.getElementById('add-binding-form');
    const bindingAlert = document.getElementById('binding-alert');
    const bindingTableBody = document.getElementById('binding-table-body');

    function showBindingAlert(message, isSuccess = false) {
        const bgColor = isSuccess ? 'bg-green-600' : 'bg-red-600';
        bindingAlert.innerHTML = `<div class="${bgColor} text-white p-4 rounded-md text-sm">${message}</div>`;
    }

    async function loadIpBindings() {
        bindingTableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">Memuat data...</td></tr>';
        try {
            const response = await fetch('../api/hotspot_manager.php?action=get_ip_bindings');
            const result = await response.json();
            if (result.status === 'success') {
                bindingTableBody.innerHTML = '';
                if (result.data.length === 0) {
                    bindingTableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">Tidak ada data IP Binding.</td></tr>';
                } else {
                    result.data.forEach(item => {
                        const row = `
                            <tr class="border-b border-gray-700 hover:bg-gray-800">
                                <td class="font-mono">${item['mac-address']}</td>
                                <td>${item['to-address'] || item['address']}</td>
                                <td><span class="px-2 py-1 text-xs rounded-full ${item.type === 'bypassed' ? 'bg-green-500 text-white' : 'bg-yellow-500 text-black'}">${item.type}</span></td>
                                <td>${item.comment || ''}</td>
                                <td><button data-id="${item['.id']}" class="remove-binding-btn text-red-400 hover:text-red-300">Hapus</button></td>
                            </tr>
                        `;
                        bindingTableBody.innerHTML += row;
                    });
                }
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            bindingTableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4 text-red-400">Error: ${error.message}</td></tr>`;
        }
    }

    addBindingForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addBindingForm);
        showBindingAlert('');
        try {
            const response = await fetch('../api/hotspot_manager.php?action=add_ip_binding', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                showBindingAlert(result.message, true);
                addBindingForm.reset();
                loadIpBindings(); // Muat ulang data tabel
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            showBindingAlert(error.message);
        }
    });
    
    bindingTableBody.addEventListener('click', async (e) => {
        if (e.target.classList.contains('remove-binding-btn')) {
            const id = e.target.dataset.id;
            if (confirm('Apakah Anda yakin ingin menghapus IP Binding ini?')) {
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    const response = await fetch('../api/hotspot_manager.php?action=remove_ip_binding', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        showBindingAlert(result.message, true);
                        loadIpBindings();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    showBindingAlert(error.message);
                }
            }
        }
    });

    // Inisialisasi awal
    loadHotspotProfiles();
});
</script>

</body>
</html>
