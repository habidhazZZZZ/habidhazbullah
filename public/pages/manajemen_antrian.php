<?php
// Keamanan: Mencegah akses langsung ke file ini
if (!defined('IS_INCLUDED')) {
    die("Akses langsung tidak diizinkan.");
}
?>
<div class="bg-gray-800 rounded-lg p-4">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Target</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Max Limit (UL/DL)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Traffic (UL/DL)</th>
                    <th class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody id="queues-table-body" class="divide-y divide-gray-600">
                <tr><td colspan="5" class="text-center py-4 text-gray-400">Memuat data antrian...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const queueBody = document.getElementById('queues-table-body');
    let queueInterval;

    // Fungsi untuk memformat rate (bps, Kbps, Mbps)
    function formatRate(bits) {
        bits = parseInt(bits, 10);
        if (isNaN(bits) || bits === 0) return '0 bps';
        if (bits < 1000) return `${bits} bps`;
        if (bits < 1000000) return `${(bits / 1000).toFixed(1)} Kbps`;
        return `${(bits / 1000000).toFixed(2)} Mbps`;
    }

    // Fungsi BARU untuk memformat max-limit (M, G)
    function formatMaxLimit(limit) {
        if (!limit || limit === '0') return '0';
        const value = parseInt(limit, 10);
        if (limit.toUpperCase().endsWith('K')) return `${value / 1000}M`;
        if (limit.toUpperCase().endsWith('M')) return `${value}M`;
        if (limit.toUpperCase().endsWith('G')) return `${value}G`;
        // Default jika hanya angka (dianggap bps)
        return formatRate(value);
    }

    function fetchQueues() {
        fetch('../api/data_fetcher.php')
            .then(res => res.ok ? res.json() : Promise.reject('Gagal memuat data'))
            .then(data => {
                if (data.status !== 'success') throw new Error(data.message);
                if (!data.queues) throw new Error('Data antrian tidak ditemukan di respons API.');
                
                if (data.queues.length === 0) {
                    queueBody.innerHTML = `<tr><td colspan="5" class="text-center py-4">Tidak ada simple queue.</td></tr>`;
                    return;
                }
                
                if(queueBody.querySelector('td[colspan="5"]')) queueBody.innerHTML = '';

                data.queues.forEach(q => {
                    const disabled = q.disabled === 'true';
                    const [uploadRate, downloadRate] = (q.rate || "0/0").split('/');
                    const [uploadLimit, downloadLimit] = (q['max-limit'] || "0/0").split('/');
                    
                    const rowId = `queue-${q['.id']}`;
                    let row = document.getElementById(rowId);

                    if (!row) {
                        row = document.createElement('tr');
                        row.id = rowId;
                        queueBody.appendChild(row);
                    }
                    
                    row.className = disabled ? 'opacity-50' : '';
                    row.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap text-white font-semibold">${q.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap font-mono">${q.target}</td>
                        <td class="px-4 py-2 whitespace-nowrap font-mono">${formatMaxLimit(uploadLimit)} / ${formatMaxLimit(downloadLimit)}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <span class="text-red-400"><i class="bi bi-arrow-up"></i> ${formatRate(uploadRate)}</span> / 
                            <span class="text-green-400"><i class="bi bi-arrow-down"></i> ${formatRate(downloadRate)}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button 
                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer transition-transform transform hover:scale-110 ${disabled ? 'bg-red-800 text-red-100' : 'bg-green-800 text-green-100'}"
                                onclick="toggleQueueStatus('${q['.id']}', '${q.disabled}')"
                                title="Klik untuk ${disabled ? 'mengaktifkan' : 'menonaktifkan'}">
                                ${disabled ? 'Disabled' : 'Enabled'}
                            </button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error("Fetch error:", error);
                queueBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-red-400">${error.message}</td></tr>`;
                if(queueInterval) clearInterval(queueInterval);
            });
    }

    // Fungsi BARU untuk toggle status
    window.toggleQueueStatus = async function(id, currentStatus) {
        const actionText = currentStatus === 'true' ? 'mengaktifkan' : 'menonaktifkan';
        
        const result = await Swal.fire({
            title: `Anda yakin?`,
            text: `Anda akan ${actionText} antrian ini.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Ya, ${actionText}!`,
            cancelButtonText: 'Batal',
            background: '#1f2937', color: '#e5e7eb'
        });

        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', currentStatus);

            try {
                const response = await fetch('../api/queue_manager.php?action=toggle_status', {
                    method: 'POST',
                    body: formData
                });
                const resJson = await response.json();
                if (resJson.status !== 'success') throw new Error(resJson.message);
                
                Swal.fire({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                    icon: 'success', title: resJson.message, background: '#1f2937', color: '#e5e7eb'
                });
                
                fetchQueues(); // Langsung refresh data
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    }

    fetchQueues();
    queueInterval = setInterval(fetchQueues, 3000);
});
</script>
