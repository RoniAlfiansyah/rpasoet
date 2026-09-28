<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<style>
body{font-family:"Segoe UI",Tahoma,sans-serif}
.brand-link{display:flex;align-items:center;font-weight:800}
.brand-link .brand-image{float:none;max-height:none;width:2.1rem;height:2.1rem;line-height:2.1rem;text-align:center;margin-right:.65rem;margin-left:0;font-size:1.05rem;border-radius:.55rem;background:rgba(255,255,255,.12)}
.user-panel{align-items:flex-start!important;gap:.75rem}
.user-panel .image{flex:0 0 auto}
.user-panel .info{flex:1 1 auto;min-width:0;padding-top:.1rem!important}
.user-panel .info span{white-space:normal;overflow-wrap:anywhere;line-height:1.35;font-size:.98rem}
.main-sidebar{background:linear-gradient(180deg,#16202c,#1c2735 58%,#213245)!important}
.main-sidebar,.main-sidebar .brand-link,.main-sidebar .nav-link,.content-wrapper,.main-header{transition:margin-left .22s ease,width .22s ease,transform .22s ease}
.sidebar-mini.sidebar-collapse .brand-text,.sidebar-mini.sidebar-collapse .user-panel .info,.sidebar-mini.sidebar-collapse .nav-sidebar .nav-link p{opacity:0;transition:opacity .12s ease}
.sidebar-mini .brand-text,.sidebar-mini .user-panel .info,.sidebar-mini .nav-sidebar .nav-link p{opacity:1;transition:opacity .18s ease}
.sidebar-mini.sidebar-collapse .main-sidebar .nav-link{justify-content:center}
.sidebar-mini.sidebar-collapse .main-sidebar .nav-icon{margin-right:0!important}
.content-wrapper{background:linear-gradient(180deg,#eff3f8,#e7edf5)}
.content-header h1{font-weight:800}
.nav-sidebar .nav-link{border-radius:.6rem;margin-bottom:.2rem}
.nav-sidebar .nav-link.active{background:linear-gradient(135deg,#0f766e,#169188)!important;color:#fff}
.topbar-chip,.hero-tag{display:inline-flex;align-items:center;gap:.5rem;border-radius:999px;font-weight:700}
.topbar-chip{padding:.38rem .75rem;background:#edf7f6;border:1px solid rgba(15,118,110,.15);font-size:.78rem;color:#0f766e}
.hero-card{background:linear-gradient(135deg,#08111c,#0f2d46 52%,#0f766e)!important;border:0;color:#fff;box-shadow:0 28px 50px rgba(15,23,42,.16)}
.hero-card .card-body{padding:2rem}
.hero-card h2{font-size:2rem;font-weight:800;letter-spacing:-.03em;margin-bottom:.85rem}
.hero-card p{max-width:760px;margin:0;color:rgba(255,255,255,.86);line-height:1.7}
.hero-tag{padding:.45rem .8rem;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.14);font-size:.8rem;margin-bottom:1rem}
.validation-card{border-top:4px solid #0f766e}
.small-box h3{font-weight:800}
.small-box p{font-weight:600}
.table thead th{text-transform:uppercase;font-size:.75rem;letter-spacing:.08em;color:#64748b}
.table td,.table th{vertical-align:middle}
.preview-card .card-body{display:flex;flex-direction:column}
.scroll-table{max-height:278px;overflow:auto}
.working-scroll{max-height:320px;overflow:auto}
.drop-zone{border:2px dashed #b8cad8;border-radius:1rem;padding:1.35rem;background:#f8fbfd;transition:border-color .18s ease,background .18s ease}
.drop-zone:hover{border-color:#0f766e;background:#f2fbfa}
.drop-zone-title{font-weight:800;color:#0f172a;font-size:1rem}
.drop-zone-note{color:#64748b;margin-bottom:0}
.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-top:1rem}
.metric-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem}
.metric-item{padding:1rem;border:1px solid #dbe5ee;border-radius:.95rem;background:#f8fbfd}
.metric-item strong{display:block;color:#0f172a;margin-bottom:.35rem}
.metric-item span{color:#64748b}
.status-pill{display:inline-flex;align-items:center;gap:.45rem;border-radius:999px;padding:.35rem .7rem;font-weight:700;font-size:.84rem}
.status-pill.ok{background:#e9f9f3;color:#0f766e}
.status-pill.warn{background:#fff4df;color:#b7791f}
.status-pill.err{background:#fff1f2;color:#c53030}
.stage-card{border-top:4px solid #0f766e}
.stage-note{border:1px dashed #cbd5e1;border-radius:.95rem;padding:1rem;background:#f8fafc;color:#64748b}
.calc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
.calc-item{padding:1rem;border:1px solid #dbe5ee;border-radius:.95rem;background:#f8fbfd}
.calc-item strong{display:block;color:#0f172a;margin-bottom:.35rem}
.calc-item span{color:#64748b}
.model-select-panel{border:1px solid #bfe7e2;border-radius:1rem;background:linear-gradient(180deg,#f2fbfa,#ecf8f6);padding:1rem 1rem 1.1rem;margin-bottom:1rem}
.model-select-head{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:.9rem}
.model-select-title{font-weight:800;color:#0f172a;font-size:1rem;margin-bottom:.2rem}
.model-select-subtitle{color:#4b5563;margin:0}
.model-select-badge{display:inline-flex;align-items:center;gap:.45rem;padding:.42rem .8rem;border-radius:999px;background:#0f766e;color:#fff;font-weight:700;font-size:.82rem}
.model-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-top:1rem}
.model-option{padding:1rem 1rem;border:1px solid #cfe3f1;border-radius:.95rem;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,.04);transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease}
.model-option:hover{border-color:#0f766e;box-shadow:0 10px 22px rgba(15,118,110,.10);transform:translateY(-1px)}
.model-option label{display:flex;align-items:flex-start;gap:.65rem;margin:0;font-weight:700;color:#0f172a;cursor:pointer}
.model-option small{display:block;font-weight:400;color:#64748b;margin-top:.15rem}
.model-option input[type="checkbox"]{transform:scale(1.18);margin-top:.18rem}
.model-select-note{margin-top:.85rem;color:#0f766e;font-weight:700;font-size:.88rem}
.multi-result-card{border:1px solid #dbe5ee;border-radius:1rem;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05);margin-bottom:1rem}
.multi-result-head{padding:1rem 1rem .75rem;border-bottom:1px solid #edf2f7;display:flex;justify-content:space-between;gap:.75rem;align-items:center;flex-wrap:wrap}
.multi-result-body{padding:1rem}
.subpanel-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:.75rem;margin-top:.75rem;align-items:start}
.subpanel-grid.single-panel{grid-template-columns:minmax(320px,520px)}
.subpanel-item{padding:.9rem;border:1px solid #dbe5ee;border-radius:.85rem;background:#f8fbfd}
.subpanel-item h6{font-size:.92rem;font-weight:800;margin-bottom:.45rem;color:#0f172a}
.subpanel-item p{margin:0 0 .45rem;color:#64748b;font-size:.87rem}
.subpanel-item .small{display:block;color:#475569;margin-bottom:.2rem}
.skema-panel{padding:.9rem;border:1px solid #dbe5ee;border-radius:.85rem;background:#f8fbfd}
.skema-panel h6{font-size:.92rem;font-weight:800;margin-bottom:.45rem;color:#0f172a}
.skema-panel p{margin:0 0 .45rem;color:#64748b;font-size:.87rem}
.result-section-title{font-size:1rem;font-weight:800;color:#0f172a;margin:1rem 0 .75rem}
.comparison-card{border-top:4px solid #0f766e}
.comparison-table th,.comparison-table td{white-space:nowrap}
.dev-panel{border-color:#f6c453;background:#fffaf0}
.dev-panel h6{color:#92400e}
.comparison-highlight-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1rem}
.comparison-highlight-item{padding:1rem;border:1px solid #dbe5ee;border-radius:.95rem;background:#f8fbfd}
.comparison-highlight-item strong{display:block;color:#0f172a;margin-bottom:.35rem}
.comparison-highlight-item span{color:#64748b}
.comparison-chart-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1rem}
.adjustment-tools-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem;margin-bottom:1rem}
.adjustment-tool-card{border:1px solid #dbe5ee;border-radius:1rem;background:linear-gradient(180deg,#ffffff,#f8fbfd);box-shadow:0 10px 24px rgba(15,23,42,.05);transition:opacity .18s ease,box-shadow .18s ease}
.adjustment-tool-card.is-disabled{opacity:.55;box-shadow:none;background:#f8fafc}
.adjustment-tool-card .card-header{padding:.85rem 1rem}
.adjustment-tool-card .card-body{padding:1rem}
.adjustment-tool-title{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
.adjustment-tool-title strong{font-size:.96rem;color:#0f172a}
.adjustment-tool-status{display:inline-flex;align-items:center;border-radius:999px;padding:.28rem .65rem;background:#e9f9f3;color:#0f766e;font-size:.76rem;font-weight:800}
.adjustment-tool-status.is-disabled{background:#e5e7eb;color:#64748b}
.adjustment-tool-controls{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}
.adjustment-tool-controls .form-group{margin-bottom:0}
.adjustment-tool-controls label{display:block;font-size:.82rem;font-weight:800;color:#0f172a;margin-bottom:.35rem}
.adjustment-tool-controls small{display:block;min-height:1.25rem}
.adjustment-time-box{display:flex;align-items:center;justify-content:space-between;gap:.55rem;min-height:38px;padding:.4rem .55rem;border:1px solid #dbe5ee;border-radius:.8rem;background:#fff}
.adjustment-time-box .btn{min-width:2rem}
.adjustment-time-value{font-weight:800;color:#0f172a}
.adjustment-tool-help{margin-top:.85rem;font-size:.8rem;color:#64748b;line-height:1.5}
.comparison-chart-wrap{position:relative;height:380px}
.prediction-table{max-height:320px;overflow:auto}
.minimize-toggle{border:0;background:transparent;color:#64748b;padding:.25rem .35rem;line-height:1}
.minimize-toggle:hover{color:#0f172a}
@media (max-width:768px){.topbar-extra{display:none}.hero-card .card-body{padding:1.45rem}.hero-card h2{font-size:1.6rem}.subpanel-grid,.subpanel-grid.single-panel{grid-template-columns:1fr}.adjustment-tool-controls{grid-template-columns:1fr}}
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
<ul class="navbar-nav">
<li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
<li class="nav-item d-none d-sm-inline-block"><a href="<?= site_url('admiralty') ?>" class="nav-link">Admiralty</a></li>
</ul>
<ul class="navbar-nav ml-auto topbar-extra">
<li class="nav-item mr-2"><span class="topbar-chip"><i class="fas fa-chart-line"></i>Analisis Harmonik</span></li>
<li class="nav-item"><span class="topbar-chip"><i class="fas fa-water"></i>Prediksi Pasang Surut</span></li>
</ul>
</nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
<a href="<?= site_url('tides') ?>" class="brand-link"><span class="brand-image elevation-2"><i class="fas fa-water"></i></span><span class="brand-text">R-Pasoet</span></a>
<div class="sidebar">
<div class="user-panel mt-3 pb-3 mb-3 d-flex"><div class="image"><div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-teal" style="width:34px;height:34px;"><i class="fas fa-compass"></i></div></div><div class="info"><span class="d-block text-white">Analisa dan Prediksi pasang surut</span></div></div>
<nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false"><li class="nav-item"><a href="<?= site_url('tides') ?>" class="nav-link"><i class="nav-icon fas fa-cloud-download-alt"></i><p>Fetch Tide SRGI</p></a></li><li class="nav-item"><a href="<?= site_url('admiralty') ?>" class="nav-link active"><i class="nav-icon fas fa-chart-area"></i><p>Predictor</p></a></li></ul></nav>
</div>
</aside>
<div class="content-wrapper">
<section class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>Admiralty</h1></div></div></div></section>
<section class="content"><div class="container-fluid">
<div class="card hero-card mb-4"><div class="card-body"><div class="hero-tag"><i class="fas fa-drafting-compass"></i>Validasi data sebelum perhitungan</div><h2>Upload Data Pasang Surut</h2><p>Mulai dari file CSV observasi pasut. Sistem akan membaca data, menghitung interval dominan, jumlah data, rentang waktu, dan mendeteksi apakah ada gap sebelum kita lanjut ke perhitungan Admiralty.</p></div></div>

<div class="card validation-card">
<div class="card-header d-flex justify-content-between align-items-center">
<h3 class="card-title mb-0"><i class="fas fa-file-upload mr-2"></i>Upload Dan Validasi Data</h3>
<div class="d-flex align-items-center">
<button type="button" class="btn btn-sm btn-outline-danger mr-2 reset-admiralty-btn" style="display:none;" title="Reset data validasi dan unggah file baru"><i class="fas fa-undo mr-1"></i>Reset / Upload Baru</button>
<button type="button" class="minimize-toggle" id="validationCardToggle" aria-label="Minimize upload dan validasi"><i class="fas fa-minus"></i></button>
</div>
</div>
<div class="card-body" id="validationCardBody">
<div id="restoredSessionAlert" class="alert alert-info mb-3 py-2" style="display:none;">
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
<div><i class="fas fa-info-circle mr-2"></i><strong>Data Dipulihkan:</strong> Data validasi sebelumnya dipulihkan dari sesi. Anda dapat langsung melanjutkan atau klik tombol reset jika ingin mengganti file.</div>
<button type="button" class="btn btn-xs btn-outline-danger reset-admiralty-btn"><i class="fas fa-undo mr-1"></i>Reset / Ganti File</button>
</div>
</div>
<form id="uploadForm" enctype="multipart/form-data">
<div class="drop-zone mb-3">
<div class="drop-zone-title mb-2">Pilih file CSV atau Excel data pasang surut</div>
<p class="drop-zone-note mb-3">Format yang didukung saat ini adalah `csv`, `txt`, dan `xlsx`. Sistem akan mencoba mengenali baris yang berisi kolom tanggal/jam dan nilai tinggi muka air, termasuk format seperti `dd/mm/yyyy hh:mm` atau `yyyy-mm-dd hh:mm:ss`.</p>
<div class="meta-grid">
<div class="form-group mb-0"><label for="station_name">Nama Stasiun</label><input type="text" class="form-control" id="station_name" name="station_name" placeholder="Contoh: Tanjung Priok"></div>
<div class="form-group mb-0"><label for="latitude">Latitude</label><input type="text" class="form-control" id="latitude" name="latitude" placeholder="Contoh: -6.107800"></div>
<div class="form-group mb-0"><label for="longitude">Longitude</label><input type="text" class="form-control" id="longitude" name="longitude" placeholder="Contoh: 106.880300"></div>
<div class="form-group mb-0"><label for="timezone">Time Zone</label><select class="form-control" id="timezone" name="timezone"><option value="Asia/Jakarta" selected>(GMT+7) WIB - Asia/Jakarta</option><option value="Asia/Makassar">(GMT+8) WITA - Asia/Makassar</option><option value="Asia/Jayapura">(GMT+9) WIT - Asia/Jayapura</option></select></div>
</div>
<div class="row align-items-end">
<div class="col-lg-8"><div class="form-group mb-lg-0"><label for="tide_file">File Data</label><input type="file" class="form-control-file" id="tide_file" name="tide_file" accept=".csv,.txt,.xlsx"></div></div>
<div class="col-lg-4">
<div class="d-flex" style="gap:.5rem;">
<button type="submit" class="btn btn-primary flex-fill" id="validateButton"><i class="fas fa-check-circle mr-1"></i>Validasi Data</button>
<button type="button" class="btn btn-outline-danger reset-admiralty-btn" style="display:none;" title="Reset form dan hapus data validasi"><i class="fas fa-undo mr-1"></i>Reset</button>
</div>
</div>
</div>
</div>
</form>
<div class="alert alert-info mb-0">Target validasi awal: cek interval, jumlah data, tanggal/jam mulai, tanggal/jam akhir, duplikasi, dan gap data.</div>
</div>
</div>

<div id="resultArea" style="display:none;">
<div class="row">
<div class="col-lg-3 col-md-6"><div class="small-box bg-info"><div class="inner"><h3 id="summaryInterval">-</h3><p>Interval dominan</p></div><div class="icon"><i class="fas fa-stopwatch"></i></div></div></div>
<div class="col-lg-3 col-md-6"><div class="small-box bg-success"><div class="inner"><h3 id="summaryCount">0</h3><p>Jumlah data valid</p></div><div class="icon"><i class="fas fa-list-ol"></i></div></div></div>
<div class="col-lg-3 col-md-6"><div class="small-box bg-warning"><div class="inner"><h3 id="summaryGap">Tidak</h3><p>Status gap data</p></div><div class="icon"><i class="fas fa-unlink"></i></div></div></div>
<div class="col-lg-3 col-md-6"><div class="small-box bg-secondary"><div class="inner"><h3 id="summaryDuplicates">0</h3><p>Duplikasi timestamp</p></div><div class="icon"><i class="fas fa-clone"></i></div></div></div>
</div>

<div class="card">
<div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check mr-2"></i>Ringkasan Validasi</h3></div>
<div class="card-body">
<div class="metric-list mb-3">
<div class="metric-item"><strong>Tanggal/Jam Mulai</strong><span id="metricStart">-</span></div>
<div class="metric-item"><strong>Tanggal/Jam Akhir</strong><span id="metricEnd">-</span></div>
<div class="metric-item"><strong>Status Minimum 360 Data</strong><span id="metricMinStatus">-</span></div>
<div class="metric-item"><strong>Status Maksimum 720 Data</strong><span id="metricMaxStatus">-</span></div>
<div class="metric-item"><strong>Kualitas Dataset</strong><span id="metricQualityStatus">-</span></div>
</div>
<div class="row">
<div class="col-lg-4"><div class="metric-item h-100"><strong>Baris valid</strong><span id="metricValidRows">-</span></div></div>
<div class="col-lg-4"><div class="metric-item h-100"><strong>Baris invalid</strong><span id="metricInvalidRows">-</span></div></div>
<div class="col-lg-4"><div class="metric-item h-100"><strong>Total gap terdeteksi</strong><span id="metricGapCount">-</span></div></div>
</div>
<div class="mt-3 d-flex flex-wrap" style="gap:.75rem;">
<button type="button" class="btn btn-primary" id="saveDatasetButton" disabled><i class="fas fa-save mr-1"></i>Simpan Dataset</button>
<button type="button" class="btn btn-outline-danger reset-admiralty-btn" style="display:none;"><i class="fas fa-undo mr-1"></i>Reset / Ganti File</button>
<span id="calculationEligibility" class="text-muted align-self-center">Lakukan validasi data terlebih dahulu.</span>
</div>
<div id="qualityReasons" class="stage-note mt-3">Status kualitas dataset akan muncul di sini.</div>
</div>
</div>

<div class="row">
<div class="col-lg-6">
<div class="card preview-card">
<div class="card-header"><h3 class="card-title"><i class="fas fa-table mr-2"></i>Preview Data Valid</h3></div>
<div class="card-body p-0">
<div class="table-responsive scroll-table">
<table class="table table-striped mb-0">
<thead><tr><th>Line</th><th>Tanggal/Jam</th><th>Water Level</th></tr></thead>
<tbody id="previewBody"><tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>
<div class="col-lg-6">
<div class="card">
<div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Temuan Validasi</h3></div>
<div class="card-body">
<div class="mb-3"><strong class="d-block mb-2">Status interval dan jumlah data</strong><div id="statusList"></div></div>
<div class="mb-3"><strong class="d-block mb-2">Contoh gap</strong><div id="gapList" class="text-muted">Belum ada data.</div></div>
<div><strong class="d-block mb-2">Baris invalid</strong><div id="invalidList" class="text-muted">Belum ada data.</div></div>
</div>
</div>
</div>
</div>
</div>

<div id="calculationArea" style="display:none;">
<div class="card stage-card mt-4">
<div class="card-header"><h3 class="card-title"><i class="fas fa-cogs mr-2"></i>Tahap Perhitungan Admiralty</h3></div>
<div class="card-body">
<div class="stage-note mb-3" id="calculationIntro">Dataset sudah lolos validasi awal. Tahap berikutnya adalah menjalankan engine Admiralty 9 komponen dan menampilkan tabel perhitungan secara read-only untuk audit langkah hitung.</div>
<div class="model-select-panel">
<div class="model-select-head">
<div>
<div class="model-select-title">Pilih Model Perhitungan</div>
<p class="model-select-subtitle">Centang minimal satu model sebelum klik <strong>Mulai Hitung Admiralty</strong>. Saat ini sistem mendukung empat metode aktif yang bisa dihitung dan dibandingkan pada dataset yang sama.</p>
</div>
<div class="model-select-badge"><i class="fas fa-check-double"></i>Wajib dipilih</div>
</div>
<div class="model-grid">
<div class="model-option"><label><input type="checkbox" class="model-checkbox mt-1" name="model_names[]" value="admiralty_indonesia"> <span>Admiralty Hidro-Oseanografi Indonesia<small>Format kerja yang diarahkan ke praktik hidro-oseanografi Indonesia.</small></span></label></div>
<div class="model-option"><label><input type="checkbox" class="model-checkbox mt-1" name="model_names[]" value="admiralty_hidros"> <span>Admiralty Hidros<small>Formula berbasis workbook Excel terpisah untuk pembanding Admiralty.</small></span></label></div>
<div class="model-option"><label><input type="checkbox" class="model-checkbox mt-1" name="model_names[]" value="admiralty_cat_a"> <span>Admiralty Cat A<small>Ekstraksi harmonik Admiralty PHP-native berbasis observasi langsung untuk pembanding akurasi lapangan.</small></span></label></div>
<div class="model-option"><label><input type="checkbox" class="model-checkbox mt-1" name="model_names[]" value="least_square"> <span>Least Square<small>Pendekatan numerik dengan basis sin/cos untuk fitting harmonik.</small></span></label></div>
</div>
<div class="model-select-note"><i class="fas fa-info-circle mr-1"></i>Tips: centang beberapa metode sekaligus jika ingin langsung melihat perbandingan hasil pada dataset yang sama.</div>
<div class="mt-3 d-flex flex-wrap align-items-center" style="gap:.75rem;">
<button type="button" class="btn btn-success" id="startCalculationButton" disabled><i class="fas fa-play-circle mr-1"></i>Mulai Hitung Admiralty</button>
<span id="modelSelectionHint" class="text-muted">Simpan dataset terlebih dahulu, lalu pilih minimal satu model perhitungan.</span>
</div>
</div>
<div class="calc-grid mb-3">
<div class="calc-item"><strong>Dataset Siap Hitung</strong><span id="calcDatasetStatus">-</span></div>
<div class="calc-item"><strong>Interval Kerja</strong><span id="calcInterval">-</span></div>
<div class="calc-item"><strong>Jumlah Data Dipakai</strong><span id="calcCount">-</span></div>
<div class="calc-item"><strong>Rentang Waktu</strong><span id="calcRange">-</span></div>
</div>
<div class="alert alert-light border mb-3">
<strong>Rencana tahap berikutnya:</strong>
<div>1. Bentuk deret observasi siap hitung dari data valid.</div>
<div>2. Jalankan perhitungan Admiralty 9 komponen di backend.</div>
<div>3. Tampilkan tabel perhitungan bantu dan hasil harmonik sebagai tampilan read-only.</div>
<div>4. Lanjutkan ke prediksi pasang surut dari konstanta harmonik.</div>
</div>
</div>
</div>

<div id="calculationTables" style="display:none;">
<div class="col-lg-6">
<div class="card">
<div class="card-header"><h3 class="card-title" id="workingTableTitle"><i class="fas fa-table mr-2"></i>Tabel Kerja Admiralty</h3></div>
<div class="card-body p-0">
<div class="table-responsive working-scroll">
<table class="table table-striped mb-0">
<thead><tr id="workingTableHead"><th>No</th><th>Tanggal/Jam</th><th>Hari</th><th>Jam</th><th>Elevasi</th><th>Deviasi MSL</th></tr></thead>
<tbody id="workingTableBody"><tr><td colspan="6" class="text-center text-muted">Tabel kerja belum dibuat.</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>
<div class="col-lg-6">
<div class="card">
<div class="card-header"><h3 class="card-title"><i class="fas fa-wave-square mr-2"></i>Hasil Harmonika 9 Komponen</h3></div>
<div class="card-body">
<div class="calc-grid mb-3">
<div class="calc-item"><strong>Status Dataset</strong><span id="componentDatasetStatus">-</span></div>
<div class="calc-item"><strong>MSL</strong><span id="componentMsl">-</span></div>
<div class="calc-item"><strong>Model Aktif</strong><span id="componentModelLabel">-</span></div>
<div class="calc-item"><strong>Run Code</strong><span id="componentRunCode">-</span></div>
</div>
<div class="table-responsive">
<table class="table table-striped mb-0">
<thead><tr><th>Komponen</th><th>Kelompok</th><th>Amplitudo</th><th>Fase</th><th>Status</th></tr></thead>
<tbody id="componentTableBody"><tr><td colspan="5" class="text-center text-muted">Komponen belum disiapkan.</td></tr></tbody>
</table>
</div>
<div class="stage-note mt-3" id="calculationNotes">Catatan tahap perhitungan akan muncul di sini.</div>
</div>
</div>
</div>
</div>
</div>
<div id="multiModelResults" style="display:none;"></div>
<div id="comparisonArea" style="display:none;">
<div class="card comparison-card mt-4">
<div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
<h3 class="card-title mb-0"><i class="fas fa-balance-scale mr-2"></i>Perbandingan Model</h3>
<button type="button" class="btn btn-outline-danger btn-sm" id="exportWorkbookPdfButton" disabled><i class="fas fa-file-pdf mr-1"></i>Export PDF</button>
</div>
<div class="card-body">
<div id="comparisonHighlights" class="mb-3">
<div class="text-muted">Ringkasan perbandingan model akan muncul di sini.</div>
</div>
<div class="card border mb-3">
<div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
<strong>Grafik Perbandingan 15 Hari</strong>
<button type="button" class="btn btn-outline-success btn-sm" id="exportComparisonCsvButton" disabled><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
</div>
<div class="card-body">
<div id="comparisonChartMeta" class="comparison-chart-meta">
<div class="metric-item"><strong>Status Grafik</strong><span id="comparisonChartStatus">Belum ada data grafik.</span></div>
<div class="metric-item"><strong>Jumlah Titik</strong><span id="comparisonChartPoints">-</span></div>
<div class="metric-item"><strong>Seri Aktif</strong><span id="comparisonChartSeries">-</span></div>
</div>
<div class="comparison-chart-wrap">
<canvas id="comparisonChartCanvas"></canvas>
</div>
<div class="adjustment-tools-grid">
<div id="indonesiaAdjustmentCard" class="adjustment-tool-card" data-model-name="admiralty_indonesia">
<div class="card-header bg-light"><div class="adjustment-tool-title"><strong>Adjustment Admiralty Indonesia</strong><span class="adjustment-tool-status" id="indonesiaAdjustmentStatus">Aktif</span></div></div>
<div class="card-body">
<div class="adjustment-tool-controls">
<div class="form-group"><label for="indonesiaAmplitudeAdjust">Amplitude</label><input type="range" min="-100" max="100" step="1" value="0" id="indonesiaAmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Global amplitudo: <span id="indonesiaAmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="indonesiaPhaseAdjust">Phase</label><input type="range" min="-180" max="180" step="1" value="0" id="indonesiaPhaseAdjust" class="custom-range"><small class="form-text text-muted">Global fase: <span id="indonesiaPhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label for="indonesiaP1AmplitudeAdjust">P1(A)</label><input type="range" min="-100" max="100" step="1" value="0" id="indonesiaP1AmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 amplitudo: <span id="indonesiaP1AmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="indonesiaP1PhaseAdjust">P1(g)</label><input type="range" min="-180" max="180" step="1" value="0" id="indonesiaP1PhaseAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 fase: <span id="indonesiaP1PhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label>Adjust Waktu</label><div class="adjustment-time-box"><button type="button" class="btn btn-outline-secondary btn-sm" id="indonesiaTimeShiftMinus">-</button><span id="indonesiaTimeShiftValue" class="adjustment-time-value">0 jam</span><button type="button" class="btn btn-outline-secondary btn-sm" id="indonesiaTimeShiftPlus">+</button></div><small class="form-text text-muted">Setiap klik menggeser 1 jam.</small></div>
<div class="form-group"><label>&nbsp;</label><button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="resetIndonesiaAdjustmentButton">Reset Tools</button><small class="form-text text-muted">Kembalikan semua adjustment ke nilai awal.</small></div>
</div>
<div class="adjustment-tool-help">Kurva model akan berubah live di grafik perbandingan saat tool ini aktif.</div>
</div>
</div>
<div id="hidrosAdjustmentCard" class="adjustment-tool-card" data-model-name="admiralty_hidros">
<div class="card-header bg-light"><div class="adjustment-tool-title"><strong>Adjustment Admiralty Hidros</strong><span class="adjustment-tool-status" id="hidrosAdjustmentStatus">Aktif</span></div></div>
<div class="card-body">
<div class="adjustment-tool-controls">
<div class="form-group"><label for="hidrosAmplitudeAdjust">Amplitude</label><input type="range" min="-100" max="100" step="1" value="0" id="hidrosAmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Global amplitudo: <span id="hidrosAmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="hidrosPhaseAdjust">Phase</label><input type="range" min="-180" max="180" step="1" value="0" id="hidrosPhaseAdjust" class="custom-range"><small class="form-text text-muted">Global fase: <span id="hidrosPhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label for="hidrosP1AmplitudeAdjust">P1(A)</label><input type="range" min="-100" max="100" step="1" value="0" id="hidrosP1AmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 amplitudo: <span id="hidrosP1AmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="hidrosP1PhaseAdjust">P1(g)</label><input type="range" min="-180" max="180" step="1" value="0" id="hidrosP1PhaseAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 fase: <span id="hidrosP1PhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label>Adjust Waktu</label><div class="adjustment-time-box"><button type="button" class="btn btn-outline-secondary btn-sm" id="hidrosTimeShiftMinus">-</button><span id="hidrosTimeShiftValue" class="adjustment-time-value">0 jam</span><button type="button" class="btn btn-outline-secondary btn-sm" id="hidrosTimeShiftPlus">+</button></div><small class="form-text text-muted">Setiap klik menggeser 1 jam.</small></div>
<div class="form-group"><label>&nbsp;</label><button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="resetHidrosAdjustmentButton">Reset Tools</button><small class="form-text text-muted">Kembalikan semua adjustment ke nilai awal.</small></div>
</div>
<div class="adjustment-tool-help">Kurva model akan berubah live di grafik perbandingan saat tool ini aktif.</div>
</div>
</div>
<div id="catAAdjustmentCard" class="adjustment-tool-card" data-model-name="admiralty_cat_a">
<div class="card-header bg-light"><div class="adjustment-tool-title"><strong>Adjustment Admiralty Cat A</strong><span class="adjustment-tool-status" id="catAAdjustmentStatus">Aktif</span></div></div>
<div class="card-body">
<div class="adjustment-tool-controls">
<div class="form-group"><label for="catAAmplitudeAdjust">Amplitude</label><input type="range" min="-100" max="100" step="1" value="0" id="catAAmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Global amplitudo: <span id="catAAmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="catAPhaseAdjust">Phase</label><input type="range" min="-180" max="180" step="1" value="0" id="catAPhaseAdjust" class="custom-range"><small class="form-text text-muted">Global fase: <span id="catAPhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label for="catAP1AmplitudeAdjust">P1(A)</label><input type="range" min="-100" max="100" step="1" value="0" id="catAP1AmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 amplitudo: <span id="catAP1AmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="catAP1PhaseAdjust">P1(g)</label><input type="range" min="-180" max="180" step="1" value="0" id="catAP1PhaseAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 fase: <span id="catAP1PhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label>Adjust Waktu</label><div class="adjustment-time-box"><button type="button" class="btn btn-outline-secondary btn-sm" id="catATimeShiftMinus">-</button><span id="catATimeShiftValue" class="adjustment-time-value">0 jam</span><button type="button" class="btn btn-outline-secondary btn-sm" id="catATimeShiftPlus">+</button></div><small class="form-text text-muted">Setiap klik menggeser 1 jam.</small></div>
<div class="form-group"><label>&nbsp;</label><button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="resetCatAAdjustmentButton">Reset Tools</button><small class="form-text text-muted">Kembalikan semua adjustment ke nilai awal.</small></div>
</div>
<div class="adjustment-tool-help">Kurva model akan berubah live di grafik perbandingan saat tool ini aktif.</div>
</div>
</div>
<div id="leastSquareAdjustmentCard" class="adjustment-tool-card" data-model-name="least_square">
<div class="card-header bg-light"><div class="adjustment-tool-title"><strong>Adjustment Least Square</strong><span class="adjustment-tool-status" id="leastSquareAdjustmentStatus">Aktif</span></div></div>
<div class="card-body">
<div class="adjustment-tool-controls">
<div class="form-group"><label for="leastSquareAmplitudeAdjust">Amplitude</label><input type="range" min="-100" max="100" step="1" value="0" id="leastSquareAmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Global amplitudo: <span id="leastSquareAmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="leastSquarePhaseAdjust">Phase</label><input type="range" min="-180" max="180" step="1" value="0" id="leastSquarePhaseAdjust" class="custom-range"><small class="form-text text-muted">Global fase: <span id="leastSquarePhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label for="leastSquareP1AmplitudeAdjust">P1(A)</label><input type="range" min="-100" max="100" step="1" value="0" id="leastSquareP1AmplitudeAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 amplitudo: <span id="leastSquareP1AmplitudeAdjustValue">0%</span></small></div>
<div class="form-group"><label for="leastSquareP1PhaseAdjust">P1(g)</label><input type="range" min="-180" max="180" step="1" value="0" id="leastSquareP1PhaseAdjust" class="custom-range"><small class="form-text text-muted">Adjust P1 fase: <span id="leastSquareP1PhaseAdjustValue">0&deg;</span></small></div>
<div class="form-group"><label>Adjust Waktu</label><div class="adjustment-time-box"><button type="button" class="btn btn-outline-secondary btn-sm" id="leastSquareTimeShiftMinus">-</button><span id="leastSquareTimeShiftValue" class="adjustment-time-value">0 jam</span><button type="button" class="btn btn-outline-secondary btn-sm" id="leastSquareTimeShiftPlus">+</button></div><small class="form-text text-muted">Setiap klik menggeser 1 jam.</small></div>
<div class="form-group"><label>&nbsp;</label><button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="resetLeastSquareAdjustmentButton">Reset Tools</button><small class="form-text text-muted">Kembalikan semua adjustment ke nilai awal.</small></div>
</div>
<div class="adjustment-tool-help">Kurva model akan berubah live di grafik perbandingan saat tool ini aktif.</div>
</div>
</div>
</div>
</div>
</div>
<div class="table-responsive mb-3">
<table class="table table-bordered table-sm comparison-table mb-0">
<thead><tr><th>Model</th><th>Komponen</th><th>Amplitudo Awal</th><th>Amplitudo Adjusted</th><th>Fase Awal</th><th>Fase Adjusted</th><th>Shift Waktu</th></tr></thead>
<tbody id="adjustedConstantsBody"><tr><td colspan="7" class="text-center text-muted">Adjustment konstanta harmonik akan muncul di sini.</td></tr></tbody>
</table>
</div>
<div class="table-responsive mb-3">
<table class="table table-bordered table-sm comparison-table mb-0">
<thead><tr><th>Evaluasi</th><th>Mode</th><th>Status</th><th>Titik</th><th>RMSE</th><th>MAE</th><th>Bias</th><th>Error Puncak</th><th>Error Surut</th></tr></thead>
<tbody id="comparisonEvaluationBody"><tr><td colspan="9" class="text-center text-muted">Evaluasi akan muncul setelah grafik tersedia.</td></tr></tbody>
</table>
</div>
<div id="phaseAlignmentCard" class="alert alert-light border mb-3" style="display:none;">
<strong>Phase Alignment</strong>
<div id="phaseAlignmentBody" class="small text-muted mt-2">Belum ada phase alignment.</div>
</div>
<div class="table-responsive mb-3">
<table class="table table-striped table-sm comparison-table mb-0">
<thead><tr><th>Model</th><th>Run Code</th><th>MSL</th><th>Min</th><th>Max</th><th>Range</th><th>Durasi</th><th>Data</th><th>Zona Waktu</th></tr></thead>
<tbody id="comparisonSummaryBody"><tr><td colspan="9" class="text-center text-muted">Belum ada hasil untuk dibandingkan.</td></tr></tbody>
</table>
</div>
<div class="table-responsive">
<table class="table table-bordered table-sm comparison-table mb-0">
<thead><tr id="comparisonComponentHead"><th>Komponen</th><th>Kelompok</th></tr></thead>
<tbody id="comparisonComponentBody"><tr><td colspan="2" class="text-center text-muted">Jalankan minimal satu model untuk melihat status komponen.</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>
<div id="predictionArea" style="display:none;">
<div class="card stage-card mt-4">
<div class="card-header"><h3 class="card-title"><i class="fas fa-water mr-2"></i>Generate Prediksi Pasang Surut</h3></div>
<div class="card-body">
<div class="alert alert-info">
Prediksi saat ini aktif untuk model <strong>Least Square</strong>, <strong>Admiralty Cat A</strong>, dan <strong>Admiralty Hidro-Oseanografi Indonesia</strong>. Model <strong>Admiralty Hidros</strong> baru mendukung perhitungan harmonik dan audit workbook, belum masuk modul prediksi.
</div>
<div class="row">
<div class="col-lg-3"><div class="form-group"><label for="predictionRunSelect">Run Prediksi</label><select id="predictionRunSelect" class="form-control"><option value="">Pilih run</option></select></div></div>
<div class="col-lg-3"><div class="form-group"><label for="predictionStart">Mulai</label><input type="datetime-local" id="predictionStart" class="form-control"></div></div>
<div class="col-lg-3"><div class="form-group"><label for="predictionEnd">Akhir</label><input type="datetime-local" id="predictionEnd" class="form-control"></div></div>
<div class="col-lg-2"><div class="form-group"><label for="predictionInterval">Interval</label><select id="predictionInterval" class="form-control"><option value="10">10 menit</option><option value="15">15 menit</option><option value="30">30 menit</option><option value="60" selected>60 menit</option></select></div></div>
<div class="col-lg-1 d-flex align-items-end"><button type="button" class="btn btn-primary btn-block" id="generatePredictionButton">Generate</button></div>
</div>
<div class="calc-grid mb-3">
<div class="calc-item"><strong>Run Aktif</strong><span id="predictionRunCode">-</span></div>
<div class="calc-item"><strong>Model</strong><span id="predictionModel">-</span></div>
<div class="calc-item"><strong>Stasiun</strong><span id="predictionStation">-</span></div>
<div class="calc-item"><strong>Jumlah Titik</strong><span id="predictionCount">-</span></div>
</div>
<div class="table-responsive prediction-table">
<table class="table table-striped table-sm mb-0">
<thead><tr><th>Tanggal/Jam</th><th>Prediksi Water Level</th></tr></thead>
<tbody id="predictionTableBody"><tr><td colspan="2" class="text-center text-muted">Belum ada prediksi.</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</section></div>
<aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function(){
const initialValidatedResult=<?= json_encode($initialValidatedResult ?? null, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
const initialDatasetMeta=<?= json_encode($initialDatasetMeta ?? null, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
const formDraftStorageKey='admiralty_form_draft_v1';
const form=document.getElementById('uploadForm');
const fileField=document.getElementById('tide_file');
const stationNameField=document.getElementById('station_name');
const latitudeField=document.getElementById('latitude');
const longitudeField=document.getElementById('longitude');
const timezoneField=document.getElementById('timezone');
const modelCheckboxes=Array.from(document.querySelectorAll('.model-checkbox'));
const validateButton=document.getElementById('validateButton');
const resultArea=document.getElementById('resultArea');
const calculationArea=document.getElementById('calculationArea');
const calculationTables=document.getElementById('calculationTables');
const multiModelResults=document.getElementById('multiModelResults');
const comparisonArea=document.getElementById('comparisonArea');
const saveDatasetButton=document.getElementById('saveDatasetButton');
const startCalculationButton=document.getElementById('startCalculationButton');
const calculationEligibility=document.getElementById('calculationEligibility');
const validationCardBody=document.getElementById('validationCardBody');
const validationCardToggle=document.getElementById('validationCardToggle');
const predictionArea=document.getElementById('predictionArea');
const predictionRunSelect=document.getElementById('predictionRunSelect');
const predictionStart=document.getElementById('predictionStart');
const predictionEnd=document.getElementById('predictionEnd');
const predictionInterval=document.getElementById('predictionInterval');
const generatePredictionButton=document.getElementById('generatePredictionButton');
const modelSelectionHint=document.getElementById('modelSelectionHint');
const exportWorkbookPdfButton=document.getElementById('exportWorkbookPdfButton');
const exportComparisonCsvButton=document.getElementById('exportComparisonCsvButton');
const indonesiaAdjustmentCard=document.getElementById('indonesiaAdjustmentCard');
const indonesiaAmplitudeAdjust=document.getElementById('indonesiaAmplitudeAdjust');
const indonesiaAmplitudeAdjustValue=document.getElementById('indonesiaAmplitudeAdjustValue');
const indonesiaPhaseAdjust=document.getElementById('indonesiaPhaseAdjust');
const indonesiaPhaseAdjustValue=document.getElementById('indonesiaPhaseAdjustValue');
const indonesiaP1AmplitudeAdjust=document.getElementById('indonesiaP1AmplitudeAdjust');
const indonesiaP1AmplitudeAdjustValue=document.getElementById('indonesiaP1AmplitudeAdjustValue');
const indonesiaP1PhaseAdjust=document.getElementById('indonesiaP1PhaseAdjust');
const indonesiaP1PhaseAdjustValue=document.getElementById('indonesiaP1PhaseAdjustValue');
const indonesiaTimeShiftMinus=document.getElementById('indonesiaTimeShiftMinus');
const indonesiaTimeShiftPlus=document.getElementById('indonesiaTimeShiftPlus');
const indonesiaTimeShiftValue=document.getElementById('indonesiaTimeShiftValue');
const resetIndonesiaAdjustmentButton=document.getElementById('resetIndonesiaAdjustmentButton');
const hidrosAdjustmentCard=document.getElementById('hidrosAdjustmentCard');
const hidrosAmplitudeAdjust=document.getElementById('hidrosAmplitudeAdjust');
const hidrosAmplitudeAdjustValue=document.getElementById('hidrosAmplitudeAdjustValue');
const hidrosPhaseAdjust=document.getElementById('hidrosPhaseAdjust');
const hidrosPhaseAdjustValue=document.getElementById('hidrosPhaseAdjustValue');
const hidrosP1AmplitudeAdjust=document.getElementById('hidrosP1AmplitudeAdjust');
const hidrosP1AmplitudeAdjustValue=document.getElementById('hidrosP1AmplitudeAdjustValue');
const hidrosP1PhaseAdjust=document.getElementById('hidrosP1PhaseAdjust');
const hidrosP1PhaseAdjustValue=document.getElementById('hidrosP1PhaseAdjustValue');
const hidrosTimeShiftMinus=document.getElementById('hidrosTimeShiftMinus');
const hidrosTimeShiftPlus=document.getElementById('hidrosTimeShiftPlus');
const hidrosTimeShiftValue=document.getElementById('hidrosTimeShiftValue');
const resetHidrosAdjustmentButton=document.getElementById('resetHidrosAdjustmentButton');
const catAAdjustmentCard=document.getElementById('catAAdjustmentCard');
const catAAdjustmentStatus=document.getElementById('catAAdjustmentStatus');
const catAAmplitudeAdjust=document.getElementById('catAAmplitudeAdjust');
const catAAmplitudeAdjustValue=document.getElementById('catAAmplitudeAdjustValue');
const catAPhaseAdjust=document.getElementById('catAPhaseAdjust');
const catAPhaseAdjustValue=document.getElementById('catAPhaseAdjustValue');
const catAP1AmplitudeAdjust=document.getElementById('catAP1AmplitudeAdjust');
const catAP1AmplitudeAdjustValue=document.getElementById('catAP1AmplitudeAdjustValue');
const catAP1PhaseAdjust=document.getElementById('catAP1PhaseAdjust');
const catAP1PhaseAdjustValue=document.getElementById('catAP1PhaseAdjustValue');
const catATimeShiftMinus=document.getElementById('catATimeShiftMinus');
const catATimeShiftPlus=document.getElementById('catATimeShiftPlus');
const catATimeShiftValue=document.getElementById('catATimeShiftValue');
const resetCatAAdjustmentButton=document.getElementById('resetCatAAdjustmentButton');
const indonesiaAdjustmentStatus=document.getElementById('indonesiaAdjustmentStatus');
const hidrosAdjustmentStatus=document.getElementById('hidrosAdjustmentStatus');
const leastSquareAdjustmentCard=document.getElementById('leastSquareAdjustmentCard');
const leastSquareAdjustmentStatus=document.getElementById('leastSquareAdjustmentStatus');
const leastSquareAmplitudeAdjust=document.getElementById('leastSquareAmplitudeAdjust');
const leastSquareAmplitudeAdjustValue=document.getElementById('leastSquareAmplitudeAdjustValue');
const leastSquarePhaseAdjust=document.getElementById('leastSquarePhaseAdjust');
const leastSquarePhaseAdjustValue=document.getElementById('leastSquarePhaseAdjustValue');
const leastSquareP1AmplitudeAdjust=document.getElementById('leastSquareP1AmplitudeAdjust');
const leastSquareP1AmplitudeAdjustValue=document.getElementById('leastSquareP1AmplitudeAdjustValue');
const leastSquareP1PhaseAdjust=document.getElementById('leastSquareP1PhaseAdjust');
const leastSquareP1PhaseAdjustValue=document.getElementById('leastSquareP1PhaseAdjustValue');
const leastSquareTimeShiftMinus=document.getElementById('leastSquareTimeShiftMinus');
const leastSquareTimeShiftPlus=document.getElementById('leastSquareTimeShiftPlus');
const leastSquareTimeShiftValue=document.getElementById('leastSquareTimeShiftValue');
const resetLeastSquareAdjustmentButton=document.getElementById('resetLeastSquareAdjustmentButton');
let comparisonChartInstance=null;
let latestComparisonChartExport=null;
let comparisonChartVisibility={};
let latestIndonesiaAdjustmentBasis=null;
let latestHidrosAdjustmentBasis=null;
let latestCatAAdjustmentBasis=null;
let latestLeastSquareAdjustmentBasis=null;
let indonesiaAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
let hidrosAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
let catAAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
let leastSquareAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
const ids={
summaryInterval:'summaryInterval',summaryCount:'summaryCount',summaryGap:'summaryGap',summaryDuplicates:'summaryDuplicates',
metricStart:'metricStart',metricEnd:'metricEnd',metricMinStatus:'metricMinStatus',metricMaxStatus:'metricMaxStatus',
metricQualityStatus:'metricQualityStatus',
metricValidRows:'metricValidRows',metricInvalidRows:'metricInvalidRows',metricGapCount:'metricGapCount',
previewBody:'previewBody',statusList:'statusList',gapList:'gapList',invalidList:'invalidList',
calcDatasetStatus:'calcDatasetStatus',calcInterval:'calcInterval',calcCount:'calcCount',calcRange:'calcRange',calculationIntro:'calculationIntro',workingTableTitle:'workingTableTitle',workingTableHead:'workingTableHead',
workingTableBody:'workingTableBody',componentDatasetStatus:'componentDatasetStatus',componentMsl:'componentMsl',componentModelLabel:'componentModelLabel',componentRunCode:'componentRunCode',componentTableBody:'componentTableBody',calculationNotes:'calculationNotes',qualityReasons:'qualityReasons',
comparisonSummaryBody:'comparisonSummaryBody',comparisonComponentHead:'comparisonComponentHead',comparisonComponentBody:'comparisonComponentBody',
comparisonEvaluationBody:'comparisonEvaluationBody',phaseAlignmentBody:'phaseAlignmentBody',adjustedConstantsBody:'adjustedConstantsBody'
};
let latestValidatedResult=null;
let latestDatasetMeta=null;
let isValidationCardMinimized=false;
let latestCalculationResults=[];
function el(id){return document.getElementById(ids[id]||id)}
function esc(value){return String(value??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;')}
function pill(label,type){return '<span class="status-pill '+type+'">'+label+'</span>'}
function setHtml(id,html){el(id).innerHTML=html}
function setText(id,text){el(id).textContent=String(text)}
function num(value,defaultValue){
const parsed=Number(value);
return Number.isFinite(parsed)?parsed:defaultValue;
}
function syncIndonesiaAdjustmentUi(){
if(indonesiaAmplitudeAdjustValue){indonesiaAmplitudeAdjustValue.textContent=(indonesiaAdjustmentState.amplitudePercent>0?'+':'')+indonesiaAdjustmentState.amplitudePercent+'%';}
if(indonesiaPhaseAdjustValue){indonesiaPhaseAdjustValue.textContent=(indonesiaAdjustmentState.phaseDegrees>0?'+':'')+indonesiaAdjustmentState.phaseDegrees+'°';}
if(indonesiaP1AmplitudeAdjustValue){indonesiaP1AmplitudeAdjustValue.textContent=(indonesiaAdjustmentState.p1AmplitudePercent>0?'+':'')+indonesiaAdjustmentState.p1AmplitudePercent+'%';}
if(indonesiaP1PhaseAdjustValue){indonesiaP1PhaseAdjustValue.textContent=(indonesiaAdjustmentState.p1PhaseDegrees>0?'+':'')+indonesiaAdjustmentState.p1PhaseDegrees+'°';}
if(indonesiaTimeShiftValue){indonesiaTimeShiftValue.textContent=(indonesiaAdjustmentState.timeShiftHours>0?'+':'')+indonesiaAdjustmentState.timeShiftHours+' jam';}
}
function syncHidrosAdjustmentUi(){
if(hidrosAmplitudeAdjustValue){hidrosAmplitudeAdjustValue.textContent=(hidrosAdjustmentState.amplitudePercent>0?'+':'')+hidrosAdjustmentState.amplitudePercent+'%';}
if(hidrosPhaseAdjustValue){hidrosPhaseAdjustValue.textContent=(hidrosAdjustmentState.phaseDegrees>0?'+':'')+hidrosAdjustmentState.phaseDegrees+'°';}
if(hidrosP1AmplitudeAdjustValue){hidrosP1AmplitudeAdjustValue.textContent=(hidrosAdjustmentState.p1AmplitudePercent>0?'+':'')+hidrosAdjustmentState.p1AmplitudePercent+'%';}
if(hidrosP1PhaseAdjustValue){hidrosP1PhaseAdjustValue.textContent=(hidrosAdjustmentState.p1PhaseDegrees>0?'+':'')+hidrosAdjustmentState.p1PhaseDegrees+'°';}
if(hidrosTimeShiftValue){hidrosTimeShiftValue.textContent=(hidrosAdjustmentState.timeShiftHours>0?'+':'')+hidrosAdjustmentState.timeShiftHours+' jam';}
}
function resetIndonesiaAdjustmentState(){
indonesiaAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
if(indonesiaAmplitudeAdjust){indonesiaAmplitudeAdjust.value='0';}
if(indonesiaPhaseAdjust){indonesiaPhaseAdjust.value='0';}
if(indonesiaP1AmplitudeAdjust){indonesiaP1AmplitudeAdjust.value='0';}
if(indonesiaP1PhaseAdjust){indonesiaP1PhaseAdjust.value='0';}
syncIndonesiaAdjustmentUi();
}
function resetHidrosAdjustmentState(){
hidrosAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
if(hidrosAmplitudeAdjust){hidrosAmplitudeAdjust.value='0';}
if(hidrosPhaseAdjust){hidrosPhaseAdjust.value='0';}
if(hidrosP1AmplitudeAdjust){hidrosP1AmplitudeAdjust.value='0';}
if(hidrosP1PhaseAdjust){hidrosP1PhaseAdjust.value='0';}
syncHidrosAdjustmentUi();
}
function syncCatAAdjustmentUi(){
if(catAAmplitudeAdjustValue){catAAmplitudeAdjustValue.textContent=(catAAdjustmentState.amplitudePercent>0?'+':'')+catAAdjustmentState.amplitudePercent+'%';}
if(catAPhaseAdjustValue){catAPhaseAdjustValue.textContent=(catAAdjustmentState.phaseDegrees>0?'+':'')+catAAdjustmentState.phaseDegrees+'°';}
if(catAP1AmplitudeAdjustValue){catAP1AmplitudeAdjustValue.textContent=(catAAdjustmentState.p1AmplitudePercent>0?'+':'')+catAAdjustmentState.p1AmplitudePercent+'%';}
if(catAP1PhaseAdjustValue){catAP1PhaseAdjustValue.textContent=(catAAdjustmentState.p1PhaseDegrees>0?'+':'')+catAAdjustmentState.p1PhaseDegrees+'°';}
if(catATimeShiftValue){catATimeShiftValue.textContent=(catAAdjustmentState.timeShiftHours>0?'+':'')+catAAdjustmentState.timeShiftHours+' jam';}
}
function resetCatAAdjustmentState(){
catAAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
if(catAAmplitudeAdjust){catAAmplitudeAdjust.value='0';}
if(catAPhaseAdjust){catAPhaseAdjust.value='0';}
if(catAP1AmplitudeAdjust){catAP1AmplitudeAdjust.value='0';}
if(catAP1PhaseAdjust){catAP1PhaseAdjust.value='0';}
syncCatAAdjustmentUi();
}
function syncLeastSquareAdjustmentUi(){
if(leastSquareAmplitudeAdjustValue){leastSquareAmplitudeAdjustValue.textContent=(leastSquareAdjustmentState.amplitudePercent>0?'+':'')+leastSquareAdjustmentState.amplitudePercent+'%';}
if(leastSquarePhaseAdjustValue){leastSquarePhaseAdjustValue.textContent=(leastSquareAdjustmentState.phaseDegrees>0?'+':'')+leastSquareAdjustmentState.phaseDegrees+'\u00B0';}
if(leastSquareP1AmplitudeAdjustValue){leastSquareP1AmplitudeAdjustValue.textContent=(leastSquareAdjustmentState.p1AmplitudePercent>0?'+':'')+leastSquareAdjustmentState.p1AmplitudePercent+'%';}
if(leastSquareP1PhaseAdjustValue){leastSquareP1PhaseAdjustValue.textContent=(leastSquareAdjustmentState.p1PhaseDegrees>0?'+':'')+leastSquareAdjustmentState.p1PhaseDegrees+'\u00B0';}
if(leastSquareTimeShiftValue){leastSquareTimeShiftValue.textContent=(leastSquareAdjustmentState.timeShiftHours>0?'+':'')+leastSquareAdjustmentState.timeShiftHours+' jam';}
}
function resetLeastSquareAdjustmentState(){
leastSquareAdjustmentState={amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
if(leastSquareAmplitudeAdjust){leastSquareAmplitudeAdjust.value='0';}
if(leastSquarePhaseAdjust){leastSquarePhaseAdjust.value='0';}
if(leastSquareP1AmplitudeAdjust){leastSquareP1AmplitudeAdjust.value='0';}
if(leastSquareP1PhaseAdjust){leastSquareP1PhaseAdjust.value='0';}
syncLeastSquareAdjustmentUi();
}
function buildAdjustedSeriesFromBasis(basis,state){
if(!basis||!Array.isArray(basis.rows)||!Array.isArray(basis.components)){return null;}
const offset=num(basis.offset,0);
const basePhaseOffset=num(basis.phase_offset_deg,0);
const amplitudeScale=1+(state.amplitudePercent/100);
const p1AmplitudeScale=1+(state.p1AmplitudePercent/100);
return basis.rows.map(function(row){
const timeHours=num(row.time_hours,0)-state.timeShiftHours;
let predicted=offset;
basis.components.forEach(function(component){
const periodHours=num(component.period_hours,0);
const amplitude=num(component.amplitude,0);
if(periodHours<=0||!amplitude){return;}
let phaseDegrees=num(component.phase,0)+basePhaseOffset+state.phaseDegrees;
let localAmplitude=amplitude*amplitudeScale;
if(String(component.name||'').toUpperCase()==='P1'){
phaseDegrees+=state.p1PhaseDegrees;
localAmplitude*=p1AmplitudeScale;
}
const omega=(2*Math.PI)/periodHours;
predicted+=localAmplitude*Math.cos((omega*timeHours)-((phaseDegrees*Math.PI)/180));
});
return Number(predicted.toFixed(4));
});
}
function buildAdjustedIndonesiaSeries(){
return buildAdjustedSeriesFromBasis(latestIndonesiaAdjustmentBasis,indonesiaAdjustmentState);
}
function buildAdjustedHidrosSeries(){
return buildAdjustedSeriesFromBasis(latestHidrosAdjustmentBasis,hidrosAdjustmentState);
}
function buildAdjustedCatASeries(){
return buildAdjustedSeriesFromBasis(latestCatAAdjustmentBasis,catAAdjustmentState);
}
function buildAdjustedLeastSquareSeries(){
return buildAdjustedSeriesFromBasis(latestLeastSquareAdjustmentBasis,leastSquareAdjustmentState);
}
function renderAdjustedConstantsTable(){
const rows=[];
function pushRows(modelLabel,basis,state){
if(!basis||!Array.isArray(basis.components)||!basis.components.length){return;}
const globalAmpScale=1+(state.amplitudePercent/100);
const p1AmpScale=1+(state.p1AmplitudePercent/100);
basis.components.forEach(function(component){
const originalAmplitude=num(component.amplitude,0);
const originalPhase=num(component.phase,0)+num(basis.phase_offset_deg,0);
const isP1=String(component.name||'').toUpperCase()==='P1';
const adjustedAmplitude=originalAmplitude*globalAmpScale*(isP1?p1AmpScale:1);
const adjustedPhase=originalPhase+state.phaseDegrees+(isP1?state.p1PhaseDegrees:0);
rows.push('<tr><td>'+esc(modelLabel)+'</td><td>'+esc(component.name||'-')+'</td><td>'+esc(originalAmplitude.toFixed(4))+'</td><td>'+esc(adjustedAmplitude.toFixed(4))+'</td><td>'+esc(originalPhase.toFixed(2))+'</td><td>'+esc(adjustedPhase.toFixed(2))+'</td><td>'+esc((state.timeShiftHours>0?'+':'')+state.timeShiftHours)+' jam</td></tr>');
});
}
pushRows('Admiralty Indonesia',latestIndonesiaAdjustmentBasis,indonesiaAdjustmentState);
pushRows('Admiralty Hidros',latestHidrosAdjustmentBasis,hidrosAdjustmentState);
pushRows('Admiralty Cat A',latestCatAAdjustmentBasis,catAAdjustmentState);
pushRows('Least Square',latestLeastSquareAdjustmentBasis,leastSquareAdjustmentState);
setHtml('adjustedConstantsBody',rows.length?rows.join(''):'<tr><td colspan="7" class="text-center text-muted">Adjustment konstanta harmonik akan muncul di sini.</td></tr>');
}
function setValidationCardMinimized(minimized){
isValidationCardMinimized=!!minimized;
validationCardBody.style.display=isValidationCardMinimized?'none':'block';
validationCardToggle.innerHTML=isValidationCardMinimized?'<i class="fas fa-plus"></i>':'<i class="fas fa-minus"></i>';
validationCardToggle.setAttribute('aria-label',isValidationCardMinimized?'Buka upload dan validasi':'Minimize upload dan validasi');
}
function saveFormDraft(){
const payload={
station_name:stationNameField.value||'',
latitude:latitudeField.value||'',
longitude:longitudeField.value||'',
timezone:timezoneField.value||'Asia/Jakarta'
};
try{
window.localStorage.setItem(formDraftStorageKey,JSON.stringify(payload));
}catch(error){}
}
function restoreFormDraft(){
try{
const raw=window.localStorage.getItem(formDraftStorageKey);
if(!raw)return;
const payload=JSON.parse(raw);
if(payload&&typeof payload==='object'){
if(!stationNameField.value&&payload.station_name)stationNameField.value=String(payload.station_name);
if(!latitudeField.value&&payload.latitude)latitudeField.value=String(payload.latitude);
if(!longitudeField.value&&payload.longitude)longitudeField.value=String(payload.longitude);
if(payload.timezone)timezoneField.value=String(payload.timezone);
}
}catch(error){}
}
function isModelSelected(modelName){
return modelCheckboxes.some(function(input){
return input.checked&&String(input.value||'')===String(modelName||'');
});
}
function toggleAdjustmentCard(card,enabled,statusEl){
if(!card){return;}
const disabled=!enabled;
card.classList.toggle('is-disabled',disabled);
card.querySelectorAll('input,button').forEach(function(control){
control.disabled=disabled;
});
if(statusEl){
statusEl.textContent=enabled?'Aktif':'Nonaktif';
statusEl.classList.toggle('is-disabled',disabled);
}
}
function syncAdjustmentCardAvailability(){
toggleAdjustmentCard(indonesiaAdjustmentCard,isModelSelected('admiralty_indonesia')&&!!latestIndonesiaAdjustmentBasis,indonesiaAdjustmentStatus);
toggleAdjustmentCard(hidrosAdjustmentCard,isModelSelected('admiralty_hidros')&&!!latestHidrosAdjustmentBasis,hidrosAdjustmentStatus);
toggleAdjustmentCard(catAAdjustmentCard,isModelSelected('admiralty_cat_a')&&!!latestCatAAdjustmentBasis,catAAdjustmentStatus);
toggleAdjustmentCard(leastSquareAdjustmentCard,isModelSelected('least_square')&&!!latestLeastSquareAdjustmentBasis,leastSquareAdjustmentStatus);
}
function updateStartCalculationState(){
const hasDataset=latestDatasetMeta!==null;
const selectedModels=getSelectedModels();
const canStart=hasDataset&&selectedModels.length>0;
startCalculationButton.disabled=!canStart;
syncAdjustmentCardAvailability();

if(!hasDataset){
modelSelectionHint.textContent='Simpan dataset terlebih dahulu, lalu pilih minimal satu model perhitungan.';
return;
}

if(selectedModels.length===0){
modelSelectionHint.textContent='Dataset sudah tersimpan. Pilih minimal satu model perhitungan untuk mengaktifkan tombol hitung.';
return;
}

modelSelectionHint.textContent='Model siap dijalankan. Klik Mulai Hitung Admiralty untuk memproses model terpilih.';
}
function resetCalculationPanels(){
latestValidatedResult=null;
latestDatasetMeta=null;
latestCalculationResults=[];
if(exportWorkbookPdfButton){exportWorkbookPdfButton.disabled=true;}
if(exportComparisonCsvButton){exportComparisonCsvButton.disabled=true;}
latestComparisonChartExport=null;
comparisonChartVisibility={};
latestIndonesiaAdjustmentBasis=null;
latestHidrosAdjustmentBasis=null;
latestCatAAdjustmentBasis=null;
latestLeastSquareAdjustmentBasis=null;
resetIndonesiaAdjustmentState();
resetHidrosAdjustmentState();
resetCatAAdjustmentState();
resetLeastSquareAdjustmentState();
saveDatasetButton.disabled=true;
startCalculationButton.disabled=true;
calculationEligibility.textContent='Lakukan validasi data terlebih dahulu.';
setText('summaryInterval','-');
setText('summaryCount','0');
setText('summaryGap','Tidak');
setText('summaryDuplicates','0');
setText('metricStart','-');
setText('metricEnd','-');
setHtml('metricMinStatus','-');
setHtml('metricMaxStatus','-');
setHtml('metricQualityStatus','-');
setText('metricValidRows','-');
setText('metricInvalidRows','-');
setText('metricGapCount','-');
setHtml('qualityReasons','Status kualitas dataset akan muncul di sini.');
modelCheckboxes.forEach(function(input){input.checked=false;});
calculationArea.style.display='none';
calculationTables.style.display='none';
setText('workingTableTitle','Tabel Kerja Admiralty');
setHtml('workingTableHead','<th>No</th><th>Tanggal/Jam</th><th>Hari</th><th>Jam</th><th>Elevasi</th><th>Deviasi MSL</th>');
setHtml('workingTableBody','<tr><td colspan="6" class="text-center text-muted">Tabel kerja belum dibuat.</td></tr>');
setHtml('componentTableBody','<tr><td colspan="5" class="text-center text-muted">Komponen belum disiapkan.</td></tr>');
setText('componentDatasetStatus','-');
setText('componentMsl','-');
setText('componentModelLabel','-');
setText('componentRunCode','-');
setText('calculationNotes','Catatan tahap perhitungan akan muncul di sini.');
multiModelResults.style.display='none';
multiModelResults.innerHTML='';
comparisonArea.style.display='none';
setHtml('comparisonSummaryBody','<tr><td colspan="9" class="text-center text-muted">Belum ada hasil untuk dibandingkan.</td></tr>');
setHtml('comparisonComponentHead','<th>Komponen</th><th>Kelompok</th>');
setHtml('comparisonComponentBody','<tr><td colspan="2" class="text-center text-muted">Jalankan minimal satu model untuk melihat status komponen.</td></tr>');
setText('comparisonChartStatus','Belum ada data grafik.');
setText('comparisonChartPoints','-');
setText('comparisonChartSeries','-');
if(indonesiaAdjustmentCard){indonesiaAdjustmentCard.style.display='none';}
if(hidrosAdjustmentCard){hidrosAdjustmentCard.style.display='none';}
if(catAAdjustmentCard){catAAdjustmentCard.style.display='none';}
if(leastSquareAdjustmentCard){leastSquareAdjustmentCard.style.display='none';}
setHtml('adjustedConstantsBody','<tr><td colspan="7" class="text-center text-muted">Adjustment konstanta harmonik akan muncul di sini.</td></tr>');
if(comparisonChartInstance){comparisonChartInstance.destroy();comparisonChartInstance=null;}
predictionArea.style.display='none';
predictionRunSelect.innerHTML='<option value="">Pilih run</option>';
predictionStart.value='';
predictionEnd.value='';
setText('predictionRunCode','-');
setText('predictionModel','-');
setText('predictionStation','-');
setText('predictionCount','-');
setHtml('predictionTableBody','<tr><td colspan="2" class="text-center text-muted">Belum ada prediksi.</td></tr>');
modelSelectionHint.textContent='Simpan dataset terlebih dahulu, lalu pilih minimal satu model perhitungan.';
setValidationCardMinimized(false);
syncAdjustmentCardAvailability();
resultArea.style.display='none';
syncResetButtonVisibility(false);
}
function syncResetButtonVisibility(show){
document.querySelectorAll('.reset-admiralty-btn').forEach(function(el){
el.style.display=show?'':'none';
});
}
async function resetAdmiraltyData(){
if(!window.confirm('Apakah Anda yakin ingin mereset formulir dan menghapus data validasi saat ini?')){return;}
try{
await fetch('<?= site_url('admiralty/reset') ?>',{method:'POST',headers:{Accept:'application/json'}});
}catch(err){console.warn('Gagal mereset sesi di server:',err);}
resetCalculationPanels();
fileField.value='';
const alertEl=document.getElementById('restoredSessionAlert');
if(alertEl){alertEl.style.display='none';}
setValidationCardMinimized(false);
}
function renderPreview(rows){
if(!rows.length){setHtml('previewBody','<tr><td colspan="3" class="text-center text-muted">Tidak ada preview data valid.</td></tr>');return}
setHtml('previewBody',rows.map(function(row){return '<tr><td>'+esc(row.line)+'</td><td>'+esc(row.datetime)+'</td><td>'+esc(row.water_level)+'</td></tr>'}).join(''))
}
function renderGapList(gaps){
if(!gaps.length){setHtml('gapList','<span class="text-success">Tidak ada gap terdeteksi.</span>');return}
setHtml('gapList',gaps.map(function(gap){return '<div class="mb-2 p-2 border rounded bg-light"><strong>'+esc(gap.from)+'</strong> sampai <strong>'+esc(gap.to)+'</strong><div>Jarak '+esc(gap.distance_minutes)+' menit, titik hilang '+esc(gap.missing_points)+'</div></div>'}).join(''))
}
function renderInvalidList(rows){
if(!rows.length){setHtml('invalidList','<span class="text-success">Tidak ada baris invalid.</span>');return}
setHtml('invalidList',rows.map(function(row){return '<div class="mb-2 p-2 border rounded bg-light"><strong>Line '+esc(row.line)+'</strong><div>'+esc(row.reason)+'</div><small class="text-muted">'+esc(row.raw)+'</small></div>'}).join(''))
}
function renderStatus(summary){
const items=[
summary.quality_status==='analysis_ready'?pill(summary.quality_label||'Layak Analisa','ok'):(summary.quality_status==='analysis_warning'?pill(summary.quality_label||'Layak Dengan Peringatan','warn'):pill(summary.quality_label||'Tidak Layak Analisa','err')),
summary.interval_minutes===60?pill('Interval 60 menit terdeteksi','ok'):pill('Interval dominan '+summary.interval_label,'warn'),
summary.is_minimum_satisfied?pill('Jumlah data memenuhi minimum 360','ok'):pill('Jumlah data kurang dari 360','err'),
summary.is_maximum_satisfied?pill('Jumlah data tidak melebihi 720','ok'):pill('Jumlah data melebihi 720','warn'),
summary.has_gap?pill('Ada gap pada data','warn'):pill('Tidak ada gap data','ok'),
summary.duplicate_rows_total>0?pill('Ada duplikasi timestamp','warn'):pill('Tidak ada duplikasi timestamp','ok')
];
setHtml('statusList',items.join(' '));
}
function renderQualityReasons(summary){
const reasons=Array.isArray(summary.quality_reasons)?summary.quality_reasons:[];
const note=summary.quality_note||'Belum ada evaluasi kualitas dataset.';
if(!reasons.length){
setHtml('qualityReasons','<strong>'+(summary.quality_label||'Status kualitas dataset')+'</strong><div class="mt-2">'+esc(note)+'</div>');
return;
}
setHtml('qualityReasons','<strong>'+(summary.quality_label||'Status kualitas dataset')+'</strong><div class="mt-2">'+esc(note)+'</div><div class="mt-2">'+reasons.map(function(reason){return '<div>- '+esc(reason)+'</div>';}).join('')+'</div>');
}
function applyValidatedResult(result){
if(!result||!result.summary)return;
const summary=result.summary;
latestValidatedResult=result;
resultArea.style.display='block';
setText('summaryInterval',summary.interval_label);
setText('summaryCount',summary.valid_rows_total);
setText('summaryGap',summary.has_gap?'Ada':'Tidak');
setText('summaryDuplicates',summary.duplicate_rows_total);
setText('metricStart',summary.start_at);
setText('metricEnd',summary.end_at);
setHtml('metricMinStatus',summary.is_minimum_satisfied?pill('Memenuhi','ok'):pill('Belum memenuhi','err'));
setHtml('metricMaxStatus',summary.is_maximum_satisfied?pill('Masih dalam batas','ok'):pill('Melebihi batas','warn'));
setHtml('metricQualityStatus',summary.quality_status==='analysis_ready'?pill(summary.quality_label||'Layak Analisa','ok'):(summary.quality_status==='analysis_warning'?pill(summary.quality_label||'Layak Dengan Peringatan','warn'):pill(summary.quality_label||'Tidak Layak Analisa','err')));
setText('metricValidRows',summary.valid_rows_total);
setText('metricInvalidRows',summary.invalid_rows_total);
setText('metricGapCount',summary.gap_count);
renderPreview(result.preview||[]);
renderGapList((result.gaps&&result.gaps.gap_examples)||[]);
renderInvalidList(result.invalid_rows||[]);
renderStatus(summary);
renderQualityReasons(summary);
updateCalculationEligibility(summary);
if(summary.quality_can_analyze){
setHtml('calculationIntro','Dataset sudah lolos validasi awal. Pilih model perhitungan yang ingin dijalankan, lalu simpan dataset sebelum klik <strong>Mulai Hitung Admiralty</strong>.');
}
syncResetButtonVisibility(true);
}
function applyDatasetMeta(datasetMeta){
if(!datasetMeta)return;
latestDatasetMeta=datasetMeta;
if(datasetMeta.station_name)stationNameField.value=String(datasetMeta.station_name);
if(typeof datasetMeta.latitude!=='undefined'&&datasetMeta.latitude!==null)latitudeField.value=String(datasetMeta.latitude);
if(typeof datasetMeta.longitude!=='undefined'&&datasetMeta.longitude!==null)longitudeField.value=String(datasetMeta.longitude);
if(datasetMeta.timezone)timezoneField.value=String(datasetMeta.timezone);
calculationArea.style.display='block';
calculationEligibility.textContent='Dataset tersimpan dengan kode '+(datasetMeta.dataset_code||'-')+' untuk stasiun '+(datasetMeta.station_name||'-')+'. Status kualitas: '+(datasetMeta.validation_label||'Belum dinilai')+'.';
setValidationCardMinimized(true);
updateStartCalculationState();
syncResetButtonVisibility(true);
}
function updateCalculationEligibility(summary){
const eligible=!!summary.quality_can_analyze;
saveDatasetButton.disabled=!eligible;
calculationArea.style.display=eligible?'block':'none';
if(eligible){
calculationEligibility.textContent=(summary.quality_status==='analysis_warning'?'Dataset bisa disimpan dan dihitung, tetapi masih punya peringatan kualitas.':'Dataset siap disimpan sebelum masuk ke tahap perhitungan Admiralty.');
updateStartCalculationState();
return;
}
const reasons=Array.isArray(summary.quality_reasons)?summary.quality_reasons.slice():[];
if(!reasons.length)reasons.push('dataset belum memenuhi syarat kualitas analisa');
calculationEligibility.textContent='Belum bisa dihitung: '+reasons.join(', ')+'.';
updateStartCalculationState();
}
async function saveDataset(){
if(!latestValidatedResult)return;
if(!stationNameField.value.trim()||!latitudeField.value.trim()||!longitudeField.value.trim()||!timezoneField.value.trim()){window.alert('Nama stasiun, latitude, longitude, dan time zone wajib diisi.');return}
const originalLabel=saveDatasetButton.innerHTML;
saveDatasetButton.disabled=true;
saveDatasetButton.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';
try{
const formData=new FormData();
formData.set('station_name',stationNameField.value.trim());
formData.set('latitude',latitudeField.value.trim());
formData.set('longitude',longitudeField.value.trim());
formData.set('timezone',timezoneField.value.trim());
const response=await fetch('<?= site_url('admiralty/save-dataset') ?>',{method:'POST',body:formData,headers:{Accept:'application/json'}});
const payload=await response.json();
if(!response.ok||!payload.success){throw new Error(payload.message||'Dataset gagal disimpan.')}
latestDatasetMeta=payload.result||null;
saveFormDraft();
calculationEligibility.textContent='Dataset tersimpan dengan kode '+(latestDatasetMeta&&latestDatasetMeta.dataset_code?latestDatasetMeta.dataset_code:'-')+' untuk stasiun '+(latestDatasetMeta&&latestDatasetMeta.station_name?latestDatasetMeta.station_name:'-')+'. Status kualitas: '+(latestDatasetMeta&&latestDatasetMeta.validation_label?latestDatasetMeta.validation_label:'Belum dinilai')+'.';
setValidationCardMinimized(true);
updateStartCalculationState();
setTimeout(function(){
calculationArea.scrollIntoView({behavior:'smooth',block:'start'});
},120);
}catch(error){
window.alert(error.message||'Terjadi kesalahan saat menyimpan dataset.');
saveDatasetButton.disabled=false;
}finally{
saveDatasetButton.innerHTML=originalLabel;
if(latestDatasetMeta===null&&latestValidatedResult!==null){
saveDatasetButton.disabled=false;
}
updateStartCalculationState();
}
}
function renderWorkingTable(columns,rows){
const safeColumns=Array.isArray(columns)&&columns.length?columns:[{key:'no',label:'No'}];
setHtml('workingTableHead',safeColumns.map(function(column){return '<th>'+esc(column.label)+'</th>'}).join(''));
if(!rows.length){setHtml('workingTableBody','<tr><td colspan="'+safeColumns.length+'" class="text-center text-muted">Tabel kerja tidak tersedia.</td></tr>');return}
setHtml('workingTableBody',rows.map(function(row){return '<tr>'+safeColumns.map(function(column){return '<td>'+esc(row[column.key]??'')+'</td>'}).join('')+'</tr>'}).join(''));
}
function renderExportTable(columns,rows,emptyText){
const safeColumns=Array.isArray(columns)?columns:[];
if(!safeColumns.length){
return '<div class="empty-note">'+esc(emptyText||'Tabel tidak tersedia.')+'</div>';
}
const bodyRows=Array.isArray(rows)&&rows.length?rows.map(function(row){
return '<tr>'+safeColumns.map(function(column){return '<td>'+esc(row[column.key]??'')+'</td>'}).join('')+'</tr>';
}).join(''):'<tr><td colspan="'+safeColumns.length+'" class="empty-cell">'+esc(emptyText||'Tabel tidak tersedia.')+'</td></tr>';
return '<table><thead><tr>'+safeColumns.map(function(column){return '<th>'+esc(column.label)+'</th>'}).join('')+'</tr></thead><tbody>'+bodyRows+'</tbody></table>';
}
function buildExportModelSection(result){
const summary=result.summary||{};
const subPanels=Array.isArray(result.sub_panels)?result.sub_panels:[];
const components=Array.isArray(result.component_targets)?result.component_targets:[];
const notes=(result.notes||[]).map(function(note){return '<li>'+esc(note)+'</li>'}).join('');
const summaryRows=[
['Model',summary.model_label||'-'],
['Run Code',((result.run_meta||{}).run_code)||'-'],
['Stasiun',summary.station_name||'-'],
['MSL',typeof summary.msl!=='undefined'?summary.msl:'-'],
['Interval',summary.interval_label||'-'],
['Jumlah Data',typeof summary.data_count!=='undefined'?summary.data_count:'-'],
['Mulai',summary.start_at||'-'],
['Akhir',summary.end_at||'-']
].map(function(row){return '<tr><th>'+esc(row[0])+'</th><td>'+esc(row[1])+'</td></tr>';}).join('');
const summaryTable='<table class="meta-table"><tbody>'+summaryRows+'</tbody></table>';
const workingTable=renderExportTable(result.working_columns||[],result.working_table||[],'Tabel kerja tidak tersedia.');
const componentTable=renderExportTable(
[
{key:'name',label:'Komponen'},
{key:'group',label:'Kelompok'},
{key:'amplitude',label:'Amplitudo'},
{key:'phase',label:'Fase'},
{key:'status',label:'Status'}
],
components,
'Komponen belum tersedia.'
);
const panelHtml=subPanels.map(function(panel){
const items=Array.isArray(panel.items)?panel.items:[];
return '<section class="sub-section"><h4>'+esc(panel.title||'Sub-panel')+'</h4>'
+'<p class="sub-desc">'+esc(panel.description||'')+'</p>'
+renderExportTable(panel.columns||[],panel.rows||[],'Belum ada data.')
+(items.length?'<ul>'+items.map(function(item){return '<li>'+esc(item)+'</li>'}).join('')+'</ul>':'')
+'</section>';
}).join('');
return '<section class="model-section">'
+'<h2>'+esc(summary.model_label||'Model')+'</h2>'
+summaryTable
+(notes?'<div class="note-block"><strong>Catatan</strong><ul>'+notes+'</ul></div>':'')
+'<section class="sub-section"><h4>'+esc(result.working_table_title||'Tabel Kerja')+'</h4>'+workingTable+'</section>'
+panelHtml
+'<section class="sub-section"><h4>Komponen Target</h4>'+componentTable+'</section>'
+'</section>';
}
function exportWorkbookPdf(){
if(!latestCalculationResults.length){
window.alert('Belum ada hasil perhitungan untuk diexport.');
return;
}
const chartCanvas=document.getElementById('comparisonChartCanvas');
const chartImage=(chartCanvas&&chartCanvas.width>0)?chartCanvas.toDataURL('image/png'):'';
const summaryTable=document.getElementById('comparisonSummaryBody')?document.getElementById('comparisonSummaryBody').innerHTML:'';
const componentHead=document.getElementById('comparisonComponentHead')?document.getElementById('comparisonComponentHead').innerHTML:'';
const componentBody=document.getElementById('comparisonComponentBody')?document.getElementById('comparisonComponentBody').innerHTML:'';
const printWindow=window.open('','_blank','width=1280,height=900');
if(!printWindow){
window.alert('Popup diblokir browser. Izinkan popup terlebih dahulu untuk export PDF.');
return;
}
const exportedAt=new Date().toLocaleString('id-ID',{timeZone:'Asia/Jakarta'});
const documentHtml='<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Export Workbook Admiralty</title><style>'
+'body{font-family:Segoe UI,Tahoma,sans-serif;color:#111827;margin:24px;background:#fff} h1,h2,h3,h4{margin:0 0 10px} h1{font-size:24px} h2{font-size:20px;margin-top:24px;padding-bottom:6px;border-bottom:2px solid #0f766e} h3{font-size:17px;margin-top:20px} h4{font-size:15px;margin-top:14px} p,li{font-size:12px;line-height:1.5} table{width:100%;border-collapse:collapse;margin:10px 0 16px} th,td{border:1px solid #d1d5db;padding:6px 8px;font-size:11px;vertical-align:top} th{background:#f3f4f6;text-align:left} .meta-table th{width:180px;background:#f8fafc} .meta-table td{background:#fff} .sub-section{margin-top:14px} .sub-desc{color:#475569;margin-bottom:8px} .note-block{background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:8px;margin:12px 0} .chart-block img{max-width:100%;border:1px solid #d1d5db} .empty-note,.empty-cell{color:#64748b;text-align:center} .page-break{page-break-before:always} .top-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:18px 0}.top-card{border:1px solid #dbe5ee;border-radius:10px;padding:12px;background:#f8fbfd}.top-card strong{display:block;margin-bottom:6px}'
+'@media print{body{margin:14mm} .no-print{display:none}}'
+'</style></head><body>'
+'<h1>Export Workbook Admiralty</h1>'
+'<p>Dibuat pada '+esc(exportedAt)+' WIB. Dokumen ini merangkum grafik perbandingan, tabel komparasi, dan seluruh isi workbook yang sedang tampil di modul Admiralty.</p>'
+'<div class="top-meta">'
+'<div class="top-card"><strong>Jumlah Model</strong>'+(latestCalculationResults.length)+'</div>'
+'<div class="top-card"><strong>Rentang Grafik</strong>15 hari data observasi</div>'
+'<div class="top-card"><strong>Seri Aktif</strong>'+esc(document.getElementById('comparisonChartSeries')?document.getElementById('comparisonChartSeries').textContent:'-')+'</div>'
+'</div>'
+(chartImage?'<section class="chart-block"><h2>Grafik Perbandingan 15 Hari</h2><img src="'+chartImage+'" alt="Grafik Perbandingan 15 Hari"></section>':'')
+'<section><h2>Tabel Perbandingan Ringkas</h2><table><thead><tr><th>Model</th><th>Run Code</th><th>MSL</th><th>Min</th><th>Max</th><th>Range</th><th>Durasi</th><th>Data</th><th>Zona Waktu</th></tr></thead><tbody>'+summaryTable+'</tbody></table></section>'
+'<section><h2>Perbandingan Komponen</h2><table><thead><tr>'+componentHead+'</tr></thead><tbody>'+componentBody+'</tbody></table></section>'
+latestCalculationResults.map(function(result,index){return (index===0?'':'<div class="page-break"></div>')+buildExportModelSection(result);}).join('')
+'<script>window.onload=function(){setTimeout(function(){window.print();},300);};<\/script>'
+'</body></html>';
printWindow.document.open();
printWindow.document.write(documentHtml);
printWindow.document.close();
}
function renderComponentTable(rows){
if(!rows.length){setHtml('componentTableBody','<tr><td colspan="5" class="text-center text-muted">Komponen belum disiapkan.</td></tr>');return}
setHtml('componentTableBody',rows.map(function(row){return '<tr><td>'+esc(row.name)+'</td><td>'+esc(row.group)+'</td><td>'+esc(row.amplitude||'-')+'</td><td>'+esc(row.phase||'-')+'</td><td>'+esc(row.status)+'</td></tr>'}).join(''));
}
function getSelectedModels(){
return modelCheckboxes.filter(function(input){return input.checked}).map(function(input){return input.value});
}
function renderMultiModelResults(results){
if(!results.length){multiModelResults.style.display='none';multiModelResults.innerHTML='';return}
multiModelResults.style.display='block';
multiModelResults.innerHTML=results.map(function(result,index){
const summary=result.summary||{};
const runMeta=result.run_meta||{};
const columns=result.working_columns||[];
const rows=result.working_table||[];
  const head=columns.map(function(column){return '<th>'+esc(column.label)+'</th>'}).join('');
const body=rows.length?rows.map(function(row){return '<tr>'+columns.map(function(column){return '<td>'+esc(row[column.key]??'')+'</td>'}).join('')+'</tr>'}).join(''):'<tr><td colspan="'+Math.max(columns.length,1)+'" class="text-center text-muted">Belum ada data tabel kerja.</td></tr>';
const comps=(result.component_targets||[]).map(function(row){return '<tr><td>'+esc(row.name)+'</td><td>'+esc(row.group)+'</td><td>'+esc(row.amplitude||'-')+'</td><td>'+esc(row.phase||'-')+'</td><td>'+esc(row.status)+'</td></tr>'}).join('')||'<tr><td colspan="5" class="text-center text-muted">Belum ada komponen.</td></tr>';
const notes=(result.notes||[]).map(function(note){return '<div>'+esc(note)+'</div>'}).join('');
const subPanels=Array.isArray(result.sub_panels)?result.sub_panels:[];
function renderPanelCard(panel){
const items=Array.isArray(panel.items)?panel.items:[];
const panelColumns=Array.isArray(panel.columns)?panel.columns:[];
const panelRows=Array.isArray(panel.rows)?panel.rows:[];
const panelTable=panelColumns.length?'<div class="table-responsive working-scroll mt-2"><table class="table table-striped table-sm mb-0"><thead><tr>'+panelColumns.map(function(column){return '<th>'+esc(column.label)+'</th>'}).join('')+'</tr></thead><tbody>'+(panelRows.length?panelRows.map(function(row){return '<tr>'+panelColumns.map(function(column){return '<td>'+esc(row[column.key]??'')+'</td>'}).join('')+'</tr>'}).join(''):'<tr><td colspan="'+panelColumns.length+'" class="text-center text-muted">Belum ada data.</td></tr>')+'</tbody></table></div>':'';
const extraClass=String(panel.title||'').includes('(Dev)')?' dev-panel':'';
return '<div class="subpanel-item'+extraClass+'"><h6>'+esc(panel.title||'Sub-panel')+'</h6><p>'+esc(panel.description||'')+'</p>'+panelTable+items.map(function(item){return '<span class="small">- '+esc(item)+'</span>'}).join('')+'</div>';
}
function renderWorkbookTabs(tabSpecs,tabIdPrefix,title){
const validTabs=tabSpecs.filter(function(tab){return tab.html&&tab.html.trim()!=='';});
if(!validTabs.length){return ''}
const navTabs='<ul class="nav nav-tabs mb-3" role="tablist">'+validTabs.map(function(tab,tabIndex){const tabId=tabIdPrefix+'-'+tabIndex;return '<li class="nav-item"><a class="nav-link '+(tabIndex===0?'active':'')+'" data-toggle="tab" href="#'+tabId+'" role="tab" aria-selected="'+(tabIndex===0?'true':'false')+'">'+esc(tab.label)+'</a></li>';}).join('')+'</ul>';
const tabPanes='<div class="tab-content">'+validTabs.map(function(tab,tabIndex){const tabId=tabIdPrefix+'-'+tabIndex;return '<div class="tab-pane fade '+(tabIndex===0?'show active':'')+'" id="'+tabId+'" role="tabpanel">'+tab.html+'</div>';}).join('')+'</div>';
return '<div class="result-section-title">'+esc(title)+'</div>'+navTabs+tabPanes;
}
const subPanelGridClass='subpanel-grid'+(subPanels.length===1?' single-panel':'');
const subPanelHtml=subPanels.length?'<div class="'+subPanelGridClass+'">'+subPanels.map(function(panel){
return renderPanelCard(panel);
}).join('')+'</div>':'';
const inlineSubPanelHtml=subPanels.length===1?renderPanelCard(subPanels[0]):'';
const stackedSubPanelHtml=subPanels.length>1?subPanelHtml:'';
if((summary.model_name||'')==='admiralty_indonesia'){
const skemaIPanel='<div class="skema-panel"><h6>'+esc(result.working_table_title||'Skema 1')+'</h6><p>Matriks observasi jam referensi untuk tahap awal Admiralty Indonesia.</p><div class="table-responsive working-scroll mt-2"><table class="table table-striped table-sm mb-0"><thead><tr>'+head+'</tr></thead><tbody>'+body+'</tbody></table></div></div>';
const panelsByTitle={};
subPanels.forEach(function(panel){
panelsByTitle[String(panel.title||'')]=panel;
});
const tabSpecs=[
{title:'Skema 1',label:'Skema 1',html:skemaIPanel},
{title:'Skema 2',label:'Skema 2',html:panelsByTitle['Skema 2']?renderPanelCard(panelsByTitle['Skema 2']):''},
{title:'Skema 3',label:'Skema 3',html:panelsByTitle['Skema 3']?renderPanelCard(panelsByTitle['Skema 3']):''},
{title:'Skema 4',label:'Skema 4',html:panelsByTitle['Skema 4']?renderPanelCard(panelsByTitle['Skema 4']):''},
{title:'Skema 5',label:'Skema 5',html:panelsByTitle['Skema 5']?renderPanelCard(panelsByTitle['Skema 5']):''},
{title:'Skema 6',label:'Skema 6',html:panelsByTitle['Skema 6']?renderPanelCard(panelsByTitle['Skema 6']):''},
{title:'Rekap Skema 5&6',label:'Rekap 5&6',html:panelsByTitle['Rekap Skema 5&6']?renderPanelCard(panelsByTitle['Rekap Skema 5&6']):''},
{title:'Skema 7',label:'Skema 7',html:panelsByTitle['Skema 7']?renderPanelCard(panelsByTitle['Skema 7']):''},
{title:'Forecasting Pasut',label:'Forecasting',html:panelsByTitle['Forecasting Pasut']?renderPanelCard(panelsByTitle['Forecasting Pasut']):''},
{title:'Referensi',label:'Referensi',html:[
panelsByTitle['Konsistensi Workbook']?renderPanelCard(panelsByTitle['Konsistensi Workbook']):'',
panelsByTitle['Diagnostik M2 vs N2 (Dev)']?renderPanelCard(panelsByTitle['Diagnostik M2 vs N2 (Dev)']):'',
panelsByTitle['Tabel Faktor Admiralty']?renderPanelCard(panelsByTitle['Tabel Faktor Admiralty']):'',
panelsByTitle['Engine Workbook']?renderPanelCard(panelsByTitle['Engine Workbook']):''
].join('')}
].filter(function(tab){return tab.html&&tab.html.trim()!==''});
const tabIdPrefix='admiralty-indonesia-'+index;
return '<div class="multi-result-card"><div class="multi-result-head"><div><strong>'+esc(summary.model_label||('Model '+(index+1)))+'</strong><div class="text-muted small">Run '+esc(runMeta.run_code||'-')+'</div></div><div class="status-pill ok">'+esc(summary.interval_label||'-')+' - MSL '+esc(typeof summary.msl!=='undefined'?summary.msl:'-')+'</div></div><div class="multi-result-body"><div class="stage-note">'+(notes||'Catatan belum tersedia.')+'</div>'+renderWorkbookTabs(tabSpecs,tabIdPrefix,'Workbook Admiralty')+'<div class="result-section-title">Komponen Target</div><div class="table-responsive"><table class="table table-striped table-sm mb-0"><thead><tr><th>Komponen</th><th>Kelompok</th><th>Amplitudo</th><th>Fase</th><th>Status</th></tr></thead><tbody>'+comps+'</tbody></table></div></div></div>';
}
if((summary.model_name||'')==='admiralty_hidros'){
const matrixPanel='<div class="skema-panel"><h6>'+esc(result.working_table_title||'Matriks 29 Piantan')+'</h6><p>Matriks input observasi 29 piantan yang dibaca dari workbook Admiralty Hidros.</p><div class="table-responsive working-scroll mt-2"><table class="table table-striped table-sm mb-0"><thead><tr>'+head+'</tr></thead><tbody>'+body+'</tbody></table></div></div>';
const panelsByTitle={};
subPanels.forEach(function(panel){panelsByTitle[String(panel.title||'')]=panel;});
const tabSpecs=[
{label:'Matriks',html:matrixPanel},
{label:'Konstanta',html:panelsByTitle['Konstanta Harmonik']?renderPanelCard(panelsByTitle['Konstanta Harmonik']):''},
{label:'Turunan',html:panelsByTitle['Turunan Rumus']?renderPanelCard(panelsByTitle['Turunan Rumus']):''},
{label:'Klasifikasi',html:panelsByTitle['Klasifikasi']?renderPanelCard(panelsByTitle['Klasifikasi']):''},
{label:'Referensi',html:[
panelsByTitle['Konsistensi Datum']?renderPanelCard(panelsByTitle['Konsistensi Datum']):'',
panelsByTitle['Engine Workbook']?renderPanelCard(panelsByTitle['Engine Workbook']):''
].join('')}
];
return '<div class="multi-result-card"><div class="multi-result-head"><div><strong>'+esc(summary.model_label||('Model '+(index+1)))+'</strong><div class="text-muted small">Run '+esc(runMeta.run_code||'-')+'</div></div><div class="status-pill ok">'+esc(summary.interval_label||'-')+' - MSL '+esc(typeof summary.msl!=='undefined'?summary.msl:'-')+'</div></div><div class="multi-result-body"><div class="stage-note">'+(notes||'Catatan belum tersedia.')+'</div>'+renderWorkbookTabs(tabSpecs,'admiralty-hidros-'+index,'Workbook Admiralty Hidros')+'<div class="result-section-title">Komponen Target</div><div class="table-responsive"><table class="table table-striped table-sm mb-0"><thead><tr><th>Komponen</th><th>Kelompok</th><th>Amplitudo</th><th>Fase</th><th>Status</th></tr></thead><tbody>'+comps+'</tbody></table></div></div></div>';
}
return '<div class="multi-result-card"><div class="multi-result-head"><div><strong>'+esc(summary.model_label||('Model '+(index+1)))+'</strong><div class="text-muted small">Run '+esc(runMeta.run_code||'-')+'</div></div><div class="status-pill ok">'+esc(summary.interval_label||'-')+' - MSL '+esc(typeof summary.msl!=='undefined'?summary.msl:'-')+'</div></div><div class="multi-result-body"><div class="row"><div class="col-lg-7"><h6 class="font-weight-bold mb-3">'+esc(result.working_table_title||'Tabel Kerja')+'</h6><div class="table-responsive working-scroll mb-3"><table class="table table-striped table-sm mb-0"><thead><tr>'+head+'</tr></thead><tbody>'+body+'</tbody></table></div></div><div class="col-lg-5"><h6 class="font-weight-bold mb-3">Komponen Target</h6><div class="table-responsive mb-3"><table class="table table-striped table-sm mb-0"><thead><tr><th>Komponen</th><th>Kelompok</th><th>Amplitudo</th><th>Fase</th><th>Status</th></tr></thead><tbody>'+comps+'</tbody></table></div><div class="stage-note'+(inlineSubPanelHtml?' mb-3':'')+'">'+(notes||'Catatan belum tersedia.')+'</div>'+inlineSubPanelHtml+'</div></div>'+stackedSubPanelHtml+'</div></div>';
  }).join('');
  }
function renderComparison(results){
if(!results.length){
comparisonArea.style.display='none';
return;
}
comparisonArea.style.display='block';
setHtml('comparisonSummaryBody',results.map(function(result){
const summary=result.summary||{};
const runMeta=result.run_meta||{};
return '<tr><td>'+esc(summary.model_label||'-')+'</td><td>'+esc(runMeta.run_code||'-')+'</td><td>'+esc(typeof summary.msl!=="undefined"?summary.msl:"-")+'</td><td>'+esc(typeof summary.min_level!=="undefined"?summary.min_level:"-")+'</td><td>'+esc(typeof summary.max_level!=="undefined"?summary.max_level:"-")+'</td><td>'+esc(typeof summary.tidal_range!=="undefined"?summary.tidal_range:"-")+'</td><td>'+esc(typeof summary.duration_hours!=="undefined"?summary.duration_hours+" jam":"-")+'</td><td>'+esc(typeof summary.data_count!=="undefined"?summary.data_count:"-")+'</td><td>'+esc(summary.timezone||"-")+'</td></tr>';
}).join(''));

const componentMap={};
results.forEach(function(result){
const summary=result.summary||{};
const modelLabel=summary.model_label||'Model';
const components=Array.isArray(result.component_targets)?result.component_targets:[];
components.forEach(function(component){
const key=String(component.name||'');
if(!componentMap[key]){
componentMap[key]={group:String(component.group||'-'),statuses:{}};
}
componentMap[key].statuses[modelLabel]={
amplitude:String(component.amplitude||'-'),
phase:String(component.phase||'-')
};
});
});

const labels=results.map(function(result){return (result.summary||{}).model_label||'Model'});
setHtml('comparisonComponentHead','<th>Komponen</th><th>Kelompok</th>'+labels.map(function(label){return '<th>'+esc(label)+'<div class="small text-muted font-weight-normal">Amp | Fase</div></th>'}).join('')+'<th>Selisih Amp Max</th><th>Selisih Fase Max</th>');
const componentRows=Object.keys(componentMap).length?Object.keys(componentMap).map(function(name){
const item=componentMap[name];
const ampValues=[];
const phaseValues=[];
labels.forEach(function(label){
const value=item.statuses[label]||{amplitude:'-',phase:'-'};
const amp=toNum(value.amplitude);
const phase=toNum(value.phase);
if(amp!==null){ampValues.push(amp);}
if(phase!==null){phaseValues.push(phase);}
});
const ampSpread=ampValues.length>1?(Math.max.apply(null,ampValues)-Math.min.apply(null,ampValues)):null;
const phaseSpread=phaseValues.length>1?(Math.max.apply(null,phaseValues)-Math.min.apply(null,phaseValues)):null;
return '<tr><td>'+esc(name)+'</td><td>'+esc(item.group)+'</td>'+labels.map(function(label){
const value=item.statuses[label]||{amplitude:'-',phase:'-'};
return '<td><div><strong>A:</strong> '+esc(value.amplitude)+'</div><div><strong>F:</strong> '+esc(value.phase)+'</div></td>';
}).join('')+'<td>'+(ampSpread===null?'-':esc(ampSpread.toFixed(4))+' m')+'</td><td>'+(phaseSpread===null?'-':esc(phaseSpread.toFixed(2))+'°')+'</td></tr>';
}).join(''):'<tr><td colspan="'+(labels.length+4)+'" class="text-center text-muted">Komponen belum tersedia.</td></tr>';
setHtml('comparisonComponentBody',componentRows);
renderComparisonChart(results);
const evaluationRows=[];
const phaseAlignments=[];
results.forEach(function(result){
const chart=result.comparison_chart||{};
const evaluations=Array.isArray(chart.evaluations)?chart.evaluations:[];
evaluations.forEach(function(item){evaluationRows.push(item);});
if(chart.phase_alignment){
phaseAlignments.push({
label:((result.summary||{}).model_label)||'Model',
offset:chart.phase_alignment.phase_offset_deg,
before:chart.phase_alignment.rmse_before,
after:chart.phase_alignment.rmse_after,
improved:chart.phase_alignment.improved
});
}
});
setHtml('comparisonEvaluationBody',evaluationRows.length?evaluationRows.map(function(item){
return '<tr>'
+'<td>'+esc(item.label||'-')+'</td>'
+'<td>'+esc(item.mode||'-')+'</td>'
+'<td>'+esc(item.status||'-')+'</td>'
+'<td>'+esc(typeof item.point_count!=='undefined'?item.point_count:'-')+'</td>'
+'<td>'+esc(typeof item.rmse!=='undefined'?item.rmse:'-')+'</td>'
+'<td>'+esc(typeof item.mae!=='undefined'?item.mae:'-')+'</td>'
+'<td>'+esc(typeof item.bias!=='undefined'?item.bias:'-')+'</td>'
+'<td>'+esc(typeof item.peak_error!=='undefined'&&item.peak_error!==null?item.peak_error:'-')+'</td>'
+'<td>'+esc(typeof item.low_error!=='undefined'&&item.low_error!==null?item.low_error:'-')+'</td>'
+'</tr>';
}).join(''):'<tr><td colspan="9" class="text-center text-muted">Evaluasi belum tersedia.</td></tr>');
const phaseAlignmentCard=document.getElementById('phaseAlignmentCard');
if(phaseAlignmentCard){
if(phaseAlignments.length){
phaseAlignmentCard.style.display='block';
setHtml('phaseAlignmentBody',phaseAlignments.map(function(item){
return '<div><strong>'+esc(item.label)+':</strong> phase offset '+esc(item.offset)+' deg | RMSE sebelum '+esc(item.before)+' | RMSE sesudah '+esc(item.after)+' | perbaikan '+esc(item.improved?'Ya':'Tidak')+'</div>';
}).join(''));
}else{
phaseAlignmentCard.style.display='none';
setHtml('phaseAlignmentBody','Belum ada phase alignment.');
}
}

if(results.length<2){
document.getElementById('comparisonHighlights').innerHTML='<div class="text-muted">Jalankan minimal dua metode sekaligus untuk melihat ringkasan selisih amplitudo dan fase.</div>';
return;
}

function toComponentMap(rows){
const map={};
rows.forEach(function(row){
map[String(row.name||'')]=row;
});
return map;
}
function toNum(value){
const parsed=parseFloat(value);
return Number.isFinite(parsed)?parsed:null;
}
const componentNames=['S0','M2','S2','N2','K2','K1','O1','P1','M4','MS4'];
function buildPairSummary(leftResult,rightResult){
if(!leftResult||!rightResult){return null;}
const leftComponents=toComponentMap(Array.isArray(leftResult.component_targets)?leftResult.component_targets:[]);
const rightComponents=toComponentMap(Array.isArray(rightResult.component_targets)?rightResult.component_targets:[]);
const deltaRows=componentNames.map(function(name){
const left=leftComponents[name]||{};
const right=rightComponents[name]||{};
const leftAmp=toNum(left.amplitude);
const rightAmp=toNum(right.amplitude);
const leftPhase=toNum(left.phase);
const rightPhase=toNum(right.phase);
return {
name:name,
leftAmp:leftAmp,
rightAmp:rightAmp,
ampDelta:(leftAmp!==null&&rightAmp!==null)?Math.abs(leftAmp-rightAmp):null,
phaseDelta:(leftPhase!==null&&rightPhase!==null)?Math.abs(leftPhase-rightPhase):null
};
}).filter(function(row){return row.ampDelta!==null;});
if(!deltaRows.length){return null;}
const comparableCount=deltaRows.length;
return {
leftLabel:((leftResult.summary||{}).model_label)||'Model A',
rightLabel:((rightResult.summary||{}).model_label)||'Model B',
meanAmpDelta:deltaRows.reduce(function(sum,row){return sum+row.ampDelta},0)/comparableCount,
meanPhaseDelta:deltaRows.filter(function(row){return row.phaseDelta!==null}).reduce(function(sum,row){return sum+row.phaseDelta},0)/Math.max(deltaRows.filter(function(row){return row.phaseDelta!==null}).length,1),
maxAmpRow:deltaRows.reduce(function(best,row){return !best||row.ampDelta>best.ampDelta?row:best},null),
m2Row:deltaRows.find(function(row){return row.name==='M2'})||null,
n2Row:deltaRows.find(function(row){return row.name==='N2'})||null
};
}
function fmt(value,digits){
return value===null?'-':value.toFixed(digits);
}
const comparableResults=results.filter(function(result){
return Array.isArray(result.component_targets)&&result.component_targets.length>0;
});
const pairSummaries=[];
for(let leftIndex=0;leftIndex<comparableResults.length;leftIndex++){
for(let rightIndex=leftIndex+1;rightIndex<comparableResults.length;rightIndex++){
const summary=buildPairSummary(comparableResults[leftIndex],comparableResults[rightIndex]);
if(summary){pairSummaries.push(summary);}
}
}
if(!pairSummaries.length){
document.getElementById('comparisonHighlights').innerHTML='<div class="text-muted">Belum ada pasangan model yang cukup untuk dibuat ringkasan selisih.</div>';
return;
}
document.getElementById('comparisonHighlights').innerHTML=pairSummaries.map(function(summary){
return '<div class="comparison-highlight-grid">'
+'<div class="comparison-highlight-item"><strong>Model Dibandingkan</strong><span>'+esc(summary.leftLabel)+' vs '+esc(summary.rightLabel)+'</span></div>'
+'<div class="comparison-highlight-item"><strong>Rata-rata Selisih Amplitudo</strong><span>'+fmt(summary.meanAmpDelta,4)+' m</span></div>'
+'<div class="comparison-highlight-item"><strong>Selisih Fase Rata-rata</strong><span>'+fmt(summary.meanPhaseDelta,2)+' derajat</span></div>'
+'<div class="comparison-highlight-item"><strong>Selisih Amplitudo Terbesar</strong><span>'+(summary.maxAmpRow?esc(summary.maxAmpRow.name)+' ('+fmt(summary.maxAmpRow.ampDelta,4)+' m)':'-')+'</span></div>'
+'<div class="comparison-highlight-item"><strong>M2</strong><span>'+esc(summary.leftLabel)+' '+fmt(summary.m2Row&&summary.m2Row.leftAmp!==null?summary.m2Row.leftAmp:null,4)+' m | '+esc(summary.rightLabel)+' '+fmt(summary.m2Row&&summary.m2Row.rightAmp!==null?summary.m2Row.rightAmp:null,4)+' m</span></div>'
+'<div class="comparison-highlight-item"><strong>N2</strong><span>'+esc(summary.leftLabel)+' '+fmt(summary.n2Row&&summary.n2Row.leftAmp!==null?summary.n2Row.leftAmp:null,4)+' m | '+esc(summary.rightLabel)+' '+fmt(summary.n2Row&&summary.n2Row.rightAmp!==null?summary.n2Row.rightAmp:null,4)+' m</span></div>'
+'</div>';
}).join('');
}
function renderComparisonChart(results){
const canvas=document.getElementById('comparisonChartCanvas');
if(!canvas){return}
captureComparisonChartVisibility();
if(comparisonChartInstance){comparisonChartInstance.destroy();comparisonChartInstance=null;}
latestComparisonChartExport=null;
const chartSource=results.find(function(result){
const chart=result.comparison_chart||{};
return Array.isArray(chart.labels)&&chart.labels.length>0;
});
if(!chartSource){
setText('comparisonChartStatus','Data grafik belum tersedia.');
setText('comparisonChartPoints','-');
setText('comparisonChartSeries','-');
latestIndonesiaAdjustmentBasis=null;
latestHidrosAdjustmentBasis=null;
latestCatAAdjustmentBasis=null;
latestLeastSquareAdjustmentBasis=null;
if(indonesiaAdjustmentCard){indonesiaAdjustmentCard.style.display='none';}
if(hidrosAdjustmentCard){hidrosAdjustmentCard.style.display='none';}
if(catAAdjustmentCard){catAAdjustmentCard.style.display='none';}
if(leastSquareAdjustmentCard){leastSquareAdjustmentCard.style.display='none';}
setHtml('adjustedConstantsBody','<tr><td colspan="7" class="text-center text-muted">Adjustment konstanta harmonik akan muncul di sini.</td></tr>');
if(exportComparisonCsvButton){exportComparisonCsvButton.disabled=true;}
syncAdjustmentCardAvailability();
return;
}
const chartPayload=chartSource.comparison_chart||{};
const labels=Array.isArray(chartPayload.labels)?chartPayload.labels:[];
const observedPoints=(chartPayload.observed&&Array.isArray(chartPayload.observed.points))?chartPayload.observed.points:[];
const datasets=[];
const activeSeries=[];
if(indonesiaAdjustmentCard){indonesiaAdjustmentCard.style.display='block';}
if(hidrosAdjustmentCard){hidrosAdjustmentCard.style.display='block';}
if(catAAdjustmentCard){catAAdjustmentCard.style.display='block';}
if(leastSquareAdjustmentCard){leastSquareAdjustmentCard.style.display='block';}
latestIndonesiaAdjustmentBasis=null;
latestHidrosAdjustmentBasis=null;
latestCatAAdjustmentBasis=null;
latestLeastSquareAdjustmentBasis=null;
const colors={
dataset:{border:'#1f2937',background:'rgba(31,41,55,.10)'},
admiralty_indonesia:{border:'#0f766e',background:'rgba(15,118,110,.10)'},
admiralty_hidros:{border:'#d97706',background:'rgba(217,119,6,.10)'},
admiralty_cat_a:{border:'#b91c1c',background:'rgba(185,28,28,.10)'},
least_square:{border:'#2563eb',background:'rgba(37,99,235,.10)'},
};
if(labels.length&&observedPoints.length){
datasets.push({
label:'Dataset (Pengamatan)',
data:observedPoints,
borderColor:colors.dataset.border,
backgroundColor:colors.dataset.background,
pointRadius:0,
borderWidth:2,
fill:false,
spanGaps:true,
tension:.15
});
activeSeries.push('Dataset');
}
results.forEach(function(result){
const chart=result.comparison_chart||{};
const seriesList=Array.isArray(chart.series)?chart.series:[];
if(((result.summary||{}).model_name||'')==='admiralty_indonesia'&&chart.adjustment_basis){
latestIndonesiaAdjustmentBasis=chart.adjustment_basis;
if(indonesiaAdjustmentCard){indonesiaAdjustmentCard.style.display='block';}
}
if(((result.summary||{}).model_name||'')==='admiralty_hidros'&&chart.adjustment_basis){
latestHidrosAdjustmentBasis=chart.adjustment_basis;
if(hidrosAdjustmentCard){hidrosAdjustmentCard.style.display='block';}
}
if(((result.summary||{}).model_name||'')==='admiralty_cat_a'&&chart.adjustment_basis){
latestCatAAdjustmentBasis=chart.adjustment_basis;
if(catAAdjustmentCard){catAAdjustmentCard.style.display='block';}
}
if(((result.summary||{}).model_name||'')==='least_square'&&chart.adjustment_basis){
latestLeastSquareAdjustmentBasis=chart.adjustment_basis;
if(leastSquareAdjustmentCard){leastSquareAdjustmentCard.style.display='block';}
}
seriesList.forEach(function(series){
if(!series.available||!Array.isArray(series.points)||!series.points.length){return}
const modelName=String(series.model_name||'');
const palette=colors[modelName]||{border:'#64748b',background:'rgba(100,116,139,.10)'};
let seriesPoints=series.points;
if(modelName==='admiralty_indonesia'&&latestIndonesiaAdjustmentBasis){seriesPoints=buildAdjustedIndonesiaSeries()||series.points;}
if(modelName==='admiralty_hidros'&&latestHidrosAdjustmentBasis){seriesPoints=buildAdjustedHidrosSeries()||series.points;}
if(modelName==='admiralty_cat_a'&&latestCatAAdjustmentBasis){seriesPoints=buildAdjustedCatASeries()||series.points;}
if(modelName==='least_square'&&latestLeastSquareAdjustmentBasis){seriesPoints=buildAdjustedLeastSquareSeries()||series.points;}
datasets.push({
label:String(series.model_label||modelName||'Model'),
data:seriesPoints,
borderColor:palette.border,
backgroundColor:palette.background,
pointRadius:0,
borderWidth:2,
fill:false,
spanGaps:true,
tension:.15
});
activeSeries.push(String(series.model_label||modelName||'Model'));
});
});
syncAdjustmentCardAvailability();
applyComparisonChartVisibility(datasets);
renderAdjustedConstantsTable();
latestComparisonChartExport={
labels:labels.slice(),
rows:[]
};
if(labels.length){
for(let index=0;index<labels.length;index++){
const row={timestamp:labels[index],dataset:observedPoints[index]??''};
datasets.forEach(function(dataset){
if(dataset.label==='Dataset (Pengamatan)'){return;}
row[dataset.label]=Array.isArray(dataset.data)?(typeof dataset.data[index]!=='undefined'?dataset.data[index]:''):'';
});
latestComparisonChartExport.rows.push(row);
}
}
setText('comparisonChartStatus',datasets.length>1?'Grafik 15 hari siap dibandingkan.':'Baru satu seri yang tersedia untuk grafik.');
setText('comparisonChartPoints',String(labels.length));
setText('comparisonChartSeries',activeSeries.join(', ')||'-');
if(exportComparisonCsvButton){exportComparisonCsvButton.disabled=!latestComparisonChartExport||!latestComparisonChartExport.rows.length;}
comparisonChartInstance=new Chart(canvas.getContext('2d'),{
type:'line',
data:{labels:labels,datasets:datasets},
options:{
responsive:true,
maintainAspectRatio:false,
animation:false,
interaction:{mode:'index',intersect:false},
plugins:{
legend:{
position:'top',
onClick:function(event,legendItem,legend){
const chart=legend.chart;
const datasetIndex=legendItem.datasetIndex;
if(datasetIndex===undefined||datasetIndex===null){return;}
chart.setDatasetVisibility(datasetIndex,!chart.isDatasetVisible(datasetIndex));
chart.update();
const dataset=chart.data.datasets[datasetIndex];
if(dataset&&dataset.label){
comparisonChartVisibility[String(dataset.label)]=chart.isDatasetVisible(datasetIndex);
}
}
},
tooltip:{callbacks:{label:function(context){return context.dataset.label+': '+context.parsed.y.toFixed(4)+' m';}}}
},
scales:{
x:{ticks:{maxTicksLimit:12,autoSkip:true}},
y:{title:{display:true,text:'Water Level (m)'}}
}
}
});
}
function exportComparisonCsv(){
if(!latestComparisonChartExport||!Array.isArray(latestComparisonChartExport.rows)||!latestComparisonChartExport.rows.length){
window.alert('Data grafik belum tersedia untuk diexport.');
return;
}
const columns=['timestamp','dataset'];
latestComparisonChartExport.rows.forEach(function(row){
Object.keys(row).forEach(function(key){
if(!columns.includes(key)){columns.push(key);}
});
});
const csvLines=[
columns.map(function(column){return '"'+String(column).replace(/"/g,'""')+'"';}).join(',')
];
latestComparisonChartExport.rows.forEach(function(row){
csvLines.push(columns.map(function(column){
const value=typeof row[column]!=='undefined'&&row[column]!==null?row[column]:'';
return '"'+String(value).replace(/"/g,'""')+'"';
}).join(','));
});
const blob=new Blob(["\uFEFF"+csvLines.join('\r\n')],{type:'text/csv;charset=utf-8;'});
const url=URL.createObjectURL(blob);
const link=document.createElement('a');
const stamp=new Date();
const filename='grafik-perbandingan-15-hari-'+stamp.getFullYear()
+String(stamp.getMonth()+1).padStart(2,'0')
+String(stamp.getDate()).padStart(2,'0')
'-'+String(stamp.getHours()).padStart(2,'0')
+String(stamp.getMinutes()).padStart(2,'0')
+String(stamp.getSeconds()).padStart(2,'0')
'.csv';
link.href=url;
link.download=filename;
document.body.appendChild(link);
link.click();
document.body.removeChild(link);
URL.revokeObjectURL(url);
}
function rerenderIndonesiaAdjustment(){
if(!latestCalculationResults.length){return;}
renderComparisonChart(latestCalculationResults);
}
function captureComparisonChartVisibility(){
if(!comparisonChartInstance||!comparisonChartInstance.data||!Array.isArray(comparisonChartInstance.data.datasets)){return;}
comparisonChartInstance.data.datasets.forEach(function(dataset,index){
if(!dataset||!dataset.label){return;}
comparisonChartVisibility[String(dataset.label)]=comparisonChartInstance.isDatasetVisible(index);
});
}
function applyComparisonChartVisibility(datasets){
if(!Array.isArray(datasets)){return;}
datasets.forEach(function(dataset){
if(!dataset||!dataset.label){return;}
const key=String(dataset.label);
if(Object.prototype.hasOwnProperty.call(comparisonChartVisibility,key)){
dataset.hidden=!comparisonChartVisibility[key];
}
});
}
function toDatetimeLocal(displayValue){
if(!displayValue||displayValue==='-')return '';
const parts=String(displayValue).split(' ');
if(parts.length<2)return '';
const dateParts=parts[0].split('/');
const timeParts=parts[1].split(':');
if(dateParts.length!==3||timeParts.length<2)return '';
return dateParts[2]+'-'+dateParts[1]+'-'+dateParts[0]+'T'+timeParts[0]+':'+timeParts[1];
}
function getPredictionRunMeta(){
const runId=predictionRunSelect.value;
if(!runId)return null;
return latestCalculationResults.find(function(result){
return String((result.run_meta||{}).run_id||'')===String(runId);
})||null;
}
function getPredictionAdjustmentState(){
const selectedResult=getPredictionRunMeta();
const modelName=((selectedResult||{}).summary||{}).model_name||'';
if(modelName==='admiralty_indonesia'){return indonesiaAdjustmentState;}
if(modelName==='admiralty_cat_a'){return catAAdjustmentState;}
if(modelName==='admiralty_hidros'){return hidrosAdjustmentState;}
if(modelName==='least_square'){return leastSquareAdjustmentState;}
return {amplitudePercent:0,phaseDegrees:0,p1AmplitudePercent:0,p1PhaseDegrees:0,timeShiftHours:0};
}
function syncPredictionRunMeta(){
const selectedResult=getPredictionRunMeta();
const summary=((selectedResult||{}).summary)||{};
const runMeta=((selectedResult||{}).run_meta)||{};
setText('predictionRunCode',runMeta.run_code||'-');
setText('predictionModel',summary.model_label||'-');
setText('predictionStation',summary.station_name||'-');
if(summary.start_at){predictionStart.value=toDatetimeLocal(summary.start_at||'');}
if(summary.end_at){predictionEnd.value=toDatetimeLocal(summary.end_at||'');}
}
function preparePredictionPanel(results){
latestCalculationResults=Array.isArray(results)?results:[];
if(exportWorkbookPdfButton){exportWorkbookPdfButton.disabled=!latestCalculationResults.length;}
const predictionRuns=latestCalculationResults.filter(function(result){
const modelName=((result.summary||{}).model_name||'');
return ['least_square','admiralty_indonesia','admiralty_cat_a'].includes(modelName) && result.run_meta && result.run_meta.run_id;
});
if(!predictionRuns.length){
predictionArea.style.display='none';
return;
}
predictionArea.style.display='block';
predictionRunSelect.innerHTML='<option value="">Pilih run prediksi</option>'+predictionRuns.map(function(result,index){
const summary=result.summary||{};
const runMeta=result.run_meta||{};
return '<option value="'+esc(runMeta.run_id)+'" '+(index===0?'selected':'')+'>'
    +esc(runMeta.run_code||'-')+' - '+esc(summary.model_label||'-')+' - '+esc(summary.station_name||'-')+'</option>';
}).join('');
const firstSummary=predictionRuns[0].summary||{};
predictionStart.value=toDatetimeLocal(firstSummary.start_at||'');
predictionEnd.value=toDatetimeLocal(firstSummary.end_at||'');
setText('predictionRunCode',predictionRuns[0].run_meta.run_code||'-');
setText('predictionModel',firstSummary.model_label||'-');
setText('predictionStation',firstSummary.station_name||'-');
setText('predictionCount','-');
setHtml('predictionTableBody','<tr><td colspan="2" class="text-center text-muted">Klik Generate untuk membuat prediksi.</td></tr>');
}
async function generatePrediction(){
const runId=predictionRunSelect.value;
if(!runId||!predictionStart.value||!predictionEnd.value){
window.alert('Run prediksi, waktu mulai, dan waktu akhir prediksi wajib diisi.');
return;
}
const originalLabel=generatePredictionButton.innerHTML;
generatePredictionButton.disabled=true;
generatePredictionButton.innerHTML='Memproses...';
try{
const formData=new FormData();
formData.set('run_id',runId);
formData.set('start_at',predictionStart.value);
formData.set('end_at',predictionEnd.value);
formData.set('interval_minutes',predictionInterval.value);
const adjustmentState=getPredictionAdjustmentState();
formData.set('adjustment_amplitude_percent',String(adjustmentState.amplitudePercent||0));
formData.set('adjustment_phase_degrees',String(adjustmentState.phaseDegrees||0));
formData.set('adjustment_p1_amplitude_percent',String(adjustmentState.p1AmplitudePercent||0));
formData.set('adjustment_p1_phase_degrees',String(adjustmentState.p1PhaseDegrees||0));
formData.set('adjustment_time_shift_hours',String(adjustmentState.timeShiftHours||0));
const response=await fetch('<?= site_url('admiralty/generate-prediction') ?>',{method:'POST',body:formData,headers:{Accept:'application/json'}});
const payload=await response.json();
if(!response.ok||!payload.success){throw new Error(payload.message||'Prediksi gagal dibuat.')}
const result=payload.result||{};
const meta=result.meta||{};
const rows=result.rows||[];
setText('predictionRunCode',meta.run_code||'-');
setText('predictionModel',meta.model_name||'-');
setText('predictionStation',meta.station_name||'-');
setText('predictionCount',String(meta.prediction_count||0));
setHtml('predictionTableBody',rows.length?rows.map(function(row){return '<tr><td>'+esc(row.datetime)+'</td><td>'+esc(row.water_level)+'</td></tr>'}).join(''):'<tr><td colspan="2" class="text-center text-muted">Tidak ada data prediksi.</td></tr>');
window.scrollTo({top:predictionArea.offsetTop-20,behavior:'smooth'});
}catch(error){
window.alert(error.message||'Terjadi kesalahan saat membuat prediksi.');
}finally{
generatePredictionButton.disabled=false;
generatePredictionButton.innerHTML=originalLabel;
}
}
async function openCalculationStage(){
if(!latestValidatedResult)return;
const selectedModels=getSelectedModels();
if(!selectedModels.length){window.alert('Pilih minimal satu model perhitungan.');return}
const originalLabel=startCalculationButton.innerHTML;
startCalculationButton.disabled=true;
startCalculationButton.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i>Menyiapkan...';
try{
const formData=new FormData();
selectedModels.forEach(function(model){formData.append('model_names[]',model)});
const response=await fetch('<?= site_url('admiralty/start-calculation') ?>',{method:'POST',body:formData,headers:{Accept:'application/json'}});
const payload=await response.json();
if(!response.ok||!payload.success){throw new Error(payload.message||'Tahap perhitungan gagal disiapkan.')}
const results=payload.results||[];
const result=results[0]||{};
const summary=result.summary||{};
const runMeta=result.run_meta||{};
comparisonChartVisibility={};
calculationArea.style.display='block';
calculationTables.style.display=results.length===1?'block':'none';
setValidationCardMinimized(true);
setText('workingTableTitle',result.working_table_title||'Tabel Kerja Admiralty');
setText('calcDatasetStatus',summary.dataset_status||'-');
setText('calcInterval',summary.interval_label||'-');
setText('calcCount',(summary.data_count||0)+' data');
setText('calcRange',(summary.start_at||'-')+' sampai '+(summary.end_at||'-'));
setHtml('calculationIntro','Dataset sudah lolos validasi awal dan engine tahap-1 berhasil membentuk tabel kerja observasi read-only untuk '+results.length+' model terpilih.<div class="mt-2"><span class="status-pill ok">Tabel kerja berhasil dibuat</span> <span class="status-pill ok">MSL berhasil dihitung</span></div>');
setText('componentDatasetStatus',summary.dataset_status||'-');
setText('componentMsl',typeof summary.msl!=='undefined'?String(summary.msl):'-');
setText('componentModelLabel',summary.model_label||'-');
setText('componentRunCode',runMeta.run_code||'-');
renderWorkingTable(result.working_columns||[],result.working_table||[]);
renderComponentTable(result.component_targets||[]);
const notes=(result.notes||[]).map(function(note){return '<div>'+esc(note)+'</div>'}).join('');
const datasetInfo=latestDatasetMeta?'<div class="mt-2"><strong>Dataset:</strong> '+esc(latestDatasetMeta.dataset_code)+' (ID '+esc(latestDatasetMeta.dataset_id)+')</div>':'';
const runInfo=runMeta.run_code?'<div><strong>Run:</strong> '+esc(runMeta.run_code)+' - '+esc(runMeta.model_name||'')+'</div>':'';
setHtml('calculationNotes',(notes||'Catatan tahap perhitungan akan muncul di sini.')+datasetInfo+runInfo);
renderMultiModelResults(results);
renderComparison(results);
preparePredictionPanel(results);
window.scrollTo({top:calculationArea.offsetTop-30,behavior:'smooth'});
}catch(error){
window.alert(error.message||'Terjadi kesalahan saat menyiapkan tahap perhitungan.');
}finally{
startCalculationButton.disabled=false;
startCalculationButton.innerHTML=originalLabel;
}
}
async function submitForm(event){
event.preventDefault();
if(!fileField.files.length){window.alert('Pilih file data terlebih dahulu.');return}
const originalLabel=validateButton.innerHTML;
validateButton.disabled=true;
validateButton.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i>Memvalidasi...';
const alertEl=document.getElementById('restoredSessionAlert');
if(alertEl){alertEl.style.display='none';}
resetCalculationPanels();
try{
const formData=new FormData(form);
const response=await fetch('<?= site_url('admiralty/validate-upload') ?>',{method:'POST',body:formData,headers:{Accept:'application/json'}});
const payload=await response.json();
if(!response.ok||!payload.success){throw new Error(payload.message||'Validasi gagal.')}
const result=payload.result;
saveFormDraft();
applyValidatedResult(result);
window.scrollTo({top:document.body.scrollHeight/3,behavior:'smooth'});
}catch(error){
window.alert(error.message||'Terjadi kesalahan saat memvalidasi file.');
}finally{
validateButton.disabled=false;
validateButton.innerHTML=originalLabel;
}
}
form.addEventListener('submit',submitForm);
document.querySelectorAll('.reset-admiralty-btn').forEach(function(btn){btn.addEventListener('click',resetAdmiraltyData);});
saveDatasetButton.addEventListener('click',saveDataset);
startCalculationButton.addEventListener('click',openCalculationStage);
validationCardToggle.addEventListener('click',function(){setValidationCardMinimized(!isValidationCardMinimized)});
generatePredictionButton.addEventListener('click',generatePrediction);
predictionRunSelect.addEventListener('change',syncPredictionRunMeta);
if(exportWorkbookPdfButton){exportWorkbookPdfButton.addEventListener('click',exportWorkbookPdf);}
if(exportComparisonCsvButton){exportComparisonCsvButton.addEventListener('click',exportComparisonCsv);}
if(indonesiaAmplitudeAdjust){indonesiaAmplitudeAdjust.addEventListener('input',function(){indonesiaAdjustmentState.amplitudePercent=num(this.value,0);syncIndonesiaAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(indonesiaPhaseAdjust){indonesiaPhaseAdjust.addEventListener('input',function(){indonesiaAdjustmentState.phaseDegrees=num(this.value,0);syncIndonesiaAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(indonesiaP1AmplitudeAdjust){indonesiaP1AmplitudeAdjust.addEventListener('input',function(){indonesiaAdjustmentState.p1AmplitudePercent=num(this.value,0);syncIndonesiaAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(indonesiaP1PhaseAdjust){indonesiaP1PhaseAdjust.addEventListener('input',function(){indonesiaAdjustmentState.p1PhaseDegrees=num(this.value,0);syncIndonesiaAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(indonesiaTimeShiftMinus){indonesiaTimeShiftMinus.addEventListener('click',function(){indonesiaAdjustmentState.timeShiftHours-=1;syncIndonesiaAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(indonesiaTimeShiftPlus){indonesiaTimeShiftPlus.addEventListener('click',function(){indonesiaAdjustmentState.timeShiftHours+=1;syncIndonesiaAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(resetIndonesiaAdjustmentButton){resetIndonesiaAdjustmentButton.addEventListener('click',function(){resetIndonesiaAdjustmentState();rerenderIndonesiaAdjustment();});}
if(hidrosAmplitudeAdjust){hidrosAmplitudeAdjust.addEventListener('input',function(){hidrosAdjustmentState.amplitudePercent=num(this.value,0);syncHidrosAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(hidrosPhaseAdjust){hidrosPhaseAdjust.addEventListener('input',function(){hidrosAdjustmentState.phaseDegrees=num(this.value,0);syncHidrosAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(hidrosP1AmplitudeAdjust){hidrosP1AmplitudeAdjust.addEventListener('input',function(){hidrosAdjustmentState.p1AmplitudePercent=num(this.value,0);syncHidrosAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(hidrosP1PhaseAdjust){hidrosP1PhaseAdjust.addEventListener('input',function(){hidrosAdjustmentState.p1PhaseDegrees=num(this.value,0);syncHidrosAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(hidrosTimeShiftMinus){hidrosTimeShiftMinus.addEventListener('click',function(){hidrosAdjustmentState.timeShiftHours-=1;syncHidrosAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(hidrosTimeShiftPlus){hidrosTimeShiftPlus.addEventListener('click',function(){hidrosAdjustmentState.timeShiftHours+=1;syncHidrosAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(resetHidrosAdjustmentButton){resetHidrosAdjustmentButton.addEventListener('click',function(){resetHidrosAdjustmentState();rerenderIndonesiaAdjustment();});}
if(catAAmplitudeAdjust){catAAmplitudeAdjust.addEventListener('input',function(){catAAdjustmentState.amplitudePercent=num(this.value,0);syncCatAAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(catAPhaseAdjust){catAPhaseAdjust.addEventListener('input',function(){catAAdjustmentState.phaseDegrees=num(this.value,0);syncCatAAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(catAP1AmplitudeAdjust){catAP1AmplitudeAdjust.addEventListener('input',function(){catAAdjustmentState.p1AmplitudePercent=num(this.value,0);syncCatAAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(catAP1PhaseAdjust){catAP1PhaseAdjust.addEventListener('input',function(){catAAdjustmentState.p1PhaseDegrees=num(this.value,0);syncCatAAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(catATimeShiftMinus){catATimeShiftMinus.addEventListener('click',function(){catAAdjustmentState.timeShiftHours-=1;syncCatAAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(catATimeShiftPlus){catATimeShiftPlus.addEventListener('click',function(){catAAdjustmentState.timeShiftHours+=1;syncCatAAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(resetCatAAdjustmentButton){resetCatAAdjustmentButton.addEventListener('click',function(){resetCatAAdjustmentState();rerenderIndonesiaAdjustment();});}
if(leastSquareAmplitudeAdjust){leastSquareAmplitudeAdjust.addEventListener('input',function(){leastSquareAdjustmentState.amplitudePercent=num(this.value,0);syncLeastSquareAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(leastSquarePhaseAdjust){leastSquarePhaseAdjust.addEventListener('input',function(){leastSquareAdjustmentState.phaseDegrees=num(this.value,0);syncLeastSquareAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(leastSquareP1AmplitudeAdjust){leastSquareP1AmplitudeAdjust.addEventListener('input',function(){leastSquareAdjustmentState.p1AmplitudePercent=num(this.value,0);syncLeastSquareAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(leastSquareP1PhaseAdjust){leastSquareP1PhaseAdjust.addEventListener('input',function(){leastSquareAdjustmentState.p1PhaseDegrees=num(this.value,0);syncLeastSquareAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(leastSquareTimeShiftMinus){leastSquareTimeShiftMinus.addEventListener('click',function(){leastSquareAdjustmentState.timeShiftHours-=1;syncLeastSquareAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(leastSquareTimeShiftPlus){leastSquareTimeShiftPlus.addEventListener('click',function(){leastSquareAdjustmentState.timeShiftHours+=1;syncLeastSquareAdjustmentUi();rerenderIndonesiaAdjustment();});}
if(resetLeastSquareAdjustmentButton){resetLeastSquareAdjustmentButton.addEventListener('click',function(){resetLeastSquareAdjustmentState();rerenderIndonesiaAdjustment();});}
modelCheckboxes.forEach(function(input){input.addEventListener('change',updateStartCalculationState);});
stationNameField.addEventListener('input',saveFormDraft);
latitudeField.addEventListener('input',saveFormDraft);
longitudeField.addEventListener('input',saveFormDraft);
timezoneField.addEventListener('change',saveFormDraft);
syncIndonesiaAdjustmentUi();
syncHidrosAdjustmentUi();
syncCatAAdjustmentUi();
syncLeastSquareAdjustmentUi();
resetCalculationPanels();
restoreFormDraft();
if(initialValidatedResult&&typeof initialValidatedResult==='object'){
applyValidatedResult(initialValidatedResult);
const alertEl=document.getElementById('restoredSessionAlert');
if(alertEl){alertEl.style.display='block';}
}
if(initialDatasetMeta&&typeof initialDatasetMeta==='object'){
applyDatasetMeta(initialDatasetMeta);
}
})();
</script>
</body>
</html>
