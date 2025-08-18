<!-- Library untuk notifikasi & dialog modern -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="bg-gray-800 p-6 rounded-lg">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Pantauan Perangkat (DHCP Leases)</h3>
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
                    <th class="px-6 py-3">IP Address</th>
                    <th class="px-6 py-3">MAC Address</th>
                    <th class="px-6 py-3">Nama Host</th>
                    <th class="px-6 py-3">Pemilik/Keterangan</th>
                    <th class="px-6 py-3">Jenis Perangkat</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
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

    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
        timerProgressBar: true, background: '#1f2937', color: '#e5e7eb',
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    async function fetchDevices() {
        loadingIndicator.style.display = 'block';
        tableContainer.style.display = 'none';
        tableBody.innerHTML = '';

        try {
            // (INI PERBAIKANNYA) URL diubah ke hotspot_manager.php dengan action yang benar
            const response = await fetch('../api/hotspot_manager.php?action=get_dhcp_devices');
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');
            
            const result = await response.json();
            if (result.status !== 'success') throw new Error(result.message || 'Format respons tidak valid.');

            populateTable(result.data);
            
        } catch (error) {
            Toast.fire({ icon: 'error', title: error.message });
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-red-400">${error.message}</td></tr>`;
        } finally {
            loadingIndicator.style.display = 'none';
            tableContainer.style.display = 'block';
        }
    }

    function populateTable(devices) {
        if (devices.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Tidak ada perangkat yang terdeteksi di DHCP server.</td></tr>';
            return;
        }

        devices.forEach(device => {
            const statusClass = device.status === 'bound' ? 'bg-green-500/20 text-green-300' : 'bg-yellow-500/20 text-yellow-300';
            const statusText = device.status.charAt(0).toUpperCase() + device.status.slice(1);

            const row = `
                <tr class="bg-gray-800 border-b border-gray-700">
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">${statusText}</span></td>
                    <td class="px-6 py-4 font-mono text-white">${device.address}</td>
                    <td class="px-6 py-4 font-mono">${device['mac-address']}</td>
                    <td class="px-6 py-4">${device['host-name'] || '-'}</td>
                    <td class="px-6 py-4 font-semibold text-cyan-400">${device.owner_name || '-'}</td>
                    <td class="px-6 py-4">${device.device_type || '-'}</td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="editDevice(this)" 
                                data-mac="${device['mac-address']}" 
                                data-type="${device.device_type}" 
                                data-name="${device.owner_name}"
                                class="font-medium text-indigo-400 hover:underline">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    window.editDevice = async function(button) {
        const mac = button.dataset.mac;
        const currentType = button.dataset.type;
        const currentName = button.dataset.name;

        const { value: formValues } = await Swal.fire({
            title: 'Edit Label Perangkat',
            html: `
                <p class="text-sm text-gray-400 mb-4 font-mono">${mac}</p>
                <input id="swal-input-name" class="swal2-input" placeholder="Pemilik / Keterangan" value="${currentName}">
                <input id="swal-input-type" class="swal2-input" placeholder="Jenis Perangkat (HP, Komputer, dll)" value="${currentType}">
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            background: '#1f2937', color: '#e5e7eb',
            preConfirm: () => {
                return {
                    owner_name: document.getElementById('swal-input-name').value,
                    device_type: document.getElementById('swal-input-type').value
                }
            }
        });

        if (formValues) {
            const formData = new FormData();
            formData.append('mac_address', mac);
            formData.append('owner_name', formValues.owner_name);
            formData.append('device_type', formValues.device_type);

            try {
                // (INI PERBAIKANNYA) URL diubah ke hotspot_manager.php dengan action yang benar
                const response = await fetch('../api/hotspot_manager.php?action=save_device_label', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status !== 'success') throw new Error(result.message);
                
                Toast.fire({ icon: 'success', title: 'Label berhasil disimpan!' });
                fetchDevices(); // Refresh tabel
            } catch (error) {
                Toast.fire({ icon: 'error', title: error.message });
            }
        }
    }

    refreshBtn.addEventListener('click', fetchDevices);
    fetchDevices(); // Muat data saat halaman pertama kali dibuka
});
</script>
