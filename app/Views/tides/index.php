<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Data Pasang Surut SRGI') ?> | R-Pasoet</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
    .glass-card {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(226, 232, 240, 0.9);
    }
    .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f8fafc; }
    .flatpickr-calendar {
      border-radius: 1rem;
      border: 1px solid #e2e8f0;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
      background: #0284c7 !important;
      border-color: #0284c7 !important;
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

  <!-- TOP APP BAR -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="<?= site_url('tides') ?>" class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-600 flex items-center justify-center text-white shadow-md shadow-sky-600/20 hover:scale-105 transition">
          <i class="fa-solid fa-water text-lg"></i>
        </a>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-extrabold text-xl tracking-tight text-slate-900">R-PASOET</span>
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-cyan-100 text-cyan-800 border border-cyan-200">SRGI Live Portal</span>
          </div>
          <p class="text-xs text-slate-500">Live Ocean Observation & Fetcher BIG</p>
        </div>
      </div>

      <!-- Navigation Links (Integrated System Tabs) -->
      <div class="flex items-center gap-2">
        <a href="<?= site_url('tides') ?>" class="text-xs font-bold text-sky-700 bg-sky-50 border border-sky-200 px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-xs">
          <i class="fa-solid fa-cloud-arrow-down"></i>
          <span>Fetch Tide SRGI</span>
        </a>
        <a href="<?= site_url('admiralty') ?>" class="text-xs font-semibold text-slate-600 hover:text-sky-600 px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition flex items-center gap-1.5">
          <i class="fa-solid fa-chart-line text-sky-600"></i>
          <span>Tide Predictor (Admiralty)</span>
        </a>
      </div>
    </div>
  </header>

  <!-- MAIN CONTAINER -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full flex flex-col gap-6">

    <!-- HERO BANNER -->
    <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white relative overflow-hidden">
      <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
        <div class="max-w-3xl flex flex-col gap-2">
          <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
              Ocean Observation Workspace
            </span>
            <span class="text-xs text-slate-400">• Standar BIG & SRGI</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
            Pengambilan Data Pasang Surut SRGI
          </h1>
          <p class="text-sm text-slate-300 leading-relaxed">
            Ambil data pasut stasiun pasang surut Badan Informasi Geospasial (BIG) secara langsung. Data observasi dapat langsung diinspeksi, diunduh, atau <strong>ditransfer ke Tide Predictor</strong> untuk analisis harmonik Form 20 Dishidros TNI AL dan Least Square.
          </p>
        </div>

        <div class="flex flex-wrap lg:flex-col gap-2 flex-shrink-0">
          <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 flex items-center gap-2">
            <i class="fa-solid fa-satellite-dish text-sky-400"></i>Live Stream API SRGI
          </span>
          <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 flex items-center gap-2">
            <i class="fa-solid fa-clock text-emerald-400"></i>Resolusi 5 - 60 Menit
          </span>
          <a href="<?= site_url('admiralty/download-sample/30days') ?>" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-cyan-500/20 hover:bg-cyan-500/30 backdrop-blur-md border border-cyan-400/40 text-cyan-200 hover:text-white flex items-center gap-2 transition cursor-pointer" title="Unduh data contoh pasut 30 hari untuk latihan/uji">
            <i class="fa-solid fa-file-csv text-cyan-300"></i>Download Sample CSV
          </a>
        </div>
      </div>
      <!-- Background glow decorative circles -->
      <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -top-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- CONTROL PANEL FORM -->
    <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
        <div>
          <h2 class="font-bold text-slate-900 text-lg flex items-center gap-2">
            <i class="fa-solid fa-sliders text-sky-600"></i>
            Control Panel Pengambilan Data
          </h2>
          <p class="text-xs text-slate-500">Pilih stasiun pengamatan, rentang tanggal (UTC), dan resolusi sampling data pasang surut.</p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-semibold border border-slate-200">
          Maksimal 31 Hari / Sekali Fetch
        </span>
      </div>

      <form id="fetchForm" class="flex flex-col gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
          
          <!-- Stasiun (Searchable Dropdown) -->
          <div class="lg:col-span-4 relative">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Stasiun Pasang Surut
            </label>
            <div class="relative">
              <input type="hidden" name="station" id="station" value="">
              <button type="button" id="stationTrigger" class="w-full bg-white border border-slate-300 hover:border-sky-400 rounded-xl px-3.5 py-2.5 text-xs text-left font-semibold text-slate-800 flex items-center justify-between shadow-xs transition cursor-pointer">
                <span id="stationTriggerText" class="truncate text-slate-500">Pilih stasiun pasut...</span>
                <i class="fa-solid fa-chevron-down text-slate-400 text-xs ml-2"></i>
              </button>

              <!-- Dropdown Menu -->
              <div id="stationMenu" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-3 flex flex-col gap-2">
                <div class="relative">
                  <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                  <input type="search" id="stationSearch" class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-sky-500" placeholder="Ketik nama atau kode stasiun...">
                </div>
                <div id="stationOptions" class="overflow-y-auto max-h-56 custom-scroll divide-y divide-slate-100 flex flex-col">
                  <!-- Populated dynamically -->
                </div>
                <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-100 flex items-center justify-between" id="stationSearchInfo">
                  <span>Memuat daftar stasiun...</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tanggal Mulai (UTC) -->
          <div class="lg:col-span-2 sm:col-span-1">
            <label for="date_from_display" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Tanggal Mulai (UTC)
            </label>
            <div class="relative">
              <input type="text" id="date_from_display" class="w-full bg-white border border-slate-300 hover:border-sky-400 rounded-xl pl-3.5 pr-8 py-2.5 text-xs font-semibold text-slate-800 shadow-xs transition" placeholder="dd/mm/yyyy" inputmode="numeric">
              <span class="absolute right-3 top-3 text-slate-400 text-xs pointer-events-none" data-picker-target="date_from_display">
                <i class="fa-regular fa-calendar"></i>
              </span>
            </div>
            <input type="hidden" name="date_from" id="date_from" value="<?= esc($defaultFrom) ?>">
          </div>

          <!-- Tanggal Selesai (UTC) -->
          <div class="lg:col-span-2 sm:col-span-1">
            <label for="date_to_display" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Tanggal Selesai (UTC)
            </label>
            <div class="relative">
              <input type="text" id="date_to_display" class="w-full bg-white border border-slate-300 hover:border-sky-400 rounded-xl pl-3.5 pr-8 py-2.5 text-xs font-semibold text-slate-800 shadow-xs transition" placeholder="dd/mm/yyyy" inputmode="numeric">
              <span class="absolute right-3 top-3 text-slate-400 text-xs pointer-events-none" data-picker-target="date_to_display">
                <i class="fa-regular fa-calendar"></i>
              </span>
            </div>
            <input type="hidden" name="date_to" id="date_to" value="<?= esc($today) ?>">
          </div>

          <!-- Resolusi -->
          <div class="lg:col-span-2 sm:col-span-1">
            <label for="resolution" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
              Resolusi Sampling
            </label>
            <div class="relative">
              <select name="resolution" id="resolution" class="w-full bg-white border border-slate-300 hover:border-sky-400 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 shadow-xs transition appearance-none cursor-pointer">
                <?php foreach ($resolutions as $res): ?>
                  <option value="<?= esc((string) $res) ?>" <?= $res === $defaultResolution ? 'selected' : '' ?>>
                    <?= esc((string) $res) ?> menit <?= $res === 60 ? '(Standar Admiralty)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <i class="fa-solid fa-chevron-down absolute right-3 top-3.5 text-slate-400 text-xs pointer-events-none"></i>
            </div>
          </div>

          <!-- Tombol Aksi -->
          <div class="lg:col-span-2 flex items-center gap-2">
            <button type="submit" id="fetchButton" disabled class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-sm transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
              <i class="fa-solid fa-cloud-arrow-down" id="fetchIcon"></i>
              <span>Ambil Data</span>
            </button>
            <button type="button" id="resetButton" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs py-2.5 px-3 rounded-xl transition cursor-pointer" title="Reset Formulir">
              <i class="fa-solid fa-rotate-left"></i>
            </button>
          </div>

        </div>
      </form>
    </div>

    <!-- QUICK METRIC TILES (5 CARDS) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Titik Data</div>
        <div class="text-2xl font-extrabold text-slate-900 font-mono mt-1" id="summaryRows">0</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Observasi diterima</div>
      </div>
      <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Task Selesai</div>
        <div class="text-2xl font-extrabold text-sky-600 font-mono mt-1" id="summaryTasks">0 / 0</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Hari berhasil diproses</div>
      </div>
      <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Fetch</div>
        <div class="text-xl font-extrabold text-emerald-600 mt-1 flex items-center gap-1.5" id="summaryStatus">
          <span>Siap</span>
        </div>
        <div class="text-[10px] text-slate-400 mt-0.5">Koneksi SRGI</div>
      </div>
      <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sumber Stasiun</div>
        <div class="text-xl font-extrabold text-slate-800 mt-1"><?= $stationsSource === 'srgi' ? 'SRGI Live API' : 'Config' ?></div>
        <div class="text-[10px] text-slate-400 mt-0.5"><?= count($stations) ?> Stasiun terdaftar</div>
      </div>
      <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Batas Fetch</div>
        <div class="text-xl font-extrabold text-indigo-600 mt-1"><?= esc((string) $maxRangeDays) ?> Hari</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Maksimum per batch</div>
      </div>
    </div>

    <!-- MAIN TWO-COLUMN SPLIT: LOG & CHART -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- LEFT: PROGRESS & REAL-TIME LOG -->
      <div class="lg:col-span-4 glass-card rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between gap-4">
        <div>
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
              <i class="fa-solid fa-list-check text-sky-600"></i>
              Progress & Log Fetch
            </h3>
            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-800" id="progressCounter">0%</span>
          </div>

          <p class="text-xs text-slate-500 mb-2 leading-relaxed" id="progressStatus">
            Pilih stasiun dan rentang tanggal, lalu klik tombol Ambil Data.
          </p>

          <!-- Progress Bar -->
          <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-3">
            <div id="progressFill" class="h-full bg-gradient-to-r from-sky-500 to-teal-500 rounded-full transition-all duration-300" style="width: 0%"></div>
          </div>

          <!-- Log Activity Container -->
          <div id="progressLog" class="overflow-y-auto max-h-80 custom-scroll flex flex-col gap-2 pr-1">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs text-slate-500 flex items-center gap-2">
              <i class="fa-solid fa-info-circle text-slate-400"></i>
              <span>Belum ada aktivitas pengambilan data.</span>
            </div>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
          <span>Otomatis menangani HTTP 429</span>
          <i class="fa-solid fa-shield-halved text-emerald-500"></i>
        </div>
      </div>

      <!-- RIGHT: INTERACTIVE CHART -->
      <div class="lg:col-span-8 glass-card rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between gap-3">
        <div>
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3 flex-wrap gap-2">
            <div>
              <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-sky-600"></i>
                Grafik Pasang Surut Observasi
              </h3>
              <p class="text-xs text-slate-500">Zona waktu sumbu waktu mengikuti standar UTC (SRGI). Arahkan mouse untuk melihat detail tiap titik.</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold border border-slate-200" id="chartPointCount">
                0 Titik
              </span>
            </div>
          </div>

          <!-- Empty Chart Notice -->
          <div id="chartEmpty" class="h-72 border border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center text-slate-400 text-xs gap-2">
            <i class="fa-solid fa-water text-3xl text-slate-300"></i>
            <span>Belum ada data pasang surut untuk digambar.</span>
          </div>

          <!-- Chart Container -->
          <div id="chartShell" class="h-72 w-full relative" style="display:none;">
            <canvas id="tidesChart"></canvas>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 flex-wrap gap-2">
          <div class="flex items-center gap-3">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>Data Asli</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Gap Fill</span>
          </div>
          <span>Gunakan grafik untuk memastikan data bersih sebelum analisis harmonik</span>
        </div>
      </div>

    </div>

    <!-- TABLE & INTEGRATION ACTIONS SECTION -->
    <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-4">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-3">
        <div>
          <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
            <i class="fa-solid fa-table text-sky-600"></i>
            Tabel Data Pasang Surut
          </h3>
          <p class="text-xs text-slate-500">Daftar inspeksi nilai pengukuran sensor PRS1, ENC1, RAD1, dan nilai terpilih.</p>
        </div>

        <!-- Integrated Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
          <!-- Export CSV -->
          <button type="button" id="exportCsvButton" disabled class="bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs px-3.5 py-2 rounded-xl border border-slate-300 shadow-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-solid fa-file-csv text-emerald-600"></i>
            <span>Export CSV</span>
          </button>

          <!-- KIRIM KE TIDE PREDICTOR (KILLER INTEGRATION BUTTON) -->
          <button type="button" id="sendToPredictorBtn" disabled class="bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" title="Transfer dataset ini langsung ke Tide Predictor untuk analisis harmonik Form 20 & Least Square">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            <span>Kirim ke Tide Predictor</span>
          </button>
        </div>
      </div>

      <!-- Empty State Table -->
      <div id="tableEmpty" class="p-8 border border-dashed border-slate-200 rounded-xl text-center text-slate-400 text-xs">
        <i class="fa-solid fa-table-list text-3xl mb-2 text-slate-300 block"></i>
        Belum ada data pasang surut yang ditampilkan. Silakan pilih stasiun dan tanggal di atas, lalu klik <strong>Ambil Data</strong>.
      </div>

      <!-- Table Wrapper -->
      <div class="overflow-x-auto custom-scroll border border-slate-200 rounded-xl bg-white max-h-96" id="tableWrap" style="display:none;">
        <table class="w-full text-xs text-left">
          <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider sticky top-0 border-b border-slate-200">
            <tr>
              <th class="p-3">Stasiun</th>
              <th class="p-3 font-mono">Measured At (UTC)</th>
              <th class="p-3 font-mono">Waktu Lokal (WIB)</th>
              <th class="p-3 text-right">PRS1 (m)</th>
              <th class="p-3 text-right">ENC1 (m)</th>
              <th class="p-3 text-right">RAD1 (m)</th>
              <th class="p-3 text-right font-bold text-slate-900">Nilai Muka Air (m)</th>
              <th class="p-3">Sumber Sensor</th>
              <th class="p-3">Status Data</th>
            </tr>
          </thead>
          <tbody id="tableBody" class="divide-y divide-slate-100 font-mono text-slate-700">
          </tbody>
        </table>
      </div>

      <!-- Bottom info banner on integration -->
      <div class="text-[11px] text-slate-600 bg-sky-50/80 border border-sky-200/80 rounded-xl p-3 flex items-start gap-2.5">
        <i class="fa-solid fa-circle-nodes text-sky-600 text-sm mt-0.5"></i>
        <div>
          <strong>Alur Kerja Terintegrasi (Fetch &rarr; Predictor):</strong> Setelah data selesai di-fetch, Anda dapat mengklik tombol <strong>Kirim ke Tide Predictor</strong>. Sistem akan otomatis memvalidasi dataset, menyelaraskan interval waktu sesuai kaidah Form 20 Dishidros TNI AL, dan membuka halaman Admiralty dengan dataset yang sudah siap dihitung tanpa perlu unggah berkas manual.
        </div>
      </div>
    </div>

  </main>

  <!-- FOOTER -->
  <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <span class="font-bold text-slate-800">R-PASOET</span>
        <span>• Platform Analisis & Prediksi Pasang Surut Terintegrasi</span>
      </div>
      <div class="flex items-center gap-4">
        <span>Sumber Data: Portal SRGI BIG</span>
        <span>Dibuat oleh Roni Alfiansyah</span>
      </div>
    </div>
  </footer>

  <!-- SCRIPT LOGIC -->
  <script>
  (function(){
    // Element References
    const form = document.getElementById('fetchForm');
    const stationField = document.getElementById('station');
    const stationTrigger = document.getElementById('stationTrigger');
    const stationTriggerText = document.getElementById('stationTriggerText');
    const stationMenu = document.getElementById('stationMenu');
    const stationOptions = document.getElementById('stationOptions');
    const stationSearchField = document.getElementById('stationSearch');
    const stationSearchInfo = document.getElementById('stationSearchInfo');
    const dateFromField = document.getElementById('date_from');
    const dateToField = document.getElementById('date_to');
    const dateFromDisplayField = document.getElementById('date_from_display');
    const dateToDisplayField = document.getElementById('date_to_display');
    const resolutionField = document.getElementById('resolution');
    const fetchButton = document.getElementById('fetchButton');
    const fetchIcon = document.getElementById('fetchIcon');
    const resetButton = document.getElementById('resetButton');
    const exportCsvButton = document.getElementById('exportCsvButton');
    const sendToPredictorBtn = document.getElementById('sendToPredictorBtn');
    const progressFill = document.getElementById('progressFill');
    const progressCounter = document.getElementById('progressCounter');
    const progressStatus = document.getElementById('progressStatus');
    const progressLog = document.getElementById('progressLog');
    const summaryRows = document.getElementById('summaryRows');
    const summaryTasks = document.getElementById('summaryTasks');
    const summaryStatus = document.getElementById('summaryStatus');
    const chartShell = document.getElementById('chartShell');
    const chartEmpty = document.getElementById('chartEmpty');
    const chartPointCount = document.getElementById('chartPointCount');
    const tableWrap = document.getElementById('tableWrap');
    const tableEmpty = document.getElementById('tableEmpty');
    const tableBody = document.getElementById('tableBody');
    const canvas = document.getElementById('tidesChart');

    // Data Config
    const maxRangeDays = <?= (int) $maxRangeDays ?>;
    const defaultResolution = <?= (int) $defaultResolution ?>;
    const stations = <?= json_encode(array_values($stations), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const allStationOptions = stations.slice();

    let chartInstance = null;
    let currentRows = [];
    let fromPicker = null;
    let toPicker = null;

    // Helpers
    function esc(v){ return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
    function num(v){ if(v === null || v === '' || typeof v === 'undefined') return null; const p = Number(String(v).replace(',','.')); return Number.isFinite(p) ? p : null; }
    
    function formatWib(v){
      const d = new Date(String(v).replace(' ','T') + 'Z');
      if(Number.isNaN(d.getTime())) return v;
      return new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      }).format(d);
    }

    function displayDate(v){
      if(!v) return '';
      const p = v.split('-');
      return p.length === 3 ? [p[2], p[1], p[0]].join('/') : v;
    }

    function isoDate(v){
      const m = String(v).trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
      if(!m) return '';
      const day = Number(m[1]), month = Number(m[2]), year = Number(m[3]);
      const d = new Date(year, month - 1, day);
      if(d.getFullYear() !== year || d.getMonth() !== month - 1 || d.getDate() !== day) return '';
      return year + '-' + String(month).padStart(2,'0') + '-' + String(day).padStart(2,'0');
    }

    function syncDisplayDates(){
      dateFromDisplayField.value = displayDate(dateFromField.value);
      dateToDisplayField.value = displayDate(dateToField.value);
      if(fromPicker) fromPicker.setDate(dateFromField.value, false, 'Y-m-d');
      if(toPicker) toPicker.setDate(dateToField.value, false, 'Y-m-d');
    }

    function syncHiddenDate(hiddenField, displayField){
      const iso = isoDate(displayField.value);
      if(iso){
        hiddenField.value = iso;
        displayField.classList.remove('border-rose-400');
        return true;
      }
      displayField.classList.add('border-rose-400');
      return false;
    }

    function setupDatePicker(displayField, hiddenField){
      return flatpickr(displayField, {
        dateFormat: 'd/m/Y',
        allowInput: true,
        defaultDate: hiddenField.value || null,
        onChange: function(selectedDates, dateStr, instance){
          const raw = selectedDates.length ? instance.formatDate(selectedDates[0], 'Y-m-d') : isoDate(dateStr);
          if(raw){
            hiddenField.value = raw;
            displayField.classList.remove('border-rose-400');
          }
        },
        onClose: function(selectedDates, dateStr){
          if(!selectedDates.length && dateStr){
            syncHiddenDate(hiddenField, displayField);
          }
        }
      });
    }

    function generateDateRange(a, b){
      const dates = [], s = new Date(a + 'T00:00:00'), t = new Date(b + 'T00:00:00');
      while(s <= t){
        dates.push(s.getFullYear() + '-' + String(s.getMonth() + 1).padStart(2,'0') + '-' + String(s.getDate()).padStart(2,'0'));
        s.setDate(s.getDate() + 1);
      }
      return dates;
    }

    function calcDays(a, b){
      return Math.floor((new Date(b + 'T00:00:00').getTime() - new Date(a + 'T00:00:00').getTime()) / 86400000) + 1;
    }

    function setProgress(current, total, label){
      const p = total > 0 ? Math.round(current / total * 100) : 0;
      progressFill.style.width = p + '%';
      progressCounter.textContent = p + '%';
      summaryTasks.textContent = current + ' / ' + total;
      progressStatus.textContent = label;
    }

    async function waitForRateLimit(minutes, station, date){
      let remainingSeconds = minutes * 60;
      function updateCountdown(){
        const minutePart = Math.floor(remainingSeconds / 60), secondPart = remainingSeconds % 60;
        summaryStatus.innerHTML = '<span class="text-amber-600">Rate Limit</span>';
        progressStatus.innerHTML = `<span class="text-amber-700 font-semibold"><i class="fa-solid fa-hourglass-half mr-1"></i>HTTP 429 untuk ${esc(station)} tgl ${esc(date)}. Menunggu jeda ${minutePart}:${String(secondPart).padStart(2,'0')}...</span>`;
      }
      updateCountdown();
      await new Promise(resolve => {
        const timer = setInterval(() => {
          remainingSeconds--;
          updateCountdown();
          if(remainingSeconds <= 0){
            clearInterval(timer);
            resolve();
          }
        }, 1000);
      });
    }

    function addLog(item){
      if(progressLog.children.length === 1 && progressLog.textContent.indexOf('Belum ada aktivitas') !== -1){
        progressLog.innerHTML = '';
      }
      const isOk = item.status === 'success';
      const gapBadge = item.completeness ? (item.completeness.status === 'complete' ? '<span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Lengkap</span>' : '<span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Ada gap</span>') : '';
      const pointBadge = item.completeness ? `<span class="text-[10px] font-mono text-slate-500">${item.completeness.actual_points}/${item.completeness.expected_points}</span>` : '';
      const message = item.completeness ? (item.message + (item.completeness.missing_points > 0 ? ` (hilang ${item.completeness.missing_points} titik)` : '')) : item.message;

      const html = `
        <div class="p-2.5 rounded-xl border ${isOk ? 'bg-emerald-50/50 border-emerald-200/80 text-emerald-950' : 'bg-rose-50/50 border-rose-200/80 text-rose-950'} text-xs flex flex-col gap-1 transition">
          <div class="flex items-center justify-between gap-1 flex-wrap">
            <span class="font-bold flex items-center gap-1.5">
              <i class="fa-solid ${isOk ? 'fa-check text-emerald-600' : 'fa-xmark text-rose-600'}"></i>
              ${esc(item.station)} • ${esc(item.date)}
            </span>
            <div class="flex items-center gap-1">
              ${gapBadge}
              ${pointBadge}
            </div>
          </div>
          <div class="text-[11px] text-slate-600">${esc(message)}</div>
        </div>
      `;
      progressLog.insertAdjacentHTML('afterbegin', html);
    }

    function renderTable(rows){
      currentRows = rows;
      if(!rows.length){
        tableWrap.style.display = 'none';
        tableEmpty.style.display = 'block';
        tableBody.innerHTML = '';
        exportCsvButton.disabled = true;
        sendToPredictorBtn.disabled = true;
        return;
      }
      exportCsvButton.disabled = false;
      sendToPredictorBtn.disabled = false;

      const sorted = rows.slice().sort((a,b) => a.measured_at_utc.localeCompare(b.measured_at_utc));
      const html = sorted.slice(0, 500).map(row => {
        const isGap = Boolean(row.is_gap_fill);
        return `
          <tr class="hover:bg-slate-50 transition">
            <td class="p-3"><span class="px-2 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-700">${esc(row.station_code)}</span></td>
            <td class="p-3 font-mono text-slate-800">${esc(row.measured_at_utc)}</td>
            <td class="p-3 font-mono text-slate-600">${esc(formatWib(row.measured_at_utc))}</td>
            <td class="p-3 text-right font-mono">${esc(row.prs1 ?? '-')}</td>
            <td class="p-3 text-right font-mono">${esc(row.enc1 ?? '-')}</td>
            <td class="p-3 text-right font-mono">${esc(row.rad1 ?? '-')}</td>
            <td class="p-3 text-right font-mono font-bold text-sky-700">${esc(row.water_level)}</td>
            <td class="p-3"><span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600">${esc(row.water_level_source || '-')}</span></td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold ${isGap ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">
                ${isGap ? 'Gap Fill' : 'Data Asli'}
              </span>
            </td>
          </tr>
        `;
      }).join('');

      tableBody.innerHTML = html;
      tableEmpty.style.display = 'none';
      tableWrap.style.display = 'block';
    }

    function renderChart(rows){
      const sorted = rows.slice().sort((a,b) => a.measured_at_utc.localeCompare(b.measured_at_utc));
      if(!sorted.length){
        if(chartInstance){ chartInstance.destroy(); chartInstance = null; }
        chartShell.style.display = 'none';
        chartEmpty.style.display = 'flex';
        chartPointCount.textContent = '0 Titik';
        return;
      }

      chartEmpty.style.display = 'none';
      chartShell.style.display = 'block';
      chartPointCount.textContent = sorted.length + ' Titik';

      const labels = sorted.map(r => r.measured_at_utc);
      const points = sorted.map(r => num(r.water_level));

      if(chartInstance){
        chartInstance.data.labels = labels;
        chartInstance.data.datasets[0].data = points;
        chartInstance.update();
        return;
      }

      const ctx = canvas.getContext('2d');
      const gradient = ctx.createLinearGradient(0, 0, 0, 300);
      gradient.addColorStop(0, 'rgba(14, 165, 233, 0.25)');
      gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

      chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Muka Air Laut (m)',
            data: points,
            borderColor: '#0284c7',
            backgroundColor: gradient,
            fill: true,
            borderWidth: 2,
            pointRadius: labels.length > 200 ? 0 : 2,
            pointHoverRadius: 5,
            tension: 0.25
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                title: function(items){
                  if(!items.length) return '';
                  const row = sorted[items[0].dataIndex];
                  return `${row.station_code} • ${row.measured_at_utc} UTC`;
                },
                label: function(ctx){
                  const row = sorted[ctx.dataIndex];
                  return [
                    `Elevasi: ${ctx.parsed.y !== null ? ctx.parsed.y.toFixed(3) : '-'} m`,
                    `Waktu WIB: ${formatWib(row.measured_at_utc)}`,
                    `Status: ${row.is_gap_fill ? 'Gap Fill' : 'Data Asli'}`
                  ];
                }
              }
            }
          },
          scales: {
            y: {
              grid: { color: '#f1f5f9' },
              title: { display: true, text: 'Tinggi Muka Air (m)', font: { size: 11, weight: 'bold' } }
            },
            x: {
              grid: { display: false },
              ticks: { maxTicksLimit: 10, font: { size: 10 } }
            }
          }
        }
      });
    }

    function resetView(){
      currentRows = [];
      if(chartInstance){ chartInstance.destroy(); chartInstance = null; }
      chartShell.style.display = 'none';
      chartEmpty.style.display = 'flex';
      chartPointCount.textContent = '0 Titik';
      summaryRows.textContent = '0';
      summaryTasks.textContent = '0 / 0';
      summaryStatus.innerHTML = '<span>Siap</span>';
      setProgress(0, 0, 'Pilih stasiun dan rentang tanggal, lalu klik tombol Ambil Data.');
      progressLog.innerHTML = '<div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs text-slate-500 flex items-center gap-2"><i class="fa-solid fa-info-circle text-slate-400"></i><span>Belum ada aktivitas pengambilan data.</span></div>';
      renderTable([]);
    }

    function stationLabel(code){
      if(!code) return 'Pilih stasiun pasut...';
      const m = allStationOptions.find(s => s.code === code);
      return m ? m.label : code;
    }

    function updateFetchButtonState(){
      fetchButton.disabled = !stationField.value;
    }

    function closeMenu(){
      stationMenu.classList.add('hidden');
    }

    function openMenu(){
      stationMenu.classList.remove('hidden');
      stationSearchField.focus();
      stationSearchField.select();
    }

    function setStation(code){
      stationField.value = code || '';
      stationTriggerText.textContent = stationLabel(stationField.value);
      if(code){
        stationTriggerText.classList.remove('text-slate-500');
        stationTriggerText.classList.add('text-slate-900');
      } else {
        stationTriggerText.classList.add('text-slate-500');
        stationTriggerText.classList.remove('text-slate-900');
      }
      updateFetchButtonState();
    }

    function renderStations(list){
      const selected = stationField.value;
      const html = list.map(st => {
        const isSel = st.code === selected;
        return `
          <button type="button" class="w-full text-left p-2 rounded-lg text-xs font-semibold flex items-center justify-between transition cursor-pointer ${isSel ? 'bg-sky-50 text-sky-700' : 'text-slate-700 hover:bg-slate-50'}" data-code="${esc(st.code)}">
            <span class="truncate">${esc(st.label)}</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-mono ${isSel ? 'bg-sky-200 text-sky-800' : 'bg-slate-100 text-slate-500'}">${esc(st.code)}</span>
          </button>
        `;
      }).join('');
      stationOptions.innerHTML = html || '<div class="p-3 text-center text-xs text-slate-400">Tidak ada stasiun yang cocok.</div>';
      stationSearchInfo.textContent = stationSearchField.value.trim() ? `Menampilkan ${list.length} stasiun yang cocok.` : 'Pilih stasiun untuk mulai mengambil data.';
    }

    function filterStations(){
      const k = stationSearchField.value.trim().toLowerCase();
      if(!k) return renderStations(allStationOptions);
      renderStations(allStationOptions.filter(st => st.code.toLowerCase().includes(k) || st.label.toLowerCase().includes(k)));
    }

    async function fetchTask(station, date, resolution){
      const url = new URL('<?= site_url('tides/fetch-day') ?>', window.location.origin);
      url.searchParams.set('station', station);
      url.searchParams.set('date', date);
      url.searchParams.set('resolution', resolution);
      const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
      const payload = await response.json();
      if(!response.ok || !payload.success) throw new Error(payload.message || 'Gagal mengambil data.');
      return payload;
    }

    // FORM SUBMIT
    form.addEventListener('submit', async function(event){
      event.preventDefault();
      if(!stationField.value) return alert('Silakan pilih stasiun pasut terlebih dahulu.');
      if(!syncHiddenDate(dateFromField, dateFromDisplayField) || !syncHiddenDate(dateToField, dateToDisplayField)){
        return alert('Gunakan format tanggal dd/mm/yyyy.');
      }
      const from = dateFromField.value, to = dateToField.value, resolution = resolutionField.value;
      if(!from || !to) return alert('Tanggal mulai dan tanggal selesai wajib diisi.');
      if(from > to) return alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
      if(calcDays(from, to) > maxRangeDays) return alert(`Rentang tanggal maksimal ${maxRangeDays} hari untuk sekali fetch.`);

      const selectedStation = stationField.value;
      const selectedDates = generateDateRange(from, to);
      const tasks = selectedDates.map(d => ({ station: selectedStation, date: d }));

      resetView();
      fetchButton.disabled = true;
      fetchIcon.className = 'fa-solid fa-circle-notch fa-spin';
      summaryStatus.innerHTML = '<span class="text-sky-600"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Berjalan</span>';

      const allRows = new Map();

      for(let i = 0; i < tasks.length; i++){
        const task = tasks[i];
        let rateLimitWaitIndex = 0;

        while(true){
          setProgress(i, tasks.length, `Mengambil data ${task.station} tgl ${task.date} (resolusi ${resolution}m)...`);
          try {
            const payload = await fetchTask(task.station, task.date, resolution);
            const rows = payload.rows || [];
            const actualPoints = payload.completeness ? payload.completeness.actual_points : rows.filter(row => !row.is_gap_fill).length;
            rows.forEach(row => allRows.set(row.station_code + '|' + row.measured_at_utc, row));

            addLog({
              station: task.station,
              date: task.date,
              status: 'success',
              message: `${actualPoints} titik data asli diterima pada resolusi ${resolution} menit`,
              completeness: payload.completeness || null
            });
            break;
          } catch(error){
            const errMsg = error.message || 'Terjadi kesalahan';
            if(String(errMsg).includes('HTTP 429') && rateLimitWaitIndex < 4){
              rateLimitWaitIndex++;
              await waitForRateLimit(rateLimitWaitIndex, task.station, task.date);
              continue;
            }
            addLog({ station: task.station, date: task.date, status: 'error', message: errMsg });
            if(String(errMsg).includes('HTTP 429')){
              summaryStatus.innerHTML = '<span class="text-rose-600">429 Limit</span>';
              progressStatus.textContent = 'Batch dihentikan karena SRGI limit 429 berulang kali.';
              fetchIcon.className = 'fa-solid fa-cloud-arrow-down';
              updateFetchButtonState();
              return;
            }
            break;
          }
        }

        const rowsArr = Array.from(allRows.values());
        summaryRows.textContent = String(rowsArr.length);
        renderTable(rowsArr);
        renderChart(rowsArr);
        setProgress(i + 1, tasks.length, `Selesai memproses ${i + 1} dari ${tasks.length} task.`);
      }

      summaryStatus.innerHTML = '<span class="text-emerald-600"><i class="fa-solid fa-check mr-1"></i>Selesai</span>';
      progressStatus.textContent = `Fetch selesai. ${allRows.size} titik data berhasil diterima dan siap dianalisis di Tide Predictor.`;
      fetchIcon.className = 'fa-solid fa-cloud-arrow-down';
      updateFetchButtonState();
    });

    // RESET BUTTON
    resetButton.addEventListener('click', function(){
      form.reset();
      dateFromField.value = '<?= esc($defaultFrom) ?>';
      dateToField.value = '<?= esc($today) ?>';
      syncDisplayDates();
      dateFromDisplayField.classList.remove('border-rose-400');
      dateToDisplayField.classList.remove('border-rose-400');
      resolutionField.value = String(defaultResolution);
      stationSearchField.value = '';
      setStation('');
      renderStations(allStationOptions);
      closeMenu();
      resetView();
    });

    // Date picker event bindings
    [dateFromDisplayField, dateToDisplayField].forEach(function(field){
      field.addEventListener('blur', function(){
        syncHiddenDate(field === dateFromDisplayField ? dateFromField : dateToField, field);
      });
      field.addEventListener('input', function(){
        field.classList.remove('border-rose-400');
      });
    });

    document.querySelectorAll('[data-picker-target]').forEach(function(trigger){
      trigger.addEventListener('click', function(){
        const target = this.getAttribute('data-picker-target');
        if(target === 'date_from_display' && fromPicker) fromPicker.open();
        if(target === 'date_to_display' && toPicker) toPicker.open();
      });
    });

    // Searchable station dropdown events
    stationTrigger.addEventListener('click', function(){
      if(stationMenu.classList.contains('hidden')) openMenu();
      else closeMenu();
    });
    stationSearchField.addEventListener('input', filterStations);
    stationOptions.addEventListener('click', function(event){
      const option = event.target.closest('button[data-code]');
      if(!option) return;
      setStation(option.getAttribute('data-code') || '');
      filterStations();
      closeMenu();
    });
    document.addEventListener('click', function(event){
      if(event.target.closest('#stationTrigger') || event.target.closest('#stationMenu')) return;
      closeMenu();
    });
    document.addEventListener('keydown', function(event){
      if(event.key === 'Escape') closeMenu();
    });

    // EXPORT CSV
    function exportTableToCsv(){
      if(!currentRows.length) return;
      const sorted = currentRows.slice().sort((a,b) => a.measured_at_utc.localeCompare(b.measured_at_utc));
      const header = ['station_code','measured_at_utc','measured_at_local','local_timezone','filter_timezone','prs1','enc1','rad1','water_level','water_level_source','source_status'];
      const lines = [header.join(',')];

      sorted.forEach(row => {
        lines.push([
          `"${row.station_code || ''}"`,
          `"${row.measured_at_utc || ''}"`,
          `"${formatWib(row.measured_at_utc)}"`,
          '"Asia/Jakarta"',
          '"UTC"',
          `"${row.prs1 ?? ''}"`,
          `"${row.enc1 ?? ''}"`,
          `"${row.rad1 ?? ''}"`,
          `"${row.water_level ?? ''}"`,
          `"${row.water_level_source ?? '-'}"`,
          `"${row.is_gap_fill ? 'Gap Fill' : 'Data Asli'}"`
        ].join(','));
      });

      const blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const stationCode = stationField.value || 'data';
      const fileName = `pasang_surut_${stationCode}_${dateFromField.value}_sampai_${dateToField.value}.csv`;
      const link = document.createElement('a');
      link.href = url;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }
    exportCsvButton.addEventListener('click', exportTableToCsv);

    // ============================================================
    // DIRECT BRIDGE TO TIDE PREDICTOR (ADMIRALTY)
    // ============================================================
    async function sendToTidePredictor(){
      if(!currentRows.length) return;

      const origText = sendToPredictorBtn.innerHTML;
      sendToPredictorBtn.disabled = true;
      sendToPredictorBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i><span>Memvalidasi & Mengirim...</span>';

      try {
        const sorted = currentRows.slice().sort((a,b) => a.measured_at_utc.localeCompare(b.measured_at_utc));

        // Format for Admiralty: Form 20 expects 1 observation per hour (hourly: 00 minutes).
        // If data is collected with higher resolution (e.g. 5m, 10m, 15m, 30m):
        // Automatically select hourly points (:00:00) so that count is within 360-720 points!
        let targetRows = sorted;
        const resolution = parseInt(resolutionField.value, 10);
        if(resolution < 60){
          const hourly = sorted.filter(r => {
            const time = String(r.measured_at_utc || '');
            return time.endsWith(':00:00') || time.endsWith(':00');
          });
          if(hourly.length >= 360){
            targetRows = hourly;
          }
        }

        // Build standard CSV
        const csvLines = ['station_code,datetime,water_level'];
        targetRows.forEach(r => {
          csvLines.push(`"${r.station_code}","${r.measured_at_utc}",${r.water_level}`);
        });
        const csvContent = csvLines.join('\r\n');
        const csvBlob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

        const stationCode = stationField.value || 'data';
        const stLabel = stationLabel(stationCode);
        const fileName = `fetch_${stationCode}_${dateFromField.value}_to_${dateToField.value}.csv`;

        const formData = new FormData();
        formData.append('tide_file', csvBlob, fileName);

        // Upload and validate directly with Admiralty controller
        const response = await fetch('<?= site_url('admiralty/validate-upload') ?>', {
          method: 'POST',
          body: formData,
          headers: { 'Accept': 'application/json' }
        });
        const payload = await response.json();

        if(!response.ok || !payload.success){
          throw new Error(payload.message || 'Validasi dataset ke Tide Predictor gagal.');
        }

        // Save pre-filled draft for Admiralty Step 1 form
        try {
          window.localStorage.setItem('admiralty_form_draft_v1', JSON.stringify({
            station_name: stLabel,
            latitude: '',
            longitude: '',
            timezone: 'Asia/Jakarta'
          }));
        } catch(e){}

        // Redirect immediately to Admiralty with dataset ready!
        window.location.href = '<?= site_url('admiralty') ?>';

      } catch(err){
        alert('Gagal mengirim data ke Tide Predictor: ' + err.message);
        sendToPredictorBtn.disabled = false;
        sendToPredictorBtn.innerHTML = origText;
      }
    }
    sendToPredictorBtn.addEventListener('click', sendToTidePredictor);

    // Initializers
    fromPicker = setupDatePicker(dateFromDisplayField, dateFromField);
    toPicker = setupDatePicker(dateToDisplayField, dateToField);
    setStation('');
    renderStations(allStationOptions);
    syncDisplayDates();
    resetView();
  })();
  </script>
</body>
</html>
