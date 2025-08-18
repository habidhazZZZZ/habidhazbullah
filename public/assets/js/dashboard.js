// File: assets/js/dashboard.js (Versi Final dengan Perbaikan)

// --- Konfigurasi dan Variabel Global ---
const POLL_INTERVAL = 3000; // Interval pengambilan data (ms), 3 detik
let poller, clockInterval; 
let charts = {}; 
let activeInterfaceChart = ''; 

// --- Fungsi Bantuan ---
function formatBps(bps) {
    bps = Number(bps) || 0;
    if (bps < 1000) return `${bps} bps`;
    if (bps < 1000000) return `${(bps / 1000).toFixed(2)} Kbps`;
    return `${(bps / 1000000).toFixed(2)} Mbps`;
}

function formatBytes(bytes) {
    bytes = Number(bytes) || 0;
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// --- Fungsi-Fungsi Grafik ---
function createChart(ctx, type, options, datasets) {
    return new Chart(ctx, { type, data: { labels: [], datasets }, options });
}

function initCharts() {
    const commonLineOptions = {
        responsive: true, maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, ticks: { color: '#9ca3af', callback: (value) => formatBps(value * 1000000) }},
            x: { ticks: { color: '#9ca3af' } }
        },
        plugins: {
            legend: { labels: { color: '#d1d5db' } },
            tooltip: { callbacks: { label: (context) => `${context.dataset.label || ''}: ${context.parsed.y.toFixed(2)} Mbps` }}
        }
    };
    const doughnutOptions = {
        responsive: true, cutout: '70%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } }
    };

    charts.internet = createChart(document.getElementById('chart_internet').getContext('2d'), 'line', commonLineOptions, [
        { label: 'TX (Upload)', data: [], borderColor: '#34d399', backgroundColor: '#34d39920', tension: 0.3, borderWidth: 2, pointRadius: 0 },
        { label: 'RX (Download)', data: [], borderColor: '#60a5fa', backgroundColor: '#60a5fa20', tension: 0.3, borderWidth: 2, pointRadius: 0 }
    ]);
    charts.interfaceDetail = createChart(document.getElementById('chart_interface_detail').getContext('2d'), 'line', commonLineOptions, [
        { label: 'TX', data: [], borderColor: '#f472b6', backgroundColor: '#f472b620', tension: 0.3, borderWidth: 2, pointRadius: 0 },
        { label: 'RX', data: [], borderColor: '#818cf8', backgroundColor: '#818cf820', tension: 0.3, borderWidth: 2, pointRadius: 0 }
    ]);
    charts.cpu = createChart(document.getElementById('chart_cpu').getContext('2d'), 'doughnut', doughnutOptions, [
        { label: 'Load', data: [0, 100], backgroundColor: ['#f87171', '#374151'], borderWidth: 0 }
    ]);
    charts.ram = createChart(document.getElementById('chart_ram').getContext('2d'), 'doughnut', doughnutOptions, [
        { label: 'Used', data: [0, 100], backgroundColor: ['#fb923c', '#374151'], borderWidth: 0 }
    ]);
}

// --- Fungsi-Fungsi Update UI ---
function createSpecCard(label, value) {
    return `<div class="spec-card"><div class="spec-card-label">${label}</div><div class="spec-card-value" title="${value || '-'}">${value || '-'}</div></div>`;
}

function updateUI(data) {
    document.getElementById('errorDetails').classList.add('hidden');
    document.getElementById('routerName').innerText = data.identity || 'Monitoring Mikrotik';
    document.getElementById('statusText').innerText = 'Terhubung';
    document.getElementById('statusText').style.color = '#34d399';

    const specsContainer = document.getElementById('specs');
    specsContainer.innerHTML = [
        createSpecCard('Model', data.system.board_name),
        createSpecCard('Versi', data.system.version),
        createSpecCard('Uptime', data.system.uptime),
        createSpecCard('CPU', data.system.cpu),
        createSpecCard('Total RAM', formatBytes(data.system.total_ram)),
        createSpecCard('Arsitektur', data.system.architecture)
    ].join('');

    const rbContainer = document.getElementById('routerboardDetails');
    rbContainer.innerHTML = [
        createSpecCard('RB Model', data.routerboard.model),
        createSpecCard('Serial', data.routerboard['serial-number']),
        createSpecCard('Firmware', data.routerboard['current-firmware'])
    ].join('');

    const serviceContainer = document.getElementById('serviceList');
    serviceContainer.innerHTML = data.services.map(service => {
        const isDisabled = service.disabled === 'true';
        return `<div class="service-card" title="${service.name} - Port ${service.port} - ${isDisabled ? 'Disabled' : 'Enabled'}"><div class="service-card-name truncate">${service.name}</div><div class="service-card-port">port ${service.port}</div><div class="text-xs font-semibold px-2 py-0.5 rounded-full mt-1 ${isDisabled ? 'bg-red-900/50 text-red-400' : 'bg-green-900/50 text-green-400'}">${isDisabled ? 'Off' : 'On'}</div></div>`;
    }).join('');

    const ports = data.interfaces.map(iface => ({
        name: iface.name,
        status: iface.disabled === 'true' ? 'false' : 'true',
        tx_mbps: parseFloat((iface.tx / 1000000).toFixed(2)),
        rx_mbps: parseFloat((iface.rx / 1000000).toFixed(2))
    }));

    const portsArea = document.getElementById('portsArea');
    portsArea.innerHTML = ports.map(port => {
        const isActive = port.name === activeInterfaceChart;
        return `<div class="port-card p-2 rounded-lg border flex flex-col ${port.status === 'true' ? 'bg-gray-900 border-gray-700' : 'bg-red-900/30 border-red-800/50'} ${isActive ? 'active' : ''}" data-interface-name="${port.name}"><div><div class="flex justify-between items-center text-sm"><span class="font-bold truncate" title="${port.name}">${port.name}</span><span class="text-xs font-semibold px-1.5 py-0.5 rounded-full ${port.status === 'true' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'}">${port.status === 'true' ? 'On' : 'Off'}</span></div><div class="h-4"></div><div class="text-xs text-gray-400 mt-1"><span>TX: ${formatBps(port.tx_mbps*1000000)}</span> | <span>RX: ${formatBps(port.rx_mbps*1000000)}</span></div></div></div>`;
    }).join('');
    
    document.getElementById('hotspot_active_users').innerText = data.hotspot_active_users || 0;
    updateCharts(data, ports);
}

function updateCharts(data, ports) {
    const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    const total_tx_mbps = parseFloat(((data.total_traffic.tx || 0) / 1000000).toFixed(2));
    const total_rx_mbps = parseFloat(((data.total_traffic.rx || 0) / 1000000).toFixed(2));
    updateLineChart(charts.internet, time, [total_tx_mbps, total_rx_mbps]);

    const activePort = ports.find(p => p.name === activeInterfaceChart);
    if (activePort) {
        document.getElementById('interface-chart-title').innerText = `Traffic: ${activePort.name}`;
        updateLineChart(charts.interfaceDetail, time, [activePort.tx_mbps, activePort.rx_mbps]);
    } else if (ports.length > 0 && !activeInterfaceChart) {
        switchActiveInterface(ports[0].name);
    }
    
    const ram_percent = Math.round((data.system.used_ram / data.system.total_ram) * 100);
    updateDoughnutChart(charts.cpu, data.system.cpu_load, 'cpu_percent_text');
    updateDoughnutChart(charts.ram, ram_percent, 'ram_percent_text');
}

function updateLineChart(chart, label, dataPoints) {
    chart.data.labels.push(label);
    dataPoints.forEach((point, index) => chart.data.datasets[index].data.push(point));
    
    // PENYESUAIAN: Jumlah data di grafik diubah dari 20 menjadi 60
    if (chart.data.labels.length > 60) {
        chart.data.labels.shift();
        chart.data.datasets.forEach(ds => ds.data.shift());
    }
    chart.update('none');
}

function updateDoughnutChart(chart, value, textElementId) {
    value = Number(value) || 0;
    chart.data.datasets[0].data = [value, 100 - value];
    chart.update('none');
    document.getElementById(textElementId).innerText = `${value}%`;
}

function switchActiveInterface(interfaceName) {
    activeInterfaceChart = interfaceName;
    charts.interfaceDetail.data.labels = [];
    charts.interfaceDetail.data.datasets.forEach(ds => ds.data = []);
    charts.interfaceDetail.update();
    document.querySelectorAll('.port-card').forEach(card => {
        card.classList.toggle('active', card.dataset.interfaceName === interfaceName);
    });
}

async function pollOnce() {
    try {
        const response = await fetch('../api/device_pantauan_manager.php?action=get_dashboard_data');
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server merespons dengan status: ${response.status}`);
        }
        const data = await response.json();
        if (data.status === 'error') {
            throw new Error(data.message);
        }
        updateUI(data);
    } catch (error) {
        console.error('Gagal mengambil data:', error);
        clearInterval(poller);
        clearInterval(clockInterval);
        document.getElementById('statusText').innerText = 'Error';
        document.getElementById('statusText').style.color = '#f87171';
        const errorDetails = document.getElementById('errorDetails');
        if (errorDetails) {
            errorDetails.classList.remove('hidden');
            // PERBAIKAN: Posisi '}' yang benar
            document.getElementById('errorMessage').innerText = error.message;
        }
    }
}

// --- Event Listener ---
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    pollOnce();
    poller = setInterval(pollOnce, POLL_INTERVAL);
    
    const clockElem = document.getElementById('realtime-clock');
    clockInterval = setInterval(() => {
        clockElem.innerText = new Date().toLocaleTimeString('id-ID');
    }, 1000);

    document.getElementById('portsArea').addEventListener('click', (e) => {
        const card = e.target.closest('.port-card');
        if (card && card.dataset.interfaceName) {
            switchActiveInterface(card.dataset.interfaceName);
        }
    });
});