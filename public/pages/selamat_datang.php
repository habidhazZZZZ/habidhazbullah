<?php require_once __DIR__ . '/../auth_check.php'; ?>

<!-- Styling khusus untuk kartu -->
<style>
    /* Styling untuk kartu spesifikasi sistem */
    .spec-card-item {
        background-color: #1f2937; /* bg-gray-800 */
        border: 1px solid #374151; /* border-gray-700 */
        padding: 1rem; /* p-4 */
        border-radius: 0.5rem; /* rounded-lg */
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .spec-label {
        font-size: 0.75rem; /* text-xs */
        color: #9ca3af; /* text-gray-400 */
        margin-bottom: 0.25rem; /* mb-1 */
        text-transform: uppercase;
        font-weight: 600;
    }
    .spec-value {
        font-family: monospace;
        font-weight: 600; /* font-semibold */
        color: #e5e7eb; /* text-gray-200 */
        font-size: 1rem; /* text-base */
        line-height: 1.2;
    }

    /* Styling untuk kartu port */
    .port-card {
        background-color: #1f2937;
        border: 1px solid #374151;
        padding: 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease-in-out;
    }
    .port-card.status-on {
        border-left: 4px solid #22c55e;
    }
    .port-card.status-off {
        border-left: 4px solid #ef4444;
        opacity: 0.7;
    }
    .port-name {
        font-weight: 600;
        color: #e5e7eb;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .port-ip {
        font-family: monospace;
        font-size: 0.75rem;
        color: #60a5fa;
    }
    .port-traffic {
        font-family: monospace;
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.5rem;
        /* Tambahkan transisi untuk update yang lebih mulus */
        transition: color 0.5s ease;
    }
    .status-badge {
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.on {
        background-color: #166534;
        color: #bbf7d0;
    }
    .status-badge.off {
        background-color: #991b1b;
        color: #fecaca;
    }
</style>

<!-- Bagian Spesifikasi Sistem (Layout Baru) -->
<div class="bg-gray-800 p-6 rounded-lg">
    <h3 class="text-xl font-semibold mb-4">Spesifikasi Sistem</h3>
    <div id="specsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <!-- Kartu-kartu spesifikasi akan dimuat oleh JavaScript -->
        <p class="text-gray-400 col-span-full">Memuat data sistem...</p>
    </div>
</div>

<!-- Bagian Daftar Port & Traffic -->
<div class="bg-gray-800 p-6 rounded-lg mt-6">
    <h3 class="text-xl font-semibold mb-4">Daftar Port & Traffic</h3>
    <div id="portsArea" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        <!-- Kartu-kartu port akan dimuat oleh JavaScript -->
        <p class="text-gray-400 col-span-full">Memuat data port...</p>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    // Variabel untuk menyimpan interval agar bisa dihentikan jika perlu
    let dataFetchInterval;

    function fetchData() {
        fetch('../api/data_fetcher.php')
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (data.status !== 'success') {
                    if(dataFetchInterval) clearInterval(dataFetchInterval);
                    throw new Error(data.message || 'Gagal mengambil data dari API');
                }

                // 1. Proses dan tampilkan Spesifikasi Sistem dalam bentuk Kartu
                // PERBAIKAN ERROR: Gunakan default value ('-') jika data tidak ada untuk mencegah error
                const specsContainer = document.getElementById('specsGrid');
                if (data.system) {
                    const systemData = {
                        "Model": data.system.cpu_model || '-',
                        "Versi": data.system.version || '-',
                        "Uptime": data.system.uptime || '-',
                        "Total RAM": `${data.system.ram_total || '0'} MB`,
                        "CPU Freq": `${data.system.cpu_frequency || '0'} MHz`,
                        "Arsitektur": data.system.arch || '-',
                        "Serial": data.system.serial_number || '-',
                        "Waktu Router": data.system.router_time || '-'
                    };

                    const newSpecsHTML = Object.entries(systemData).map(([label, value]) => `
                        <div class="spec-card-item">
                            <div class="spec-label">${label}</div>
                            <div class="spec-value">${value}</div>
                        </div>
                    `).join('');
                    
                    if (specsContainer.innerHTML !== newSpecsHTML) {
                        specsContainer.innerHTML = newSpecsHTML;
                    }
                } else {
                    specsContainer.innerHTML = `<p class="text-red-400 col-span-full">Gagal memuat data sistem.</p>`;
                }

                // 2. Proses dan tampilkan Daftar Port secara efisien
                const portsContainer = document.getElementById('portsArea');
                if (data.ports && Array.isArray(data.ports)) {
                    if (data.ports.length === 0) {
                        portsContainer.innerHTML = `<p class="text-gray-400 col-span-full">Tidak ada port ditemukan.</p>`;
                    } else {
                        data.ports.forEach(port => {
                            const portId = `port-${port.name.replace(/[^a-zA-Z0-9]/g, '-')}`;
                            let portElement = document.getElementById(portId);

                            if (!portElement) {
                                // Jika elemen port belum ada, buat baru
                                const isRunning = port.status === 'true';
                                const statusClass = isRunning ? 'status-on' : 'status-off';
                                const badgeClass = isRunning ? 'on' : 'off';
                                const badgeText = isRunning ? 'On' : 'Off';
                                const ipAddress = port.ip_address ? `<div class="port-ip">${port.ip_address}</div>` : '';

                                const portHTML = `
                                    <div class="flex justify-between items-start">
                                        <div class="port-name" title="${port.name}">${port.name}</div>
                                        <div class="status-badge ${badgeClass}">${badgeText}</div>
                                    </div>
                                    ${ipAddress}
                                    <div class="port-traffic">
                                        <div class="tx-value">TX: ${port.tx_mbps} Mbps</div>
                                        <div class="rx-value">RX: ${port.rx_mbps} Mbps</div>
                                    </div>
                                `;
                                
                                const newPortDiv = document.createElement('div');
                                newPortDiv.id = portId;
                                newPortDiv.className = `port-card ${statusClass}`;
                                newPortDiv.innerHTML = portHTML;
                                
                                if(portsContainer.querySelector('p')) {
                                    portsContainer.innerHTML = '';
                                }
                                portsContainer.appendChild(newPortDiv);

                            } else {
                                // Jika elemen sudah ada, update saja nilainya
                                const txElement = portElement.querySelector('.tx-value');
                                const rxElement = portElement.querySelector('.rx-value');
                                txElement.textContent = `TX: ${port.tx_mbps} Mbps`;
                                rxElement.textContent = `RX: ${port.rx_mbps} Mbps`;
                                
                                // Update warna jika ada traffic
                                const trafficDiv = portElement.querySelector('.port-traffic');
                                if (port.tx_mbps > 0 || port.rx_mbps > 0) {
                                    trafficDiv.style.color = '#6ee7b7'; // Hijau terang
                                } else {
                                    trafficDiv.style.color = '#9ca3af'; // Abu-abu
                                }
                            }
                        });
                    }
                } else {
                     portsContainer.innerHTML = `<p class="text-red-400 col-span-full">Gagal memuat data port.</p>`;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if(dataFetchInterval) clearInterval(dataFetchInterval);
                document.getElementById('specsGrid').innerHTML = `<p class="text-red-400 col-span-full">Terjadi kesalahan: ${error.message}</p>`;
                document.getElementById('portsArea').innerHTML = `<p class="text-red-400 col-span-full">Terjadi kesalahan: ${error.message}</p>`;
            });
    }

    // Panggil fungsi pertama kali saat halaman dimuat
    fetchData();
    
    // Atur interval untuk memanggil fetchData setiap 3 detik
    dataFetchInterval = setInterval(fetchData, 3000);
});
</script>
