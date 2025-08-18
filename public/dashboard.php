<?php require_once __DIR__ . '/auth_check.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard — Mikrotik Monitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CDN untuk styling dan ikon -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Styling kustom untuk komponen dashboard -->
  <style>
    html, body { height: 100%; }
    .chart-container { position: relative; height: 100%; width: 100%; }
    /* Styling untuk kartu informasi umum */
    .spec-card {
        background-color: #1f2937;
        border: 1px solid #374151;
        padding: 0.75rem 0.5rem;
        border-radius: 0.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .spec-card-label {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-bottom: 0.25rem;
    }
    .spec-card-value {
        font-family: monospace;
        font-weight: 600;
        color: #e5e7eb;
        font-size: 0.875rem;
        line-height: 1.1;
    }
    /* Styling untuk kartu port interface */
    .port-card { cursor: pointer; transition: all 0.2s ease-in-out; }
    .port-card:hover, .port-card.active {
        transform: translateY(-2px);
        background-color: #374151;
        border-color: #4f46e5;
    }
    /* Styling khusus untuk kartu layanan */
    .service-card {
        background-color: #1f2937;
        border: 1px solid #374151;
        padding: 0.5rem;
        border-radius: 0.5rem;
        text-align: center;
    }
    .service-card-name {
        font-weight: 600;
        color: #e5e7eb;
    }
    .service-card-port {
        font-family: monospace;
        font-size: 0.75rem;
        color: #9ca3af;
    }
  </style>
</head>
<body class="bg-gray-900 text-gray-100">
  <div id="app" class="h-screen w-screen flex flex-col">
    <main class="flex-1 p-4 overflow-y-auto">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-full">
            <!-- Kolom Kiri (Grafik, Port, dan Layanan) -->
            <div class="lg:col-span-8 flex flex-col gap-4">
                <!-- Panel Grafik -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-800 rounded-lg p-4 flex flex-col h-80">
                        <h3 class="font-semibold mb-2 flex-shrink-0">Total Traffic Internet</h3>
                        <div class="chart-container flex-grow"><canvas id="chart_internet"></canvas></div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4 flex flex-col h-80">
                        <h3 id="interface-chart-title" class="font-semibold mb-2 flex-shrink-0">Traffic per Interface</h3>
                        <div class="chart-container flex-grow"><canvas id="chart_interface_detail"></canvas></div>
                    </div>
                </div>
                <!-- Panel untuk Port dan Layanan IP (Digabung) -->
                <div class="bg-gray-800 rounded-lg p-4 flex flex-col flex-1 min-h-0 overflow-y-auto">
                    <!-- Bagian Port -->
                    <div>
                        <h3 class="font-semibold mb-2 flex-shrink-0">Daftar Port & Traffic</h3>
                        <div id="portsArea" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                            <p>Memuat...</p>
                        </div>
                    </div>
                    <!-- Bagian Layanan IP -->
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <h3 class="font-semibold mb-2 flex-shrink-0">Layanan IP</h3>
                        <div id="serviceList" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                            <p>Memuat...</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kolom Kanan (Info Sistem) -->
            <div class="lg:col-span-4 bg-gray-800 rounded-lg p-4 flex flex-col space-y-4 overflow-y-auto">
                <!-- Header -->
                <div class="border-b border-gray-700 pb-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 id="routerName" class="text-xl font-bold leading-tight">Monitoring Mikrotik</h1>
                            <div class="text-sm text-gray-400">Status: <span id="statusText" class="font-semibold">—</span></div>
                        </div>
                        <a href="setup.php" class="ml-4 px-3 py-1 bg-indigo-600 rounded hover:bg-indigo-700 transition text-xs flex-shrink-0">Ubah</a>
                    </div>
                    <!-- ## AREA ERROR BARU ## -->
                    <div id="errorDetails" class="hidden mt-2 p-3 bg-red-900/50 border border-red-700 rounded-lg text-sm">
                        <p class="font-bold mb-1 text-red-300">Terjadi Kesalahan Koneksi</p>
                        <p id="errorMessage" class="font-mono text-white"></p>
                        <div class="mt-3 pt-2 border-t border-red-800/50">
                            <p class="font-semibold text-xs text-yellow-300">Tips Perbaikan:</p>
                            <ul class="list-disc list-inside text-xs text-yellow-200/80 mt-1 space-y-1">
                                <li>Pastikan IP, Username, dan Password di file `config/connection.json` sudah benar.</li>
                                <li>Cek koneksi jaringan dari server ke router (ping).</li>
                                <li>Pastikan port API (default: 8728) tidak diblokir oleh firewall.</li>
                                <li>Periksa apakah layanan 'api' di MikroTik aktif (`/ip service print`).</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Status Sistem (Donat Chart) -->
                <div>
                    <h3 class="text-lg font-semibold">Status Sistem</h3>
                    <div id="systemStatus" class="grid grid-cols-2 gap-4 mt-2">
                        <div class="relative text-center">
                            <canvas id="chart_cpu"></canvas>
                            <div id="cpu_percent_text" class="absolute inset-0 flex items-center justify-center text-xl font-bold text-white pointer-events-none"></div>
                            <p class="mt-2 text-sm">CPU Load</p>
                        </div>
                        <div class="relative text-center">
                            <canvas id="chart_ram"></canvas>
                            <div id="ram_percent_text" class="absolute inset-0 flex items-center justify-center text-xl font-bold text-white pointer-events-none"></div>
                            <p class="mt-2 text-sm">RAM Usage</p>
                        </div>
                    </div>
                </div>

                <!-- Spesifikasi Sistem -->
                <div>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Spesifikasi Sistem</h3>
                        <div id="realtime-clock" class="text-sm font-mono text-cyan-400"></div>
                    </div>
                    <div id="specs" class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2"><p>Memuat...</p></div>
                </div>
                
                <!-- Detail RouterBOARD -->
                <div>
                    <h3 class="text-lg font-semibold">Detail RouterBOARD</h3>
                    <div id="routerboardDetails" class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2"><p>Memuat...</p></div>
                </div>

                <!-- Hotspot & Pengguna Aktif -->
                <div class="mt-auto pt-3 border-t border-gray-700">
                     <div class="grid grid-cols-2 gap-4">
                        <div class="text-center bg-gray-900 p-3 rounded-lg">
                            <h4 class="font-semibold text-sm">Hotspot Aktif</h4>
                            <p id="hotspot_active_users" class="text-2xl font-bold text-teal-400">0</p>
                        </div>
                        <div class="text-center bg-gray-900 p-3 rounded-lg">
                            <h4 class="font-semibold text-sm">Pengguna Aktif</h4>
                            <p id="active_users_placeholder" class="text-2xl font-bold text-sky-400">N/A</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
  </div>

<!-- Memanggil file JavaScript eksternal -->
<script src="assets/js/dashboard.js" defer></script>

</body>
</html>
