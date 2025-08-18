<?php require_once __DIR__ . '/../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User Hotspot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #111827; color: #d1d5db; }
        .card { background-color: #1f2937; border: 1px solid #374151; transition: all 0.2s ease-in-out; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        
        /* --- MODIFIKASI CSS --- */
        /* Gaya asli untuk Guru (border biru) - Sekarang digunakan untuk menandakan USER AKTIF/LOGIN */
        .card-guru { background-color: #1e3a8a; border-left: 4px solid #3b82f6; }
        .card-guru:hover { border-color: #60a5fa; }
        
        /* Gaya untuk Siswa atau user non-aktif (border abu-abu) */
        .card-siswa { background-color: #1f2937; border-left: 4px solid #4b5563; }
        .card-siswa:hover { border-color: #6b7280; }
        /* --- AKHIR MODIFIKASI CSS --- */

        .btn { transition: all 0.2s; }
        .badge { display: inline-block; padding: 0.25em 0.6em; font-size: 0.75em; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.375rem; }
        .badge-green { color: #ffffff; background-color: #16a34a; }
        .badge-blue { color: #ffffff; background-color: #2563eb; }
        .badge-red { color: #ffffff; background-color: #dc2626; }
        .badge-gray { color: #ffffff; background-color: #6b7280; }
        .card-checkbox { position: absolute; top: 1rem; right: 1rem; width: 1.5rem; height: 1.5rem; background-color: rgba(55, 65, 81, 0.5); border-color: #4b5563; }
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex; align-items: center; justify-content: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .modal-overlay.visible {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body class="p-4 md:p-8">

<div class="max-w-full mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Daftar User Hotspot</h1>

    <div class="card p-6 rounded-lg shadow-lg mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div id="bulk-action-container" class="flex items-center gap-2 hidden">
                <span id="selected-count" class="text-sm font-medium text-gray-300">0 terpilih</span>
                <button data-action="disable" class="btn bulk-action-btn bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-3 rounded-lg text-sm">Nonaktifkan</button>
                <button data-action="enable" class="btn bulk-action-btn bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg text-sm">Aktifkan</button>
                <button data-action="delete" class="btn bulk-action-btn bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg text-sm">Hapus</button>
            </div>
            <div class="flex items-center gap-4 w-full md:w-auto md:ml-auto">
                <input type="text" id="search-input" placeholder="Cari apa saja..." class="w-full md:w-auto bg-gray-700 border-gray-600 rounded-md p-2 pl-4 text-white">
                <select id="show-entries" class="bg-gray-700 border-gray-600 rounded-md p-2 text-white">
                    <option value="10">Tampilkan 10</option>
                    <option value="50" selected>Tampilkan 50</option>
                    <option value="100">Tampilkan 100</option>
                    <option value="all">Tampilkan Semua</option>
                </select>
                <button id="refresh-btn" title="Segarkan Data" class="btn bg-blue-600 hover:bg-blue-700 text-white font-bold p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </div>
        <div class="flex items-center mt-4">
            <input type="checkbox" id="select-all-checkbox" class="h-4 w-4 rounded bg-gray-700 border-gray-600 text-indigo-600 focus:ring-indigo-500">
            <label for="select-all-checkbox" class="ml-2 block text-sm text-gray-300">Pilih Semua yang Ditampilkan</label>
        </div>
    </div>

    <div id="user-alert" class="my-4"></div>

    <div id="user-card-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <p id="loading-indicator" class="text-center text-gray-400 col-span-full">Memuat data...</p>
    </div>
</div>

<div id="confirmation-modal" class="modal-overlay">
    <div class="card rounded-lg shadow-xl w-full max-w-md m-4">
        <div class="p-6">
            <h3 id="modal-title" class="text-xl font-bold text-white">Konfirmasi Aksi</h3>
            <p id="modal-message" class="mt-2 text-gray-300">Apakah Anda yakin?</p>
            <div id="modal-input-container" class="mt-4 hidden">
                <label for="modal-confirm-input" class="text-sm font-medium text-gray-400">Untuk konfirmasi, ketik "<strong id="modal-confirm-text"></strong>" di bawah ini.</label>
                <input type="text" id="modal-confirm-input" class="mt-1 w-full bg-gray-800 border-gray-600 rounded-md p-2 text-white">
            </div>
        </div>
        <div class="bg-gray-800 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
            <button id="modal-cancel-btn" class="btn bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">Batal</button>
            <button id="modal-confirm-btn" class="btn bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed">Konfirmasi</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi elemen (tidak berubah)
    const cardContainer = document.getElementById('user-card-container');
    const searchInput = document.getElementById('search-input');
    const refreshBtn = document.getElementById('refresh-btn');
    const alertContainer = document.getElementById('user-alert');
    const showEntriesSelect = document.getElementById('show-entries');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const bulkActionContainer = document.getElementById('bulk-action-container');
    const selectedCountSpan = document.getElementById('selected-count');
    const loadingIndicator = document.getElementById('loading-indicator');
    const modal = document.getElementById('confirmation-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const modalInputContainer = document.getElementById('modal-input-container');
    const modalConfirmText = document.getElementById('modal-confirm-text');
    const modalConfirmInput = document.getElementById('modal-confirm-input');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const modalConfirmBtn = document.getElementById('modal-confirm-btn');

    let allUsers = [];
    let displayedUsers = [];
    let currentBulkAction = null;

    // --- Fungsi Inti ---
    function showAlert(message, isSuccess = false) { 
        const bgColor = isSuccess ? 'bg-green-600' : 'bg-red-600';
        alertContainer.innerHTML = `<div class="${bgColor} text-white p-4 rounded-md text-sm"><pre class="whitespace-pre-wrap font-sans">${message}</pre></div>`;
        setTimeout(() => alertContainer.innerHTML = '', 5000);
    }
    
    // --- PERBAIKAN DI SINI ---
    // Seluruh fungsi renderCards diperbarui untuk menangani status login
    function renderCards(users) {
        displayedUsers = users;
        const limit = showEntriesSelect.value === 'all' ? users.length : parseInt(showEntriesSelect.value, 10);
        const usersToRender = users.slice(0, limit);
        loadingIndicator.classList.add('hidden');
        cardContainer.innerHTML = '';
        if (usersToRender.length === 0) {
            cardContainer.innerHTML = `<p class="text-center text-gray-400 col-span-full">Tidak ada data pengguna yang cocok.</p>`;
            return;
        }
        usersToRender.forEach(user => {
            const card = document.createElement('div');
            
            // LOGIKA 1: Tentukan kelas CSS berdasarkan status login
            // Jika user login, beri kelas 'card-guru' (border biru), jika tidak 'card-siswa' (border abu-abu)
            const profileCardClass = user.logged_in ? 'card-guru' : 'card-siswa';

            // LOGIKA 2: Tentukan badge status login
            // Jika user login, tampilkan badge biru "Login"
            const loginStatusBadge = user.logged_in 
                ? `<span class="badge badge-blue">Login</span>`
                : '';

            // LOGIKA 3: Siapkan blok info IP dan MAC jika user login
            // Jika user login, buat blok HTML untuk menampilkan IP dan MAC Address
            const activeInfoBlock = user.logged_in
                ? `
                <div class="border-t border-gray-700 my-4"></div>
                <div class="text-xs text-gray-400 space-y-1">
                    <div class="flex justify-between"><span>IP Address:</span> <span class="font-mono text-cyan-400">${user.address}</span></div>
                    <div class="flex justify-between"><span>MAC Address:</span> <span class="font-mono text-cyan-400">${user['mac-address']}</span></div>
                </div>
                `
                : ''; // Jika tidak login, blok ini akan kosong

            // Gabungkan semua logika di atas ke dalam HTML kartu
            card.className = `rounded-lg shadow-lg p-5 relative ${profileCardClass}`;
            card.innerHTML = `
                <input type="checkbox" class="card-checkbox user-checkbox rounded" data-id="${user['.id']}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="badge ${user.disabled === 'true' ? 'badge-red' : 'badge-green'}">${user.disabled === 'true' ? 'Nonaktif' : 'Aktif'}</span>
                        ${loginStatusBadge}
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-white">${user.name}</h3>
                    <p class="text-sm text-gray-400">${user.nama_lengkap || 'Tidak ada nama'}</p>
                    <p class="text-xs text-gray-500 mt-1">${user.jurusan || ''} - ${user.kelas || ''}</p>
                </div>
                <div class="text-center mt-2">
                    <span class="badge badge-gray">${user.profile}</span>
                </div>
                <div class="border-t border-gray-700 my-4"></div>
                <div class="text-xs text-gray-400 space-y-1">
                    <div class="flex justify-between"><span>⬆️ Upload:</span> <span class="font-mono">${user.bytes_out}</span></div>
                    <div class="flex justify-between"><span>⬇️ Download:</span> <span class="font-mono">${user.bytes_in}</span></div>
                </div>
                ${activeInfoBlock}
            `;
            cardContainer.appendChild(card);
        });
        updateBulkActionUI();
    }

    async function fetchUsers() {
        loadingIndicator.classList.remove('hidden');
        cardContainer.innerHTML = '';
        try {
            const response = await fetch('../api/active_users_api.php?action=get_all_users');
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');
            const result = await response.json();
            if (result.status === 'success') {
                allUsers = result.data;
                applyFilters();
            } else { throw new Error(result.message); }
        } catch (error) {
            loadingIndicator.classList.add('hidden');
            cardContainer.innerHTML = `<p class="text-center text-red-400 col-span-full">Error: ${error.message}</p>`;
        }
    }

    // Sisa fungsi JavaScript tidak berubah
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const filtered = allUsers.filter(user => 
            user.name.toLowerCase().includes(searchTerm) ||
            (user.nama_lengkap && user.nama_lengkap.toLowerCase().includes(searchTerm)) ||
            (user.jurusan && user.jurusan.toLowerCase().includes(searchTerm)) ||
            (user.kelas && user.kelas.toLowerCase().includes(searchTerm))
        );
        renderCards(filtered);
    }

    function updateBulkActionUI() {
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const count = selectedCheckboxes.length;
        if (count > 0) {
            bulkActionContainer.classList.remove('hidden');
            selectedCountSpan.textContent = `${count} terpilih`;
        } else {
            bulkActionContainer.classList.add('hidden');
        }
        const displayedCheckboxes = document.querySelectorAll('.user-checkbox').length;
        selectAllCheckbox.checked = displayedCheckboxes > 0 && count === displayedCheckboxes;
    }
    
    function setupAndShowModal(action) {
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const count = selectedCheckboxes.length;

        if (count === 0) {
            showAlert('Tidak ada pengguna yang dipilih.');
            return;
        }
        
        currentBulkAction = action;

        const actionText = {
            'disable': { verb: 'menonaktifkan', title: 'Nonaktifkan Pengguna?', color: 'bg-yellow-600' },
            'enable': { verb: 'mengaktifkan', title: 'Aktifkan Pengguna?', color: 'bg-green-600' },
            'delete': { verb: 'menghapus', title: 'HAPUS PENGGUNA?', color: 'bg-red-600' }
        };

        modalTitle.textContent = actionText[action].title;
        modalMessage.textContent = `Anda akan ${actionText[action].verb} ${count} pengguna. Aksi ini tidak dapat diurungkan.`;
        modalConfirmBtn.className = `btn ${actionText[action].color} text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed`;

        if (action === 'delete') {
            const confirmWord = "HAPUS";
            modalInputContainer.classList.remove('hidden');
            modalConfirmText.textContent = confirmWord;
            modalConfirmBtn.disabled = true;
            modalConfirmInput.value = '';
            modalConfirmInput.oninput = () => {
                modalConfirmBtn.disabled = modalConfirmInput.value !== confirmWord;
            };
        } else {
            modalInputContainer.classList.add('hidden');
            modalConfirmBtn.disabled = false;
        }

        modal.classList.add('visible');
    }

    async function handleConfirmAction() {
        if (!currentBulkAction) return;

        const action = currentBulkAction;
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.id);
        
        modal.classList.remove('visible');

        const formData = new FormData();
        selectedIds.forEach(id => formData.append('ids[]', id));
        formData.append('action', action);

        try {
            const response = await fetch('../api/active_users_api.php?action=bulk_action', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                showAlert(result.message, true);
                fetchUsers();
            } else { throw new Error(result.message); }
        } catch (error) {
            showAlert(error.message);
        } finally {
            currentBulkAction = null;
        }
    }

    // Event Listeners (tidak berubah)
    searchInput.addEventListener('input', applyFilters);
    showEntriesSelect.addEventListener('change', applyFilters);
    refreshBtn.addEventListener('click', fetchUsers);

    selectAllCheckbox.addEventListener('change', (e) => {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => checkbox.checked = e.target.checked);
        updateBulkActionUI();
    });

    cardContainer.addEventListener('change', (e) => {
        if (e.target.classList.contains('user-checkbox')) {
            updateBulkActionUI();
        }
    });

    document.querySelectorAll('.bulk-action-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const action = e.currentTarget.dataset.action;
            setupAndShowModal(action);
        });
    });

    modalCancelBtn.addEventListener('click', () => modal.classList.remove('visible'));
    modalConfirmBtn.addEventListener('click', handleConfirmAction);

    // Inisialisasi
    fetchUsers();
});
</script>

</body>
</html>