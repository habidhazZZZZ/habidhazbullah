<?php
// Keamanan: Mencegah akses langsung ke file ini
if (!defined('IS_INCLUDED')) {
    die("Akses langsung tidak diizinkan.");
}
?>
<div class="bg-gray-800 p-6 rounded-lg">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Daftar Perangkat Tersimpan</h3>
        <button id="refresh-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
            <i class="bi bi-arrow-clockwise"></i> Segarkan
        </button>
    </div>
    
    <div id="loading-indicator" class="text-center py-8">
        <p class="text-gray-400">Memuat data perangkat...</p>
    </div>

    <div id="table-container" class="overflow-x-auto hidden">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-300 uppercase bg-gray-700">
                <tr>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Nama Perangkat</th>
                    <th class="px-6 py-3">IP Address</th>
                    <th class="px-6 py-3">User API</th>
                    <th class="px-6 py-3">Port API</th>
                </tr>
            </thead>
            <tbody id="device-table-body">
                <!-- Data akan diisi oleh JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('device-table-body');
    const loadingIndicator = document.getElementById('loading-indicator');
    const tableContainer = document.getElementById('table-container');
    const refreshBtn = document.getElementById('refresh-btn');

    async function fetchSavedDevices() {
        loadingIndicator.style.display = 'block';
        tableContainer.style.display = 'none';
        tableBody.innerHTML = '';

        try {
            const response = await fetch('../api/get_saved_devices.php');
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');
            
            const result = await response.json();
            if (result.status !== 'success') throw new Error(result.message);

            populateTable(result.data);
            
        } catch (error) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-red-400">${error.message}</td></tr>`;
        } finally {
            loadingIndicator.style.display = 'none';
            tableContainer.style.display = 'block';
        }
    }

    function populateTable(devices) {
        if (!devices || devices.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Anda belum menyimpan perangkat apapun.</td></tr>';
            return;
        }

        devices.forEach(device => {
            const statusClass = device.is_active ? 'bg-green-500/20 text-green-300' : 'bg-gray-500/20 text-gray-300';
            const statusText = device.is_active ? 'Aktif' : 'Non-Aktif';
            const rowClass = device.is_active ? 'bg-gray-700/50' : 'bg-gray-800';

            const row = `
                <tr class="${rowClass} border-b border-gray-700">
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">${statusText}</span></td>
                    <td class="px-6 py-4 font-semibold text-white">${device.device_name}</td>
                    <td class="px-6 py-4 font-mono">${device.host}</td>
                    <td class="px-6 py-4 font-mono">${device.api_user}</td>
                    <td class="px-6 py-4 font-mono">${device.api_port}</td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    refreshBtn.addEventListener('click', fetchSavedDevices);
    fetchSavedDevices();
});
</script>
