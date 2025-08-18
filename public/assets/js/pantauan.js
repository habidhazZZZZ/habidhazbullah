// File: assets/js/pantauan.js

// --- Konfigurasi dan Variabel Global ---
const POLL_INTERVAL_PANTAUAN = 5000; // Interval pengambilan data (ms), 5 detik
let pollerPantauan, clockIntervalPantauan; // Variabel untuk menyimpan interval
let chartsPantauan = {}; // Objek untuk menyimpan semua instance grafik

// --- Fungsi-Fungsi Utama ---

/**
 * Membuat instance grafik Chart.js baru.
 */
function createChartPantauan(ctx, type, options, datasets) {
    return new Chart(ctx, { type, data: { labels: [], datasets }, options });
}

/**
 * Menginisialisasi semua grafik pada saat halaman dimuat.
 */
function initChartsPantauan() {
    // Opsi umum untuk grafik donat (doughnut chart)
    const doughnutOptions = {
        responsive: true,
        cutout: '70%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } }
    };

    // Inisialisasi setiap grafik
    chartsPantauan.cpu = createChartPantauan(document.getElementById('chart_cpu').getContext('2d'), 'doughnut', doughnutOptions, [
        { label: 'Load', data: [0, 100], backgroundColor: ['#f87171', '#374151'], borderWidth: 0 }
    ]);
    chartsPantauan.ram = createChartPantauan(document.getElementById('chart_ram').getContext('2d'), 'doughnut', doughnutOptions, [
        { label: 'Used', data: [0, 100], backgroundColor: ['#fb923c', '#374151'], borderWidth: 0 }
    ]);
}

/**
 * Membuat HTML untuk satu kartu informasi.
 */
function createSpecCardPantauan(label, value) {
    return `
        <div class="spec-card">
            <div class="spec-card-label">${label}</div>
            <div class="spec-card-value" title="${value || '-'}">${value || '-'}</div>
        </div>
    `;
}

/**
 * Memperbarui semua elemen UI dengan data baru dari API.
 */
function updateUIPantauan(data) {
    // Update kartu spesifikasi sistem
    const specsContainer = document.getElementById('specs');
    const specData = {
        'Model': data.system.cpu_model,
        'Versi': data.system.version,
        'Uptime': data.system.uptime,
        'CPU Temp': data.system.cpu_temp,
        'Total RAM': `${data.system.ram_total} MB`,
        'Arsitektur': data.system.arch
    };
    specsContainer.innerHTML = Object.entries(specData)
        .map(([label, value]) => createSpecCardPantauan(label, value))
        .join('');
    
    // Update kartu detail RouterBOARD
    if (data.routerboard) {
        const rbContainer = document.getElementById('routerboardDetails');
        const rbData = {
            'RB Model': data.routerboard.model,
            'Revisi': data.routerboard.revision,
            'Serial': data.routerboard['serial-number'],
            'Firmware': data.routerboard['current-firmware'],
            'FW Upgrade': data.routerboard['upgrade-firmware'],
        };
        rbContainer.innerHTML = Object.entries(rbData)
            .map(([label, value]) => createSpecCardPantauan(label, value))
            .join('');
    }

    // Update kartu Layanan IP
    if (data.services && data.services.length > 0) {
        const serviceContainer = document.getElementById('serviceList');
        serviceContainer.innerHTML = data.services.map(service => {
            const isDisabled = service.disabled === 'true';
            const statusClass = isDisabled ? 'bg-red-500/30 text-red-300' : 'bg-green-500/30 text-green-300';
            return `
                <div class="service-card" title="${service.name} - Port ${service.port} - ${isDisabled ? 'Disabled' : 'Enabled'}">
                    <div class="service-card-name truncate">${service.name}</div>
                    <div class="service-card-port">port ${service.port}</div>
                    <div class="text-xs font-semibold px-2 py-0.5 rounded-full mt-1 ${statusClass}">
                        ${isDisabled ? 'Off' : 'On'}
                    </div>
                </div>
            `;
        }).join('');
    }

    // Update kartu port
    const portsArea = document.getElementById('portsArea');
    portsArea.innerHTML = ''; // Kosongkan area sebelum mengisi ulang
    (data.ports || []).forEach(port => {
        const ip_info = port.ip_address ? `<div class="text-cyan-400 font-mono text-xs truncate" title="${port.ip_address}">${port.ip_address}</div>` : '<div class="h-4"></div>';
        
        const portCard = document.createElement('div');
        portCard.className = `port-card p-2 rounded-lg border flex flex-col ${port.status === 'true' ? 'bg-gray-900 border-gray-700' : 'bg-red-900/30 border-red-800/50'}`;
        portCard.dataset.interfaceName = port.name;
        
        portCard.innerHTML = `
          <div>
            <div class="flex justify-between items-center text-sm">
              <span class="font-bold truncate" title="${port.name}">${port.name}</span>
              <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full ${port.status === 'true' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'}">${port.status === 'true' ? 'On' : 'Off'}</span>
            </div>
            ${ip_info}
            <div class="text-xs text-gray-400 mt-1">
              <span>TX: ${port.tx_mbps} Mbps</span> | <span>RX: ${port.rx_mbps} Mbps</span>
            </div>
          </div>
          `;
        portsArea.appendChild(portCard);
    });
    
    // Update status lainnya
    document.getElementById('hotspot_active_users').innerText = data.hotspot.active_users || 0;
    
    // Update grafik
    updateDoughnutChartPantauan(chartsPantauan.cpu, data.system.cpu_load_percent, 'cpu_percent_text');
    updateDoughnutChartPantauan(chartsPantauan.ram, data.system.ram_percent, 'ram_percent_text');
}

/**
 * Memperbarui data pada grafik donat dan teks persentasenya.
 */
function updateDoughnutChartPantauan(chart, value, textElementId) {
    if (chart && document.getElementById(textElementId)) {
        chart.data.datasets[0].data = [value, 100 - value];
        chart.update('none');
        document.getElementById(textElementId).innerText = `${value}%`;
    }
}

/**
 * Mengambil data dari API satu kali dan memperbarui UI.
 */
async function pollOncePantauan() {
  try {
    // Path disesuaikan karena file ini dipanggil dari index.php di root
    const response = await fetch('api/data_fetcher.php'); 
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || `Server merespons dengan status: ${response.status}`);
    }
    const data = await response.json();
    updateUIPantauan(data);
  } catch (error) {
    console.error('Gagal mengambil data pantauan:', error);
    // Hentikan polling jika terjadi error fatal
    if(pollerPantauan) clearInterval(pollerPantauan);
    if(clockIntervalPantauan) clearInterval(clockIntervalPantauan);
  }
}

// --- Event Listener ---
// Pastikan listener ini unik untuk halaman pantauan
document.addEventListener('DOMContentLoaded', () => {
    // Hanya jalankan jika kita berada di halaman pantauan perangkat
    if (document.querySelector('#systemStatus')) {
        initChartsPantauan();
        pollOncePantauan();
        pollerPantauan = setInterval(pollOncePantauan, POLL_INTERVAL_PANTAUAN);
        
        const clockElem = document.getElementById('realtime-clock');
        if (clockElem) {
            clockIntervalPantauan = setInterval(() => {
                clockElem.innerText = new Date().toLocaleTimeString('id-ID');
            }, 1000);
        }
    }
});
