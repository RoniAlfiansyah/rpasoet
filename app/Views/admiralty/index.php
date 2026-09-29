<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Admiralty') ?> | R-Pasoet</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
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
    .step-active {
      background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
      color: white;
      box-shadow: 0 4px 14px 0 rgba(2, 132, 199, 0.35);
    }
    .step-done {
      background: #ecfdf5;
      color: #059669;
      border: 1px solid #a7f3d0;
    }
    .step-idle {
      background: #f8fafc;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }
    .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f8fafc; }
    .adjustment-card.is-disabled { opacity: 0.55; pointer-events: none; background: #f8fafc; }
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
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-sky-100 text-sky-800 border border-sky-200">Admiralty Engine</span>
          </div>
          <p class="text-xs text-slate-500">Analisis Harmonik & Generator Prediksi Pasang Surut</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="<?= site_url('tides') ?>" class="text-xs font-semibold text-slate-600 hover:text-sky-600 px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition flex items-center gap-1.5">
          <i class="fa-solid fa-cloud-arrow-down text-sky-600"></i>
          <span>Fetch Tide SRGI</span>
        </a>
        <a href="<?= site_url('admiralty') ?>" class="text-xs font-bold text-sky-700 bg-sky-50 border border-sky-200 px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-xs">
          <i class="fa-solid fa-chart-line"></i>
          <span>Tide Predictor (Admiralty)</span>
        </a>
        <button type="button" class="reset-admiralty-btn text-xs font-semibold text-rose-600 hover:text-rose-700 px-3.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 transition flex items-center gap-1.5" style="display:none;" title="Bersihkan dan mulai dari awal">
          <i class="fa-solid fa-rotate-left"></i>Reset Sesi
        </button>
      </div>
    </div>
  </header>

  <!-- MAIN CONTAINER -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full flex flex-col gap-6">

    <!-- STEPPER PROGRESS BAR -->
    <div class="glass-card rounded-2xl p-3 sm:p-4 shadow-sm border border-slate-200/80">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3" id="stepperNav">
        <!-- Step 1 -->
        <button type="button" onclick="goToStep(1)" id="stepBtn-1" class="step-active flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer">
          <div id="stepNum-1" class="w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center font-bold text-sm">1</div>
          <div class="truncate">
            <div class="text-[10px] font-medium opacity-90 uppercase tracking-wider">Tahap 1</div>
            <div class="font-bold text-sm truncate">Upload & Validasi</div>
          </div>
        </button>
        <!-- Step 2 -->
        <button type="button" onclick="goToStep(2)" id="stepBtn-2" class="step-idle flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer">
          <div id="stepNum-2" class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm">2</div>
          <div class="truncate">
            <div class="text-[10px] font-medium opacity-75 uppercase tracking-wider">Tahap 2</div>
            <div class="font-bold text-sm truncate">Model & Hitung</div>
          </div>
        </button>
        <!-- Step 3 -->
        <button type="button" onclick="goToStep(3)" id="stepBtn-3" class="step-idle flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer">
          <div id="stepNum-3" class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm">3</div>
          <div class="truncate">
            <div class="text-[10px] font-medium opacity-75 uppercase tracking-wider">Tahap 3</div>
            <div class="font-bold text-sm truncate">Harmonik & Evaluasi</div>
          </div>
        </button>
        <!-- Step 4 -->
        <button type="button" onclick="goToStep(4)" id="stepBtn-4" class="step-idle flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer">
          <div id="stepNum-4" class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm">4</div>
          <div class="truncate">
            <div class="text-[10px] font-medium opacity-75 uppercase tracking-wider">Tahap 4</div>
            <div class="font-bold text-sm truncate">Prediksi & Ekspor</div>
          </div>
        </button>
      </div>
    </div>

    <!-- NOTIFIKASI RESTORED SESSION -->
    <div id="restoredSessionAlert" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-amber-900" style="display:none;">
      <div class="flex items-center gap-3 text-sm">
        <i class="fa-solid fa-circle-info text-amber-600 text-lg"></i>
        <span><strong>Data Dipulihkan:</strong> Data validasi sebelumnya dipulihkan dari sesi. Anda dapat langsung melanjutkan atau klik tombol reset jika ingin mengganti berkas.</span>
      </div>
      <button type="button" class="reset-admiralty-btn text-xs font-semibold px-3 py-1.5 rounded-lg border border-amber-300 bg-white hover:bg-amber-100 text-amber-900 transition flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
        <i class="fa-solid fa-undo"></i>Reset & Ganti File
      </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAHAP 1: UPLOAD & VALIDASI DATA -->
    <!-- ============================================================== -->
    <div id="stepContent-1" class="flex flex-col gap-6">
      
      <!-- Quick sample & Fetch bridge banner -->
      <div class="bg-gradient-to-r from-sky-500/10 via-cyan-500/5 to-transparent border border-sky-200/80 rounded-2xl p-5 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-base">Mulai Cepat dengan Data Observasi</h3>
            <p class="text-sm text-slate-600">Unggah berkas observasi pasut Anda (.CSV, .TXT, .XLSX), ambil data live dari portal SRGI BIG, atau gunakan data uji 30 hari.</p>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
          <a href="<?= site_url('tides') ?>" class="bg-white hover:bg-slate-50 text-sky-700 font-semibold text-xs px-3.5 py-2.5 rounded-xl border border-sky-300 shadow-xs transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-down text-sky-600"></i>
            <span>Ambil Data dari SRGI</span>
          </a>
          <button type="button" onclick="loadSampleDataset('30days')" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl shadow-sm shadow-sky-600/20 transition flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-bolt-lightning text-amber-300"></i>
            <span>Sample 30 Hari</span>
          </button>
          <button type="button" onclick="loadSampleDataset('15days')" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl shadow-sm shadow-teal-600/20 transition flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-bolt-lightning text-teal-200"></i>
            <span>Sample 15 Hari</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Upload -->
        <div class="lg:col-span-7 flex flex-col gap-5">
          <form id="uploadForm" enctype="multipart/form-data" class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <i class="fa-regular fa-folder-open text-sky-600"></i>
                Metadata & Berkas Pasut
              </h3>
              <button type="button" id="validationCardToggle" class="text-xs text-slate-500 hover:text-slate-800" style="display:none;"><i class="fa-solid fa-minus"></i></button>
            </div>

            <!-- Metadata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="station_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Stasiun</label>
                <input type="text" id="station_name" name="station_name" placeholder="Contoh: Tanjung Priok" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
              </div>
              <div>
                <label for="timezone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Zona Waktu</label>
                <select id="timezone" name="timezone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                  <option value="Asia/Jakarta" selected>(GMT+7) WIB - Asia/Jakarta</option>
                  <option value="Asia/Makassar">(GMT+8) WITA - Asia/Makassar</option>
                  <option value="Asia/Jayapura">(GMT+9) WIT - Asia/Jayapura</option>
                </select>
              </div>
              <div>
                <label for="latitude" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Latitude (Lintang)</label>
                <input type="text" id="latitude" name="latitude" placeholder="Contoh: -6.107800" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white font-mono">
              </div>
              <div>
                <label for="longitude" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Longitude (Bujur)</label>
                <input type="text" id="longitude" name="longitude" placeholder="Contoh: 106.880300" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white font-mono">
              </div>
            </div>

            <!-- File Upload Zone -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Berkas Observasi</label>
              <div id="dropZoneContainer" class="relative border-2 border-dashed border-sky-300 bg-sky-50/40 hover:bg-sky-50 transition rounded-2xl p-6 text-center flex flex-col items-center justify-center cursor-pointer">
                <input type="file" id="tide_file" name="tide_file" accept=".csv,.txt,.xlsx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div class="w-12 h-12 rounded-xl bg-white text-sky-600 shadow-xs border border-sky-100 flex items-center justify-center text-xl mb-2">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <p class="font-bold text-slate-800 text-sm mb-0.5" id="selectedFileName">Klik atau seret berkas ke sini</p>
                <p class="text-xs text-slate-500 mb-2">Dukungan: CSV, TXT, XLSX (kolom datetime & water_level)</p>
                <span class="inline-block text-[11px] font-semibold text-sky-700 bg-sky-100/80 px-2.5 py-0.5 rounded-full">
                  Target: 360 s.d. 720 Titik (15 - 30 Hari, interval 60m)
                </span>
              </div>
            </div>

            <!-- Sample Data Download Box -->
            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-slate-600">
              <span class="flex items-center gap-1.5 font-medium">
                <i class="fa-solid fa-file-csv text-emerald-600 text-sm"></i>
                <span>Belum punya data pasut? Unduh sample data:</span>
              </span>
              <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= site_url('admiralty/download-sample/30days') ?>" class="text-sky-700 hover:text-sky-900 font-bold bg-white border border-slate-200 hover:border-sky-300 px-2.5 py-1 rounded-lg transition flex items-center gap-1 shadow-2xs">
                  <i class="fa-solid fa-download text-[10px]"></i>
                  <span>Sample 30 Hari (CSV)</span>
                </a>
                <a href="<?= site_url('admiralty/download-sample/15days') ?>" class="text-teal-700 hover:text-teal-900 font-bold bg-white border border-slate-200 hover:border-teal-300 px-2.5 py-1 rounded-lg transition flex items-center gap-1 shadow-2xs">
                  <i class="fa-solid fa-download text-[10px]"></i>
                  <span>Sample 15 Hari (CSV)</span>
                </a>
              </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
              <button type="submit" id="validateButton" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span>Validasi Berkas</span>
              </button>
              <button type="button" class="reset-admiralty-btn px-4 py-3 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-semibold text-sm transition cursor-pointer" style="display:none;" title="Reset Form">
                <i class="fa-solid fa-undo"></i>
              </button>
            </div>
          </form>
        </div>

        <!-- Hasil Evaluasi / Health Card -->
        <div class="lg:col-span-5 flex flex-col gap-5">
          <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between h-full">
            <div>
              <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                  <i class="fa-solid fa-heart-pulse text-rose-500"></i>
                  Status Kelayakan Data
                </h3>
                <span id="metricQualityStatus" class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 border border-slate-200">
                  Belum Divalidasi
                </span>
              </div>

              <!-- Metric Tiles -->
              <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3">
                  <div class="text-[11px] text-slate-500 mb-0.5">Jumlah Baris Valid</div>
                  <div class="text-2xl font-extrabold text-slate-900" id="summaryCount">0</div>
                  <div class="text-[11px] text-slate-500 mt-0.5" id="metricMinStatus">Min. 360 data</div>
                </div>
                <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3">
                  <div class="text-[11px] text-slate-500 mb-0.5">Interval Waktu</div>
                  <div class="text-2xl font-extrabold text-slate-900" id="summaryInterval">-</div>
                  <div class="text-[11px] text-slate-500 mt-0.5" id="metricMaxStatus">Maks. 720 data</div>
                </div>
                <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3">
                  <div class="text-[11px] text-slate-500 mb-0.5">Status Gap Data</div>
                  <div class="text-xl font-bold text-slate-900" id="summaryGap">Tidak</div>
                  <div class="text-[11px] text-slate-500 mt-0.5">Gap terdeteksi: <span id="metricGapCount" class="font-semibold">0</span></div>
                </div>
                <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3">
                  <div class="text-[11px] text-slate-500 mb-0.5">Duplikasi Baris</div>
                  <div class="text-xl font-bold text-slate-900" id="summaryDuplicates">0</div>
                  <div class="text-[11px] text-slate-500 mt-0.5">Baris invalid: <span id="metricInvalidRows" class="font-semibold">0</span></div>
                </div>
              </div>

              <!-- Timeline & Quality Notes -->
              <div class="border border-slate-200/80 rounded-xl p-3.5 bg-slate-50/50 mb-4 text-xs text-slate-600 flex flex-col gap-2">
                <div class="flex justify-between">
                  <span class="text-slate-400">Tanggal/Jam Mulai:</span>
                  <span id="metricStart" class="font-semibold text-slate-800">-</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400">Tanggal/Jam Akhir:</span>
                  <span id="metricEnd" class="font-semibold text-slate-800">-</span>
                </div>
                <div class="pt-2 border-t border-slate-200 text-slate-700" id="qualityReasons">
                  Status kualitas dataset akan muncul setelah validasi.
                </div>
              </div>

              <!-- Hidden legacy placeholders for compatibility -->
              <div style="display:none;">
                <span id="metricValidRows">-</span>
                <div id="statusList"></div>
                <div id="gapList"></div>
                <div id="invalidList"></div>
              </div>
            </div>

            <!-- Step 1 Footer Action -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
              <span id="calculationEligibility" class="text-xs text-slate-500">Lakukan validasi data terlebih dahulu.</span>
              <button type="button" id="saveDatasetButton" disabled class="bg-sky-600 hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                <span>Simpan & Lanjut ke Model</span>
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Data Observasi (Collapsible) -->
      <div id="resultArea" style="display:none;" class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80">
        <h4 class="font-bold text-slate-900 text-base mb-3 flex items-center gap-2">
          <i class="fa-solid fa-table-list text-sky-600"></i>
          Pratinjau Data Observasi (10 Baris Pertama)
        </h4>
        <div class="overflow-x-auto custom-scroll max-h-56 border border-slate-200 rounded-xl">
          <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
              <tr>
                <th class="p-2.5">Line</th>
                <th class="p-2.5">Tanggal / Jam</th>
                <th class="p-2.5">Tinggi Muka Air (m)</th>
              </tr>
            </thead>
            <tbody id="previewBody" class="divide-y divide-slate-100 text-slate-700 font-mono">
              <tr><td colspan="3" class="p-3 text-center text-slate-400 font-sans">Belum ada data observasi.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAHAP 2: PILIHAN MODEL & KALKULASI -->
    <!-- ============================================================== -->
    <div id="stepContent-2" class="hidden flex flex-col gap-6">
      <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h3 class="font-bold text-slate-900 text-lg">Pilih Model Perhitungan Admiralty</h3>
            <p class="text-sm text-slate-500">Pilih satu atau beberapa metode sekaligus untuk dijalankan dan dibandingkan akurasinya.</p>
          </div>
          <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
            <i class="fa-solid fa-check-circle"></i>Dataset Aktif Siap Dihitung
          </span>
        </div>

        <!-- Model Cards Grid (2 Model Admiralty + 1 Least Square) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Model 1: Admiralty Indonesia -->
          <label class="relative flex flex-col justify-between p-5 rounded-2xl border-2 border-sky-500 bg-sky-50/40 cursor-pointer hover:bg-sky-50 transition">
            <div>
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-sky-600 text-white">Rekomendasi Dishidros</span>
                <input type="checkbox" value="admiralty_indonesia" checked class="model-checkbox w-5 h-5 rounded text-sky-600 focus:ring-sky-500">
              </div>
              <h4 class="font-bold text-slate-900 text-base mb-1">Admiralty Indonesia</h4>
              <p class="text-xs text-slate-600 leading-relaxed">Standar baku Dishidros TNI AL, menghasilkan 9 konstanta harmonik utama dengan tabel Form 20 (Engine Native PHP).</p>
            </div>
            <div class="text-[11px] font-semibold text-sky-700 mt-4 pt-3 border-t border-sky-200 flex items-center justify-between">
              <span>9 Komponen + Formzahl</span>
              <i class="fa-solid fa-circle-check text-sky-600"></i>
            </div>
          </label>

          <!-- Model 2: Admiralty Cat A -->
          <label class="relative flex flex-col justify-between p-5 rounded-2xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition">
            <div>
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">IHO Cat A</span>
                <input type="checkbox" value="admiralty_cat_a" checked class="model-checkbox w-5 h-5 rounded text-sky-600 focus:ring-sky-500">
              </div>
              <h4 class="font-bold text-slate-900 text-base mb-1">Admiralty Cat A</h4>
              <p class="text-xs text-slate-600 leading-relaxed">Penyelarasan parameter referensi standar hidrografi internasional (Cat A) via Fourier harmonik kontinu.</p>
            </div>
            <div class="text-[11px] font-semibold text-slate-600 mt-4 pt-3 border-t border-slate-100">
              Standar Organisasi Hidrografi
            </div>
          </label>

          <!-- Model 3: Least Square -->
          <label class="relative flex flex-col justify-between p-5 rounded-2xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition">
            <div>
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">Regresi</span>
                <input type="checkbox" value="least_square" checked class="model-checkbox w-5 h-5 rounded text-sky-600 focus:ring-sky-500">
              </div>
              <h4 class="font-bold text-slate-900 text-base mb-1">Least Square</h4>
              <p class="text-xs text-slate-600 leading-relaxed">Pencocokan kuadrat terkecil langsung dari kurva observasi untuk kontrol RMSE optimal.</p>
            </div>
            <div class="text-[11px] font-semibold text-slate-600 mt-4 pt-3 border-t border-slate-100">
              Evaluasi Error Minimum
            </div>
          </label>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <button type="button" onclick="goToStep(1)" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
            </button>
            <span id="modelSelectionHint" class="text-xs text-slate-500">Pilih minimal satu model perhitungan untuk mengaktifkan tombol hitung.</span>
          </div>
          <button type="button" id="startCalculationButton" class="bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow-md shadow-sky-600/25 transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-solid fa-calculator"></i>
            <span>Mulai Hitung Admiralty</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAHAP 3: HASIL HARMONIK & EVALUASI -->
    <!-- ============================================================== -->
    <div id="stepContent-3" class="hidden flex flex-col gap-6">
      
      <!-- Key Metric Tiles -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-5 shadow-sm border border-slate-200/80">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tipe Pasang Surut</div>
          <div class="text-xl font-extrabold text-sky-600" id="dynTideType">Campuran Ganda</div>
          <div class="text-xs text-slate-500 mt-1">Formzahl: <strong id="dynFormzahl">F = -</strong></div>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-sm border border-slate-200/80">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Mean Sea Level (MSL)</div>
          <div class="text-xl font-extrabold text-slate-900" id="componentMsl">- m</div>
          <div class="text-xs text-slate-500 mt-1">Rerata muka air acuan (S0)</div>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-sm border border-slate-200/80">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Run Aktif Terpilih</div>
          <div class="text-xl font-extrabold text-emerald-600" id="componentModelLabel">-</div>
          <div class="text-xs text-slate-500 mt-1">Kode: <span id="componentRunCode" class="font-mono font-semibold">-</span></div>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-sm border border-slate-200/80">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Komparasi</div>
          <div class="text-xl font-extrabold text-indigo-600" id="comparisonChartStatus">Siap Dievaluasi</div>
          <div class="text-xs text-slate-500 mt-1">Titik sinkron: <strong id="comparisonChartPoints">-</strong> titik</div>
        </div>
      </div>

      <!-- Chart & Table Split -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Comparison Chart Canvas -->
        <div class="lg:col-span-7 glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
              <div>
                <h3 class="font-bold text-slate-900 text-lg">Perbandingan Model vs Observasi</h3>
                <p class="text-xs text-slate-500">Evaluasi pencocokan elevasi pasut hasil model terhadap data riil.</p>
              </div>
              <span id="comparisonChartSeries" class="text-xs font-semibold text-slate-600 font-mono">-</span>
            </div>
            <div class="h-72 w-full relative">
              <canvas id="comparisonChartCanvas"></canvas>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Klik label seri pada legenda grafik untuk menyembunyikan / menampilkan kurva.</span>
            <button type="button" id="exportComparisonCsvButton" disabled class="text-xs font-semibold text-slate-700 hover:text-sky-600 flex items-center gap-1 cursor-pointer">
              <i class="fa-solid fa-download"></i>Download CSV Komparasi
            </button>
          </div>
        </div>

        <!-- 9 Harmonic Constituents Table -->
        <div class="lg:col-span-5 glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-3">
              <h3 class="font-bold text-slate-900 text-lg">9 Konstanta Harmonik</h3>
              <span class="text-xs text-slate-500 font-mono">Form 20 Dishidros</span>
            </div>
            <div class="overflow-x-auto custom-scroll max-h-72 border border-slate-200 rounded-xl">
              <table class="w-full text-xs text-left">
                <thead class="text-slate-500 bg-slate-50 uppercase tracking-wider sticky top-0">
                  <tr>
                    <th class="p-2.5">Komponen</th>
                    <th class="p-2.5">Kelompok</th>
                    <th class="p-2.5">Amplitudo (cm)</th>
                    <th class="p-2.5">Fase g°</th>
                  </tr>
                </thead>
                <tbody id="componentTableBody" class="divide-y divide-slate-100 text-slate-700 font-mono">
                  <tr><td colspan="4" class="p-3 text-center text-slate-400 font-sans">Komponen belum dihitung.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="goToStep(2)" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              <i class="fa-solid fa-arrow-left mr-1"></i>Pilih Model
            </button>
            <div class="flex items-center gap-2">
              <button type="button" id="openOfficialReportModalBtn2" disabled class="bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" title="Buka Dokumen Laporan Resmi A4 Siap Cetak PDF">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Laporan Survei Resmi (A4)</span>
              </button>
              <button type="button" onclick="goToStep(4)" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                <span>Buka Generator Prediksi</span>
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- SECTION ELEVASI MUKA AIR PENTING (TIDAL DATUMS) -->
      <!-- ============================================================ -->
      <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-3">
          <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
              <i class="fa-solid fa-water text-sky-600"></i>
              Elevasi Muka Air Penting (Tidal Datums) & Tunggang Pasut
            </h3>
            <p class="text-xs text-slate-500">Standar hidro-oseanografi (Dishidros TNI AL / IHO). Nilai terkalibrasi otomatis terupdate saat slider kalibrasi digeser.</p>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <div id="tidalDatumsModelTabs" class="flex items-center bg-slate-100 p-1 rounded-xl gap-1 text-xs font-semibold">
              <span class="text-slate-400 px-2 py-1 text-xs font-normal">Menunggu kalkulasi...</span>
            </div>
            <button type="button" id="exportTidalDatumsCsvButton" disabled class="text-xs font-semibold text-slate-700 hover:text-sky-600 bg-white border border-slate-200 hover:border-sky-300 px-3 py-1.5 rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
              <i class="fa-solid fa-download"></i>
              <span>Download CSV Datums</span>
            </button>
            <button type="button" id="openOfficialReportModalBtn" disabled class="text-xs font-semibold text-rose-700 hover:text-white hover:bg-rose-600 bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" title="Buka Dokumen Laporan Resmi A4 Siap Cetak PDF">
              <i class="fa-solid fa-file-pdf"></i>
              <span>Laporan Resmi PDF</span>
            </button>
          </div>
        </div>

        <!-- Key Quick Metric Tiles (HAT, MHWS, MSL, LAT) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="bg-gradient-to-br from-rose-50 to-orange-50/40 border border-rose-100 rounded-xl p-3.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-rose-700 flex items-center justify-between">
              <span>HAT (Tertinggi)</span>
              <span class="text-[9px] px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">Astronomis</span>
            </div>
            <div class="text-xl font-extrabold text-rose-900 font-mono mt-1" id="datumHatVal">-</div>
            <div class="text-[10px] text-rose-600 mt-0.5" id="datumHatDiff">- dari MSL</div>
          </div>
          <div class="bg-gradient-to-br from-emerald-50 to-teal-50/40 border border-emerald-100 rounded-xl p-3.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 flex items-center justify-between">
              <span>MHWS (Purnama)</span>
              <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">Spring High</span>
            </div>
            <div class="text-xl font-extrabold text-emerald-900 font-mono mt-1" id="datumMhwsVal">-</div>
            <div class="text-[10px] text-emerald-600 mt-0.5" id="datumMhwsDiff">- dari MSL</div>
          </div>
          <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-200 rounded-xl p-3.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center justify-between">
              <span>MSL (Muka Air Rerata)</span>
              <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-800 font-mono">S0</span>
            </div>
            <div class="text-xl font-extrabold text-slate-900 font-mono mt-1" id="datumMslVal">-</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Datum Acuan Nol (0.00 m)</div>
          </div>
          <div class="bg-gradient-to-br from-indigo-50 to-violet-50/40 border border-indigo-100 rounded-xl p-3.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 flex items-center justify-between">
              <span>LAT (Chart Datum)</span>
              <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800">Bidang Peta</span>
            </div>
            <div class="text-xl font-extrabold text-indigo-900 font-mono mt-1" id="datumLatVal">-</div>
            <div class="text-[10px] text-indigo-600 mt-0.5" id="datumLatDiff">- dari MSL</div>
          </div>
        </div>

        <!-- Single Model Datums View -->
        <div id="singleModelDatumsView" class="flex flex-col gap-4">
          <div class="overflow-x-auto custom-scroll border border-slate-200 rounded-xl bg-white">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider border-b border-slate-200">
                <tr>
                  <th class="p-3">Elevasi Datum</th>
                  <th class="p-3">Definisi Hidrografi & Keterangan</th>
                  <th class="p-3">Formula Harmonik (Dishidros)</th>
                  <th class="p-3 text-right">Nilai Model Asli</th>
                  <th class="p-3 text-right text-teal-700">Terkalibrasi</th>
                  <th class="p-3 text-right">Relatif MSL</th>
                  <th class="p-3 text-right">Di Atas LAT (CD)</th>
                </tr>
              </thead>
              <tbody id="tidalDatumsTableBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                <tr><td colspan="7" class="p-4 text-center text-slate-400 font-sans">Elevasi muka air penting belum dihitung.</td></tr>
              </tbody>
            </table>
          </div>

          <!-- Tidal Ranges Section -->
          <div>
            <div class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1.5">
              <i class="fa-solid fa-arrows-up-down text-sky-600"></i>
              <span>Tunggang Pasang Surut (Tidal Ranges)</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                <div>
                  <div class="text-[11px] font-bold text-slate-700">Tunggang Purnama (Spring Range)</div>
                  <div class="text-[10px] text-slate-500 font-mono">2 × (M2 + S2)</div>
                </div>
                <div class="text-lg font-extrabold text-slate-900 font-mono mt-2" id="datumSpringRange">-</div>
              </div>
              <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                <div>
                  <div class="text-[11px] font-bold text-slate-700">Tunggang Perbani (Neap Range)</div>
                  <div class="text-[10px] text-slate-500 font-mono">2 × |M2 - S2|</div>
                </div>
                <div class="text-lg font-extrabold text-slate-900 font-mono mt-2" id="datumNeapRange">-</div>
              </div>
              <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                <div>
                  <div class="text-[11px] font-bold text-slate-700">Tunggang Rerata (Mean Range)</div>
                  <div class="text-[10px] text-slate-500 font-mono">2 × M2</div>
                </div>
                <div class="text-lg font-extrabold text-slate-900 font-mono mt-2" id="datumMeanRange">-</div>
              </div>
              <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                <div>
                  <div class="text-[11px] font-bold text-slate-700">Tunggang Maks. Astronomis</div>
                  <div class="text-[10px] text-slate-500 font-mono">HAT - LAT (2 × Σ Ai)</div>
                </div>
                <div class="text-lg font-extrabold text-sky-700 font-mono mt-2" id="datumMaxRange">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Multi-Model Comparison Table View -->
        <div id="multiModelDatumsView" class="flex flex-col gap-4" style="display:none;">
          <div class="overflow-x-auto custom-scroll border border-slate-200 rounded-xl bg-white">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider border-b border-slate-200" id="multiModelDatumsHead">
                <!-- Injected dynamically -->
              </thead>
              <tbody id="multiModelDatumsBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                <!-- Injected dynamically -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tidal Datums Elevation Chart Card (Reference Elevation Lines) -->
        <div class="border border-slate-200 rounded-2xl p-5 bg-white shadow-xs flex flex-col gap-3">
          <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-slate-100">
            <div>
              <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-sky-600"></i>
                <span id="datumChartHeaderTitle">Grafik Kedudukan Elevasi Penting Pasang Surut</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-sky-100 text-sky-800" id="datumChartModelBadge">Admiralty Indonesia</span>
              </h4>
              <p class="text-[11px] text-slate-500 mt-0.5" id="datumChartHeaderDesc">
                Visualisasi kurva fluktuasi muka air terhadap garis elevasi datum astronomis (HAT, HHWL, MHWS, MHWL, MHWN, MSL, MLWN, MLWL, MLWS, LLWL, LAT).
              </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <!-- Engine Selector for Chart -->
              <div id="datumChartEngineSelectorWrap" class="flex items-center gap-1.5 text-xs text-slate-600">
                <span class="font-medium text-[11px] text-slate-500">Engine Acuan:</span>
                <select id="datumChartEngineSelect" class="border border-slate-200 rounded-lg px-2 py-1 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-1 focus:ring-sky-500 cursor-pointer">
                  <option value="admiralty_indonesia">Admiralty Indonesia (Dishidros Form 20)</option>
                  <option value="admiralty_cat_a">Admiralty Cat A (IHO)</option>
                  <option value="least_square">Least Square</option>
                </select>
              </div>
              <!-- Curve Selector: Both vs Observasi vs Model -->
              <div class="flex items-center bg-slate-100 p-0.5 rounded-lg text-xs font-semibold">
                <button type="button" id="datumChartBtnBoth" class="px-2.5 py-1 rounded-md text-xs cursor-pointer bg-white text-slate-900 shadow-xs font-bold transition">Semua Kurva</button>
                <button type="button" id="datumChartBtnObserved" class="px-2.5 py-1 rounded-md text-xs cursor-pointer text-slate-500 hover:text-slate-800 transition">Observasi Saja</button>
                <button type="button" id="datumChartBtnModel" class="px-2.5 py-1 rounded-md text-xs cursor-pointer text-slate-500 hover:text-slate-800 transition">Model Saja</button>
              </div>
              <!-- Download PNG -->
              <button type="button" id="exportDatumChartPngButton" class="text-xs font-semibold text-slate-700 hover:text-sky-600 bg-white border border-slate-200 hover:border-sky-300 px-3 py-1 rounded-lg shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-camera text-slate-400"></i>
                <span>Simpan Gambar PNG</span>
              </button>
            </div>
          </div>

          <div class="relative w-full h-[420px] md:h-[480px]">
            <canvas id="datumElevationChartCanvas"></canvas>
          </div>
          
          <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-50 flex-wrap gap-2">
            <div class="flex items-center gap-2.5 flex-wrap text-[10px]">
              <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-rose-700 inline-block border-t border-dashed border-rose-700"></span> <strong>HAT / HHWL</strong> (Ekstrem Pasang)</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-amber-600 inline-block border-t border-dashed border-amber-600"></span> <strong>MHWS / MHWL / MHWN</strong> (Pasang Rerata)</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-slate-900 inline-block"></span> <strong>MSL</strong> (Duduk Tengah Acuan)</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-teal-600 inline-block border-t border-dashed border-teal-600"></span> <strong>MLWN / MLWL / MLWS</strong> (Surut Rerata)</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-violet-700 inline-block border-t border-dashed border-violet-700"></span> <strong>LLWL / LAT</strong> (Ekstrem Surut)</span>
            </div>
            <span class="italic text-slate-400">💡 Klik nama garis pada legenda untuk filter garis datum</span>
          </div>
        </div>

        <!-- Information Note -->
        <div class="text-[11px] text-slate-600 bg-slate-50/80 border border-slate-200/80 rounded-xl p-3 flex items-start gap-2.5">
          <i class="fa-solid fa-circle-info text-sky-600 text-sm mt-0.5"></i>
          <div>
            <strong>Keterangan Acuan Elevasi:</strong> Elevasi dihitung secara analitis dari komponen harmonik pasut. Nilai <strong>Model Asli</strong> dan <strong>Terkalibrasi</strong> adalah tinggi elevasi di atas titik nol palem ukur (zero gauge datum). Kolom <strong>Di Atas LAT</strong> (Lowest Astronomical Tide) adalah tinggi muka air terhadap bidang surutan peta navigasi (Chart Datum), yang menjadi parameter kritis penentuan kedalaman alur kapal aman (depth clearance) dan elevasi mercu dermaga.
          </div>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- SECTION TOOLS KALIBRASI & ADJUSTMENT MODEL (LIVE TUNING) -->
      <!-- ============================================================ -->
      <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
          <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
              <i class="fa-solid fa-sliders text-teal-600"></i>
              Tools Kalibrasi & Penyesuaian Model (Live Tuning)
            </h3>
            <p class="text-xs text-slate-500">Geser slider amplitudo, fasa, atau pergeseran waktu (time shift). Kurva pada grafik di atas akan berubah secara langsung (real-time).</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="autoCalibrateAllButton" class="bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer" title="Otomatis mencari kombinasi parameter terbaik untuk semua model aktif">
              <i class="fa-solid fa-wand-magic-sparkles"></i>
              <span>Auto-Kalibrasi Semua Model</span>
            </button>
            <span class="text-xs px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 font-semibold border border-teal-200">
              Realtime Live Sync
            </span>
          </div>
        </div>

        <!-- 3 Model Adjustment Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- 1. Admiralty Indonesia Adjustment -->
          <div class="adjustment-card bg-slate-50/70 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between gap-3" id="indonesiaAdjustmentCard" data-model-name="admiralty_indonesia" style="display:none;">
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                <strong class="text-xs font-bold text-slate-800">Adjustment Admiralty Indonesia</strong>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-800" id="indonesiaAdjustmentStatus">Aktif</span>
                <button type="button" class="text-xs font-semibold text-teal-700 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200/80 px-2 py-0.5 rounded-lg transition flex items-center gap-1 cursor-pointer" id="autoCalibrateIndonesiaButton" title="Auto-Kalibrasi ke Data Observasi">
                  <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>Auto
                </button>
                <button type="button" class="text-xs text-slate-500 hover:text-rose-600 transition px-1" id="resetIndonesiaAdjustmentButton" title="Reset Nilai"><i class="fa-solid fa-rotate-left mr-0.5"></i>Reset</button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 text-xs">
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">Amplitudo</span>
                  <span class="text-teal-700 font-mono" id="indonesiaAmplitudeAdjustValue">0%</span>
                </div>
                <input type="range" min="-100" max="100" step="1" value="0" id="indonesiaAmplitudeAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-teal-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">Fase</span>
                  <span class="text-teal-700 font-mono" id="indonesiaPhaseAdjustValue">0°</span>
                </div>
                <input type="range" min="-180" max="180" step="1" value="0" id="indonesiaPhaseAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-teal-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">P1 (Amplitudo)</span>
                  <span class="text-teal-700 font-mono" id="indonesiaP1AmplitudeAdjustValue">0%</span>
                </div>
                <input type="range" min="-100" max="100" step="1" value="0" id="indonesiaP1AmplitudeAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-teal-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">P1 (Fase)</span>
                  <span class="text-teal-700 font-mono" id="indonesiaP1PhaseAdjustValue">0°</span>
                </div>
                <input type="range" min="-180" max="180" step="1" value="0" id="indonesiaP1PhaseAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-teal-600">
              </div>
            </div>

            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
              <span class="text-[11px] text-slate-500 font-medium">Shift Waktu:</span>
              <div class="flex items-center gap-1.5">
                <button type="button" class="w-6 h-6 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-xs cursor-pointer" id="indonesiaTimeShiftMinus">-</button>
                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono font-bold text-slate-800 text-xs" id="indonesiaTimeShiftValue">0 jam</span>
                <button type="button" class="w-6 h-6 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-xs cursor-pointer" id="indonesiaTimeShiftPlus">+</button>
              </div>
            </div>
            <div id="indonesiaCalibrationInfo" class="text-[11px] font-medium text-slate-600 bg-emerald-50/90 border border-emerald-200/80 rounded-lg p-2.5 flex items-center justify-between" style="display:none;"></div>
          </div>

          <!-- 3. Admiralty Cat A Adjustment -->
          <div class="adjustment-card bg-slate-50/70 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between gap-3" id="catAAdjustmentCard" data-model-name="admiralty_cat_a" style="display:none;">
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                <strong class="text-xs font-bold text-slate-800">Adjustment Admiralty Cat A</strong>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800" id="catAAdjustmentStatus">Aktif</span>
                <button type="button" class="text-xs font-semibold text-rose-700 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 px-2 py-0.5 rounded-lg transition flex items-center gap-1 cursor-pointer" id="autoCalibrateCatAButton" title="Auto-Kalibrasi ke Data Observasi">
                  <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>Auto
                </button>
                <button type="button" class="text-xs text-slate-500 hover:text-rose-600 transition px-1" id="resetCatAAdjustmentButton" title="Reset Nilai"><i class="fa-solid fa-rotate-left mr-0.5"></i>Reset</button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 text-xs">
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">Amplitudo</span>
                  <span class="text-rose-700 font-mono" id="catAAmplitudeAdjustValue">0%</span>
                </div>
                <input type="range" min="-100" max="100" step="1" value="0" id="catAAmplitudeAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">Fase</span>
                  <span class="text-rose-700 font-mono" id="catAPhaseAdjustValue">0°</span>
                </div>
                <input type="range" min="-180" max="180" step="1" value="0" id="catAPhaseAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">P1 (Amplitudo)</span>
                  <span class="text-rose-700 font-mono" id="catAP1AmplitudeAdjustValue">0%</span>
                </div>
                <input type="range" min="-100" max="100" step="1" value="0" id="catAP1AmplitudeAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">P1 (Fase)</span>
                  <span class="text-rose-700 font-mono" id="catAP1PhaseAdjustValue">0°</span>
                </div>
                <input type="range" min="-180" max="180" step="1" value="0" id="catAP1PhaseAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-600">
              </div>
            </div>

            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
              <span class="text-[11px] text-slate-500 font-medium">Shift Waktu:</span>
              <div class="flex items-center gap-1.5">
                <button type="button" class="w-6 h-6 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-xs cursor-pointer" id="catATimeShiftMinus">-</button>
                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono font-bold text-slate-800 text-xs" id="catATimeShiftValue">0 jam</span>
                <button type="button" class="w-6 h-6 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-xs cursor-pointer" id="catATimeShiftPlus">+</button>
              </div>
            </div>
            <div id="catACalibrationInfo" class="text-[11px] font-medium text-slate-600 bg-emerald-50/90 border border-emerald-200/80 rounded-lg p-2.5 flex items-center justify-between" style="display:none;"></div>
          </div>

          <!-- 4. Least Square Adjustment -->
          <div class="adjustment-card bg-slate-50/70 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between gap-3" id="leastSquareAdjustmentCard" data-model-name="least_square" style="display:none;">
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <strong class="text-xs font-bold text-slate-800">Adjustment Least Square</strong>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800" id="leastSquareAdjustmentStatus">Aktif</span>
                <button type="button" class="text-xs font-semibold text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200/80 px-2 py-0.5 rounded-lg transition flex items-center gap-1 cursor-pointer" id="autoCalibrateLeastSquareButton" title="Auto-Kalibrasi ke Data Observasi">
                  <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>Auto
                </button>
                <button type="button" class="text-xs text-slate-500 hover:text-rose-600 transition px-1" id="resetLeastSquareAdjustmentButton" title="Reset Nilai"><i class="fa-solid fa-rotate-left mr-0.5"></i>Reset</button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 text-xs">
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">Amplitudo</span>
                  <span class="text-blue-700 font-mono" id="leastSquareAmplitudeAdjustValue">0%</span>
                </div>
                <input type="range" min="-100" max="100" step="1" value="0" id="leastSquareAmplitudeAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">Fase</span>
                  <span class="text-blue-700 font-mono" id="leastSquarePhaseAdjustValue">0°</span>
                </div>
                <input type="range" min="-180" max="180" step="1" value="0" id="leastSquarePhaseAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">P1 (Amplitudo)</span>
                  <span class="text-blue-700 font-mono" id="leastSquareP1AmplitudeAdjustValue">0%</span>
                </div>
                <input type="range" min="-100" max="100" step="1" value="0" id="leastSquareP1AmplitudeAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
              </div>
              <div>
                <div class="flex justify-between text-[11px] font-semibold mb-1">
                  <span class="text-slate-600">P1 (Fase)</span>
                  <span class="text-blue-700 font-mono" id="leastSquareP1PhaseAdjustValue">0°</span>
                </div>
                <input type="range" min="-180" max="180" step="1" value="0" id="leastSquareP1PhaseAdjust" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
              </div>
            </div>

            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
              <span class="text-[11px] text-slate-500 font-medium">Shift Waktu:</span>
              <div class="flex items-center gap-1.5">
                <button type="button" class="w-6 h-6 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-xs cursor-pointer" id="leastSquareTimeShiftMinus">-</button>
                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono font-bold text-slate-800 text-xs" id="leastSquareTimeShiftValue">0 jam</span>
                <button type="button" class="w-6 h-6 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-xs cursor-pointer" id="leastSquareTimeShiftPlus">+</button>
              </div>
            </div>
            <div id="leastSquareCalibrationInfo" class="text-[11px] font-medium text-slate-600 bg-emerald-50/90 border border-emerald-200/80 rounded-lg p-2.5 flex items-center justify-between" style="display:none;"></div>
          </div>

        </div>

        <!-- Tabel Perbandingan Hasil Adjustment Konstanta Harmonik -->
        <div>
          <h4 class="font-bold text-sm text-slate-800 mb-2">Penyesuaian Konstanta Harmonik (Original vs Calibrated)</h4>
          <div class="overflow-x-auto custom-scroll max-h-56 border border-slate-200 rounded-xl bg-white">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
                <tr>
                  <th class="p-2.5">Model</th>
                  <th class="p-2.5">Komponen</th>
                  <th class="p-2.5">Amp Awal (cm)</th>
                  <th class="p-2.5">Amp Calibrated</th>
                  <th class="p-2.5">Fase Awal</th>
                  <th class="p-2.5">Fase Calibrated</th>
                  <th class="p-2.5">Shift Waktu</th>
                </tr>
              </thead>
              <tbody id="adjustedConstantsBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                <tr><td colspan="7" class="p-3 text-center text-slate-400 font-sans">Adjustment konstanta harmonik akan muncul di sini.</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tabel Evaluasi Error (RMSE, MAE, Bias) & Phase Alignment -->
        <div>
          <h4 class="font-bold text-sm text-slate-800 mb-2">Evaluasi Akurasi Model & Error</h4>
          <div class="overflow-x-auto custom-scroll max-h-48 border border-slate-200 rounded-xl bg-white mb-3">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
                <tr>
                  <th class="p-2.5">Model</th>
                  <th class="p-2.5">Mode</th>
                  <th class="p-2.5">Status</th>
                  <th class="p-2.5">Titik</th>
                  <th class="p-2.5">RMSE (m)</th>
                  <th class="p-2.5">MAE (m)</th>
                  <th class="p-2.5">Bias</th>
                  <th class="p-2.5">Error Puncak</th>
                  <th class="p-2.5">Error Surut</th>
                </tr>
              </thead>
              <tbody id="comparisonEvaluationBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                <tr><td colspan="9" class="p-3 text-center text-slate-400 font-sans">Evaluasi akurasi akan muncul setelah kalkulasi.</td></tr>
              </tbody>
            </table>
          </div>

          <!-- Phase Alignment Box -->
          <div id="phaseAlignmentCard" class="bg-sky-50 border border-sky-200 rounded-xl p-3 text-xs text-sky-900" style="display:none;">
            <strong><i class="fa-solid fa-code-compare mr-1"></i>Phase Alignment Summary:</strong>
            <div id="phaseAlignmentBody" class="mt-1 font-mono text-slate-700">Belum ada phase alignment.</div>
          </div>
        </div>

      </div>

      <!-- Collapsible Form 20 Working Table & Multi-model Results -->
      <details class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 cursor-pointer">
        <summary class="font-bold text-slate-900 text-base flex items-center justify-between select-none">
          <span class="flex items-center gap-2"><i class="fa-solid fa-table text-sky-600"></i>Lihat Tabel Kerja Lengkap (Form 20, Rangkuman Model & Matriks Komparasi)</span>
          <span class="text-xs text-sky-600 font-normal">Klik untuk buka/tutup</span>
        </summary>
        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col gap-5">
          <div id="calculationTables">
            <h5 id="workingTableTitle" class="font-bold text-sm text-slate-800 mb-2">Tabel Kerja Admiralty</h5>
            <div class="overflow-x-auto custom-scroll max-h-64 border border-slate-200 rounded-xl mb-4">
              <table class="w-full text-xs text-left">
                <thead id="workingTableHead" class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
                  <tr><th>No</th><th>Tanggal/Jam</th><th>Hari</th><th>Jam</th><th>Elevasi</th><th>Deviasi MSL</th></tr>
                </thead>
                <tbody id="workingTableBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                  <tr><td colspan="6" class="p-3 text-center text-slate-400 font-sans">Tabel kerja belum dibuat.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Head to head comparison -->
          <div id="comparisonHighlights" class="text-xs text-slate-600"></div>

          <div id="comparisonArea">
            <h5 class="font-bold text-sm text-slate-800 mb-2">Rangkuman Komparasi Antar-Model</h5>
            <div class="overflow-x-auto custom-scroll max-h-64 border border-slate-200 rounded-xl mb-3">
              <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
                  <tr><th>Model</th><th>Run</th><th>MSL</th><th>Min</th><th>Max</th><th>Range</th><th>Durasi</th><th>Data</th><th>Zona Waktu</th></tr>
                </thead>
                <tbody id="comparisonSummaryBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                  <tr><td colspan="9" class="p-3 text-center text-slate-400 font-sans">Belum ada hasil komparasi.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
          <div id="multiModelResults" style="display:none;"></div>
          <div id="calculationNotes" class="text-xs text-slate-500 italic"></div>
        </div>
      </details>
    </div>

    <!-- ============================================================== -->
    <!-- TAHAP 4: PREDIKSI & EKSPOR LAPORAN -->
    <!-- ============================================================== -->
    <div id="stepContent-4" class="hidden flex flex-col gap-6">
      
      <!-- Prediction Settings Card -->
      <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-sky-600"></i>
            Konfigurasi Generator Prediksi Pasang Surut
          </h3>
          <span class="text-xs text-slate-500">Model Acuan: <strong id="predictionModel" class="text-sky-700">-</strong></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label for="predictionRunSelect" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Run Hasil Perhitungan</label>
            <select id="predictionRunSelect" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white font-mono">
              <option value="">Pilih run prediksi</option>
            </select>
          </div>
          <div>
            <label for="predictionStart" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Mulai</label>
            <input type="datetime-local" id="predictionStart" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white font-mono">
          </div>
          <div>
            <label for="predictionEnd" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Akhir</label>
            <input type="datetime-local" id="predictionEnd" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white font-mono">
          </div>
          <div>
            <label for="predictionInterval" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Interval Waktu</label>
            <select id="predictionInterval" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
              <option value="10">10 Menit (Detail Tinggi)</option>
              <option value="15">15 Menit</option>
              <option value="30">30 Menit</option>
              <option value="60" selected>60 Menit (Standar 1 Jam)</option>
            </select>
          </div>
        </div>

        <!-- Quick Range Presets -->
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs text-slate-500 font-medium">Rentang Waktu Cepat:</span>
          <button type="button" onclick="setPresetDateRange(7)" class="text-xs px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition cursor-pointer">7 Hari ke Depan</button>
          <button type="button" onclick="setPresetDateRange(14)" class="text-xs px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition cursor-pointer">14 Hari (Spring-Neap)</button>
          <button type="button" onclick="setPresetDateRange(30)" class="text-xs px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition cursor-pointer">30 Hari (1 Bulan Kalender)</button>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 border-t border-slate-100 gap-3">
          <button type="button" onclick="goToStep(3)" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali ke Harmonik & Tools
          </button>
          <div class="flex items-center gap-2 flex-wrap">
            <button type="button" id="exportWorkbookPdfButton" disabled class="px-4 py-2.5 rounded-xl border border-rose-300 bg-rose-50/70 text-xs font-bold text-rose-700 hover:bg-rose-100 transition flex items-center gap-2 cursor-pointer disabled:opacity-50" title="Buka Dokumen Laporan Resmi A4 Siap Cetak PDF">
              <i class="fa-solid fa-file-pdf text-rose-600"></i>Cetak Laporan PDF Resmi
            </button>
            <button type="button" id="generatePredictionButton" class="bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
              <i class="fa-solid fa-chart-line"></i>
              <span>Generate Prediksi</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Forecast Graph & Prediction Table -->
      <div id="predictionArea" class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col gap-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
          <div>
            <h3 class="font-bold text-slate-900 text-lg">Grafik Elevasi Pasang Surut Terprediksi</h3>
            <p class="text-xs text-slate-500">Stasiun: <strong id="predictionStation">-</strong> | Kode Run: <span id="predictionRunCode" class="font-mono">-</span></p>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
              Jumlah Titik: <strong id="predictionCount">0</strong>
            </span>
          </div>
        </div>

        <div class="h-72 w-full relative">
          <canvas id="predictionChartCanvas"></canvas>
        </div>

        <!-- Prediction Table Preview -->
        <details class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 cursor-pointer">
          <summary class="font-bold text-xs text-slate-800 select-none">
            Tampilkan Tabel Nilai Elevasi Prediksi Per Titik Waktu
          </summary>
          <div class="mt-3 overflow-x-auto custom-scroll max-h-60 border border-slate-200 rounded-lg bg-white">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
                <tr><th class="p-2.5">Tanggal / Jam</th><th class="p-2.5">Elevasi Air (m)</th></tr>
              </thead>
              <tbody id="predictionTableBody" class="divide-y divide-slate-100 font-mono text-slate-700">
                <tr><td colspan="2" class="p-3 text-center text-slate-400 font-sans">Belum ada prediksi.</td></tr>
              </tbody>
            </table>
          </div>
        </details>
      </div>
    </div>

    <!-- Hidden compatibility elements for backend logic -->
    <div style="display:none;">
      <div id="calculationArea"></div>
      <div id="componentDatasetStatus">-</div>
      <div id="calcDatasetStatus">-</div>
      <div id="calcInterval">-</div>
      <div id="calcCount">-</div>
      <div id="calcRange">-</div>
      <div id="calculationIntro"></div>
      <table id="comparisonComponentTable"><thead id="comparisonComponentHead"></thead><tbody id="comparisonComponentBody"></tbody></table>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: LAPORAN AKHIR SURVEI PASUT RESMI (OFFICIAL A4 PDF)      -->
    <!-- ============================================================== -->
    <div id="officialReportModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm hidden flex items-start justify-center p-2 sm:p-4 md:p-6 transition-all">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl flex flex-col max-h-[94vh] overflow-hidden my-auto animate-in fade-in duration-200">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 to-rose-400 flex items-center justify-center text-white shadow-md">
              <i class="fa-solid fa-file-invoice text-lg"></i>
            </div>
            <div>
              <h3 class="font-bold text-base text-white flex items-center gap-2">
                <span>Laporan Akhir Survei Pasang Surut Hidrografi</span>
                <span class="text-[10px] font-semibold uppercase tracking-wider bg-rose-500/30 text-rose-200 px-2 py-0.5 rounded border border-rose-400/30">Official A4 Ready</span>
              </h3>
              <p class="text-xs text-slate-400">Standar Pelaporan PT. Eser Geosurvey Indonesia (Hydro-Oceanography Division)</p>
            </div>
          </div>
          <button type="button" id="closeOfficialReportModalBtn" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Tutup Modal">
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <!-- Parameter Customization Bar -->
        <div class="bg-slate-50 border-b border-slate-200 p-4 text-xs shrink-0 max-h-56 overflow-y-auto custom-scroll">
          <details class="cursor-pointer" open>
            <summary class="font-bold text-slate-800 flex items-center justify-between select-none mb-3">
              <span class="flex items-center gap-1.5"><i class="fa-solid fa-sliders text-sky-600"></i>Parameter Dokumen Laporan (Sesuaikan Nama, No. Dokumen & Surveyor)</span>
              <span class="text-[11px] text-sky-600 font-normal">Klik untuk sembunyikan/tampilkan</span>
            </summary>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 font-sans">
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Model / Engine Acuan</label>
                <select id="reportParamEngine" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white font-semibold text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs"></select>
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama Perusahaan / Kop</label>
                <input type="text" id="reportParamCompany" value="PT. ESER GEOSURVEY INDONESIA" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 font-semibold focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Divisi / Unit Kerja</label>
                <input type="text" id="reportParamDivision" value="Hydro-Oceanography & Marine Survey Division" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nomor Dokumen Laporan</label>
                <input type="text" id="reportParamDocNo" value="EGI-TIDE-REP/2026/001" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 font-mono focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Judul Pekerjaan / Proyek</label>
                <input type="text" id="reportParamProject" value="SURVEI PENGAMATAN PASANG SURUT & PENENTUAN BIDANG KEDUDUKAN ELEVASI" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lokasi / Perairan</label>
                <input type="text" id="reportParamLocation" value="Perairan Indonesia" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pelaksana Survei (Surveyor)</label>
                <input type="text" id="reportParamSurveyor" value="Surveyor Hidrografi" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Quality Control (QC)</label>
                <input type="text" id="reportParamQc" value="QC Hydrographer" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Disetujui (Team Leader)</label>
                <input type="text" id="reportParamApprover" value="Chief Hydrographer" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-1 focus:ring-sky-500 text-xs">
              </div>
              <div class="sm:col-span-2 flex items-center gap-4 pt-4 flex-wrap">
                <label class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-700 cursor-pointer">
                  <input type="checkbox" id="reportParamIncludeChart" checked class="w-4 h-4 text-sky-600 rounded">
                  <span>Lampirkan Grafik Elevasi</span>
                </label>
                <label class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-700 cursor-pointer">
                  <input type="checkbox" id="reportParamIncludeSignatures" checked class="w-4 h-4 text-sky-600 rounded">
                  <span>Sertakan Lembar Pengesahan</span>
                </label>
              </div>
            </div>
          </details>
        </div>

        <!-- Action Toolbar -->
        <div class="px-6 py-2.5 bg-slate-100/90 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2 text-xs shrink-0">
          <div class="flex items-center gap-2">
            <span class="text-slate-500 font-medium">Format Output: <strong>A4 Portrait (Standar ISO)</strong></span>
            <span class="text-slate-300">|</span>
            <span class="text-emerald-700 bg-emerald-50 border border-emerald-200 font-semibold px-2 py-0.5 rounded text-[11px]">Siap Cetak / Save PDF</span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="refreshOfficialReportBtn" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 font-semibold cursor-pointer">
              <i class="fa-solid fa-arrows-rotate"></i>
              <span>Perbarui Tampilan</span>
            </button>
            <button type="button" id="openOfficialReportNewTabBtn" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 font-semibold cursor-pointer">
              <i class="fa-solid fa-arrow-up-right-from-square text-sky-600"></i>
              <span>Buka di Tab Baru</span>
            </button>
            <button type="button" id="printOfficialReportBtn" class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition flex items-center gap-1.5 font-bold shadow-sm cursor-pointer">
              <i class="fa-solid fa-print"></i>
              <span>Cetak / Simpan ke PDF</span>
            </button>
          </div>
        </div>

        <!-- Live A4 Preview Scrollable Container -->
        <div class="p-6 bg-slate-200/80 overflow-y-auto custom-scroll flex justify-center items-start flex-1 min-h-[420px]">
          <div id="officialReportDocumentTarget" class="bg-white shadow-xl rounded-sm p-8 sm:p-10 w-full max-w-[820px] text-slate-900 font-sans transition-all">
            <!-- Dynamic A4 Report Content Injected Here -->
          </div>
        </div>

      </div>
    </div>

  <!-- JAVASCRIPT LOGIC -->
  <script>
  (function(){
    const initialValidatedResult = <?= json_encode($initialValidatedResult ?? null, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
    const initialDatasetMeta = <?= json_encode($initialDatasetMeta ?? null, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
    const formDraftStorageKey = 'admiralty_form_draft_v1';

    const form = document.getElementById('uploadForm');
    const fileField = document.getElementById('tide_file');
    const selectedFileName = document.getElementById('selectedFileName');
    const stationNameField = document.getElementById('station_name');
    const latitudeField = document.getElementById('latitude');
    const longitudeField = document.getElementById('longitude');
    const timezoneField = document.getElementById('timezone');
    const modelCheckboxes = Array.from(document.querySelectorAll('.model-checkbox'));
    const validateButton = document.getElementById('validateButton');
    const resultArea = document.getElementById('resultArea');
    const saveDatasetButton = document.getElementById('saveDatasetButton');
    const startCalculationButton = document.getElementById('startCalculationButton');
    const calculationEligibility = document.getElementById('calculationEligibility');
    const modelSelectionHint = document.getElementById('modelSelectionHint');
    const predictionArea = document.getElementById('predictionArea');
    const predictionRunSelect = document.getElementById('predictionRunSelect');
    const predictionStart = document.getElementById('predictionStart');
    const predictionEnd = document.getElementById('predictionEnd');
    const predictionInterval = document.getElementById('predictionInterval');
    const generatePredictionButton = document.getElementById('generatePredictionButton');
    const exportWorkbookPdfButton = document.getElementById('exportWorkbookPdfButton');
    const exportComparisonCsvButton = document.getElementById('exportComparisonCsvButton');

    // Official Report Modal Elements
    const openOfficialReportModalBtn = document.getElementById('openOfficialReportModalBtn');
    const openOfficialReportModalBtn2 = document.getElementById('openOfficialReportModalBtn2');
    const officialReportModal = document.getElementById('officialReportModal');
    const closeOfficialReportModalBtn = document.getElementById('closeOfficialReportModalBtn');
    const refreshOfficialReportBtn = document.getElementById('refreshOfficialReportBtn');
    const openOfficialReportNewTabBtn = document.getElementById('openOfficialReportNewTabBtn');
    const printOfficialReportBtn = document.getElementById('printOfficialReportBtn');
    const officialReportDocumentTarget = document.getElementById('officialReportDocumentTarget');

    const reportParamEngine = document.getElementById('reportParamEngine');
    const reportParamCompany = document.getElementById('reportParamCompany');
    const reportParamDivision = document.getElementById('reportParamDivision');
    const reportParamDocNo = document.getElementById('reportParamDocNo');
    const reportParamProject = document.getElementById('reportParamProject');
    const reportParamLocation = document.getElementById('reportParamLocation');
    const reportParamSurveyor = document.getElementById('reportParamSurveyor');
    const reportParamQc = document.getElementById('reportParamQc');
    const reportParamApprover = document.getElementById('reportParamApprover');
    const reportParamIncludeChart = document.getElementById('reportParamIncludeChart');
    const reportParamIncludeSignatures = document.getElementById('reportParamIncludeSignatures');

    // Adjustment DOM elements
    const indonesiaAdjustmentCard = document.getElementById('indonesiaAdjustmentCard');
    const indonesiaAdjustmentStatus = document.getElementById('indonesiaAdjustmentStatus');
    const indonesiaAmplitudeAdjust = document.getElementById('indonesiaAmplitudeAdjust');
    const indonesiaAmplitudeAdjustValue = document.getElementById('indonesiaAmplitudeAdjustValue');
    const indonesiaPhaseAdjust = document.getElementById('indonesiaPhaseAdjust');
    const indonesiaPhaseAdjustValue = document.getElementById('indonesiaPhaseAdjustValue');
    const indonesiaP1AmplitudeAdjust = document.getElementById('indonesiaP1AmplitudeAdjust');
    const indonesiaP1AmplitudeAdjustValue = document.getElementById('indonesiaP1AmplitudeAdjustValue');
    const indonesiaP1PhaseAdjust = document.getElementById('indonesiaP1PhaseAdjust');
    const indonesiaP1PhaseAdjustValue = document.getElementById('indonesiaP1PhaseAdjustValue');
    const indonesiaTimeShiftMinus = document.getElementById('indonesiaTimeShiftMinus');
    const indonesiaTimeShiftPlus = document.getElementById('indonesiaTimeShiftPlus');
    const indonesiaTimeShiftValue = document.getElementById('indonesiaTimeShiftValue');
    const resetIndonesiaAdjustmentButton = document.getElementById('resetIndonesiaAdjustmentButton');

    const catAAdjustmentCard = document.getElementById('catAAdjustmentCard');
    const catAAdjustmentStatus = document.getElementById('catAAdjustmentStatus');
    const catAAmplitudeAdjust = document.getElementById('catAAmplitudeAdjust');
    const catAAmplitudeAdjustValue = document.getElementById('catAAmplitudeAdjustValue');
    const catAPhaseAdjust = document.getElementById('catAPhaseAdjust');
    const catAPhaseAdjustValue = document.getElementById('catAPhaseAdjustValue');
    const catAP1AmplitudeAdjust = document.getElementById('catAP1AmplitudeAdjust');
    const catAP1AmplitudeAdjustValue = document.getElementById('catAP1AmplitudeAdjustValue');
    const catAP1PhaseAdjust = document.getElementById('catAP1PhaseAdjust');
    const catAP1PhaseAdjustValue = document.getElementById('catAP1PhaseAdjustValue');
    const catATimeShiftMinus = document.getElementById('catATimeShiftMinus');
    const catATimeShiftPlus = document.getElementById('catATimeShiftPlus');
    const catATimeShiftValue = document.getElementById('catATimeShiftValue');
    const resetCatAAdjustmentButton = document.getElementById('resetCatAAdjustmentButton');

    const leastSquareAdjustmentCard = document.getElementById('leastSquareAdjustmentCard');
    const leastSquareAdjustmentStatus = document.getElementById('leastSquareAdjustmentStatus');
    const leastSquareAmplitudeAdjust = document.getElementById('leastSquareAmplitudeAdjust');
    const leastSquareAmplitudeAdjustValue = document.getElementById('leastSquareAmplitudeAdjustValue');
    const leastSquarePhaseAdjust = document.getElementById('leastSquarePhaseAdjust');
    const leastSquarePhaseAdjustValue = document.getElementById('leastSquarePhaseAdjustValue');
    const leastSquareP1AmplitudeAdjust = document.getElementById('leastSquareP1AmplitudeAdjust');
    const leastSquareP1AmplitudeAdjustValue = document.getElementById('leastSquareP1AmplitudeAdjustValue');
    const leastSquareP1PhaseAdjust = document.getElementById('leastSquareP1PhaseAdjust');
    const leastSquareP1PhaseAdjustValue = document.getElementById('leastSquareP1PhaseAdjustValue');
    const leastSquareTimeShiftMinus = document.getElementById('leastSquareTimeShiftMinus');
    const leastSquareTimeShiftPlus = document.getElementById('leastSquareTimeShiftPlus');
    const leastSquareTimeShiftValue = document.getElementById('leastSquareTimeShiftValue');
    const resetLeastSquareAdjustmentButton = document.getElementById('resetLeastSquareAdjustmentButton');

    // Auto-Calibration DOM elements
    const autoCalibrateAllButton = document.getElementById('autoCalibrateAllButton');
    const autoCalibrateIndonesiaButton = document.getElementById('autoCalibrateIndonesiaButton');
    const autoCalibrateCatAButton = document.getElementById('autoCalibrateCatAButton');
    const autoCalibrateLeastSquareButton = document.getElementById('autoCalibrateLeastSquareButton');

    const indonesiaCalibrationInfo = document.getElementById('indonesiaCalibrationInfo');
    const catACalibrationInfo = document.getElementById('catACalibrationInfo');
    const leastSquareCalibrationInfo = document.getElementById('leastSquareCalibrationInfo');

    // Adjustment states
    let indonesiaAdjustmentState = { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };
    let catAAdjustmentState = { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };
    let leastSquareAdjustmentState = { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };

    let latestIndonesiaAdjustmentBasis = null;
    let latestCatAAdjustmentBasis = null;
    let latestLeastSquareAdjustmentBasis = null;

    let currentStep = 1;
    let latestValidatedResult = null;
    let latestDatasetMeta = null;
    let latestCalculationResults = [];
    let latestObservedPoints = [];
    let comparisonChartInstance = null;
    let predictionChartInstance = null;
    let datumChartInstance = null;
    let datumChartCurveMode = 'both';
    let datumChartActiveEngine = 'admiralty_indonesia';
    let latestComparisonChartExport = null;
    let comparisonChartVisibility = {};

    function setText(id, text){
      const el = document.getElementById(id);
      if(el) el.textContent = text !== null && text !== undefined ? String(text) : '-';
    }
    function setHtml(id, html){
      const el = document.getElementById(id);
      if(el) el.innerHTML = html !== null && html !== undefined ? String(html) : '';
    }
    function esc(val){
      return String(val ?? '').replace(/[&<>"']/g, function(s){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
      });
    }
    function num(value, defaultValue){
      const parsed = Number(value);
      return Number.isFinite(parsed) ? parsed : defaultValue;
    }
    function toDatetimeLocal(displayValue){
      if(!displayValue || displayValue === '-') return '';
      const parts = String(displayValue).split(' ');
      if(parts.length < 2) {
        const d = new Date(displayValue);
        if(!isNaN(d.getTime())) {
          const pad = n => String(n).padStart(2,'0');
          return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }
        return '';
      }
      const dateParts = parts[0].split('/');
      const timeParts = parts[1].split(':');
      if(dateParts.length !== 3 || timeParts.length < 2) return '';
      return dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + 'T' + timeParts[0] + ':' + timeParts[1];
    }

    // STEPPER NAVIGATION
    window.goToStep = function(step){
      currentStep = step;
      for(let i = 1; i <= 4; i++){
        const content = document.getElementById(`stepContent-${i}`);
        const btn = document.getElementById(`stepBtn-${i}`);
        const numEl = document.getElementById(`stepNum-${i}`);
        if(!content || !btn || !numEl) continue;

        if(i === step){
          content.classList.remove('hidden');
          btn.className = 'step-active flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer';
          numEl.className = 'w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center font-bold text-sm';
        } else if(i < step){
          content.classList.add('hidden');
          btn.className = 'step-done flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer';
          numEl.className = 'w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm';
          numEl.innerHTML = '<i class="fa-solid fa-check text-xs"></i>';
        } else {
          content.classList.add('hidden');
          btn.className = 'step-idle flex items-center gap-3 p-3 rounded-xl transition text-left cursor-pointer';
          numEl.className = 'w-8 h-8 rounded-lg bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm';
          numEl.innerText = i;
        }
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // FORM DRAFT STORAGE
    function saveFormDraft(){
      const payload = {
        station_name: stationNameField.value || '',
        latitude: latitudeField.value || '',
        longitude: longitudeField.value || '',
        timezone: timezoneField.value || 'Asia/Jakarta'
      };
      try { window.localStorage.setItem(formDraftStorageKey, JSON.stringify(payload)); } catch(e){}
    }
    function restoreFormDraft(){
      try {
        const raw = window.localStorage.getItem(formDraftStorageKey);
        if(!raw) return;
        const payload = JSON.parse(raw);
        if(payload && typeof payload === 'object'){
          if(!stationNameField.value && payload.station_name) stationNameField.value = String(payload.station_name);
          if(!latitudeField.value && payload.latitude) latitudeField.value = String(payload.latitude);
          if(!longitudeField.value && payload.longitude) longitudeField.value = String(payload.longitude);
          if(payload.timezone) timezoneField.value = String(payload.timezone);
        }
      } catch(e){}
    }

    // RESET FUNCTIONALITY
    function syncResetButtonVisibility(show){
      document.querySelectorAll('.reset-admiralty-btn').forEach(function(el){
        el.style.display = show ? '' : 'none';
      });
    }

    async function resetAdmiraltyData(){
      if(!window.confirm('Apakah Anda yakin ingin mereset sesi dan membersihkan data validasi saat ini?')) return;
      try {
        await fetch('<?= site_url('admiralty/reset') ?>', { method: 'POST', headers: { Accept: 'application/json' } });
      } catch(e){}
      latestValidatedResult = null;
      latestDatasetMeta = null;
      latestCalculationResults = [];
      fileField.value = '';
      if(selectedFileName) selectedFileName.textContent = 'Klik atau seret berkas ke sini';
      resultArea.style.display = 'none';
      const alertEl = document.getElementById('restoredSessionAlert');
      if(alertEl) alertEl.style.display = 'none';
      syncResetButtonVisibility(false);
      saveDatasetButton.disabled = true;
      setText('summaryInterval', '-');
      setText('summaryCount', '0');
      setText('summaryGap', 'Tidak');
      setText('summaryDuplicates', '0');
      setText('metricStart', '-');
      setText('metricEnd', '-');
      setText('metricGapCount', '0');
      setText('metricInvalidRows', '0');
      setHtml('metricMinStatus', 'Min. 360 data');
      setHtml('metricMaxStatus', 'Maks. 720 data');
      setHtml('metricQualityStatus', '<span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Belum Divalidasi</span>');
      setHtml('qualityReasons', 'Status kualitas dataset akan muncul setelah validasi.');
      calculationEligibility.textContent = 'Lakukan validasi data terlebih dahulu.';
      resetIndonesiaAdjustmentState();
      resetCatAAdjustmentState();
      resetLeastSquareAdjustmentState();
      goToStep(1);
    }

    document.querySelectorAll('.reset-admiralty-btn').forEach(function(btn){
      btn.addEventListener('click', resetAdmiraltyData);
    });

    // FILE INPUT CHANGE
    fileField.addEventListener('change', function(){
      if(this.files && this.files[0]){
        selectedFileName.textContent = `${this.files[0].name} (${(this.files[0].size/1024).toFixed(1)} KB)`;
      } else {
        selectedFileName.textContent = 'Klik atau seret berkas ke sini';
      }
    });

    // SAMPLE DATA 1-CLICK LOADER
    window.loadSampleDataset = async function(type = '30days'){
      try {
        const is15 = type === '15days' || type === '15hari';
        stationNameField.value = is15 ? 'Stasiun Uji 15 Hari (Dishidros)' : 'Stasiun Uji 30 Hari';
        latitudeField.value = '-6.107800';
        longitudeField.value = '106.880300';
        timezoneField.value = 'Asia/Jakarta';
        saveFormDraft();

        const downloadUrl = '<?= site_url('admiralty/download-sample') ?>/' + (is15 ? '15days' : '30days');
        const resp = await fetch(downloadUrl);
        if(!resp.ok) throw new Error('File sample tidak ditemukan di server.');
        const blob = await resp.blob();
        const fileName = is15 ? 'sample_pasut_15hari.csv' : 'sample_pasut_30hari.csv';
        const file = new File([blob], fileName, { type: 'text/csv' });

        const dt = new DataTransfer();
        dt.items.add(file);
        fileField.files = dt.files;
        selectedFileName.textContent = `${fileName} (${is15 ? '360 Titik, 9.4 KB' : '720 Titik, 18.7 KB'})`;

        submitForm(new Event('submit'));
      } catch(err){
        window.alert('Gagal memuat sample: ' + err.message);
      }
    };

    // SUBMIT VALIDATION
    async function submitForm(event){
      if(event) event.preventDefault();
      if(!fileField.files.length){
        window.alert('Pilih berkas observasi pasut terlebih dahulu.');
        return;
      }
      const orig = validateButton.innerHTML;
      validateButton.disabled = true;
      validateButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Memvalidasi...';
      const alertEl = document.getElementById('restoredSessionAlert');
      if(alertEl) alertEl.style.display = 'none';

      try {
        const formData = new FormData(form);
        const response = await fetch('<?= site_url('admiralty/validate-upload') ?>', {
          method: 'POST',
          body: formData,
          headers: { Accept: 'application/json' }
        });
        const payload = await response.json();
        if(!response.ok || !payload.success) throw new Error(payload.message || 'Validasi gagal.');
        
        saveFormDraft();
        applyValidatedResult(payload.result);
      } catch(err){
        window.alert(err.message || 'Terjadi kesalahan saat memvalidasi file.');
      } finally {
        validateButton.disabled = false;
        validateButton.innerHTML = orig;
      }
    }
    form.addEventListener('submit', submitForm);

    function applyValidatedResult(result){
      if(!result || !result.summary) return;
      latestValidatedResult = result;
      const s = result.summary;

      setText('summaryInterval', s.interval_label || '-');
      setText('summaryCount', s.valid_rows_total || '0');
      setText('summaryGap', s.has_gap ? 'Ada Gap' : '0 Gap');
      setText('summaryDuplicates', s.duplicate_rows_total || '0');
      setText('metricStart', s.start_at || '-');
      setText('metricEnd', s.end_at || '-');
      setText('metricGapCount', s.gap_count || '0');
      setText('metricInvalidRows', s.invalid_rows_total || '0');

      setHtml('metricMinStatus', s.is_minimum_satisfied ? '<span class="text-emerald-600 font-semibold">Memenuhi (&ge; 360)</span>' : '<span class="text-rose-600 font-semibold">Kurang (&lt; 360)</span>');
      setHtml('metricMaxStatus', s.is_maximum_satisfied ? '<span class="text-emerald-600 font-semibold">Sesuai (&le; 720)</span>' : '<span class="text-amber-600 font-semibold">Lebih (&gt; 720)</span>');

      let badgeHtml = '';
      if(s.quality_status === 'analysis_ready'){
        badgeHtml = '<span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fa-solid fa-check-circle mr-1"></i>Layak Analisa</span>';
      } else if(s.quality_status === 'analysis_warning'){
        badgeHtml = '<span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Layak Bersyarat</span>';
      } else {
        badgeHtml = '<span class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300"><i class="fa-solid fa-circle-xmark mr-1"></i>Tidak Layak</span>';
      }
      setHtml('metricQualityStatus', badgeHtml);

      const reasons = Array.isArray(s.quality_reasons) ? s.quality_reasons : [];
      if(!reasons.length){
        setHtml('qualityReasons', `<div class="text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>${esc(s.quality_note || 'Dataset memenuhi kriteria untuk analisa harmonik.')}</div>`);
      } else {
        setHtml('qualityReasons', `<div class="text-amber-800 font-medium">${esc(s.quality_note || '')}</div><ul class="list-disc pl-4 mt-1 text-[11px] text-amber-700">${reasons.map(r=>`<li>${esc(r)}</li>`).join('')}</ul>`);
      }

      // Preview Rows
      if(result.preview && result.preview.length){
        resultArea.style.display = 'block';
        const rows = result.preview.slice(0, 10).map(r => `<tr><td class="p-2.5">${esc(r.line)}</td><td class="p-2.5">${esc(r.datetime)}</td><td class="p-2.5">${esc(r.water_level)}</td></tr>`).join('');
        setHtml('previewBody', rows);
      }

      const eligible = !!s.quality_can_analyze;
      saveDatasetButton.disabled = !eligible;
      calculationEligibility.textContent = eligible ? 'Dataset memenuhi kriteria. Klik Simpan & Lanjut untuk memilih model.' : 'Dataset belum memenuhi syarat analisa.';
      syncResetButtonVisibility(true);
    }

    // SAVE DATASET & GO TO STEP 2
    async function saveDataset(){
      if(!latestValidatedResult) return false;
      const orig = saveDatasetButton.innerHTML;
      saveDatasetButton.disabled = true;
      saveDatasetButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Menyimpan...';

      try {
        const formData = new FormData(form);
        const response = await fetch('<?= site_url('admiralty/save-dataset') ?>', {
          method: 'POST',
          body: formData,
          headers: { Accept: 'application/json' }
        });
        const payload = await response.json();
        if(!response.ok || !payload.success) throw new Error(payload.message || 'Penyimpanan dataset gagal.');

        applyDatasetMeta(payload.result);
        goToStep(2);
        return true;
      } catch(err){
        window.alert(err.message || 'Terjadi kesalahan saat menyimpan dataset.');
        return false;
      } finally {
        saveDatasetButton.disabled = false;
        saveDatasetButton.innerHTML = orig;
      }
    }
    saveDatasetButton.addEventListener('click', saveDataset);

    function applyDatasetMeta(meta){
      if(!meta) return;
      latestDatasetMeta = meta;
      if(meta.station_name) stationNameField.value = String(meta.station_name);
      if(meta.latitude !== undefined && meta.latitude !== null) latitudeField.value = String(meta.latitude);
      if(meta.longitude !== undefined && meta.longitude !== null) longitudeField.value = String(meta.longitude);
      if(meta.timezone) timezoneField.value = String(meta.timezone);

      updateStartCalculationState();
      syncResetButtonVisibility(true);
    }

    function getSelectedModels(){
      return modelCheckboxes.filter(c => c.checked).map(c => c.value);
    }
    function updateStartCalculationState(){
      const hasDataset = latestDatasetMeta !== null;
      const selected = getSelectedModels();
      const canStart = hasDataset && selected.length > 0;
      startCalculationButton.disabled = !canStart;
      if(!hasDataset){
        modelSelectionHint.textContent = 'Simpan dataset terlebih dahulu pada Tahap 1.';
      } else if(selected.length === 0){
        modelSelectionHint.textContent = 'Pilih minimal satu model perhitungan di atas.';
      } else {
        modelSelectionHint.textContent = `${selected.length} Model siap diproses. Klik Mulai Hitung Admiralty.`;
      }
      syncAdjustmentCardAvailability();
    }
    modelCheckboxes.forEach(cb => cb.addEventListener('change', updateStartCalculationState));

    function isModelSelected(modelName){
      return modelCheckboxes.some(cb => cb.checked && String(cb.value||'') === String(modelName||''));
    }

    function toggleAdjustmentCard(card, enabled, statusEl){
      if(!card) return;
      const disabled = !enabled;
      card.classList.toggle('is-disabled', disabled);
      card.querySelectorAll('input, button').forEach(c => c.disabled = disabled);
      if(statusEl){
        statusEl.textContent = enabled ? 'Aktif' : 'Nonaktif';
        statusEl.className = enabled ? 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-800' : 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600';
      }
    }

    function syncAdjustmentCardAvailability(){
      toggleAdjustmentCard(indonesiaAdjustmentCard, isModelSelected('admiralty_indonesia') && !!latestIndonesiaAdjustmentBasis, indonesiaAdjustmentStatus);
      toggleAdjustmentCard(catAAdjustmentCard, isModelSelected('admiralty_cat_a') && !!latestCatAAdjustmentBasis, catAAdjustmentStatus);
      toggleAdjustmentCard(leastSquareAdjustmentCard, isModelSelected('least_square') && !!latestLeastSquareAdjustmentBasis, leastSquareAdjustmentStatus);
    }

    // ADJUSTMENT UI SYNC & RESET
    function syncIndonesiaAdjustmentUi(){
      if(indonesiaAmplitudeAdjust) indonesiaAmplitudeAdjust.value = String(indonesiaAdjustmentState.amplitudePercent);
      if(indonesiaPhaseAdjust) indonesiaPhaseAdjust.value = String(indonesiaAdjustmentState.phaseDegrees);
      if(indonesiaP1AmplitudeAdjust) indonesiaP1AmplitudeAdjust.value = String(indonesiaAdjustmentState.p1AmplitudePercent);
      if(indonesiaP1PhaseAdjust) indonesiaP1PhaseAdjust.value = String(indonesiaAdjustmentState.p1PhaseDegrees);

      if(indonesiaAmplitudeAdjustValue) indonesiaAmplitudeAdjustValue.textContent = (indonesiaAdjustmentState.amplitudePercent > 0 ? '+' : '') + indonesiaAdjustmentState.amplitudePercent + '%';
      if(indonesiaPhaseAdjustValue) indonesiaPhaseAdjustValue.textContent = (indonesiaAdjustmentState.phaseDegrees > 0 ? '+' : '') + indonesiaAdjustmentState.phaseDegrees + '°';
      if(indonesiaP1AmplitudeAdjustValue) indonesiaP1AmplitudeAdjustValue.textContent = (indonesiaAdjustmentState.p1AmplitudePercent > 0 ? '+' : '') + indonesiaAdjustmentState.p1AmplitudePercent + '%';
      if(indonesiaP1PhaseAdjustValue) indonesiaP1PhaseAdjustValue.textContent = (indonesiaAdjustmentState.p1PhaseDegrees > 0 ? '+' : '') + indonesiaAdjustmentState.p1PhaseDegrees + '°';
      if(indonesiaTimeShiftValue) indonesiaTimeShiftValue.textContent = (indonesiaAdjustmentState.timeShiftHours > 0 ? '+' : '') + indonesiaAdjustmentState.timeShiftHours + ' jam';
    }
    function resetIndonesiaAdjustmentState(){
      indonesiaAdjustmentState = { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };
      syncIndonesiaAdjustmentUi();
      if(indonesiaCalibrationInfo) indonesiaCalibrationInfo.style.display = 'none';
    }

    function syncCatAAdjustmentUi(){
      if(catAAmplitudeAdjust) catAAmplitudeAdjust.value = String(catAAdjustmentState.amplitudePercent);
      if(catAPhaseAdjust) catAPhaseAdjust.value = String(catAAdjustmentState.phaseDegrees);
      if(catAP1AmplitudeAdjust) catAP1AmplitudeAdjust.value = String(catAAdjustmentState.p1AmplitudePercent);
      if(catAP1PhaseAdjust) catAP1PhaseAdjust.value = String(catAAdjustmentState.p1PhaseDegrees);

      if(catAAmplitudeAdjustValue) catAAmplitudeAdjustValue.textContent = (catAAdjustmentState.amplitudePercent > 0 ? '+' : '') + catAAdjustmentState.amplitudePercent + '%';
      if(catAPhaseAdjustValue) catAPhaseAdjustValue.textContent = (catAAdjustmentState.phaseDegrees > 0 ? '+' : '') + catAAdjustmentState.phaseDegrees + '°';
      if(catAP1AmplitudeAdjustValue) catAP1AmplitudeAdjustValue.textContent = (catAAdjustmentState.p1AmplitudePercent > 0 ? '+' : '') + catAAdjustmentState.p1AmplitudePercent + '%';
      if(catAP1PhaseAdjustValue) catAP1PhaseAdjustValue.textContent = (catAAdjustmentState.p1PhaseDegrees > 0 ? '+' : '') + catAAdjustmentState.p1PhaseDegrees + '°';
      if(catATimeShiftValue) catATimeShiftValue.textContent = (catAAdjustmentState.timeShiftHours > 0 ? '+' : '') + catAAdjustmentState.timeShiftHours + ' jam';
    }
    function resetCatAAdjustmentState(){
      catAAdjustmentState = { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };
      syncCatAAdjustmentUi();
      if(catACalibrationInfo) catACalibrationInfo.style.display = 'none';
    }

    function syncLeastSquareAdjustmentUi(){
      if(leastSquareAmplitudeAdjust) leastSquareAmplitudeAdjust.value = String(leastSquareAdjustmentState.amplitudePercent);
      if(leastSquarePhaseAdjust) leastSquarePhaseAdjust.value = String(leastSquareAdjustmentState.phaseDegrees);
      if(leastSquareP1AmplitudeAdjust) leastSquareP1AmplitudeAdjust.value = String(leastSquareAdjustmentState.p1AmplitudePercent);
      if(leastSquareP1PhaseAdjust) leastSquareP1PhaseAdjust.value = String(leastSquareAdjustmentState.p1PhaseDegrees);

      if(leastSquareAmplitudeAdjustValue) leastSquareAmplitudeAdjustValue.textContent = (leastSquareAdjustmentState.amplitudePercent > 0 ? '+' : '') + leastSquareAdjustmentState.amplitudePercent + '%';
      if(leastSquarePhaseAdjustValue) leastSquarePhaseAdjustValue.textContent = (leastSquareAdjustmentState.phaseDegrees > 0 ? '+' : '') + leastSquareAdjustmentState.phaseDegrees + '°';
      if(leastSquareP1AmplitudeAdjustValue) leastSquareP1AmplitudeAdjustValue.textContent = (leastSquareAdjustmentState.p1AmplitudePercent > 0 ? '+' : '') + leastSquareAdjustmentState.p1AmplitudePercent + '%';
      if(leastSquareP1PhaseAdjustValue) leastSquareP1PhaseAdjustValue.textContent = (leastSquareAdjustmentState.p1PhaseDegrees > 0 ? '+' : '') + leastSquareAdjustmentState.p1PhaseDegrees + '°';
      if(leastSquareTimeShiftValue) leastSquareTimeShiftValue.textContent = (leastSquareAdjustmentState.timeShiftHours > 0 ? '+' : '') + leastSquareAdjustmentState.timeShiftHours + ' jam';
    }
    function resetLeastSquareAdjustmentState(){
      leastSquareAdjustmentState = { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };
      syncLeastSquareAdjustmentUi();
      if(leastSquareCalibrationInfo) leastSquareCalibrationInfo.style.display = 'none';
    }

    // ADJUSTMENT MATH: RECALCULATING TIDAL POINTS LIVE
    function buildAdjustedSeriesFromBasis(basis, state){
      if(!basis || !Array.isArray(basis.rows) || !Array.isArray(basis.components)) return null;
      const offset = num(basis.offset, 0);
      const basePhaseOffset = num(basis.phase_offset_deg, 0);
      const amplitudeScale = 1 + (state.amplitudePercent / 100);
      const p1AmplitudeScale = 1 + (state.p1AmplitudePercent / 100);

      return basis.rows.map(function(row){
        const timeHours = num(row.time_hours, 0) - state.timeShiftHours;
        let predicted = offset;
        basis.components.forEach(function(comp){
          const periodHours = num(comp.period_hours, 0);
          const amplitude = num(comp.amplitude, 0);
          if(periodHours <= 0 || !amplitude) return;
          let phaseDegrees = num(comp.phase, 0) + basePhaseOffset + state.phaseDegrees;
          let localAmp = amplitude * amplitudeScale;
          if(String(comp.name||'').toUpperCase() === 'P1'){
            phaseDegrees += state.p1PhaseDegrees;
            localAmp *= p1AmplitudeScale;
          }
          const omega = (2 * Math.PI) / periodHours;
          predicted += localAmp * Math.cos((omega * timeHours) - ((phaseDegrees * Math.PI) / 180));
        });
        return Number(predicted.toFixed(4));
      });
    }

    function buildAdjustedIndonesiaSeries(){ return buildAdjustedSeriesFromBasis(latestIndonesiaAdjustmentBasis, indonesiaAdjustmentState); }
    function buildAdjustedCatASeries(){ return buildAdjustedSeriesFromBasis(latestCatAAdjustmentBasis, catAAdjustmentState); }
    function buildAdjustedLeastSquareSeries(){ return buildAdjustedSeriesFromBasis(latestLeastSquareAdjustmentBasis, leastSquareAdjustmentState); }

    function renderAdjustedConstantsTable(){
      const rows = [];
      function pushRows(modelLabel, basis, state){
        if(!basis || !Array.isArray(basis.components) || !basis.components.length) return;
        const globalAmpScale = 1 + (state.amplitudePercent / 100);
        const p1AmpScale = 1 + (state.p1AmplitudePercent / 100);
        basis.components.forEach(function(comp){
          const origAmp = num(comp.amplitude, 0);
          const origPhase = num(comp.phase, 0) + num(basis.phase_offset_deg, 0);
          const isP1 = String(comp.name||'').toUpperCase() === 'P1';
          const adjAmp = origAmp * globalAmpScale * (isP1 ? p1AmpScale : 1);
          const adjPhase = origPhase + state.phaseDegrees + (isP1 ? state.p1PhaseDegrees : 0);
          rows.push(`<tr>
            <td class="p-2.5 font-sans">${esc(modelLabel)}</td>
            <td class="p-2.5 font-bold text-sky-600">${esc(comp.name || '-')}</td>
            <td class="p-2.5">${esc(origAmp.toFixed(4))}</td>
            <td class="p-2.5 font-bold text-teal-700">${esc(adjAmp.toFixed(4))}</td>
            <td class="p-2.5">${esc(origPhase.toFixed(2))}°</td>
            <td class="p-2.5 font-bold text-teal-700">${esc(adjPhase.toFixed(2))}°</td>
            <td class="p-2.5">${esc((state.timeShiftHours > 0 ? '+' : '') + state.timeShiftHours)} jam</td>
          </tr>`);
        });
      }
      pushRows('Admiralty Indonesia', latestIndonesiaAdjustmentBasis, indonesiaAdjustmentState);
      pushRows('Admiralty Cat A', latestCatAAdjustmentBasis, catAAdjustmentState);
      pushRows('Least Square', latestLeastSquareAdjustmentBasis, leastSquareAdjustmentState);
      setHtml('adjustedConstantsBody', rows.length ? rows.join('') : '<tr><td colspan="7" class="p-3 text-center text-slate-400 font-sans">Adjustment konstanta harmonik akan muncul di sini.</td></tr>');
    }

    // ============================================================
    // ELEVASI MUKA AIR PENTING (TIDAL DATUMS) ENGINE & RENDERER
    // ============================================================
    let currentDatumSelectedModel = '';

    const TIDAL_DATUM_SPECS = [
      {
        code: 'HAT',
        name: 'Highest Astronomical Tide',
        desc: 'Muka Air Tertinggi Astronomis',
        formula: 'S0 + Σ(Ai)',
        badgeClass: 'bg-rose-100 text-rose-800 border border-rose-200',
        key: 'hat'
      },
      {
        code: 'HHWL',
        name: 'Highest High Water Level',
        desc: 'Air Pasang Tertinggi Campuran (Spring Diurnal)',
        formula: 'S0 + (M2 + S2 + K1 + O1)',
        badgeClass: 'bg-orange-100 text-orange-800 border border-orange-200',
        key: 'hhwl'
      },
      {
        code: 'MHWS',
        name: 'Mean High Water Springs',
        desc: 'Rerata Air Tinggi Purnama (Spring High)',
        formula: 'S0 + (M2 + S2)',
        badgeClass: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
        key: 'mhws'
      },
      {
        code: 'MHWL',
        name: 'Mean High Water Level',
        desc: 'Rerata Muka Air Tinggi (Rata-rata Pasang)',
        formula: 'S0 + M2',
        badgeClass: 'bg-sky-100 text-sky-800 border border-sky-200',
        key: 'mhwl'
      },
      {
        code: 'MHWN',
        name: 'Mean High Water Neaps',
        desc: 'Rerata Air Tinggi Perbani (Neap High)',
        formula: 'S0 + |M2 - S2|',
        badgeClass: 'bg-teal-100 text-teal-800 border border-teal-200',
        key: 'mhwn'
      },
      {
        code: 'MSL',
        name: 'Mean Sea Level (S0)',
        desc: 'Muka Air Laut Rata-rata (Datum Acuan)',
        formula: 'S0',
        badgeClass: 'bg-slate-200 text-slate-800 border border-slate-300',
        key: 'msl'
      },
      {
        code: 'MLWN',
        name: 'Mean Low Water Neaps',
        desc: 'Rerata Air Rendah Perbani (Neap Low)',
        formula: 'S0 - |M2 - S2|',
        badgeClass: 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        key: 'mlwn'
      },
      {
        code: 'MLWL',
        name: 'Mean Low Water Level',
        desc: 'Rerata Muka Air Rendah (Rata-rata Surut)',
        formula: 'S0 - M2',
        badgeClass: 'bg-blue-100 text-blue-800 border border-blue-200',
        key: 'mlwl'
      },
      {
        code: 'MLWS',
        name: 'Mean Low Water Springs',
        desc: 'Rerata Air Rendah Purnama (Spring Low)',
        formula: 'S0 - (M2 + S2)',
        badgeClass: 'bg-purple-100 text-purple-800 border border-purple-200',
        key: 'mlws'
      },
      {
        code: 'LLWL',
        name: 'Lowest Low Water Level',
        desc: 'Air Surut Terendah Campuran',
        formula: 'S0 - (M2 + S2 + K1 + O1)',
        badgeClass: 'bg-fuchsia-100 text-fuchsia-800 border border-fuchsia-200',
        key: 'llwl'
      },
      {
        code: 'LAT',
        name: 'Lowest Astronomical Tide (Chart Datum)',
        desc: 'Muka Air Terendah Astronomis / Bidang Peta',
        formula: 'S0 - Σ(Ai)',
        badgeClass: 'bg-violet-100 text-violet-800 border border-violet-200',
        key: 'lat'
      }
    ];

    function getAdjustmentStateForModel(modelName){
      if(modelName === 'admiralty_indonesia') return indonesiaAdjustmentState;
      if(modelName === 'admiralty_cat_a') return catAAdjustmentState;
      if(modelName === 'least_square') return leastSquareAdjustmentState;
      return null;
    }

    function computeDatumsFromComponents(offset, components, state){
      const s0 = typeof offset === 'number' && !isNaN(offset) ? offset : 0;
      const globalAmpScale = state ? (1 + (num(state.amplitudePercent, 0) / 100)) : 1;
      const p1AmpScale = state ? (1 + (num(state.p1AmplitudePercent, 0) / 100)) : 1;

      const amps = { M2: 0, S2: 0, N2: 0, K2: 0, K1: 0, O1: 0, P1: 0, M4: 0, MS4: 0 };
      let sumAllAmps = 0;

      if(Array.isArray(components)){
        components.forEach(function(c){
          const name = String(c.name || '').trim().toUpperCase();
          if(name === 'S0') return;
          const rawAmp = Math.abs(parseFloat(c.amplitude || 0));
          if(isNaN(rawAmp)) return;
          const isP1 = name === 'P1';
          const adjAmp = rawAmp * globalAmpScale * (isP1 ? p1AmpScale : 1);
          if(Object.prototype.hasOwnProperty.call(amps, name)){
            amps[name] = adjAmp;
          }
          sumAllAmps += adjAmp;
        });
      }

      const m2 = amps.M2;
      const s2 = amps.S2;
      const k1 = amps.K1;
      const o1 = amps.O1;

      const hat = s0 + sumAllAmps;
      const hhwl = s0 + (m2 + s2 + k1 + o1);
      const mhws = s0 + (m2 + s2);
      const mhwl = s0 + m2;
      const mhwn = s0 + Math.abs(m2 - s2);
      const msl = s0;
      const mlwn = s0 - Math.abs(m2 - s2);
      const mlwl = s0 - m2;
      const mlws = s0 - (m2 + s2);
      const llwl = s0 - (m2 + s2 + k1 + o1);
      const lat = s0 - sumAllAmps;

      const springRange = 2 * (m2 + s2);
      const neapRange = 2 * Math.abs(m2 - s2);
      const meanRange = 2 * m2;
      const maxRange = hat - lat;

      return {
        s0, sumAllAmps,
        hat, hhwl, mhws, mhwl, mhwn, msl, mlwn, mlwl, mlws, llwl, lat,
        springRange, neapRange, meanRange, maxRange,
        amps
      };
    }

    function renderTidalDatums(){
      if(!Array.isArray(latestCalculationResults) || !latestCalculationResults.length) return;

      const exportBtn = document.getElementById('exportTidalDatumsCsvButton');
      if(exportBtn) exportBtn.disabled = false;
      if(openOfficialReportModalBtn) openOfficialReportModalBtn.disabled = false;
      if(openOfficialReportModalBtn2) openOfficialReportModalBtn2.disabled = false;
      if(exportWorkbookPdfButton) exportWorkbookPdfButton.disabled = false;

      // Ensure valid selected model
      const validModelNames = latestCalculationResults.map(r => (r.summary && r.summary.model_name) || '');
      if(!currentDatumSelectedModel || (currentDatumSelectedModel !== 'compare' && !validModelNames.includes(currentDatumSelectedModel))){
        currentDatumSelectedModel = validModelNames[0] || 'admiralty_indonesia';
      }

      // Render Tabs
      const tabsContainer = document.getElementById('tidalDatumsModelTabs');
      if(tabsContainer){
        let tabHtml = '';
        latestCalculationResults.forEach(r => {
          const mName = (r.summary && r.summary.model_name) || '';
          const mLabel = (r.summary && r.summary.model_label) || mName;
          const isActive = currentDatumSelectedModel === mName;
          tabHtml += `<button type="button" onclick="window.selectDatumModel('${esc(mName)}')" class="px-2.5 py-1 rounded-lg transition cursor-pointer text-xs ${isActive ? 'bg-white text-sky-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'}">${esc(mLabel)}</button>`;
        });
        if(latestCalculationResults.length > 1){
          const isCompareActive = currentDatumSelectedModel === 'compare';
          tabHtml += `<button type="button" onclick="window.selectDatumModel('compare')" class="px-2.5 py-1 rounded-lg transition cursor-pointer text-xs flex items-center gap-1 ${isCompareActive ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'}"><i class="fa-solid fa-code-compare text-[10px]"></i><span>Komparasi Semua</span></button>`;
        }
        tabsContainer.innerHTML = tabHtml;
      }

      const singleView = document.getElementById('singleModelDatumsView');
      const multiView = document.getElementById('multiModelDatumsView');

      if(currentDatumSelectedModel === 'compare'){
        if(singleView) singleView.style.display = 'none';
        if(multiView) multiView.style.display = 'flex';
        renderMultiModelDatumsComparison();
      } else {
        if(singleView) singleView.style.display = 'flex';
        if(multiView) multiView.style.display = 'none';
        renderSingleModelDatums(currentDatumSelectedModel);
      }
    }

    function selectDatumModel(modelName){
      currentDatumSelectedModel = modelName;
      if(modelName !== 'compare'){
        datumChartActiveEngine = modelName;
      }
      renderTidalDatums();
    }
    window.selectDatumModel = selectDatumModel;

    function renderSingleModelDatums(modelName){
      const res = latestCalculationResults.find(r => r.summary && r.summary.model_name === modelName) || latestCalculationResults[0];
      if(!res) return;

      const summary = res.summary || {};
      const components = Array.isArray(res.component_targets) ? res.component_targets : ((res.comparison_chart && res.comparison_chart.adjustment_basis && res.comparison_chart.adjustment_basis.components) ? res.comparison_chart.adjustment_basis.components : []);
      const adjBasis = (res.comparison_chart && res.comparison_chart.adjustment_basis) ? res.comparison_chart.adjustment_basis : null;
      const offset = (adjBasis && typeof adjBasis.offset === 'number') ? adjBasis.offset : num(summary.msl, 0);
      const state = getAdjustmentStateForModel(summary.model_name);

      const orig = computeDatumsFromComponents(offset, components, null);
      const calib = computeDatumsFromComponents(offset, components, state);

      // Quick metric tiles
      setText('datumHatVal', calib.hat.toFixed(3) + ' m');
      setText('datumHatDiff', '+' + (calib.hat - calib.msl).toFixed(3) + ' m');
      setText('datumMhwsVal', calib.mhws.toFixed(3) + ' m');
      setText('datumMhwsDiff', '+' + (calib.mhws - calib.msl).toFixed(3) + ' m');
      setText('datumMslVal', calib.msl.toFixed(3) + ' m');
      setText('datumLatVal', calib.lat.toFixed(3) + ' m');
      setText('datumLatDiff', (calib.lat - calib.msl).toFixed(3) + ' m');

      // Table rows
      const tableRows = TIDAL_DATUM_SPECS.map(spec => {
        const origVal = orig[spec.key];
        const calibVal = calib[spec.key];
        const diffMsl = calibVal - calib.msl;
        const aboveLat = calibVal - calib.lat;

        const signMsl = diffMsl > 0.0001 ? '+' : '';
        const mslClass = Math.abs(diffMsl) < 0.0001 ? 'text-slate-600 font-semibold' : (diffMsl > 0 ? 'text-sky-700 font-semibold' : 'text-indigo-700 font-semibold');

        return `<tr>
          <td class="p-3">
            <span class="px-2 py-0.5 rounded font-bold text-xs ${spec.badgeClass}">${esc(spec.code)}</span>
          </td>
          <td class="p-3 font-sans">
            <div class="font-bold text-slate-800 text-xs">${esc(spec.desc)}</div>
            <div class="text-[10px] text-slate-400 font-sans">${esc(spec.name)}</div>
          </td>
          <td class="p-3">
            <code class="font-mono text-slate-600 bg-slate-100 px-2 py-0.5 rounded text-[11px]">${esc(spec.formula)}</code>
          </td>
          <td class="p-3 text-right font-medium text-slate-700">${origVal.toFixed(3)} m</td>
          <td class="p-3 text-right font-bold text-teal-700 bg-teal-50/40">${calibVal.toFixed(3)} m</td>
          <td class="p-3 text-right ${mslClass}">${signMsl}${diffMsl.toFixed(3)} m</td>
          <td class="p-3 text-right font-semibold text-slate-800">${aboveLat.toFixed(3)} m</td>
        </tr>`;
      }).join('');

      setHtml('tidalDatumsTableBody', tableRows);

      // Tidal Ranges
      setText('datumSpringRange', calib.springRange.toFixed(3) + ' m');
      setText('datumNeapRange', calib.neapRange.toFixed(3) + ' m');
      setText('datumMeanRange', calib.meanRange.toFixed(3) + ' m');
      setText('datumMaxRange', calib.maxRange.toFixed(3) + ' m');

      // Update Datum Elevation Chart for this model
      renderDatumElevationChart(modelName);
    }

    function renderMultiModelDatumsComparison(){
      const headEl = document.getElementById('multiModelDatumsHead');
      const bodyEl = document.getElementById('multiModelDatumsBody');
      if(!headEl || !bodyEl) return;

      const modelDatas = latestCalculationResults.map(r => {
        const sm = r.summary || {};
        const components = Array.isArray(r.component_targets) ? r.component_targets : ((r.comparison_chart && r.comparison_chart.adjustment_basis && r.comparison_chart.adjustment_basis.components) ? r.comparison_chart.adjustment_basis.components : []);
        const adjBasis = (r.comparison_chart && r.comparison_chart.adjustment_basis) ? r.comparison_chart.adjustment_basis : null;
        const offset = (adjBasis && typeof adjBasis.offset === 'number') ? adjBasis.offset : num(sm.msl, 0);
        const state = getAdjustmentStateForModel(sm.model_name);

        return {
          model_name: sm.model_name,
          model_label: sm.model_label || sm.model_name,
          orig: computeDatumsFromComponents(offset, components, null),
          calib: computeDatumsFromComponents(offset, components, state)
        };
      });

      // Build Table Header
      let headHtml = `<tr>
        <th class="p-3">Elevasi Datum</th>
        <th class="p-3">Formula</th>`;
      modelDatas.forEach(m => {
        headHtml += `<th class="p-3 text-right">${esc(m.model_label)} (Asli)</th>
                     <th class="p-3 text-right text-teal-700 bg-teal-50/50">${esc(m.model_label)} (Calib)</th>`;
      });
      headHtml += `</tr>`;
      headEl.innerHTML = headHtml;

      // Build Datum Rows
      let bodyHtml = '';
      TIDAL_DATUM_SPECS.forEach(spec => {
        bodyHtml += `<tr>
          <td class="p-3">
            <span class="px-2 py-0.5 rounded font-bold text-xs ${spec.badgeClass}">${esc(spec.code)}</span>
            <span class="text-xs font-semibold text-slate-700 ml-1.5 font-sans">${esc(spec.desc)}</span>
          </td>
          <td class="p-3"><code class="font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">${esc(spec.formula)}</code></td>`;
        modelDatas.forEach(m => {
          bodyHtml += `<td class="p-3 text-right text-slate-700">${m.orig[spec.key].toFixed(3)} m</td>
                       <td class="p-3 text-right font-bold text-teal-700 bg-teal-50/30">${m.calib[spec.key].toFixed(3)} m</td>`;
        });
        bodyHtml += `</tr>`;
      });

      // Section divider for ranges
      bodyHtml += `<tr class="bg-slate-100 text-slate-700 font-bold font-sans">
        <td colspan="${2 + (modelDatas.length * 2)}" class="p-2.5 uppercase text-[10px] tracking-wider text-slate-500">Tunggang Pasang Surut (Tidal Ranges)</td>
      </tr>`;

      const rangeSpecs = [
        { label: 'Tunggang Purnama (Spring Range)', formula: '2 × (M2 + S2)', key: 'springRange' },
        { label: 'Tunggang Perbani (Neap Range)', formula: '2 × |M2 - S2|', key: 'neapRange' },
        { label: 'Tunggang Rerata (Mean Range)', formula: '2 × M2', key: 'meanRange' },
        { label: 'Tunggang Maks. Astronomis', formula: 'HAT - LAT (2 × Σ Ai)', key: 'maxRange' }
      ];

      rangeSpecs.forEach(rng => {
        bodyHtml += `<tr>
          <td class="p-3 font-sans font-semibold text-slate-800 text-xs">${esc(rng.label)}</td>
          <td class="p-3"><code class="font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">${esc(rng.formula)}</code></td>`;
        modelDatas.forEach(m => {
          bodyHtml += `<td class="p-3 text-right text-slate-700 font-bold">${m.orig[rng.key].toFixed(3)} m</td>
                       <td class="p-3 text-right font-bold text-teal-700 bg-teal-50/30">${m.calib[rng.key].toFixed(3)} m</td>`;
        });
        bodyHtml += `</tr>`;
      });

      bodyEl.innerHTML = bodyHtml;

      // Update Datum Elevation Chart for comparison mode
      renderDatumElevationChart(datumChartActiveEngine);
    }

    function exportTidalDatumsCsv(){
      if(!Array.isArray(latestCalculationResults) || !latestCalculationResults.length) return;

      let csv = 'ELEVASI PENTING PASANG SURUT (TIDAL DATUMS) - RPASOET\r\n';
      const primary = latestCalculationResults[0];
      const station = (primary.summary && primary.summary.station_name) || 'Stasiun Pasut';
      csv += `Stasiun,"${station.replace(/"/g, '""')}"\r\n`;
      csv += `Tanggal Ekspor,"${new Date().toLocaleString('id-ID')}"\r\n\r\n`;

      if(currentDatumSelectedModel === 'compare'){
        // Comparison Export
        csv += 'Datum,Deskripsi,Formula';
        latestCalculationResults.forEach(r => {
          const lbl = (r.summary && r.summary.model_label) || r.summary.model_name;
          csv += `,"${lbl} (Asli m)","${lbl} (Calib m)"`;
        });
        csv += '\r\n';

        const modelDatas = latestCalculationResults.map(r => {
          const sm = r.summary || {};
          const components = Array.isArray(r.component_targets) ? r.component_targets : ((r.comparison_chart && r.comparison_chart.adjustment_basis && r.comparison_chart.adjustment_basis.components) ? r.comparison_chart.adjustment_basis.components : []);
          const adjBasis = (r.comparison_chart && r.comparison_chart.adjustment_basis) ? r.comparison_chart.adjustment_basis : null;
          const offset = (adjBasis && typeof adjBasis.offset === 'number') ? adjBasis.offset : num(sm.msl, 0);
          const state = getAdjustmentStateForModel(sm.model_name);
          return {
            orig: computeDatumsFromComponents(offset, components, null),
            calib: computeDatumsFromComponents(offset, components, state)
          };
        });

        TIDAL_DATUM_SPECS.forEach(spec => {
          csv += `"${spec.code}","${spec.desc}","${spec.formula}"`;
          modelDatas.forEach(m => {
            csv += `,${m.orig[spec.key].toFixed(4)},${m.calib[spec.key].toFixed(4)}`;
          });
          csv += '\r\n';
        });

        csv += '\r\nTUNGGANG PASUT,Deskripsi,Formula';
        latestCalculationResults.forEach(r => {
          const lbl = (r.summary && r.summary.model_label) || r.summary.model_name;
          csv += `,"${lbl} (Asli m)","${lbl} (Calib m)"`;
        });
        csv += '\r\n';

        const rangeSpecs = [
          { code: 'Spring Range', desc: 'Tunggang Purnama', formula: '2*(M2+S2)', key: 'springRange' },
          { code: 'Neap Range', desc: 'Tunggang Perbani', formula: '2*|M2-S2|', key: 'neapRange' },
          { code: 'Mean Range', desc: 'Tunggang Rerata', formula: '2*M2', key: 'meanRange' },
          { code: 'Max Range', desc: 'Tunggang Maks. Astronomis', formula: 'HAT-LAT', key: 'maxRange' }
        ];

        rangeSpecs.forEach(rng => {
          csv += `"${rng.code}","${rng.desc}","${rng.formula}"`;
          modelDatas.forEach(m => {
            csv += `,${m.orig[rng.key].toFixed(4)},${m.calib[rng.key].toFixed(4)}`;
          });
          csv += '\r\n';
        });

      } else {
        // Single Model Detailed Export
        const res = latestCalculationResults.find(r => r.summary && r.summary.model_name === currentDatumSelectedModel) || latestCalculationResults[0];
        const sm = res.summary || {};
        const components = Array.isArray(res.component_targets) ? res.component_targets : ((res.comparison_chart && res.comparison_chart.adjustment_basis && res.comparison_chart.adjustment_basis.components) ? res.comparison_chart.adjustment_basis.components : []);
        const adjBasis = (res.comparison_chart && res.comparison_chart.adjustment_basis) ? res.comparison_chart.adjustment_basis : null;
        const offset = (adjBasis && typeof adjBasis.offset === 'number') ? adjBasis.offset : num(sm.msl, 0);
        const state = getAdjustmentStateForModel(sm.model_name);

        const orig = computeDatumsFromComponents(offset, components, null);
        const calib = computeDatumsFromComponents(offset, components, state);

        csv += `Model,"${(sm.model_label || sm.model_name).replace(/"/g, '""')}"\r\n`;
        csv += `MSL Acuan S0,${offset.toFixed(4)} m\r\n\r\n`;
        csv += 'Datum,Deskripsi,Nama Internasional,Formula Harmonik,Model Asli (m),Terkalibrasi (m),Relatif MSL (m),Di Atas LAT Chart Datum (m)\r\n';

        TIDAL_DATUM_SPECS.forEach(spec => {
          const origVal = orig[spec.key];
          const calibVal = calib[spec.key];
          const diffMsl = calibVal - calib.msl;
          const aboveLat = calibVal - calib.lat;
          const signMsl = diffMsl > 0 ? '+' : '';
          csv += `"${spec.code}","${spec.desc}","${spec.name}","${spec.formula}",${origVal.toFixed(4)},${calibVal.toFixed(4)},"${signMsl}${diffMsl.toFixed(4)}",${aboveLat.toFixed(4)}\r\n`;
        });

        csv += '\r\nTUNGGANG PASUT (TIDAL RANGE),Deskripsi,Formula Harmonik,Model Asli (m),Terkalibrasi (m)\r\n';
        const rangeSpecs = [
          { code: 'Spring Range', desc: 'Tunggang Purnama', formula: '2*(M2+S2)', key: 'springRange' },
          { code: 'Neap Range', desc: 'Tunggang Perbani', formula: '2*|M2-S2|', key: 'neapRange' },
          { code: 'Mean Range', desc: 'Tunggang Rerata', formula: '2*M2', key: 'meanRange' },
          { code: 'Max Astronomical Range', desc: 'Tunggang Maks. Astronomis', formula: 'HAT-LAT', key: 'maxRange' }
        ];

        rangeSpecs.forEach(rng => {
          csv += `"${rng.code}","${rng.desc}","${rng.formula}",${orig[rng.key].toFixed(4)},${calib[rng.key].toFixed(4)}\r\n`;
        });
      }

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `elevasi_penting_pasut_${(station || 'data').replace(/[^a-zA-Z0-9_-]/g, '_')}_${new Date().toISOString().slice(0, 10)}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    function renderDatumElevationChart(targetEngine){
      const canvas = document.getElementById('datumElevationChartCanvas');
      if(!canvas || !Array.isArray(latestCalculationResults) || !latestCalculationResults.length) return;

      if(targetEngine && targetEngine !== 'compare'){
        datumChartActiveEngine = targetEngine;
      }
      const engineSelect = document.getElementById('datumChartEngineSelect');
      if(engineSelect && engineSelect.value !== datumChartActiveEngine){
        engineSelect.value = datumChartActiveEngine;
      }

      // Find calculation result for active engine
      const res = latestCalculationResults.find(r => (r.summary && r.summary.model_name === datumChartActiveEngine)) || latestCalculationResults[0];
      if(!res) return;

      const sm = res.summary || {};
      const modelLabel = sm.model_label || sm.model_name || 'Model';
      const badge = document.getElementById('datumChartModelBadge');
      if(badge) badge.textContent = modelLabel;

      const chartSource = latestCalculationResults.find(r => r.comparison_chart && Array.isArray(r.comparison_chart.labels) && r.comparison_chart.labels.length > 0);
      const labels = (chartSource && chartSource.comparison_chart && chartSource.comparison_chart.labels) ? chartSource.comparison_chart.labels : [];
      if(!labels.length) return;

      // Components and Datum Calculation
      const components = Array.isArray(res.component_targets) ? res.component_targets : ((res.comparison_chart && res.comparison_chart.adjustment_basis && res.comparison_chart.adjustment_basis.components) ? res.comparison_chart.adjustment_basis.components : []);
      const adjBasis = (res.comparison_chart && res.comparison_chart.adjustment_basis) ? res.comparison_chart.adjustment_basis : null;
      const offset = (adjBasis && typeof adjBasis.offset === 'number') ? adjBasis.offset : num(sm.msl, 0);
      const state = getAdjustmentStateForModel(sm.model_name);
      const calib = computeDatumsFromComponents(offset, components, state);

      // Model Points (with adjustment if applicable)
      let modelPts = [];
      if(sm.model_name === 'admiralty_indonesia' && latestIndonesiaAdjustmentBasis){
        modelPts = buildAdjustedIndonesiaSeries() || (res.comparison_chart && res.comparison_chart.series && res.comparison_chart.series[0] ? res.comparison_chart.series[0].points : []);
      } else if(sm.model_name === 'admiralty_cat_a' && latestCatAAdjustmentBasis){
        modelPts = buildAdjustedCatASeries() || (res.comparison_chart && res.comparison_chart.series && res.comparison_chart.series[0] ? res.comparison_chart.series[0].points : []);
      } else if(sm.model_name === 'least_square' && latestLeastSquareAdjustmentBasis){
        modelPts = buildAdjustedLeastSquareSeries() || (res.comparison_chart && res.comparison_chart.series && res.comparison_chart.series[0] ? res.comparison_chart.series[0].points : []);
      } else if(res.comparison_chart && Array.isArray(res.comparison_chart.series) && res.comparison_chart.series.length){
        modelPts = res.comparison_chart.series[0].points || [];
      }

      const modelColors = {
        admiralty_indonesia: '#0f766e',
        admiralty_cat_a: '#b91c1c',
        least_square: '#2563eb'
      };
      const modelColor = modelColors[sm.model_name] || '#0284c7';

      // Build Datasets
      const datasets = [];

      // 1. Observed Curve
      if(datumChartCurveMode === 'both' || datumChartCurveMode === 'observed'){
        if(Array.isArray(latestObservedPoints) && latestObservedPoints.length){
          datasets.push({
            label: 'Observasi Riil',
            data: latestObservedPoints,
            borderColor: '#0f172a',
            backgroundColor: 'transparent',
            borderWidth: 1.8,
            pointRadius: 1,
            pointHoverRadius: 4,
            tension: 0.15,
            order: 1
          });
        }
      }

      // 2. Model Curve
      if(datumChartCurveMode === 'both' || datumChartCurveMode === 'model'){
        if(Array.isArray(modelPts) && modelPts.length){
          datasets.push({
            label: `${modelLabel} (Terkalibrasi)`,
            data: modelPts,
            borderColor: modelColor,
            backgroundColor: 'transparent',
            borderWidth: 1.8,
            borderDash: [3, 2],
            pointRadius: 0,
            pointHoverRadius: 3,
            tension: 0.15,
            order: 2
          });
        }
      }

      // 3. The 11 Tidal Datum Horizontal Lines
      const datumPalette = {
        HAT:  { color: '#b91c1c', dash: [6, 4], width: 1.6 },
        HHWL: { color: '#ea580c', dash: [6, 4], width: 1.6 },
        MHWS: { color: '#d97706', dash: [5, 4], width: 1.6 },
        MHWL: { color: '#0284c7', dash: [4, 4], width: 1.6 },
        MHWN: { color: '#ca8a04', dash: [4, 4], width: 1.6 },
        MSL:  { color: '#0f172a', dash: [],     width: 2.2 },
        MLWN: { color: '#0d9488', dash: [4, 4], width: 1.6 },
        MLWL: { color: '#2563eb', dash: [4, 4], width: 1.6 },
        MLWS: { color: '#7c3aed', dash: [5, 4], width: 1.6 },
        LLWL: { color: '#c026d3', dash: [6, 4], width: 1.6 },
        LAT:  { color: '#4338ca', dash: [6, 4], width: 1.6 }
      };

      TIDAL_DATUM_SPECS.forEach(spec => {
        const val = typeof calib[spec.key] === 'number' ? calib[spec.key] : 0;
        const pal = datumPalette[spec.code] || { color: '#64748b', dash: [4, 4], width: 1.5 };
        datasets.push({
          label: `${spec.code} (${val.toFixed(3)} m)`,
          datumCode: spec.code,
          datumValue: val,
          isDatumLine: true,
          data: Array(labels.length).fill(val),
          borderColor: pal.color,
          borderWidth: pal.width,
          borderDash: pal.dash,
          pointRadius: 0,
          pointHitRadius: 0,
          fill: false,
          order: 10
        });
      });

      if(datumChartInstance){
        datumChartInstance.destroy();
        datumChartInstance = null;
      }

      // Custom Plugin: Draw Badges on Right Edge (exact match to hydrographic chart format)
      const datumRightBadgesPlugin = {
        id: 'datumRightBadgesPlugin',
        afterDraw(chart){
          const ctx = chart.ctx;
          const yAxis = chart.scales.y;
          const chartArea = chart.chartArea;
          if(!yAxis || !chartArea) return;

          chart.data.datasets.forEach((dataset, index) => {
            if(!dataset.isDatumLine || !dataset.datumCode) return;
            const meta = chart.getDatasetMeta(index);
            if(meta && meta.hidden) return;

            const val = dataset.datumValue;
            if(typeof val !== 'number' || isNaN(val)) return;

            const yPixel = yAxis.getPixelForValue(val);
            if(yPixel < chartArea.top - 8 || yPixel > chartArea.bottom + 8) return;

            const text = dataset.datumCode;
            ctx.save();
            ctx.font = 'bold 9px monospace, sans-serif';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            const textWidth = ctx.measureText(text).width;
            const x = chartArea.right + 5;
            const y = yPixel;

            // Background badge
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = dataset.borderColor || '#64748b';
            ctx.lineWidth = 1;
            const padX = 3;
            const badgeW = textWidth + padX * 2;
            const badgeH = 13;

            ctx.beginPath();
            if(ctx.roundRect){
              ctx.roundRect(x, y - 6.5, badgeW, badgeH, 2);
            } else {
              ctx.rect(x, y - 6.5, badgeW, badgeH);
            }
            ctx.fill();
            ctx.stroke();

            // Text
            ctx.fillStyle = dataset.borderColor || '#0f172a';
            ctx.fillText(text, x + padX, y);
            ctx.restore();
          });
        }
      };

      datumChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: { labels, datasets },
        plugins: [datumRightBadgesPlugin],
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,
          interaction: { mode: 'index', intersect: false },
          layout: {
            padding: { right: 55, left: 5, top: 10, bottom: 5 }
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                boxWidth: 14,
                boxHeight: 2,
                font: { size: 10, weight: 'bold' },
                padding: 8,
                usePointStyle: false
              }
            },
            tooltip: {
              callbacks: {
                label: function(ctx){
                  const ds = ctx.dataset;
                  if(ds.isDatumLine){
                    return `${ds.datumCode}: ${ctx.parsed.y.toFixed(4)} m`;
                  }
                  return `${ds.label}: ${ctx.parsed.y.toFixed(4)} m`;
                }
              }
            }
          },
          scales: {
            y: {
              grid: { color: '#f1f5f9' },
              title: { display: true, text: 'Elevasi Muka Air (m)', font: { size: 11, weight: 'bold' } }
            },
            x: {
              grid: { display: false },
              ticks: { maxTicksLimit: 12, autoSkip: true, font: { size: 10 } }
            }
          }
        }
      });
    }

    // UI listeners for Datum Chart
    const datumEngineSelect = document.getElementById('datumChartEngineSelect');
    if(datumEngineSelect){
      datumEngineSelect.addEventListener('change', function(e){
        datumChartActiveEngine = e.target.value;
        renderDatumElevationChart(datumChartActiveEngine);
      });
    }

    const datumBtnBoth = document.getElementById('datumChartBtnBoth');
    const datumBtnObs = document.getElementById('datumChartBtnObserved');
    const datumBtnMod = document.getElementById('datumChartBtnModel');

    function setDatumCurveMode(mode){
      datumChartCurveMode = mode;
      [datumBtnBoth, datumBtnObs, datumBtnMod].forEach(b => {
        if(!b) return;
        b.className = 'px-2.5 py-1 rounded-md text-xs cursor-pointer text-slate-500 hover:text-slate-800 transition';
      });
      const activeBtn = mode === 'both' ? datumBtnBoth : (mode === 'observed' ? datumBtnObs : datumBtnMod);
      if(activeBtn){
        activeBtn.className = 'px-2.5 py-1 rounded-md text-xs cursor-pointer bg-white text-slate-900 shadow-xs font-bold transition';
      }
      renderDatumElevationChart(datumChartActiveEngine);
    }
    if(datumBtnBoth) datumBtnBoth.addEventListener('click', () => setDatumCurveMode('both'));
    if(datumBtnObs) datumBtnObs.addEventListener('click', () => setDatumCurveMode('observed'));
    if(datumBtnMod) datumBtnMod.addEventListener('click', () => setDatumCurveMode('model'));

    const exportDatumPngBtn = document.getElementById('exportDatumChartPngButton');
    if(exportDatumPngBtn){
      exportDatumPngBtn.addEventListener('click', function(){
        if(!datumChartInstance){
          window.alert('Grafik elevasi pasut belum tersedia.');
          return;
        }
        const station = (latestCalculationResults[0] && latestCalculationResults[0].summary && latestCalculationResults[0].summary.station_name) || 'stasiun_pasut';
        const link = document.createElement('a');
        link.download = `grafik_elevasi_pasut_${String(station).replace(/[^a-zA-Z0-9_-]/g, '_')}_${datumChartActiveEngine}_${new Date().toISOString().slice(0, 10)}.png`;
        link.href = datumChartInstance.toBase64Image('image/png', 1.0);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }

    function captureComparisonChartVisibility(){
      if(!comparisonChartInstance || !comparisonChartInstance.data || !Array.isArray(comparisonChartInstance.data.datasets)) return;
      comparisonChartInstance.data.datasets.forEach(function(dataset, index){
        if(!dataset || !dataset.label) return;
        comparisonChartVisibility[String(dataset.label)] = comparisonChartInstance.isDatasetVisible(index);
      });
    }

    function applyComparisonChartVisibility(datasets){
      if(!Array.isArray(datasets)) return;
      datasets.forEach(function(dataset){
        if(!dataset || !dataset.label) return;
        const key = String(dataset.label);
        if(Object.prototype.hasOwnProperty.call(comparisonChartVisibility, key)){
          dataset.hidden = !comparisonChartVisibility[key];
        }
      });
    }

    function updateComparisonChartData(){
      if(!comparisonChartInstance || !comparisonChartInstance.data || !Array.isArray(comparisonChartInstance.data.datasets)){
        renderComparisonChart(latestCalculationResults);
        return;
      }

      // In-place update of model curves without destroying the canvas or resetting visibility
      comparisonChartInstance.data.datasets.forEach(dataset => {
        if(dataset.model_name === 'admiralty_indonesia' && latestIndonesiaAdjustmentBasis){
          dataset.data = buildAdjustedIndonesiaSeries() || dataset.data;
        } else if(dataset.model_name === 'admiralty_cat_a' && latestCatAAdjustmentBasis){
          dataset.data = buildAdjustedCatASeries() || dataset.data;
        } else if(dataset.model_name === 'least_square' && latestLeastSquareAdjustmentBasis){
          dataset.data = buildAdjustedLeastSquareSeries() || dataset.data;
        }
      });

      // Update Chart instantly with 'none' animation mode so it does not reset or flash
      comparisonChartInstance.update('none');

      // Update export rows if present
      if(latestComparisonChartExport && Array.isArray(latestComparisonChartExport.rows)){
        latestComparisonChartExport.rows.forEach((row, idx) => {
          comparisonChartInstance.data.datasets.forEach(dataset => {
            if(dataset.label && dataset.label !== 'Observasi Riil'){
              row[dataset.label] = Array.isArray(dataset.data) && typeof dataset.data[idx] !== 'undefined' ? dataset.data[idx] : '';
            }
          });
        });
      }

      renderAdjustedConstantsTable();
      renderTidalDatums();
    }

    function rerenderAdjustment(){
      if(!latestCalculationResults.length) return;
      updateComparisonChartData();
    }

    function calculateRMSE(seriesA, seriesB){
      if(!Array.isArray(seriesA) || !Array.isArray(seriesB) || !seriesA.length) return 9999;
      let sumSq = 0;
      let count = 0;
      const n = Math.min(seriesA.length, seriesB.length);
      for(let i = 0; i < n; i++){
        const a = seriesA[i];
        const b = seriesB[i];
        if(typeof a === 'number' && !isNaN(a) && typeof b === 'number' && !isNaN(b)){
          const diff = a - b;
          sumSq += diff * diff;
          count++;
        }
      }
      return count > 0 ? Math.sqrt(sumSq / count) : 9999;
    }

    // AUTO-CALIBRATION OPTIMIZATION ENGINE
    function runAutoCalibrationForModel(modelName){
      let basis = null;
      let state = null;
      let syncUi = null;
      let infoEl = null;
      let modelLabel = '';

      if(modelName === 'admiralty_indonesia'){
        basis = latestIndonesiaAdjustmentBasis;
        state = indonesiaAdjustmentState;
        syncUi = syncIndonesiaAdjustmentUi;
        infoEl = indonesiaCalibrationInfo;
        modelLabel = 'Admiralty Indonesia';
      } else if(modelName === 'admiralty_cat_a'){
        basis = latestCatAAdjustmentBasis;
        state = catAAdjustmentState;
        syncUi = syncCatAAdjustmentUi;
        infoEl = catACalibrationInfo;
        modelLabel = 'Admiralty Cat A';
      } else if(modelName === 'least_square'){
        basis = latestLeastSquareAdjustmentBasis;
        state = leastSquareAdjustmentState;
        syncUi = syncLeastSquareAdjustmentUi;
        infoEl = leastSquareCalibrationInfo;
        modelLabel = 'Least Square';
      }

      if(!basis || !Array.isArray(basis.rows) || !Array.isArray(basis.components) || !basis.rows.length){
        window.alert('Basis perhitungan untuk model ' + modelLabel + ' belum siap.');
        return false;
      }

      // Check observed points
      let observed = latestObservedPoints;
      if(!observed || !observed.length){
        const chartSource = latestCalculationResults.find(r => r.comparison_chart && r.comparison_chart.observed);
        if(chartSource && chartSource.comparison_chart.observed && Array.isArray(chartSource.comparison_chart.observed.points)){
          observed = chartSource.comparison_chart.observed.points;
          latestObservedPoints = observed;
        }
      }

      if(!observed || !observed.length){
        window.alert('Data observasi riil belum tersedia untuk referensi kalibrasi.');
        return false;
      }

      const offset = num(basis.offset, 0);
      const basePhaseOffset = num(basis.phase_offset_deg, 0);
      const rows = basis.rows;
      const comps = basis.components;
      const n = Math.min(rows.length, observed.length);

      // Baseline unadjusted RMSE
      const baselineSeries = buildAdjustedSeriesFromBasis(basis, { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 });
      const initialRMSE = calculateRMSE(baselineSeries, observed);

      // Fast parameter fit evaluation
      function evaluate(timeShift, phase, p1Amp, p1Phase){
        let sumUV = 0;
        let sumU2 = 0;
        const deltas = new Float64Array(n);
        const p1PhaseRad = p1Phase * 0.017453292519943295;
        const p1AmpScale = 1 + (p1Amp / 100);

        for(let i = 0; i < n; i++){
          const obs = observed[i];
          if(obs === null || obs === undefined || isNaN(obs)) continue;
          const timeHours = num(rows[i].time_hours, 0) - timeShift;
          let d = 0;
          for(let c = 0; c < comps.length; c++){
            const comp = comps[c];
            const per = num(comp.period_hours, 0);
            const amp = num(comp.amplitude, 0);
            if(!per || !amp) continue;
            let pDeg = num(comp.phase, 0) + basePhaseOffset + phase;
            let cAmp = amp;
            if(comp.name && String(comp.name).toUpperCase() === 'P1'){
              pDeg += p1Phase;
              cAmp *= p1AmpScale;
            }
            const omega = 6.283185307179586 / per;
            d += cAmp * Math.cos(omega * timeHours - (pDeg * 0.017453292519943295));
          }
          deltas[i] = d;
          const v = obs - offset;
          sumUV += d * v;
          sumU2 += d * d;
        }

        // Calculate standard deviation of deltas to prevent wave flattening
        let sumD = 0, sumD2 = 0, validN = 0;
        for(let i = 0; i < n; i++){
          if(observed[i] !== null && observed[i] !== undefined && !isNaN(observed[i])){
            sumD += deltas[i];
            sumD2 += deltas[i] * deltas[i];
            validN++;
          }
        }
        const stdD = validN > 0 ? Math.sqrt(Math.max(0, (sumD2 / validN) - ((sumD / validN) ** 2))) : 1;
        const targetPhysicalScale = stdD > 1e-4 ? (stdObs / stdD) : 1;

        let scale = sumU2 > 1e-9 ? (sumUV / sumU2) : 1;
        // Enforce physical amplitude preservation: scale must stay within 35% of observed tidal energy
        const minAllowedScale = Math.max(0.65, targetPhysicalScale * 0.75);
        const maxAllowedScale = Math.min(1.45, targetPhysicalScale * 1.25);
        scale = Math.max(minAllowedScale, Math.min(maxAllowedScale, scale));

        let sumSq = 0;
        let count = 0;
        for(let i = 0; i < n; i++){
          const obs = observed[i];
          if(obs === null || obs === undefined || isNaN(obs)) continue;
          const pred = offset + scale * deltas[i];
          const diff = obs - pred;
          sumSq += diff * diff;
          count++;
        }
        const rmse = count > 0 ? Math.sqrt(sumSq / count) : 9999;
        return { rmse, scale, timeShift, phase, p1Amp, p1Phase };
      }

      // Precalculate observed standard deviation
      let sumObs = 0, sumObs2 = 0, obsCount = 0;
      for(let i = 0; i < n; i++){
        const o = observed[i];
        if(o !== null && o !== undefined && !isNaN(o)){
          sumObs += o;
          sumObs2 += o * o;
          obsCount++;
        }
      }
      const stdObs = obsCount > 0 ? Math.sqrt(Math.max(0, (sumObs2 / obsCount) - ((sumObs / obsCount) ** 2))) : 1;

      // 1. Realistic coarse search: time shift +-4 jam, phase +-60 deg (prevents wave inversion & 12h cycle jumps)
      let best = { rmse: initialRMSE, scale: 1, timeShift: 0, phase: 0, p1Amp: 0, p1Phase: 0 };
      const candidateShifts = [-4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5, 0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4];
      const candidatePhases = [-60, -45, -30, -15, 0, 15, 30, 45, 60];

      for(let s = 0; s < candidateShifts.length; s++){
        const sh = candidateShifts[s];
        for(let p = 0; p < candidatePhases.length; p++){
          const ph = candidatePhases[p];
          const res = evaluate(sh, ph, 0, 0);
          if(res.rmse < best.rmse){
            best = res;
          }
        }
      }

      // 2. Local fine search around best time shift (+- 0.5 hour) and best phase (+- 10 deg, step 1)
      const fineShifts = [best.timeShift - 0.5, best.timeShift, best.timeShift + 0.5];
      for(let s = 0; s < fineShifts.length; s++){
        const sh = fineShifts[s];
        if(sh < -4 || sh > 4) continue;
        for(let ph = Math.max(-60, best.phase - 10); ph <= Math.min(60, best.phase + 10); ph += 1){
          const res = evaluate(sh, ph, 0, 0);
          if(res.rmse < best.rmse){
            best = res;
          }
        }
      }

      // 3. P1 fine tuning if model has P1
      const hasP1 = comps.some(c => c.name && String(c.name).toUpperCase() === 'P1');
      if(hasP1){
        const p1Phases = [-30, -15, 0, 15, 30];
        const p1Amps = [-20, -10, 0, 10, 20];
        for(let a = 0; a < p1Amps.length; a++){
          for(let p = 0; p < p1Phases.length; p++){
            const res = evaluate(best.timeShift, best.phase, p1Amps[a], p1Phases[p]);
            if(res.rmse < best.rmse){
              best = res;
            }
          }
        }
      }

      // Assign optimal values to state
      const ampPct = Math.round((best.scale - 1) * 100);
      state.amplitudePercent = Math.max(-100, Math.min(100, ampPct));
      state.phaseDegrees = Math.round(best.phase);
      state.timeShiftHours = best.timeShift;
      state.p1AmplitudePercent = Math.round(best.p1Amp || 0);
      state.p1PhaseDegrees = Math.round(best.p1Phase || 0);

      // Sync UI controls and Chart in-place
      syncUi();
      updateComparisonChartData();

      // Show info badge
      if(infoEl){
        const improvement = initialRMSE > 0 ? Math.round(((initialRMSE - best.rmse) / initialRMSE) * 100) : 0;
        const sign = state.timeShiftHours > 0 ? '+' : '';
        infoEl.innerHTML = `
          <div class="flex items-center gap-1.5 flex-wrap">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span class="text-slate-800"><strong>Terkalibrasi:</strong> RMSE ${initialRMSE.toFixed(4)}m ➔ <strong class="text-emerald-700">${best.rmse.toFixed(4)}m</strong> <span class="text-emerald-700 font-bold">(${improvement > 0 ? '-' + improvement + '%' : 'optimal'})</span></span>
          </div>
          <div class="text-[11px] text-slate-500 font-mono font-medium">Shift: ${sign}${state.timeShiftHours}j | Amp: ${state.amplitudePercent > 0 ? '+' : ''}${state.amplitudePercent}% | Fase: ${state.phaseDegrees > 0 ? '+' : ''}${state.phaseDegrees}°</div>
        `;
        infoEl.style.display = 'flex';
      }

      return true;
    }

    function runAutoCalibrationForAllModels(){
      let calibratedCount = 0;
      const cards = [
        { name: 'admiralty_indonesia', card: indonesiaAdjustmentCard },
        { name: 'admiralty_cat_a', card: catAAdjustmentCard },
        { name: 'least_square', card: leastSquareAdjustmentCard }
      ];

      cards.forEach(c => {
        if(c.card && c.card.style.display !== 'none'){
          const ok = runAutoCalibrationForModel(c.name);
          if(ok) calibratedCount++;
        }
      });

      if(calibratedCount > 0){
        if(autoCalibrateAllButton){
          const origHtml = autoCalibrateAllButton.innerHTML;
          autoCalibrateAllButton.innerHTML = '<i class="fa-solid fa-check"></i><span>Semua Model Terkalibrasi!</span>';
          setTimeout(() => {
            autoCalibrateAllButton.innerHTML = origHtml;
          }, 3000);
        }
      } else {
        window.alert('Belum ada model aktif yang siap dikalibrasi. Pastikan model sudah dihitung pada Tahap 2.');
      }
    }

    // BIND SLIDERS EVENTS
    function bindAdjustmentEvents(){
      // Indonesia
      if(indonesiaAmplitudeAdjust) indonesiaAmplitudeAdjust.addEventListener('input', function(){ indonesiaAdjustmentState.amplitudePercent = num(this.value, 0); syncIndonesiaAdjustmentUi(); rerenderAdjustment(); });
      if(indonesiaPhaseAdjust) indonesiaPhaseAdjust.addEventListener('input', function(){ indonesiaAdjustmentState.phaseDegrees = num(this.value, 0); syncIndonesiaAdjustmentUi(); rerenderAdjustment(); });
      if(indonesiaP1AmplitudeAdjust) indonesiaP1AmplitudeAdjust.addEventListener('input', function(){ indonesiaAdjustmentState.p1AmplitudePercent = num(this.value, 0); syncIndonesiaAdjustmentUi(); rerenderAdjustment(); });
      if(indonesiaP1PhaseAdjust) indonesiaP1PhaseAdjust.addEventListener('input', function(){ indonesiaAdjustmentState.p1PhaseDegrees = num(this.value, 0); syncIndonesiaAdjustmentUi(); rerenderAdjustment(); });
      if(indonesiaTimeShiftMinus) indonesiaTimeShiftMinus.addEventListener('click', function(){ indonesiaAdjustmentState.timeShiftHours -= 1; syncIndonesiaAdjustmentUi(); rerenderAdjustment(); });
      if(indonesiaTimeShiftPlus) indonesiaTimeShiftPlus.addEventListener('click', function(){ indonesiaAdjustmentState.timeShiftHours += 1; syncIndonesiaAdjustmentUi(); rerenderAdjustment(); });
      if(resetIndonesiaAdjustmentButton) resetIndonesiaAdjustmentButton.addEventListener('click', function(){ resetIndonesiaAdjustmentState(); rerenderAdjustment(); });

      // Cat A
      if(catAAmplitudeAdjust) catAAmplitudeAdjust.addEventListener('input', function(){ catAAdjustmentState.amplitudePercent = num(this.value, 0); syncCatAAdjustmentUi(); rerenderAdjustment(); });
      if(catAPhaseAdjust) catAPhaseAdjust.addEventListener('input', function(){ catAAdjustmentState.phaseDegrees = num(this.value, 0); syncCatAAdjustmentUi(); rerenderAdjustment(); });
      if(catAP1AmplitudeAdjust) catAP1AmplitudeAdjust.addEventListener('input', function(){ catAAdjustmentState.p1AmplitudePercent = num(this.value, 0); syncCatAAdjustmentUi(); rerenderAdjustment(); });
      if(catAP1PhaseAdjust) catAP1PhaseAdjust.addEventListener('input', function(){ catAAdjustmentState.p1PhaseDegrees = num(this.value, 0); syncCatAAdjustmentUi(); rerenderAdjustment(); });
      if(catATimeShiftMinus) catATimeShiftMinus.addEventListener('click', function(){ catAAdjustmentState.timeShiftHours -= 1; syncCatAAdjustmentUi(); rerenderAdjustment(); });
      if(catATimeShiftPlus) catATimeShiftPlus.addEventListener('click', function(){ catAAdjustmentState.timeShiftHours += 1; syncCatAAdjustmentUi(); rerenderAdjustment(); });
      if(resetCatAAdjustmentButton) resetCatAAdjustmentButton.addEventListener('click', function(){ resetCatAAdjustmentState(); rerenderAdjustment(); });

      // Least Square
      if(leastSquareAmplitudeAdjust) leastSquareAmplitudeAdjust.addEventListener('input', function(){ leastSquareAdjustmentState.amplitudePercent = num(this.value, 0); syncLeastSquareAdjustmentUi(); rerenderAdjustment(); });
      if(leastSquarePhaseAdjust) leastSquarePhaseAdjust.addEventListener('input', function(){ leastSquareAdjustmentState.phaseDegrees = num(this.value, 0); syncLeastSquareAdjustmentUi(); rerenderAdjustment(); });
      if(leastSquareP1AmplitudeAdjust) leastSquareP1AmplitudeAdjust.addEventListener('input', function(){ leastSquareAdjustmentState.p1AmplitudePercent = num(this.value, 0); syncLeastSquareAdjustmentUi(); rerenderAdjustment(); });
      if(leastSquareP1PhaseAdjust) leastSquareP1PhaseAdjust.addEventListener('input', function(){ leastSquareAdjustmentState.p1PhaseDegrees = num(this.value, 0); syncLeastSquareAdjustmentUi(); rerenderAdjustment(); });
      if(leastSquareTimeShiftMinus) leastSquareTimeShiftMinus.addEventListener('click', function(){ leastSquareAdjustmentState.timeShiftHours -= 1; syncLeastSquareAdjustmentUi(); rerenderAdjustment(); });
      if(leastSquareTimeShiftPlus) leastSquareTimeShiftPlus.addEventListener('click', function(){ leastSquareAdjustmentState.timeShiftHours += 1; syncLeastSquareAdjustmentUi(); rerenderAdjustment(); });
      if(resetLeastSquareAdjustmentButton) resetLeastSquareAdjustmentButton.addEventListener('click', function(){ resetLeastSquareAdjustmentState(); rerenderAdjustment(); });

      // Auto-Calibration Listeners
      if(autoCalibrateIndonesiaButton) autoCalibrateIndonesiaButton.addEventListener('click', function(){ runAutoCalibrationForModel('admiralty_indonesia'); });
      if(autoCalibrateCatAButton) autoCalibrateCatAButton.addEventListener('click', function(){ runAutoCalibrationForModel('admiralty_cat_a'); });
      if(autoCalibrateLeastSquareButton) autoCalibrateLeastSquareButton.addEventListener('click', function(){ runAutoCalibrationForModel('least_square'); });
      if(autoCalibrateAllButton) autoCalibrateAllButton.addEventListener('click', runAutoCalibrationForAllModels);
    }
    bindAdjustmentEvents();

    // START CALCULATION & GO TO STEP 3
    async function startCalculation(){
      if(!latestValidatedResult){
        window.alert('Validasi file data terlebih dahulu pada Tahap 1.');
        goToStep(1);
        return;
      }

      // Auto-save dataset if user skipped clicking save
      if(!latestDatasetMeta){
        const saved = await saveDataset();
        if(!saved) return;
      }

      const selected = getSelectedModels();
      if(!selected.length){
        window.alert('Pilih minimal satu model perhitungan.');
        return;
      }

      const orig = startCalculationButton.innerHTML;
      startCalculationButton.disabled = true;
      startCalculationButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Menghitung Model...';

      try {
        const formData = new FormData();
        selected.forEach(m => formData.append('model_names[]', m));

        const response = await fetch('<?= site_url('admiralty/start-calculation') ?>', {
          method: 'POST',
          body: formData,
          headers: { Accept: 'application/json' }
        });
        const payload = await response.json();
        if(!response.ok || !payload.success) throw new Error(payload.message || 'Perhitungan gagal dijalankan.');

        latestCalculationResults = payload.results || [];
        renderCalculationResults(latestCalculationResults);
        goToStep(3);
      } catch(err){
        window.alert(err.message || 'Terjadi kesalahan saat memproses perhitungan Admiralty.');
      } finally {
        startCalculationButton.disabled = false;
        startCalculationButton.innerHTML = orig;
      }
    }
    startCalculationButton.addEventListener('click', startCalculation);

    function renderCalculationResults(results){
      if(!results || !results.length) return;
      const primary = results[0];
      const summary = primary.summary || {};
      const runMeta = primary.run_meta || {};

      setText('componentMsl', (summary.msl !== undefined ? summary.msl : '-') + ' m');
      setText('componentModelLabel', summary.model_label || '-');
      setText('componentRunCode', runMeta.run_code || '-');

      // 9 Components Table
      const components = Array.isArray(primary.component_targets) ? primary.component_targets : [];
      if(components.length){
        let k1 = 0, o1 = 0, m2 = 0, s2 = 0;
        const rows = components.map(c => {
          const name = String(c.name || '').toUpperCase();
          const amp = parseFloat(c.amplitude || 0);
          if(name === 'K1') k1 = amp;
          if(name === 'O1') o1 = amp;
          if(name === 'M2') m2 = amp;
          if(name === 'S2') s2 = amp;

          return `<tr>
            <td class="p-2.5 font-bold text-sky-600">${esc(c.name)}</td>
            <td class="p-2.5 font-sans">${esc(c.group || '-')}</td>
            <td class="p-2.5 font-bold text-slate-800">${esc(c.amplitude || '-')}</td>
            <td class="p-2.5">${esc(c.phase || '-')}°</td>
          </tr>`;
        }).join('');
        setHtml('componentTableBody', rows);

        // Formzahl calculation
        if((m2 + s2) > 0){
          const f = (k1 + o1) / (m2 + s2);
          setText('dynFormzahl', 'F = ' + f.toFixed(2));
          if(f <= 0.25) setText('dynTideType', 'Semidiurnal (Ganda)');
          else if(f <= 1.50) setText('dynTideType', 'Campuran Ganda');
          else if(f <= 3.00) setText('dynTideType', 'Campuran Tunggal');
          else setText('dynTideType', 'Diurnal (Tunggal)');
        }
      }

      // Working Table (Form 20)
      const cols = Array.isArray(primary.working_columns) ? primary.working_columns : [];
      const wRows = Array.isArray(primary.working_table) ? primary.working_table : [];
      if(cols.length && wRows.length){
        setHtml('workingTableHead', '<tr>' + cols.map(c => `<th>${esc(c.label)}</th>`).join('') + '</tr>');
        setHtml('workingTableBody', wRows.slice(0, 50).map(r => '<tr>' + cols.map(c => `<td>${esc(r[c.key] ?? '')}</td>`).join('') + '</tr>').join(''));
      }

      // Comparison Summary Table
      const summaryRows = results.map(r => {
        const sm = r.summary || {};
        const rm = r.run_meta || {};
        return `<tr>
          <td class="p-2.5 font-bold text-slate-900">${esc(sm.model_label || '-')}</td>
          <td class="p-2.5 text-sky-700">${esc(rm.run_code || '-')}</td>
          <td class="p-2.5">${esc(sm.msl ?? '-')} m</td>
          <td class="p-2.5">${esc(sm.min_elevation ?? '-')} m</td>
          <td class="p-2.5">${esc(sm.max_elevation ?? '-')} m</td>
          <td class="p-2.5">${esc(sm.tidal_range ?? '-')} m</td>
          <td class="p-2.5">${esc(sm.duration_days ?? '-')} hari</td>
          <td class="p-2.5">${esc(sm.data_count ?? '-')}</td>
          <td class="p-2.5 font-sans">${esc(sm.timezone || '-')}</td>
        </tr>`;
      }).join('');
      setHtml('comparisonSummaryBody', summaryRows);

      // Reset chart visibility on new calculation so all curves appear
      comparisonChartVisibility = {};

      // Comparison Chart
      renderComparisonChart(results);

      // Prepare Prediction Panel (Step 4)
      preparePredictionPanel(results);
    }

    function renderComparisonChart(results){
      const canvas = document.getElementById('comparisonChartCanvas');
      if(!canvas) return;

      if(comparisonChartInstance){ comparisonChartInstance.destroy(); comparisonChartInstance = null; }

      const chartSource = results.find(r => r.comparison_chart && Array.isArray(r.comparison_chart.labels) && r.comparison_chart.labels.length > 0);
      if(!chartSource) return;

      const chartPayload = chartSource.comparison_chart || {};
      const labels = chartPayload.labels || [];
      const datasets = [];

      // Show adjustment cards
      if(indonesiaAdjustmentCard) indonesiaAdjustmentCard.style.display = 'flex';
      if(catAAdjustmentCard) catAAdjustmentCard.style.display = 'flex';
      if(leastSquareAdjustmentCard) leastSquareAdjustmentCard.style.display = 'flex';

      // Observed series
      if(chartPayload.observed && Array.isArray(chartPayload.observed.points)){
        latestObservedPoints = chartPayload.observed.points;
        datasets.push({
          label: 'Observasi Riil',
          model_name: 'observed',
          data: chartPayload.observed.points,
          borderColor: '#0f172a',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.2
        });
      }

      // Model series with live adjustment
      const colors = {
        admiralty_indonesia: { border:'#0f766e', bg:'rgba(15,118,110,0.1)' },
        admiralty_cat_a: { border:'#b91c1c', bg:'rgba(185,28,28,0.1)' },
        least_square: { border:'#2563eb', bg:'rgba(37,99,235,0.1)' }
      };

      results.forEach(r => {
        const c = r.comparison_chart || {};
        const sm = r.summary || {};
        const modelName = sm.model_name || '';

        if(modelName === 'admiralty_indonesia' && c.adjustment_basis) latestIndonesiaAdjustmentBasis = c.adjustment_basis;
        if(modelName === 'admiralty_cat_a' && c.adjustment_basis) latestCatAAdjustmentBasis = c.adjustment_basis;
        if(modelName === 'least_square' && c.adjustment_basis) latestLeastSquareAdjustmentBasis = c.adjustment_basis;

        if(Array.isArray(c.series)){
          c.series.forEach(s => {
            const pal = colors[s.model_name] || { border:'#64748b', bg:'rgba(100,116,139,0.1)' };
            let pts = s.points || [];

            if(s.model_name === 'admiralty_indonesia' && latestIndonesiaAdjustmentBasis) pts = buildAdjustedIndonesiaSeries() || s.points;
            if(s.model_name === 'admiralty_cat_a' && latestCatAAdjustmentBasis) pts = buildAdjustedCatASeries() || s.points;
            if(s.model_name === 'least_square' && latestLeastSquareAdjustmentBasis) pts = buildAdjustedLeastSquareSeries() || s.points;

            datasets.push({
              label: s.label || sm.model_label || 'Model',
              model_name: s.model_name,
              data: pts,
              borderColor: pal.border,
              backgroundColor: pal.bg,
              borderWidth: 2,
              pointRadius: 0,
              tension: 0.2
            });
          });
        }
      });

      syncAdjustmentCardAvailability();
      applyComparisonChartVisibility(datasets);
      renderAdjustedConstantsTable();
      renderTidalDatums();

      // Export rows preparation
      latestComparisonChartExport = {
        labels: labels.slice(),
        rows: []
      };
      if(labels.length){
        for(let idx = 0; idx < labels.length; idx++){
          const row = { timestamp: labels[idx], dataset: (chartPayload.observed && chartPayload.observed.points) ? (chartPayload.observed.points[idx] ?? '') : '' };
          datasets.forEach(d => {
            if(d.label === 'Observasi Riil') return;
            row[d.label] = Array.isArray(d.data) ? (typeof d.data[idx] !== 'undefined' ? d.data[idx] : '') : '';
          });
          latestComparisonChartExport.rows.push(row);
        }
      }

      // Render Evaluation Table
      const evaluationRows = [];
      const phaseAlignments = [];
      results.forEach(r => {
        const ch = r.comparison_chart || {};
        const evals = Array.isArray(ch.evaluations) ? ch.evaluations : [];
        evals.forEach(ev => evaluationRows.push(ev));
        if(ch.phase_alignment){
          phaseAlignments.push({
            label: (r.summary || {}).model_label || 'Model',
            offset: ch.phase_alignment.phase_offset_deg,
            before: ch.phase_alignment.rmse_before,
            after: ch.phase_alignment.rmse_after,
            improved: ch.phase_alignment.improved
          });
        }
      });

      setHtml('comparisonEvaluationBody', evaluationRows.length ? evaluationRows.map(ev => `<tr>
        <td class="p-2.5 font-bold text-slate-800">${esc(ev.label || '-')}</td>
        <td class="p-2.5">${esc(ev.mode || '-')}</td>
        <td class="p-2.5">${esc(ev.status || '-')}</td>
        <td class="p-2.5">${esc(ev.point_count ?? '-')}</td>
        <td class="p-2.5 font-bold text-sky-700">${esc(ev.rmse ?? '-')}</td>
        <td class="p-2.5">${esc(ev.mae ?? '-')}</td>
        <td class="p-2.5">${esc(ev.bias ?? '-')}</td>
        <td class="p-2.5">${esc(ev.peak_error ?? '-')}</td>
        <td class="p-2.5">${esc(ev.low_error ?? '-')}</td>
      </tr>`).join('') : '<tr><td colspan="9" class="p-3 text-center text-slate-400 font-sans">Evaluasi belum tersedia.</td></tr>');

      // Phase Alignment box
      const phaseCard = document.getElementById('phaseAlignmentCard');
      if(phaseCard){
        if(phaseAlignments.length){
          phaseCard.style.display = 'block';
          setHtml('phaseAlignmentBody', phaseAlignments.map(pa => `<div><strong>${esc(pa.label)}:</strong> phase offset ${esc(pa.offset)}° | RMSE sebelum ${esc(pa.before)} | RMSE sesudah ${esc(pa.after)} | Perbaikan: ${pa.improved ? '<span class="text-emerald-700 font-bold">Ya</span>' : 'Tidak'}</div>`).join(''));
        } else {
          phaseCard.style.display = 'none';
        }
      }

      setText('comparisonChartPoints', String(labels.length));
      setText('comparisonChartSeries', datasets.map(d => d.label).join(' vs '));

      comparisonChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: {
              position: 'top',
              labels: { boxWidth: 12, font: { size: 11 } },
              onClick: function(event, legendItem, legend){
                const chart = legend.chart;
                const datasetIndex = legendItem.datasetIndex;
                if(datasetIndex === undefined || datasetIndex === null) return;
                const isVisible = chart.isDatasetVisible(datasetIndex);
                chart.setDatasetVisibility(datasetIndex, !isVisible);
                chart.update();
                const dataset = chart.data.datasets[datasetIndex];
                if(dataset && dataset.label){
                  comparisonChartVisibility[String(dataset.label)] = !isVisible;
                }
              }
            },
            tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y.toFixed(4)} m` } }
          },
          scales: {
            y: { grid: { color: '#f1f5f9' }, title: { display: true, text: 'Elevasi Muka Air (m)' } },
            x: { grid: { display: false }, ticks: { maxTicksLimit: 12, autoSkip: true } }
          }
        }
      });

      if(exportComparisonCsvButton) exportComparisonCsvButton.disabled = false;
    }

    function exportComparisonCsv(){
      if(!latestComparisonChartExport || !Array.isArray(latestComparisonChartExport.rows) || !latestComparisonChartExport.rows.length){
        window.alert('Data grafik belum tersedia untuk diexport.');
        return;
      }
      const columns = ['timestamp', 'dataset'];
      latestComparisonChartExport.rows.forEach(function(row){
        Object.keys(row).forEach(function(key){
          if(!columns.includes(key)){ columns.push(key); }
        });
      });
      const csvLines = [
        columns.map(function(col){ return '"' + String(col).replace(/"/g, '""') + '"'; }).join(',')
      ];
      latestComparisonChartExport.rows.forEach(function(row){
        csvLines.push(columns.map(function(col){
          const val = typeof row[col] !== 'undefined' && row[col] !== null ? row[col] : '';
          return '"' + String(val).replace(/"/g, '""') + '"';
        }).join(','));
      });
      const blob = new Blob(["\uFEFF" + csvLines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'rpasoet_komparasi_model_' + (new Date().toISOString().slice(0, 10)) + '.csv';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }
    if(exportComparisonCsvButton) exportComparisonCsvButton.addEventListener('click', exportComparisonCsv);

    // STEP 4: PREDICTION SETUP
    function preparePredictionPanel(results){
      predictionRunSelect.innerHTML = '<option value="">Pilih run prediksi</option>' + results.map(r => {
        const runId = (r.run_meta || {}).run_id || '';
        const runCode = (r.run_meta || {}).run_code || 'RUN';
        const modelLabel = (r.summary || {}).model_label || 'Model';
        return `<option value="${esc(runId)}">${esc(modelLabel)} (${esc(runCode)})</option>`;
      }).join('');

      if(results.length){
        predictionRunSelect.selectedIndex = 1;
        syncPredictionRunMeta();
      }
    }

    function getPredictionRunMeta(){
      const runId = predictionRunSelect.value;
      if(!runId) return null;
      return latestCalculationResults.find(r => String((r.run_meta || {}).run_id || '') === String(runId)) || null;
    }

    function getPredictionAdjustmentState(){
      const selectedResult = getPredictionRunMeta();
      const modelName = ((selectedResult || {}).summary || {}).model_name || '';
      if(modelName === 'admiralty_indonesia') return indonesiaAdjustmentState;
      if(modelName === 'admiralty_cat_a') return catAAdjustmentState;
      if(modelName === 'least_square') return leastSquareAdjustmentState;
      return { amplitudePercent:0, phaseDegrees:0, p1AmplitudePercent:0, p1PhaseDegrees:0, timeShiftHours:0 };
    }

    function syncPredictionRunMeta(){
      const res = getPredictionRunMeta() || latestCalculationResults[0];
      if(!res) return;

      const s = res.summary || {};
      const rm = res.run_meta || {};
      setText('predictionRunCode', rm.run_code || '-');
      setText('predictionModel', s.model_label || '-');
      setText('predictionStation', s.station_name || '-');
      if(s.start_at) predictionStart.value = toDatetimeLocal(s.start_at);
      if(s.end_at) predictionEnd.value = toDatetimeLocal(s.end_at);
    }
    predictionRunSelect.addEventListener('change', syncPredictionRunMeta);

    window.setPresetDateRange = function(days){
      const startVal = predictionStart.value ? new Date(predictionStart.value) : new Date();
      const end = new Date(startVal.getTime() + days * 24 * 3600 * 1000);
      const pad = n => String(n).padStart(2,'0');
      predictionEnd.value = `${end.getFullYear()}-${pad(end.getMonth()+1)}-${pad(end.getDate())}T${pad(end.getHours())}:${pad(end.getMinutes())}`;
    };

    // GENERATE PREDICTION
    async function generatePrediction(){
      const runId = predictionRunSelect.value;
      if(!runId || !predictionStart.value || !predictionEnd.value){
        window.alert('Pilih run, tanggal mulai, dan tanggal akhir prediksi.');
        return;
      }
      const orig = generatePredictionButton.innerHTML;
      generatePredictionButton.disabled = true;
      generatePredictionButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Memproses...';

      try {
        const formData = new FormData();
        formData.set('run_id', runId);
        formData.set('start_at', predictionStart.value);
        formData.set('end_at', predictionEnd.value);
        formData.set('interval_minutes', predictionInterval.value);

        // Include calibrated adjustment state
        const adj = getPredictionAdjustmentState();
        formData.set('adjustment_amplitude_percent', adj.amplitudePercent);
        formData.set('adjustment_phase_degrees', adj.phaseDegrees);
        formData.set('adjustment_p1_amplitude_percent', adj.p1AmplitudePercent);
        formData.set('adjustment_p1_phase_degrees', adj.p1PhaseDegrees);
        formData.set('adjustment_time_shift_hours', adj.timeShiftHours);

        const response = await fetch('<?= site_url('admiralty/generate-prediction') ?>', {
          method: 'POST',
          body: formData,
          headers: { Accept: 'application/json' }
        });
        const payload = await response.json();
        if(!response.ok || !payload.success) throw new Error(payload.message || 'Prediksi gagal dibuat.');

        renderPredictionResult(payload.result);
      } catch(err){
        window.alert(err.message || 'Terjadi kesalahan saat generate prediksi.');
      } finally {
        generatePredictionButton.disabled = false;
        generatePredictionButton.innerHTML = orig;
      }
    }
    generatePredictionButton.addEventListener('click', generatePrediction);

    function renderPredictionResult(res){
      if(!res) return;
      const meta = res.meta || {};
      const rows = Array.isArray(res.rows) ? res.rows : [];

      setText('predictionRunCode', meta.run_code || '-');
      setText('predictionModel', meta.model_name || '-');
      setText('predictionStation', meta.station_name || '-');
      setText('predictionCount', String(meta.prediction_count || rows.length));

      // Table
      if(rows.length){
        setHtml('predictionTableBody', rows.slice(0, 100).map(r => `<tr><td class="p-2.5">${esc(r.datetime)}</td><td class="p-2.5 font-bold text-sky-600">${esc(r.water_level)}</td></tr>`).join(''));
      }

      // Chart
      renderPredictionChart(rows);
      if(exportWorkbookPdfButton) exportWorkbookPdfButton.disabled = false;
    }

    function renderPredictionChart(rows){
      const canvas = document.getElementById('predictionChartCanvas');
      if(!canvas) return;
      if(predictionChartInstance){ predictionChartInstance.destroy(); predictionChartInstance = null; }

      const labels = rows.map(r => r.datetime);
      const points = rows.map(r => parseFloat(r.water_level));

      predictionChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Elevasi Prediksi (m)',
            data: points,
            borderColor: '#0284c7',
            backgroundColor: 'rgba(2, 132, 199, 0.1)',
            fill: true,
            borderWidth: 2,
            pointRadius: labels.length > 100 ? 0 : 2,
            tension: 0.35
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => `Prediksi: ${ctx.parsed.y.toFixed(4)} m` } }
          },
          scales: {
            y: { grid: { color: '#f1f5f9' }, title: { display: true, text: 'Tinggi Muka Air (m)' } },
            x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } }
          }
        }
      });
    }

    // ==============================================================
    // ONE-CLICK OFFICIAL HYDROGRAPHIC SURVEY REPORT GENERATOR (PDF / A4)
    // ==============================================================
    const TIDAL_CONSTITUENT_INFO = {
      M2:  { name: 'M2',  speed: '28.9841°/j', period: '12.42 jam', desc: 'Semi-diurnal Utama Bulan (Principal Lunar)' },
      S2:  { name: 'S2',  speed: '30.0000°/j', period: '12.00 jam', desc: 'Semi-diurnal Utama Matahari (Principal Solar)' },
      N2:  { name: 'N2',  speed: '28.4397°/j', period: '12.66 jam', desc: 'Semi-diurnal Eliptis Bulan (Larger Lunar Elliptic)' },
      K2:  { name: 'K2',  speed: '30.0821°/j', period: '11.97 jam', desc: 'Semi-diurnal Deklinasi Luni-Solar' },
      K1:  { name: 'K1',  speed: '15.0411°/j', period: '23.93 jam', desc: 'Diurnal Deklinasi Luni-Solar (Solis-Lunar)' },
      O1:  { name: 'O1',  speed: '13.9430°/j', period: '25.82 jam', desc: 'Diurnal Utama Bulan (Principal Lunar Diurnal)' },
      P1:  { name: 'P1',  speed: '14.9589°/j', period: '24.07 jam', desc: 'Diurnal Utama Matahari (Principal Solar Diurnal)' },
      M4:  { name: 'M4',  speed: '57.9682°/j', period: '6.21 jam',  desc: 'Dangkal Kuarter-diurnal Bulan (Shallow Water)' },
      MS4: { name: 'MS4', speed: '58.9841°/j', period: '6.10 jam',  desc: 'Dangkal Kuarter-diurnal Luni-Solar' }
    };

    function getReportData(targetEngine){
      if(!Array.isArray(latestCalculationResults) || !latestCalculationResults.length) return null;
      const res = latestCalculationResults.find(r => r.summary && r.summary.model_name === targetEngine) || latestCalculationResults[0];
      if(!res) return null;

      const sm = res.summary || {};
      const rm = res.run_meta || {};
      const components = Array.isArray(res.component_targets) ? res.component_targets : ((res.comparison_chart && res.comparison_chart.adjustment_basis && res.comparison_chart.adjustment_basis.components) ? res.comparison_chart.adjustment_basis.components : []);
      const adjBasis = (res.comparison_chart && res.comparison_chart.adjustment_basis) ? res.comparison_chart.adjustment_basis : null;
      const offset = (adjBasis && typeof adjBasis.offset === 'number') ? adjBasis.offset : num(sm.msl, 0);
      const state = getAdjustmentStateForModel(sm.model_name);

      const orig = computeDatumsFromComponents(offset, components, null);
      const calib = computeDatumsFromComponents(offset, components, state);

      // Formzahl calculation
      const m2 = calib.amps.M2 || 0;
      const s2 = calib.amps.S2 || 0;
      const k1 = calib.amps.K1 || 0;
      const o1 = calib.amps.O1 || 0;
      const formzahl = (m2 + s2) > 0 ? ((k1 + o1) / (m2 + s2)) : 0;

      let tideType = 'Pasang Surut Harian Ganda (Semidiurnal)';
      let tideDesc = 'Terjadi 2 kali pasang dan 2 kali surut dalam sehari dengan ketinggian yang relatif sama (F ≤ 0.25).';
      if(formzahl <= 0.25){
        tideType = 'Pasang Surut Harian Ganda (Semidiurnal)';
        tideDesc = 'Terjadi 2 kali air pasang dan 2 kali air surut dalam 24 jam dengan tinggi yang hampir sama (F ≤ 0.25).';
      } else if(formzahl <= 1.50){
        tideType = 'Campuran Condong Harian Ganda (Mixed Prevailing Semidiurnal)';
        tideDesc = 'Terjadi 2 kali pasang dan 2 kali surut dalam sehari dengan tinggi dan interval yang berbeda (0.25 < F ≤ 1.50).';
      } else if(formzahl <= 3.00){
        tideType = 'Campuran Condong Harian Tunggal (Mixed Prevailing Diurnal)';
        tideDesc = 'Umumnya terjadi 1 kali pasang dan 1 kali surut per hari, namun kadang-kadang terjadi 2 kali (1.50 < F ≤ 3.00).';
      } else {
        tideType = 'Pasang Surut Harian Tunggal (Diurnal)';
        tideDesc = 'Hanya terjadi 1 kali pasang dan 1 kali surut penuh dalam satu hari pengamatan 24 jam (F > 3.00).';
      }

      // Format components table data
      const compRows = Object.keys(TIDAL_CONSTITUENT_INFO).map(code => {
        const info = TIDAL_CONSTITUENT_INFO[code];
        const origAmp = orig.amps[code] || 0;
        const calibAmp = calib.amps[code] || 0;

        let origPhase = 0;
        let calibPhase = 0;
        if(Array.isArray(components)){
          const c = components.find(x => String(x.name||'').trim().toUpperCase() === code);
          if(c){
            origPhase = num(c.phase, 0);
            calibPhase = origPhase + (state ? num(state.phaseDegrees, 0) : 0);
            if(code === 'P1' && state) calibPhase += num(state.p1PhaseDegrees, 0);
            calibPhase = ((calibPhase % 360) + 360) % 360;
          }
        }

        return {
          code,
          speed: info.speed,
          period: info.period,
          desc: info.desc,
          origAmp,
          calibAmp,
          origPhase,
          calibPhase
        };
      });

      return {
        res, sm, rm,
        offset,
        state,
        orig, calib,
        formzahl,
        tideType, tideDesc,
        compRows
      };
    }

    function buildOfficialReportHtml(mode = 'preview'){
      const engine = reportParamEngine ? reportParamEngine.value : (latestCalculationResults[0]?.summary?.model_name || 'admiralty_indonesia');
      const data = getReportData(engine);
      if(!data) return '<div class="p-6 text-center text-slate-400">Data hasil perhitungan belum tersedia.</div>';

      const companyName = (reportParamCompany && reportParamCompany.value.trim()) || 'PT. ESER GEOSURVEY INDONESIA';
      const divisionName = (reportParamDivision && reportParamDivision.value.trim()) || 'Hydro-Oceanography & Marine Survey Division';
      const docNo = (reportParamDocNo && reportParamDocNo.value.trim()) || 'EGI-TIDE-REP/2026/001';
      const projectTitle = (reportParamProject && reportParamProject.value.trim()) || 'SURVEI PENGAMATAN PASANG SURUT & PENENTUAN BIDANG KEDUDUKAN ELEVASI';
      const location = (reportParamLocation && reportParamLocation.value.trim()) || 'Perairan Indonesia';
      const stationName = (stationNameField && stationNameField.value.trim()) || data.sm.station_name || 'Stasiun Pasut';
      const lat = (latitudeField && latitudeField.value.trim()) || data.sm.latitude || '-';
      const lon = (longitudeField && longitudeField.value.trim()) || data.sm.longitude || '-';
      const timezone = (timezoneField && timezoneField.value.trim()) || data.sm.timezone || 'WIB (UTC+7)';
      const surveyor = (reportParamSurveyor && reportParamSurveyor.value.trim()) || 'Surveyor Hidrografi';
      const qc = (reportParamQc && reportParamQc.value.trim()) || 'QC Hydrographer';
      const approver = (reportParamApprover && reportParamApprover.value.trim()) || 'Chief Hydrographer / Team Leader';
      const includeChart = reportParamIncludeChart ? reportParamIncludeChart.checked : true;
      const includeSignatures = reportParamIncludeSignatures ? reportParamIncludeSignatures.checked : true;

      // Ensure chart has drawn target engine if needed
      let chartImgBase64 = '';
      if(includeChart && datumChartInstance){
        try {
          chartImgBase64 = datumChartInstance.toBase64Image('image/png', 1.0);
        } catch(e){
          console.warn('Gagal render chart to base64 image:', e);
        }
      }

      const todayStr = new Date().toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });

      // Assemble HTML content
      const bodyContent = `
        <div class="official-report-sheet">
          
          <!-- KOP SURAT RESMI (LETTERHEAD) -->
          <div class="report-header">
            <div class="header-left">
              <div class="company-brand">${esc(companyName)}</div>
              <div class="division-title">${esc(divisionName)}</div>
              <div class="company-desc">Marine Geophysical, Hydro-Oceanographic, Bathymetric & Coastal Engineering Survey</div>
            </div>
            <div class="header-right">
              <table class="meta-doc-table">
                <tr><td>No. Dokumen</td><td>:</td><td><strong>${esc(docNo)}</strong></td></tr>
                <tr><td>Revisi</td><td>:</td><td>00 (Final Release)</td></tr>
                <tr><td>Tanggal</td><td>:</td><td>${todayStr}</td></tr>
              </table>
            </div>
          </div>

          <!-- DOUBLE LINE ACCENT -->
          <div class="header-separator"></div>

          <!-- DOCUMENT TITLE -->
          <div class="report-title-box">
            <h1 class="main-title">LAPORAN AKHIR ANALISIS PASANG SURUT HIDROGRAFI</h1>
            <div class="sub-title">PENENTUAN 11 DATUM ELEVASI VERTIKAL & 9 KONSTANTA HARMONIK (ADMIRALTY 29 PIANTAN)</div>
            <div class="project-tag">PROYEK: ${esc(projectTitle)}</div>
          </div>

          <!-- SECTION 1: METADATA STASIUN & STATISTIK OBSERVASI -->
          <div class="section-title"><span class="sec-num">1.</span> DATA STASIUN & STATISTIK PENGAMATAN</div>
          <div class="grid-2-col">
            <table class="report-table info-table">
              <tr><th colspan="2" class="table-subhead">Informasi Posisi Stasiun</th></tr>
              <tr><td class="lbl">Nama Stasiun</td><td><strong>${esc(stationName)}</strong></td></tr>
              <tr><td class="lbl">Lokasi / Perairan</td><td>${esc(location)}</td></tr>
              <tr><td class="lbl">Koordinat Geografis</td><td><span class="mono">${esc(lat)}, ${esc(lon)}</span></td></tr>
              <tr><td class="lbl">Zona Waktu Stasiun</td><td>${esc(timezone)}</td></tr>
              <tr><td class="lbl">Model Perhitungan</td><td><strong>${esc(data.sm.model_label || data.sm.model_name)}</strong></td></tr>
            </table>

            <table class="report-table info-table">
              <tr><th colspan="2" class="table-subhead">Statistik & Tipe Pasut (Formzahl)</th></tr>
              <tr><td class="lbl">Durasi & Sampel</td><td>${data.sm.duration_days || 29} Hari / 29 Piantan (${data.sm.data_count || '-'} Data Titik)</td></tr>
              <tr><td class="lbl">Muka Air Rerata (MSL / S0)</td><td><strong class="highlight-val">${data.calib.msl.toFixed(3)} m</strong> (Datum Acuan 0.00 m)</td></tr>
              <tr><td class="lbl">Muka Air Tertinggi (Max)</td><td><strong>${(data.sm.max_elevation ? Number(data.sm.max_elevation).toFixed(3) : '-')} m</strong> (Elevasi Riil)</td></tr>
              <tr><td class="lbl">Muka Air Terendah (Min)</td><td><strong>${(data.sm.min_elevation ? Number(data.sm.min_elevation).toFixed(3) : '-')} m</strong> (Elevasi Riil)</td></tr>
              <tr><td class="lbl">Tunggang Pengamatan</td><td><strong>${(data.sm.tidal_range ? Number(data.sm.tidal_range).toFixed(3) : '-')} m</strong></td></tr>
              <tr><td class="lbl">Bilangan Formzahl (F)</td><td><strong class="highlight-val">F = ${data.formzahl.toFixed(2)}</strong></td></tr>
            </table>
          </div>

          <!-- TIPE PASUT BANNER -->
          <div class="tide-classification-box">
            <strong>Klasifikasi Tipe Pasut:</strong> <span class="tide-type-name">${esc(data.tideType)}</span>
            <div class="tide-type-desc">${esc(data.tideDesc)}</div>
          </div>

          <!-- SECTION 2: 9 KONSTANTA HARMONIK UTAMA -->
          <div class="section-title"><span class="sec-num">2.</span> 9 KONSTANTA HARMONIK UTAMA (ADMIRALTY 29 PIANTAN / DISHIDROS TNI AL)</div>
          <table class="report-table data-table">
            <thead>
              <tr>
                <th style="width: 32px;">No</th>
                <th style="width: 70px;">Konstanta</th>
                <th style="width: 80px;">Kecepatan (ω)</th>
                <th style="width: 75px;">Periode (T)</th>
                <th style="width: 85px;">Amplitudo (m)</th>
                <th style="width: 75px;">Amplitudo (cm)</th>
                <th style="width: 75px;">Fase (g°)</th>
                <th>Deskripsi Fenomena Astronomis & Hidrografi</th>
              </tr>
            </thead>
            <tbody>
              <tr class="highlight-row">
                <td class="center">-</td>
                <td><strong>S0 (MSL)</strong></td>
                <td class="center">-</td>
                <td class="center">-</td>
                <td class="right font-bold">${data.calib.msl.toFixed(4)}</td>
                <td class="right">${(data.calib.msl * 100).toFixed(1)}</td>
                <td class="center">-</td>
                <td>Muka Air Laut Rata-rata (Mean Sea Level) - Datum Referensi Vertikal</td>
              </tr>
              ${data.compRows.map((c, i) => `
                <tr>
                  <td class="center">${i + 1}</td>
                  <td><strong class="comp-name">${esc(c.code)}</strong></td>
                  <td class="center mono">${esc(c.speed)}</td>
                  <td class="center mono">${esc(c.period)}</td>
                  <td class="right mono font-bold">${c.calibAmp.toFixed(4)}</td>
                  <td class="right mono">${(c.calibAmp * 100).toFixed(2)}</td>
                  <td class="center mono font-bold">${c.calibPhase.toFixed(2)}°</td>
                  <td class="desc-col">${esc(c.desc)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>

          <!-- SECTION 3: 11 ELEVASI MUKA AIR PENTING (VERTICAL TIDAL DATUMS) -->
          <div class="section-title"><span class="sec-num">3.</span> KEDUDUKAN 11 DATUM ELEVASI VERTIKAL (TIDAL DATUMS) & TUNGGANG PASUT</div>
          <div class="grid-table-range">
            <table class="report-table data-table">
              <thead>
                <tr>
                  <th style="width: 30px;">No</th>
                  <th style="width: 60px;">Datum</th>
                  <th>Definisi Hidrografi & Nama Internasional</th>
                  <th style="width: 150px;">Formula Harmonik</th>
                  <th style="width: 85px;">Elevasi (m)</th>
                  <th style="width: 85px;">Relatif MSL</th>
                  <th style="width: 85px;">Di Atas LAT</th>
                </tr>
              </thead>
              <tbody>
                ${TIDAL_DATUM_SPECS.map((spec, i) => {
                  const val = data.calib[spec.key];
                  const diffMsl = val - data.calib.msl;
                  const aboveLat = val - data.calib.lat;
                  const signMsl = diffMsl > 0.0001 ? '+' : '';
                  const isMsl = spec.code === 'MSL';
                  const isLat = spec.code === 'LAT';
                  const isHat = spec.code === 'HAT';
                  const rowClass = isMsl ? 'row-msl' : (isLat ? 'row-lat' : (isHat ? 'row-hat' : ''));

                  return `
                    <tr class="${rowClass}">
                      <td class="center">${i + 1}</td>
                      <td><span class="datum-badge datum-${spec.code}">${esc(spec.code)}</span></td>
                      <td>
                        <strong>${esc(spec.desc)}</strong>
                        <div class="datum-intl-name">${esc(spec.name)}</div>
                      </td>
                      <td class="mono formula-cell">${esc(spec.formula)}</td>
                      <td class="right mono datum-val font-bold">${val.toFixed(3)} m</td>
                      <td class="right mono">${isMsl ? '0.000 m' : `${signMsl}${diffMsl.toFixed(3)} m`}</td>
                      <td class="right mono font-bold">${isLat ? '0.000 m' : `${aboveLat.toFixed(3)} m`}</td>
                    </tr>
                  `;
                }).join('')}
              </tbody>
            </table>

            <!-- TIDAL RANGE SUMMARY TILES -->
            <div class="range-summary-grid">
              <div class="range-tile">
                <div class="range-label">Tunggang Purnama (Spring Range)</div>
                <div class="range-formula font-mono">2 × (M2 + S2)</div>
                <div class="range-val">${data.calib.springRange.toFixed(3)} m</div>
              </div>
              <div class="range-tile">
                <div class="range-label">Tunggang Perbani (Neap Range)</div>
                <div class="range-formula font-mono">2 × |M2 - S2|</div>
                <div class="range-val">${data.calib.neapRange.toFixed(3)} m</div>
              </div>
              <div class="range-tile">
                <div class="range-label">Tunggang Rerata (Mean Range)</div>
                <div class="range-formula font-mono">2 × M2</div>
                <div class="range-val">${data.calib.meanRange.toFixed(3)} m</div>
              </div>
              <div class="range-tile range-tile-accent">
                <div class="range-label">Tunggang Maks. Astronomis</div>
                <div class="range-formula font-mono">HAT - LAT</div>
                <div class="range-val">${data.calib.maxRange.toFixed(3)} m</div>
              </div>
            </div>
          </div>

          <!-- SECTION 4: LAMPIRAN VISUAL GRAFIK ELEVASI PASUT -->
          ${includeChart && chartImgBase64 ? `
            <div class="page-break"></div>
            <div class="section-title"><span class="sec-num">4.</span> LAMPIRAN GRAFIK ELEVASI PASANG SURUT & 11 GARIS DATUM VERTIKAL</div>
            <div class="chart-attachment-box avoid-break">
              <img src="${chartImgBase64}" alt="Grafik Elevasi Pasut dan 11 Datum Vertikal" class="report-chart-img" />
              <div class="chart-caption">
                <strong>Gambar 1.</strong> Profil Elevasi Muka Air Pasang Surut Observasi dan Kedudukan 11 Bidang Datum Vertikal Selama Periode 29 Piantan (${esc(stationName)}).
              </div>
            </div>
          ` : ''}

          <!-- SECTION 5: LEMBAR PENGESAHAN (SIGNATURE BLOCK) -->
          ${includeSignatures ? `
            <div class="signatures-wrapper avoid-break">
              <div class="section-title" style="margin-top: 14px;"><span class="sec-num">${includeChart && chartImgBase64 ? '5.' : '4.'}</span> LEMBAR PENGESAHAN HASIL SURVEI & PENGOLAHAN</div>
              <div class="signature-grid">
                
                <div class="signature-box">
                  <div class="sig-role">Disusun Oleh:</div>
                  <div class="sig-job">Surveyor Hidrografi</div>
                  <div class="sig-line"></div>
                  <div class="sig-name"><strong>${esc(surveyor)}</strong></div>
                  <div class="sig-meta">Tanggal: ${todayStr}</div>
                </div>

                <div class="signature-box">
                  <div class="sig-role">Diperiksa Oleh:</div>
                  <div class="sig-job">Quality Control Hydrographer</div>
                  <div class="sig-line"></div>
                  <div class="sig-name"><strong>${esc(qc)}</strong></div>
                  <div class="sig-meta">Tanggal: ${todayStr}</div>
                </div>

                <div class="signature-box">
                  <div class="sig-role">Disetujui Oleh:</div>
                  <div class="sig-job">Chief Hydrographer / Team Leader</div>
                  <div class="sig-line"></div>
                  <div class="sig-name"><strong>${esc(approver)}</strong></div>
                  <div class="sig-meta">Tanggal: ${todayStr}</div>
                </div>

              </div>
              <div class="report-footer-note">
                Dokumen ini merupakan laporan teknis resmi hasil analisis data pasang surut menggunakan metode Admiralty 29 Piantan terkalibrasi. Seluruh data elevasi mengacu pada sistem ketinggian vertikal hidro-oseanografi standar IHO / Dishidros TNI AL.
              </div>
            </div>
          ` : ''}

        </div>
      `;

      // Full Standalone Document for Print / New Tab
      const styles = `
        <style>
          * { box-sizing: border-box; }
          body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background: #f8fafc;
            font-size: 11px;
            line-height: 1.4;
          }
          .official-report-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            padding: 24px 30px;
          }
          .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
          }
          .company-brand {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            line-height: 1.2;
          }
          .division-title {
            font-size: 11px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
          }
          .company-desc {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
          }
          .meta-doc-table {
            font-size: 10px;
            border-collapse: collapse;
          }
          .meta-doc-table td {
            padding: 1.5px 4px;
            white-space: nowrap;
          }
          .header-separator {
            height: 3px;
            border-top: 2px solid #0f172a;
            border-bottom: 1px solid #0284c7;
            margin: 10px 0 14px 0;
          }
          .report-title-box {
            text-align: center;
            margin-bottom: 14px;
          }
          .main-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
          }
          .sub-title {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            margin: 0 0 4px 0;
          }
          .project-tag {
            display: inline-block;
            font-size: 9.5px;
            font-weight: 700;
            color: #0284c7;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 2px 8px;
            border-radius: 4px;
          }
          .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 3px;
            margin: 12px 0 7px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
          }
          .sec-num {
            color: #0284c7;
            margin-right: 2px;
          }
          .grid-2-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
          }
          .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
          }
          .report-table th, .report-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
          }
          .report-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
          }
          .table-subhead {
            background-color: #e2e8f0 !important;
            font-size: 10.5px;
            text-transform: uppercase;
          }
          .info-table td.lbl {
            font-weight: 600;
            color: #475569;
            width: 44%;
            background: #f8fafc;
          }
          .highlight-val {
            color: #0284c7;
            font-family: monospace;
          }
          .tide-classification-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 10px;
          }
          .tide-type-name {
            font-weight: 800;
            color: #15803d;
            font-size: 11px;
          }
          .tide-type-desc {
            color: #475569;
            font-size: 9.5px;
            margin-top: 1px;
          }
          .data-table th {
            text-align: center;
            font-size: 9.5px;
            text-transform: uppercase;
            background: #f8fafc;
          }
          .comp-name {
            color: #0284c7;
            font-size: 10.5px;
            font-family: monospace;
          }
          .desc-col {
            font-size: 9px;
            color: #64748b;
          }
          .highlight-row {
            background-color: #f8fafc;
          }
          .datum-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 800;
            padding: 1px 4px;
            border-radius: 3px;
            border: 1px solid #cbd5e1;
            font-family: monospace;
          }
          .datum-HAT  { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
          .datum-HHWL { background: #ffedd5; color: #c2410c; border-color: #fed7aa; }
          .datum-MHWS { background: #fef3c7; color: #b45309; border-color: #fde68a; }
          .datum-MHWL { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
          .datum-MHWN { background: #fef9c3; color: #a16207; border-color: #fef08a; }
          .datum-MSL  { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; font-weight: 900; }
          .datum-MLWN { background: #ccfbf1; color: #0f766e; border-color: #99f6e4; }
          .datum-MLWL { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
          .datum-MLWS { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
          .datum-LLWL { background: #fae8ff; color: #a21caf; border-color: #f5d0fe; }
          .datum-LAT  { background: #e0e7ff; color: #4338ca; border-color: #c7d2fe; }
          .row-msl td { background-color: #f8fafc; font-weight: bold; }
          .row-lat td { background-color: #eef2ff; }
          .row-hat td { background-color: #fef2f2; }
          .datum-intl-name {
            font-size: 8.5px;
            color: #64748b;
          }
          .formula-cell {
            font-size: 9px;
            color: #475569;
          }
          .grid-table-range {
            display: flex;
            flex-direction: column;
            gap: 8px;
          }
          .range-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-top: 4px;
          }
          .range-tile {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
          }
          .range-tile-accent {
            background: #f0fdf4;
            border-color: #86efac;
          }
          .range-label {
            font-size: 9px;
            font-weight: 700;
            color: #475569;
          }
          .range-formula {
            font-size: 8px;
            color: #64748b;
          }
          .range-val {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            font-family: monospace;
            margin-top: 2px;
          }
          .chart-attachment-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px;
            background: #ffffff;
            margin-top: 6px;
          }
          .report-chart-img {
            width: 100%;
            height: auto;
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
          }
          .chart-caption {
            font-size: 9px;
            color: #475569;
            margin-top: 6px;
            text-align: center;
          }
          .signatures-wrapper {
            margin-top: 14px;
          }
          .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 8px;
          }
          .signature-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px 12px;
            text-align: center;
            background: #fdfdfd;
          }
          .sig-role {
            font-size: 9.5px;
            color: #64748b;
          }
          .sig-job {
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
          }
          .sig-line {
            height: 48px;
            border-bottom: 1px solid #94a3b8;
            margin-bottom: 6px;
          }
          .sig-name {
            font-size: 11px;
            color: #0f172a;
          }
          .sig-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
          }
          .report-footer-note {
            font-size: 8.5px;
            color: #64748b;
            font-style: italic;
            text-align: justify;
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
          }
          .center { text-align: center; }
          .right { text-align: right; }
          .mono { font-family: monospace; }
          .font-bold { font-weight: bold; }

          @media print {
            body {
              background: #ffffff !important;
              -webkit-print-color-adjust: exact !important;
              print-color-adjust: exact !important;
            }
            .no-print { display: none !important; }
            .official-report-sheet {
              max-width: 100% !important;
              padding: 0 !important;
              box-shadow: none !important;
            }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
          }
          @page {
            size: A4 portrait;
            margin: 12mm 14mm 12mm 14mm;
          }
        </style>
      `;

      if(mode === 'preview'){
        return styles + bodyContent;
      }

      if(mode === 'newtab'){
        return `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Akhir Survei Pasut - ${esc(stationName)}</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  ${styles}
  <style>
    .top-toolbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      background: #0f172a;
      color: #fff;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .print-btn {
      background: #e11d48;
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .print-btn:hover { background: #be123c; }
  </style>
</head>
<body>
  <div class="top-toolbar no-print">
    <div style="font-size: 13px; font-weight: bold;">
      <i class="fa-solid fa-file-invoice" style="margin-right: 6px; color: #fb7185;"></i>
      Laporan Survei Pasut Hidrografi: ${esc(stationName)}
    </div>
    <div style="display:flex; gap:10px;">
      <button type="button" onclick="window.print()" class="print-btn"><i class="fa-solid fa-print"></i> Cetak / Simpan ke PDF</button>
      <button type="button" onclick="window.close()" style="background:#334155; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-size:12px; cursor:pointer;">Tutup</button>
    </div>
  </div>
  <div style="padding: 20px 0;">
    ${bodyContent}
  </div>
</body>
</html>`;
      }

      // Standalone for popup window print
      return `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Akhir Survei Pasut - ${esc(stationName)}</title>
  ${styles}
</head>
<body>
  ${bodyContent}
  <script>
    window.onload = function(){
      setTimeout(function(){
        window.focus();
        window.print();
      }, 500);
    };
  <\/script>
</body>
</html>`;
    }

    function updateOfficialReportPreview(){
      if(!officialReportDocumentTarget) return;
      officialReportDocumentTarget.innerHTML = buildOfficialReportHtml('preview');
    }

    function openOfficialReportModal(){
      if(!Array.isArray(latestCalculationResults) || !latestCalculationResults.length){
        window.alert('Hasil perhitungan belum tersedia. Silakan jalankan perhitungan model pada Tahap 2 terlebih dahulu.');
        return;
      }

      // Populate engine select options
      if(reportParamEngine){
        reportParamEngine.innerHTML = latestCalculationResults.map(r => {
          const mName = (r.summary && r.summary.model_name) || '';
          const mLabel = (r.summary && r.summary.model_label) || mName;
          return `<option value="${esc(mName)}">${esc(mLabel)}</option>`;
        }).join('');
        if(datumChartActiveEngine){
          reportParamEngine.value = datumChartActiveEngine;
        }
      }

      // Pre-fill location/station if possible
      const primary = latestCalculationResults[0];
      const sm = primary.summary || {};
      if(stationNameField && stationNameField.value && reportParamProject){
        // keep project
      }

      // Ensure datum chart is drawn so image is available
      renderDatumElevationChart(reportParamEngine ? reportParamEngine.value : datumChartActiveEngine);

      // Render preview
      updateOfficialReportPreview();

      // Show modal
      if(officialReportModal){
        officialReportModal.classList.remove('hidden');
      }
    }

    function closeOfficialReportModal(){
      if(officialReportModal){
        officialReportModal.classList.add('hidden');
      }
    }

    function printOfficialReport(){
      const html = buildOfficialReportHtml('print');
      const printWindow = window.open('', '_blank', 'width=950,height=800,scrollbars=yes');
      if(!printWindow){
        window.alert('Popup browser diblokir. Harap izinkan popup pada browser Anda untuk mencetak laporan.');
        return;
      }
      printWindow.document.open();
      printWindow.document.write(html);
      printWindow.document.close();
    }

    function openOfficialReportNewTab(){
      const html = buildOfficialReportHtml('newtab');
      const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
      const url = URL.createObjectURL(blob);
      window.open(url, '_blank');
      setTimeout(() => URL.revokeObjectURL(url), 60000);
    }

    // Bind Report Listeners
    if(openOfficialReportModalBtn) openOfficialReportModalBtn.addEventListener('click', openOfficialReportModal);
    if(openOfficialReportModalBtn2) openOfficialReportModalBtn2.addEventListener('click', openOfficialReportModal);
    if(exportWorkbookPdfButton) exportWorkbookPdfButton.addEventListener('click', openOfficialReportModal);
    if(closeOfficialReportModalBtn) closeOfficialReportModalBtn.addEventListener('click', closeOfficialReportModal);
    if(refreshOfficialReportBtn) refreshOfficialReportBtn.addEventListener('click', updateOfficialReportPreview);
    if(printOfficialReportBtn) printOfficialReportBtn.addEventListener('click', printOfficialReport);
    if(openOfficialReportNewTabBtn) openOfficialReportNewTabBtn.addEventListener('click', openOfficialReportNewTab);

    if(reportParamEngine){
      reportParamEngine.addEventListener('change', function(e){
        renderDatumElevationChart(e.target.value);
        updateOfficialReportPreview();
      });
    }
    [reportParamCompany, reportParamDivision, reportParamDocNo, reportParamProject, reportParamLocation, reportParamSurveyor, reportParamQc, reportParamApprover].forEach(input => {
      if(input) input.addEventListener('input', updateOfficialReportPreview);
    });
    [reportParamIncludeChart, reportParamIncludeSignatures].forEach(chk => {
      if(chk) chk.addEventListener('change', updateOfficialReportPreview);
    });

    // Close modal on click outside sheet
    if(officialReportModal){
      officialReportModal.addEventListener('click', function(e){
        if(e.target === officialReportModal){
          closeOfficialReportModal();
        }
      });
    }

    // EXPORT TIDAL DATUMS CSV
    const exportDatumsBtn = document.getElementById('exportTidalDatumsCsvButton');
    if(exportDatumsBtn){
      exportDatumsBtn.addEventListener('click', exportTidalDatumsCsv);
    }

    // INITIALIZATION
    restoreFormDraft();
    stationNameField.addEventListener('input', saveFormDraft);
    latitudeField.addEventListener('input', saveFormDraft);
    longitudeField.addEventListener('input', saveFormDraft);
    timezoneField.addEventListener('change', saveFormDraft);

    if(initialValidatedResult && typeof initialValidatedResult === 'object'){
      applyValidatedResult(initialValidatedResult);
      const alertEl = document.getElementById('restoredSessionAlert');
      if(alertEl) alertEl.style.display = 'block';
    }
    if(initialDatasetMeta && typeof initialDatasetMeta === 'object'){
      applyDatasetMeta(initialDatasetMeta);
    }
  })();
  </script>
</body>
</html>
