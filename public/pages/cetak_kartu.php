<?php require_once __DIR__ . '/../auth_check.php'; ?>
<?php
// File: pages/cetak_kartu.php
// Deskripsi: Halaman antarmuka untuk fitur cetak kartu, sekarang dengan preview per filter dan preview semua kartu.
?>
<div class="space-y-8">
    <!-- Bagian 1: Filter dan Preview per Kelas -->
    <div class="bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Cetak Kartu Login Hotspot (Berdasarkan Kelas)</h3>
        <p class="text-gray-400 mb-6">Pilih jurusan terlebih dahulu, kemudian pilih kelas untuk melihat preview kartu dan mencetaknya dalam format PDF.</p>
        
        <div class="flex flex-col sm:flex-row items-end gap-4">
            <!-- Filter Jurusan -->
            <div class="w-full sm:w-1/3">
                <label for="jurusan-filter" class="block text-sm font-medium text-gray-300 mb-1">Pilih Jurusan</label>
                <select id="jurusan-filter" class="w-full bg-gray-700 border-gray-600 rounded-md p-2 text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Memuat jurusan...</option>
                </select>
            </div>
            
            <!-- Filter Kelas -->
            <div class="w-full sm:w-1/3">
                <label for="class-filter" class="block text-sm font-medium text-gray-300 mb-1">Pilih Kelas</label>
                <select id="class-filter" class="w-full bg-gray-700 border-gray-600 rounded-md p-2 text-white focus:ring-indigo-500 focus:border-indigo-500" disabled>
                    <option value="">Pilih jurusan dulu</option>
                </select>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="w-full sm:w-auto">
                <button id="generate-preview-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center" disabled>
                    <span>Buat Preview</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Bagian 2: Preview Semua Kartu -->
    <div class="bg-gray-800 p-6 rounded-lg shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h3 class="text-xl font-semibold">Preview Semua Kartu Pengguna</h3>
            <div class="flex items-center gap-2">
                <input type="text" id="all-cards-search" class="bg-gray-700 border-gray-600 rounded-md p-2 text-white hidden" placeholder="Cari nama atau username...">
                <button id="toggle-all-cards-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    Tampilkan Semua Kartu
                </button>
            </div>
        </div>
        <div id="all-cards-container" class="hidden mt-6 pt-6 border-t border-gray-700">
            <div id="all-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[70vh] overflow-y-auto pr-2">
                <!-- Preview semua kartu akan dimuat di sini -->
            </div>
        </div>
    </div>
</div>


<!-- Modal Preview Kartu (Tidak Berubah) -->
<div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col m-4">
        <div class="flex justify-between items-center p-4 border-b border-gray-700">
            <h3 class="text-xl font-bold text-white">Preview Kartu: <span id="preview-title"></span></h3>
            <button id="close-modal-btn" class="text-gray-400 hover:text-white text-3xl leading-none">&times;</button>
        </div>
        <div id="card-preview-container" class="p-6 flex-grow overflow-y-auto bg-gray-900 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- Kartu preview akan dimuat di sini -->
        </div>
        <div class="p-4 bg-gray-800 border-t border-gray-700 flex justify-end gap-3">
            <button id="cancel-modal-btn" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">Batal</button>
            <form id="print-pdf-form" action="../api/generate_pdf.php" method="GET" target="_blank">
                 <input type="hidden" name="jurusan" id="pdf-jurusan-input">
                 <input type="hidden" name="class" id="pdf-class-input">
                 <button type="submit" id="print-pdf-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    Cetak ke PDF
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Selektor Elemen ---
    const jurusanFilter = document.getElementById('jurusan-filter');
    const classFilter = document.getElementById('class-filter');
    const generateBtn = document.getElementById('generate-preview-btn');
    const modal = document.getElementById('preview-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelModalBtn = document.getElementById('cancel-modal-btn');
    const cardPreviewContainer = document.getElementById('card-preview-container');
    const previewTitle = document.getElementById('preview-title');
    const pdfJurusanInput = document.getElementById('pdf-jurusan-input');
    const pdfClassInput = document.getElementById('pdf-class-input');

    // --- Elemen Baru untuk Preview Semua Kartu ---
    const toggleAllCardsBtn = document.getElementById('toggle-all-cards-btn');
    const allCardsContainer = document.getElementById('all-cards-container');
    const allCardsGrid = document.getElementById('all-cards-grid');
    const allCardsSearch = document.getElementById('all-cards-search');
    let allCardsLoaded = false;
    let allUsersData = []; // Untuk menyimpan semua data pengguna untuk filtering

    // --- Fungsi Bantuan ---
    async function fetchData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();
            if (result.status !== 'success') throw new Error(result.message);
            return result.data;
        } catch (error) {
            console.error('Gagal mengambil data:', error);
            return null;
        }
    }

    function createCardHTML(user) {
        const qrCodeData = JSON.stringify({ user: user.name, pass: user.password });
        const userInfo = (user.jurusan && user.kelas)
            ? `<p class="text-xs text-gray-400 mt-1">${user.jurusan} - ${user.kelas}</p>`
            : '';

        return `
            <div class="bg-gray-700 rounded-lg p-4 border border-gray-600 text-center text-white flex flex-col">
                <h4 class="font-bold text-blue-400 text-lg">KARTU LOGIN WIFI</h4>
                <p class="text-sm font-semibold mt-1 truncate" title="${user.nama_lengkap || user.name}">${user.nama_lengkap || user.name}</p>
                ${userInfo}
                <div class="my-3 text-left text-sm space-y-1 font-mono">
                    <p><strong>Username:</strong> ${user.name}</p>
                    <p><strong>Password:</strong> ${user.password}</p>
                </div>
                <div class="flex-grow flex justify-center items-center mt-2 bg-white p-2 rounded-md">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrCodeData)}" alt="QR Code" />
                </div>
                <p class="text-xs text-gray-400 mt-2">Scan untuk login</p>
            </div>
        `;
    }

    // --- Logika untuk Filter per Kelas ---
    async function loadJurusan() {
        const data = await fetchData('../api/kartu_generator_api.php?action=get_all_jurusan');
        jurusanFilter.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
        if (data) data.forEach(jurusan => jurusanFilter.add(new Option(jurusan, jurusan)));
    }

    async function loadKelas(selectedJurusan) {
        classFilter.innerHTML = '<option value="">Memuat kelas...</option>';
        const data = await fetchData(`../api/kartu_generator_api.php?action=get_classes_by_jurusan&jurusan=${encodeURIComponent(selectedJurusan)}`);
        classFilter.innerHTML = '<option value="">-- Pilih Kelas --</option>';
        if (data && data.length > 0) {
            data.forEach(kelas => classFilter.add(new Option(kelas, kelas)));
        } else {
            classFilter.innerHTML = '<option value="">Tidak ada kelas</option>';
        }
    }

    // --- Logika untuk Preview Semua Kartu (BARU) ---
    function renderAllCards(users) {
        allCardsGrid.innerHTML = '';
        if (users && users.length > 0) {
            users.forEach(user => {
                allCardsGrid.insertAdjacentHTML('beforeend', createCardHTML(user));
            });
        } else {
            allCardsGrid.innerHTML = '<p class="text-center text-yellow-400 col-span-full">Tidak ada pengguna yang cocok dengan pencarian.</p>';
        }
    }

    toggleAllCardsBtn.addEventListener('click', async () => {
        const isHidden = allCardsContainer.classList.contains('hidden');
        
        if (isHidden) {
            allCardsContainer.classList.remove('hidden');
            allCardsSearch.classList.remove('hidden');
            toggleAllCardsBtn.textContent = 'Sembunyikan Kartu';

            if (!allCardsLoaded) {
                allCardsGrid.innerHTML = '<p class="text-center text-gray-400 col-span-full">Memuat semua data pengguna...</p>';
                const fetchedUsers = await fetchData('../api/active_users_api.php');
                
                if (fetchedUsers && fetchedUsers.length > 0) {
                    allUsersData = fetchedUsers;
                    renderAllCards(allUsersData);
                    allCardsLoaded = true;
                } else {
                    allCardsGrid.innerHTML = '<p class="text-center text-yellow-400 col-span-full">Gagal memuat data atau tidak ada pengguna.</p>';
                }
            }
        } else {
            allCardsContainer.classList.add('hidden');
            allCardsSearch.classList.add('hidden');
            toggleAllCardsBtn.textContent = 'Tampilkan Semua Kartu';
        }
    });

    allCardsSearch.addEventListener('input', () => {
        const searchTerm = allCardsSearch.value.toLowerCase();
        const filteredUsers = allUsersData.filter(user => 
            (user.nama_lengkap && user.nama_lengkap.toLowerCase().includes(searchTerm)) ||
            user.name.toLowerCase().includes(searchTerm)
        );
        renderAllCards(filteredUsers);
    });

    // --- Event Listeners Lainnya ---
    jurusanFilter.addEventListener('change', () => {
        const selectedJurusan = jurusanFilter.value;
        classFilter.innerHTML = '<option value="">Pilih jurusan dulu</option>';
        classFilter.disabled = true;
        generateBtn.disabled = true;

        if (selectedJurusan) {
            classFilter.disabled = false;
            loadKelas(selectedJurusan);
        }
    });

    classFilter.addEventListener('change', () => {
        generateBtn.disabled = !classFilter.value;
    });

    generateBtn.addEventListener('click', async () => {
        const selectedJurusan = jurusanFilter.value;
        const selectedClass = classFilter.value;
        if (!selectedJurusan || !selectedClass) return;

        previewTitle.textContent = `${selectedJurusan} - ${selectedClass}`;
        pdfJurusanInput.value = selectedJurusan;
        pdfClassInput.value = selectedClass;
        cardPreviewContainer.innerHTML = '<p class="text-center text-gray-400 col-span-full">Memuat data pengguna...</p>';
        modal.classList.remove('hidden');

        const users = await fetchData(`../api/kartu_generator_api.php?action=get_users_by_class&jurusan=${encodeURIComponent(selectedJurusan)}&class=${encodeURIComponent(selectedClass)}`); 
        
        cardPreviewContainer.innerHTML = '';
        if (users && users.length > 0) {
            users.forEach(user => {
                cardPreviewContainer.insertAdjacentHTML('beforeend', createCardHTML(user));
            });
        } else {
            cardPreviewContainer.innerHTML = `<p class="text-center text-yellow-400 col-span-full">Tidak ada pengguna yang ditemukan.</p>`;
        }
    });

    function hideModal() {
        modal.classList.add('hidden');
    }
    closeModalBtn.addEventListener('click', hideModal);
    cancelModalBtn.addEventListener('click', hideModal);

    // --- Inisialisasi ---
    loadJurusan();
});
</script>
