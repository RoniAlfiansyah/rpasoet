<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
:root{--tone-900:#09131f;--tone-800:#10253b;--tone-700:#153756;--tone-500:#0f766e;--tone-400:#169188;--tone-100:#edf7f6;--line:#d9e3ea;--panel:#ffffff;--soft:#6b7a8c;--bg:#eef3f7}
body{font-family:"Segoe UI",Tahoma,sans-serif;background:var(--bg);color:#1f2937}
.brand-link{display:flex;align-items:center;font-weight:800}
.brand-link .brand-image{float:none;max-height:none;width:2.1rem;height:2.1rem;line-height:2.1rem;text-align:center;margin-right:.65rem;margin-left:0;font-size:1.05rem;border-radius:.55rem;background:rgba(255,255,255,.12)}
.user-panel{align-items:flex-start!important;gap:.75rem}
.user-panel .image{flex:0 0 auto}
.user-panel .info{flex:1 1 auto;min-width:0;padding-top:.1rem!important}
.user-panel .info span{white-space:normal;overflow-wrap:anywhere;line-height:1.35;font-size:.98rem}
.main-sidebar{background:linear-gradient(180deg,var(--tone-900),var(--tone-800) 50%,var(--tone-700))!important}
.main-sidebar,.main-sidebar .brand-link,.main-sidebar .nav-link,.content-wrapper,.main-header{transition:margin-left .22s ease,width .22s ease,transform .22s ease}
.sidebar-mini.sidebar-collapse .brand-text,.sidebar-mini.sidebar-collapse .user-panel .info,.sidebar-mini.sidebar-collapse .nav-sidebar .nav-link p{opacity:0;transition:opacity .12s ease}
.sidebar-mini .brand-text,.sidebar-mini .user-panel .info,.sidebar-mini .nav-sidebar .nav-link p{opacity:1;transition:opacity .18s ease}
.sidebar-mini.sidebar-collapse .main-sidebar .nav-link{justify-content:center}
.sidebar-mini.sidebar-collapse .main-sidebar .nav-icon{margin-right:0!important}
.content-wrapper{background:
radial-gradient(circle at top left,rgba(15,118,110,.12),transparent 28%),
radial-gradient(circle at top right,rgba(17,94,89,.08),transparent 26%),
linear-gradient(180deg,#f4f7fb,#eaf0f5)}
.content-header h1{font-weight:800}
.nav-sidebar .nav-link{border-radius:.6rem;margin-bottom:.2rem}
.nav-sidebar .nav-link.active{background:linear-gradient(135deg,#0f766e,#169188)!important;color:#fff}
.topbar-meta{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
.topbar-chip{display:inline-flex;align-items:center;gap:.45rem;padding:.38rem .75rem;border-radius:999px;background:#edf7f6;border:1px solid rgba(15,118,110,.15);font-size:.78rem;color:#0f766e;font-weight:700}
.hero-card{position:relative;background:linear-gradient(135deg,#08111c,#0f2d46 48%,#0f766e 100%)!important;color:#fff;border:0;box-shadow:0 28px 50px rgba(15,23,42,.18);overflow:hidden}
.hero-card:before,.hero-card:after{content:"";position:absolute;border-radius:999px;pointer-events:none}
.hero-card:before{width:280px;height:280px;right:-70px;top:-70px;background:radial-gradient(circle,rgba(255,255,255,.14),transparent 66%)}
.hero-card:after{width:220px;height:220px;left:-60px;bottom:-80px;background:radial-gradient(circle,rgba(255,255,255,.12),transparent 70%)}
.hero-card .card-body{position:relative;padding:2rem 2rem 1.7rem}
.hero-card h1{font-weight:800;letter-spacing:-.03em;margin-bottom:.9rem;color:#ffffff;font-size:2rem}
.hero-card p{max-width:760px;color:rgba(255,255,255,.88);line-height:1.7;margin-bottom:0;font-size:1rem}
.hero-top{display:flex;justify-content:space-between;gap:1.2rem;align-items:flex-end;flex-wrap:wrap}
.hero-copy{max-width:820px}
.hero-badges,.hero-foot{display:flex;gap:.65rem;flex-wrap:wrap;align-items:center}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.52rem .85rem;border-radius:999px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.18);font-size:.8rem;font-weight:700}
.hero-credit{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border-radius:999px;background:rgba(8,17,28,.26);border:1px solid rgba(255,255,255,.14);font-size:.84rem;color:#e6f4f1}
.hero-credit strong{color:#fff}
.section-label{display:inline-flex;align-items:center;gap:.45rem;margin-bottom:.85rem;padding:.38rem .7rem;border-radius:999px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#d8f3ef;font-weight:700}
.card{border:0;border-radius:1rem;box-shadow:0 14px 34px rgba(15,23,42,.065);background:var(--panel)}
.card-header{background:#fff;border-bottom:1px solid #edf2f7;padding:1rem 1.15rem}
.card-title{font-weight:800;color:#0f172a}
.control-card{border-top:4px solid var(--tone-500)}
.header-stack{display:flex;flex-direction:column;align-items:flex-start;gap:.35rem}
.section-subtitle{font-size:.88rem;color:var(--soft);margin:0}
.form-group label{font-size:.76rem;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#64748b}
.form-control,.select-trigger{min-height:48px;border-radius:.8rem;border-color:var(--line);box-shadow:none;background:#fff}
.form-control:focus,.select-trigger:focus{border-color:var(--tone-500);box-shadow:0 0 0 .22rem rgba(15,118,110,.12)}
.date-input-wrap{position:relative}
.date-input-wrap .form-control{padding-right:3rem}
.date-input-icon{position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:#475569;font-size:1rem;cursor:pointer}
.flatpickr-calendar{border-radius:.9rem;border:1px solid #d9e3ea;box-shadow:0 18px 42px rgba(15,23,42,.16)}
.flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange,.flatpickr-day.selected:hover{background:var(--tone-500);border-color:var(--tone-500)}
.flatpickr-day.today{border-color:var(--tone-400)}
.searchable-select{position:relative}
.select-trigger{width:100%;color:#1f2937;text-align:left;padding:.72rem .92rem;cursor:pointer;font-weight:600}
.select-trigger:after{content:"\f078";float:right;font-family:"Font Awesome 5 Free";font-weight:900;color:#6b7280}
.select-menu{position:absolute;top:calc(100% + 8px);left:0;right:0;z-index:1050;display:none;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:.95rem;box-shadow:0 22px 40px rgba(15,23,42,.16);padding:.85rem}
.select-menu.open{display:block}
.select-options{max-height:260px;overflow:auto;border:1px solid #edf2f7;border-radius:.75rem;margin-top:.75rem;padding:.3rem}
.select-option{width:100%;border:0;background:transparent;text-align:left;padding:.68rem .78rem;border-radius:.65rem;color:#334155;cursor:pointer;font-weight:600}
.select-option:hover,.select-option.active{background:#e8f7f4;color:var(--tone-500)}
.select-empty{color:#6c757d;text-align:center;padding:.85rem}
.btn-primary{background:var(--tone-500);border-color:var(--tone-500);box-shadow:0 14px 24px rgba(15,118,110,.18);border-radius:.8rem;font-weight:700}
.btn-primary:hover{background:#0b5f59;border-color:#0b5f59}
.btn-primary:disabled{background:#9ca3af;border-color:#9ca3af;box-shadow:none;cursor:not-allowed}
.btn-outline-secondary{border-radius:.8rem;font-weight:700}
.btn-default{border-radius:.8rem;border-color:var(--line);font-weight:700}
.small-box{border-radius:1rem;overflow:hidden;box-shadow:0 16px 28px rgba(15,23,42,.08)}
.small-box>.inner{padding:1.1rem 1.15rem}
.small-box h3{font-weight:800;letter-spacing:-.03em}
.small-box p{margin-bottom:0;font-weight:600;opacity:.95}
.small-box .icon{top:14px;right:14px}
.progress-log{max-height:320px;overflow:auto}
.log-item{border:1px solid #e5e7eb;border-radius:.82rem;padding:.88rem;margin-bottom:.8rem;background:#fff}
.log-item.success{border-color:#9ad9d1;background:#f4fbfa}
.log-item.error{border-color:#f0b6b1;background:#fff5f4}
.log-item:last-child{margin-bottom:0}
.log-meta{display:flex;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:.45rem}
.log-badges{display:flex;gap:.35rem;flex-wrap:wrap}
.badge{border-radius:999px;padding:.42rem .62rem;font-weight:700}
.badge-light{background:#eef2f7;color:#475569}
.chart-shell{position:relative;min-height:390px;border:1px solid #edf2f7;border-radius:.9rem;background:linear-gradient(180deg,#ffffff,#f7fbfd);overflow:hidden}
.chart-shell canvas{width:100%;height:390px;display:block}
.chart-tooltip{position:absolute;display:none;min-width:180px;padding:.7rem .8rem;border-radius:.85rem;background:rgba(9,19,31,.94);color:#f8fafc;box-shadow:0 18px 36px rgba(15,23,42,.24);pointer-events:none;z-index:3}
.chart-tooltip strong{display:block;font-size:.86rem;margin-bottom:.25rem}
.chart-tooltip .tooltip-value{font-size:1.1rem;font-weight:800;color:#8af3dc}
.chart-tooltip .tooltip-meta{font-size:.76rem;color:#cbd5e1;margin-top:.2rem}
.table thead th{font-size:.76rem;text-transform:uppercase;letter-spacing:.08em;color:#64748b;background:#f8fafc;border-bottom:1px solid #e5e7eb}
.table td,.table th{vertical-align:middle;padding:.9rem}
.table tbody tr:hover{background:#f8fbfd}
.table-striped tbody tr:nth-of-type(odd){background:rgba(15,23,42,.018)}
.mono{font-family:Consolas,"Courier New",monospace;font-size:.92rem}
.source-badge{min-width:88px;text-align:center}
.footer-panel{margin-top:1.25rem;padding:1rem 1.15rem;border-radius:1rem;background:linear-gradient(135deg,#ffffff,#f8fbfd);border:1px solid #e1e9f0;box-shadow:0 10px 24px rgba(15,23,42,.05)}
.footer-credit{display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;color:var(--soft);font-size:.92rem}
.footer-credit strong{color:#0f172a}
.footer-brand{display:inline-flex;align-items:center;gap:.55rem;font-weight:800;color:#0f172a}
.footer-note{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .75rem;border-radius:999px;background:#edf7f6;color:#0f766e;font-weight:700}
@media (max-width:768px){
.hero-top{display:block}
.hero-card .card-body{padding:1.45rem}
.hero-card h1{font-size:1.65rem}
.topbar-meta{display:none}
.footer-credit{font-size:.84rem}
}
@media (max-width:992px){
.content-wrapper{padding-bottom:1rem}
}
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
<ul class="navbar-nav">
<li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
<li class="nav-item d-none d-sm-inline-block"><a href="<?= site_url('tides') ?>" class="nav-link">Fetch Tide SRGI</a></li>
</ul>
<ul class="navbar-nav ml-auto topbar-meta topbar-extra">
<li class="nav-item mr-2"><span class="topbar-chip"><i class="fas fa-shield-alt"></i>Professional Monitoring Tool</span></li>
<li class="nav-item"><span class="topbar-chip"><i class="fas fa-user-circle"></i>Dibuat oleh Roni Alfiansyah</span></li>
</ul>
</nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
<a href="<?= site_url('tides') ?>" class="brand-link"><span class="brand-image elevation-2"><i class="fas fa-water"></i></span><span class="brand-text">R-Pasoet</span></a>
<div class="sidebar">
<div class="user-panel mt-3 pb-3 mb-3 d-flex"><div class="image"><div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-teal" style="width:34px;height:34px;"><i class="fas fa-tint"></i></div></div><div class="info"><span class="d-block text-white">Download Data Pasang Surut Stasiun BIG</span></div></div>
<nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false"><li class="nav-item"><a href="<?= site_url('tides') ?>" class="nav-link active"><i class="nav-icon fas fa-cloud-download-alt"></i><p>Fetch Tide SRGI</p></a></li><li class="nav-item"><a href="<?= site_url('admiralty') ?>" class="nav-link"><i class="nav-icon fas fa-chart-area"></i><p>Predictor</p></a></li></ul></nav>
</div>
</aside>
<div class="content-wrapper">
<section class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>Fetch Tide SRGI</h1></div></div></div></section>
<section class="content"><div class="container-fluid">
<div class="card hero-card shadow-sm mb-3"><div class="card-body"><div class="hero-top"><div class="hero-copy"><div class="section-label"><i class="fas fa-satellite-dish"></i>Ocean Observation Workspace</div><h1><?= esc($title) ?></h1><p>Dashboard ini dirancang sebagai tool profesional untuk pengambilan, inspeksi, dan visualisasi data pasang surut SRGI secara langsung. Operator dapat memilih stasiun, rentang tanggal, dan resolusi data tanpa bergantung pada penyimpanan database lokal.</p></div><div class="hero-foot"><span class="hero-badge"><i class="fas fa-wave-square"></i>Live Fetch dari SRGI</span><span class="hero-badge"><i class="fas fa-globe"></i>Filter Tanggal UTC</span><span class="hero-badge"><i class="fas fa-clock"></i>Resolusi 5-60 Menit</span></div></div></div></div>
<div class="card control-card"><div class="card-header"><div class="header-stack"><h3 class="card-title mb-0"><i class="fas fa-sliders-h mr-2"></i>Control Panel Pengambilan Data</h3><div class="section-subtitle">Atur stasiun, rentang tanggal, dan resolusi untuk memulai fetch data pasang surut.</div></div></div><div class="card-body"><form id="fetchForm"><div class="row">
<div class="col-lg-4"><div class="form-group"><label for="station">Stasiun</label><div class="searchable-select"><input type="hidden" name="station" id="station" value=""><button type="button" class="select-trigger" id="stationTrigger">Semua stasiun</button><div class="select-menu" id="stationMenu"><input type="search" id="stationSearch" class="form-control" placeholder="Cari kode atau nama stasiun"><div class="select-options" id="stationOptions"></div></div></div><small class="form-text text-muted" id="stationSearchInfo">Menampilkan semua stasiun.</small></div></div>
<div class="col-lg-2 col-md-4"><div class="form-group"><label for="date_from_display">Tanggal Mulai (UTC)</label><div class="date-input-wrap"><input type="text" id="date_from_display" class="form-control" inputmode="numeric" placeholder="dd/mm/yyyy" value=""><span class="date-input-icon" data-picker-target="date_from_display"><i class="far fa-calendar-alt"></i></span></div><input type="hidden" name="date_from" id="date_from" value="<?= esc($defaultFrom) ?>"></div></div>
<div class="col-lg-2 col-md-4"><div class="form-group"><label for="date_to_display">Tanggal Selesai (UTC)</label><div class="date-input-wrap"><input type="text" id="date_to_display" class="form-control" inputmode="numeric" placeholder="dd/mm/yyyy" value=""><span class="date-input-icon" data-picker-target="date_to_display"><i class="far fa-calendar-alt"></i></span></div><input type="hidden" name="date_to" id="date_to" value="<?= esc($today) ?>"></div></div>
<div class="col-lg-2 col-md-4"><div class="form-group"><label for="resolution">Resolusi</label><select name="resolution" id="resolution" class="form-control"><?php foreach ($resolutions as $resolution): ?><option value="<?= esc((string) $resolution) ?>" <?= $resolution === $defaultResolution ? 'selected' : '' ?>><?= esc((string) $resolution) ?> menit</option><?php endforeach; ?></select></div></div>
<div class="col-lg-2"><div class="form-group mb-0"><label class="d-none d-lg-block">&nbsp;</label><div class="d-flex"><button type="submit" class="btn btn-primary flex-fill mr-2" id="fetchButton"><i class="fas fa-cloud-download-alt mr-1"></i>Ambil Data</button><button type="button" class="btn btn-default" id="resetButton">Reset</button></div></div></div>
</div></form></div></div>
<div class="row">
<div class="col-lg col-md-6"><div class="small-box bg-info"><div class="inner"><h3 id="summaryRows">0</h3><p>Total titik data</p></div><div class="icon"><i class="fas fa-stream"></i></div></div></div>
<div class="col-lg col-md-6"><div class="small-box bg-success"><div class="inner"><h3 id="summaryTasks">0 / 0</h3><p>Task selesai</p></div><div class="icon"><i class="fas fa-tasks"></i></div></div></div>
<div class="col-lg col-md-6"><div class="small-box bg-warning"><div class="inner"><h3 id="summaryStatus">Belum</h3><p>Status fetch</p></div><div class="icon"><i class="fas fa-spinner"></i></div></div></div>
<div class="col-lg col-md-6"><div class="small-box bg-secondary"><div class="inner"><h3><?= $stationsSource === 'srgi' ? 'SRGI' : 'Config' ?></h3><p>Sumber daftar stasiun</p></div><div class="icon"><i class="fas fa-map-marked-alt"></i></div></div></div>
<div class="col-lg col-md-6"><div class="small-box bg-danger"><div class="inner"><h3><?= esc((string) $maxRangeDays) ?> Hari</h3><p>Batas sekali fetch</p></div><div class="icon"><i class="fas fa-calendar-alt"></i></div></div></div>
</div>
<div class="row">
<div class="col-lg-4"><div class="card card-outline card-info"><div class="card-header"><h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i>Progress Fetch</h3><div class="card-tools"><span class="badge badge-info" id="progressCounter">0%</span></div></div><div class="card-body"><p class="text-muted" id="progressStatus">Pilih stasiun dan rentang tanggal, lalu klik Ambil Data.</p><div class="progress mb-3"><div class="progress-bar bg-info" id="progressFill" style="width:0%">0%</div></div><div class="progress-log" id="progressLog"><div class="log-item"><div class="log-meta"><strong>Belum ada aktivitas</strong></div><div class="text-muted">Log fetch akan muncul di sini.</div></div></div></div></div></div>
<div class="col-lg-8"><div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Grafik Pasang Surut (UTC)</h3></div><div class="card-body"><p class="text-muted"><strong>Zona waktu grafik: UTC.</strong> Filter tanggal, label sumbu waktu, dan waktu pada tooltip mengikuti UTC seperti SRGI.</p><div id="chartEmpty" class="callout callout-light mb-0">Belum ada data untuk digambar.</div><div class="chart-shell" id="chartShell" style="display:none;"><canvas id="tidesChart" aria-label="Grafik pasang surut dalam UTC"></canvas><div class="chart-tooltip" id="chartTooltip"></div></div></div></div></div>
</div>
<div class="card card-outline card-secondary"><div class="card-header"><div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;"><h3 class="card-title mb-0"><i class="fas fa-table mr-2"></i>Tabel Data Pasang Surut</h3><button type="button" class="btn btn-outline-secondary btn-sm" id="exportCsvButton" disabled><i class="fas fa-file-csv mr-1"></i>Export CSV</button></div></div><div class="card-body p-0"><div id="tableEmpty" class="callout callout-light m-3">Belum ada data yang ditampilkan.</div><div class="table-responsive" id="tableWrap" style="display:none;"><table class="table table-striped table-hover mb-0"><thead><tr><th>Stasiun</th><th>Measured At UTC</th><th>Waktu Tampilan (WIB)</th><th>PRS1</th><th>ENC1</th><th>RAD1</th><th>Nilai Grafik</th><th>Sumber Grafik</th><th>Status</th></tr></thead><tbody id="tableBody"></tbody></table></div></div></div>
</div><div class="footer-panel"><div class="footer-credit"><div><span class="footer-brand"><i class="fas fa-water"></i>Pasang Surut SRGI</span><div>Tool visualisasi dan pengambilan data pasang surut untuk kebutuhan monitoring yang lebih profesional.</div></div><div class="footer-note"><i class="fas fa-user-edit"></i>Dibuat oleh Roni Alfiansyah</div></div></div></section></div>
<aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function(){const form=document.getElementById('fetchForm'),stationField=document.getElementById('station'),stationTrigger=document.getElementById('stationTrigger'),stationMenu=document.getElementById('stationMenu'),stationOptions=document.getElementById('stationOptions'),stationSearchField=document.getElementById('stationSearch'),stationSearchInfo=document.getElementById('stationSearchInfo'),dateFromField=document.getElementById('date_from'),dateToField=document.getElementById('date_to'),dateFromDisplayField=document.getElementById('date_from_display'),dateToDisplayField=document.getElementById('date_to_display'),resolutionField=document.getElementById('resolution'),fetchButton=document.getElementById('fetchButton'),resetButton=document.getElementById('resetButton'),exportCsvButton=document.getElementById('exportCsvButton'),progressFill=document.getElementById('progressFill'),progressCounter=document.getElementById('progressCounter'),progressStatus=document.getElementById('progressStatus'),progressLog=document.getElementById('progressLog'),summaryRows=document.getElementById('summaryRows'),summaryTasks=document.getElementById('summaryTasks'),summaryStatus=document.getElementById('summaryStatus'),chartShell=document.getElementById('chartShell'),chartEmpty=document.getElementById('chartEmpty'),chartTooltip=document.getElementById('chartTooltip'),tableWrap=document.getElementById('tableWrap'),tableEmpty=document.getElementById('tableEmpty'),tableBody=document.getElementById('tableBody'),canvas=document.getElementById('tidesChart'),maxRangeDays=<?= (int) $maxRangeDays ?>,defaultResolution=<?= (int) $defaultResolution ?>,stations=<?= json_encode(array_values($stations), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,allStationOptions=stations.slice();let chartRows=[];let currentRows=[];let fromPicker=null;let toPicker=null;let activeChartPointIndex=null;let chartDrawHandler=null;function e(v){return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;')}function n(v){if(v===null||v===''||typeof v==='undefined')return null;const p=Number(String(v).replace(',','.'));return Number.isFinite(p)?p:null}function j(v){const d=new Date(String(v).replace(' ','T')+'Z');if(Number.isNaN(d.getTime()))return v;return new Intl.DateTimeFormat('id-ID',{timeZone:'Asia/Jakarta',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(d)}function u(v){const d=new Date(String(v).replace(' ','T')+'Z');if(Number.isNaN(d.getTime()))return v;return new Intl.DateTimeFormat('id-ID',{timeZone:'UTC',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(d)+' UTC'}function displayDate(v){if(!v)return'';const p=v.split('-');return p.length===3?[p[2],p[1],p[0]].join('/'):v}function isoDate(v){const m=String(v).trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);if(!m)return'';const day=Number(m[1]),month=Number(m[2]),year=Number(m[3]);const d=new Date(year,month-1,day);if(d.getFullYear()!==year||d.getMonth()!==month-1||d.getDate()!==day)return'';return year+'-'+String(month).padStart(2,'0')+'-'+String(day).padStart(2,'0')}function syncDisplayDates(){dateFromDisplayField.value=displayDate(dateFromField.value);dateToDisplayField.value=displayDate(dateToField.value);if(fromPicker)fromPicker.setDate(dateFromField.value,false,'Y-m-d');if(toPicker)toPicker.setDate(dateToField.value,false,'Y-m-d')}function syncHiddenDate(hiddenField,displayField){const iso=isoDate(displayField.value);if(iso){hiddenField.value=iso;displayField.classList.remove('is-invalid');return true}displayField.classList.add('is-invalid');return false}function setupDatePicker(displayField,hiddenField){return flatpickr(displayField,{dateFormat:'d/m/Y',allowInput:true,defaultDate:hiddenField.value||null,onChange:function(selectedDates,dateStr,instance){const raw=selectedDates.length?instance.formatDate(selectedDates[0],'Y-m-d'):isoDate(dateStr);if(raw){hiddenField.value=raw;displayField.classList.remove('is-invalid')}},onClose:function(selectedDates,dateStr){if(!selectedDates.length&&dateStr){syncHiddenDate(hiddenField,displayField)}}})}function r(a,b){const dates=[],s=new Date(a+'T00:00:00'),t=new Date(b+'T00:00:00');while(s<=t){dates.push(s.getFullYear()+'-'+String(s.getMonth()+1).padStart(2,'0')+'-'+String(s.getDate()).padStart(2,'0'));s.setDate(s.getDate()+1)}return dates}function days(a,b){return Math.floor((new Date(b+'T00:00:00').getTime()-new Date(a+'T00:00:00').getTime())/86400000)+1}function setProgress(c,t,l){const p=t>0?Math.round(c/t*100):0;progressFill.style.width=p+'%';progressFill.textContent=p+'%';progressCounter.textContent=p+'%';summaryTasks.textContent=c+' / '+t;progressStatus.textContent=l}async function waitForRateLimit(minutes,station,date){let remainingSeconds=minutes*60;function updateCountdown(){const minutePart=Math.floor(remainingSeconds/60),secondPart=remainingSeconds%60;summaryStatus.textContent='Tunggu';progressStatus.textContent='HTTP 429 untuk '+station+' tanggal '+date+'. Mencoba lagi dalam '+minutePart+':'+String(secondPart).padStart(2,'0')+' ('+minutes+' menit).'}updateCountdown();await new Promise(resolve=>{const timer=setInterval(()=>{remainingSeconds--;updateCountdown();if(remainingSeconds<=0){clearInterval(timer);resolve()}},1000)})}function addLog(i){if(progressLog.children.length===1&&progressLog.textContent.indexOf('Belum ada aktivitas')!==-1)progressLog.innerHTML='';const gap=i.completeness?'<span class="badge '+(i.completeness.status==='complete'?'badge-success':'badge-warning')+'">'+(i.completeness.status==='complete'?'Lengkap':'Ada gap')+'</span><span class="badge badge-light">'+i.completeness.actual_points+'/'+i.completeness.expected_points+'</span>':'';const message=i.completeness?i.message+' - hilang '+i.completeness.missing_points+' titik':i.message;progressLog.insertAdjacentHTML('afterbegin','<div class="log-item '+e(i.status)+'"><div class="log-meta"><strong>'+e(i.station)+' - '+e(i.date)+'</strong><div class="log-badges"><span class="badge '+(i.status==='error'?'error':'')+'">'+(i.status==='error'?'Gagal':'Berhasil')+'</span>'+gap+'</div></div><div>'+e(message)+'</div></div>')}function renderTable(rows){if(!rows.length){tableWrap.style.display='none';tableEmpty.style.display='block';tableBody.innerHTML='';return}const s=rows.slice().sort((a,b)=>a.measured_at_utc.localeCompare(b.measured_at_utc));tableBody.innerHTML=s.map(row=>'<tr><td><span class="badge">'+e(row.station_code)+'</span></td><td class="mono">'+e(row.measured_at_utc)+'</td><td class="mono">'+e(j(row.measured_at_utc))+'</td><td class="mono">'+e(row.prs1??'')+'</td><td class="mono">'+e(row.enc1??'')+'</td><td class="mono">'+e(row.rad1??'')+'</td><td class="mono">'+e(row.water_level)+'</td><td><span class="badge badge-light">'+e(row.water_level_source||'-')+'</span></td><td><span class="badge '+(row.is_gap_fill?'badge-warning':'badge-success')+' source-badge">'+(row.is_gap_fill?'Gap Fill':'Data Asli')+'</span></td></tr>').join('');tableEmpty.style.display='none';tableWrap.style.display='block'}function line(ctx,pts,color){if(!pts.filter(p=>p!==null).length)return;ctx.beginPath();ctx.strokeStyle=color;ctx.lineWidth=2.4;let started=false;pts.forEach(p=>{if(p===null){started=false;return}if(!started){ctx.moveTo(p.x,p.y);started=true;return}ctx.lineTo(p.x,p.y)});ctx.stroke()}function renderChart(rows){chartRows=rows.slice().sort((a,b)=>a.measured_at_utc.localeCompare(b.measured_at_utc));activeChartPointIndex=null;chartTooltip.style.display='none';if(!chartRows.length){chartShell.style.display='none';chartEmpty.style.display='block';return}chartEmpty.style.display='none';chartShell.style.display='block';const ctx=canvas.getContext('2d'),dpr=window.devicePixelRatio||1;function draw(){const rect=chartShell.getBoundingClientRect(),width=Math.max(320,rect.width),height=360;canvas.width=width*dpr;canvas.height=height*dpr;ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,width,height);const pad={top:24,right:24,bottom:58,left:58},cw=width-pad.left-pad.right,ch=height-pad.top-pad.bottom,vals=chartRows.map(row=>n(row.water_level)).filter(v=>v!==null);if(!vals.length)return;let min=Math.min.apply(null,vals),max=Math.max.apply(null,vals);if(min===max){min-=1;max+=1}const extra=(max-min)*.08;min-=extra;max+=extra;ctx.strokeStyle='#d8cfbf';ctx.lineWidth=1;for(let i=0;i<=4;i++){const y=pad.top+(ch/4)*i;ctx.beginPath();ctx.moveTo(pad.left,y);ctx.lineTo(width-pad.right,y);ctx.stroke();ctx.fillStyle='#6b7280';ctx.font='12px Georgia';ctx.textAlign='right';ctx.fillText((max-((max-min)/4)*i).toFixed(2),pad.left-10,y+4)}ctx.strokeStyle='#a9a08f';ctx.beginPath();ctx.moveTo(pad.left,pad.top);ctx.lineTo(pad.left,height-pad.bottom);ctx.lineTo(width-pad.right,height-pad.bottom);ctx.stroke();const total=chartRows.length,pts=chartRows.map((row,index)=>{const value=n(row.water_level);if(value===null)return null;return{x:pad.left+(cw*index/Math.max(total-1,1)),y:pad.top+((max-value)/(max-min))*ch,index:index,row:row}});line(ctx,pts,'#0f766e');if(activeChartPointIndex!==null&&pts[activeChartPointIndex]){const activePoint=pts[activeChartPointIndex],activeRow=chartRows[activeChartPointIndex];ctx.strokeStyle='rgba(15,118,110,.28)';ctx.lineWidth=1.2;ctx.beginPath();ctx.moveTo(activePoint.x,pad.top);ctx.lineTo(activePoint.x,height-pad.bottom);ctx.stroke();ctx.fillStyle=activeRow.is_gap_fill?'#f59e0b':'#0f766e';ctx.beginPath();ctx.arc(activePoint.x,activePoint.y,5.5,0,Math.PI*2);ctx.fill();ctx.strokeStyle='#ffffff';ctx.lineWidth=2;ctx.stroke()}[0,Math.floor(total/2),total-1].filter((v,i,a)=>a.indexOf(v)===i).forEach(index=>{const x=pad.left+(cw*index/Math.max(total-1,1));ctx.fillStyle='#6b7280';ctx.font='12px Georgia';ctx.textAlign=index===0?'left':(index===total-1?'right':'center');ctx.fillText(u(chartRows[index].measured_at_utc),x,height-24)});canvas._chartPoints=pts;canvas._chartBounds={pad:pad,width:width,height:height}}chartDrawHandler=draw;draw();window.onresize=draw}function updateChartTooltip(index){if(index===null||!canvas._chartPoints||!canvas._chartPoints[index]){activeChartPointIndex=null;chartTooltip.style.display='none';if(chartDrawHandler)chartDrawHandler();return}activeChartPointIndex=index;const point=canvas._chartPoints[index],row=chartRows[index],shellRect=chartShell.getBoundingClientRect(),tooltipOffset=16;chartTooltip.innerHTML='<strong>'+e(row.station_code)+'</strong><div class=\"tooltip-value\">'+e(row.water_level)+'</div><div>'+e(u(row.measured_at_utc))+'</div><div class=\"tooltip-meta\">Zona waktu UTC &middot; '+(row.is_gap_fill?'Gap Fill':'Data Asli')+'</div>';chartTooltip.style.display='block';const tooltipWidth=chartTooltip.offsetWidth||180,tooltipHeight=chartTooltip.offsetHeight||84;let left=point.x+tooltipOffset,top=point.y-tooltipHeight-tooltipOffset;if(left+tooltipWidth>shellRect.width-12){left=point.x-tooltipWidth-tooltipOffset}if(top<12){top=point.y+tooltipOffset}chartTooltip.style.left=Math.max(12,left)+'px';chartTooltip.style.top=Math.max(12,top)+'px';if(chartDrawHandler)chartDrawHandler()}function resetView(){chartRows=[];activeChartPointIndex=null;chartTooltip.style.display='none';summaryRows.textContent='0';summaryTasks.textContent='0 / 0';summaryStatus.textContent='Belum';setProgress(0,0,'Pilih stasiun dan rentang tanggal, lalu klik Ambil Data.');progressLog.innerHTML='<div class="log-item"><div class="log-meta"><strong>Belum ada aktivitas</strong></div><div class="text-muted">Log fetch akan muncul di sini.</div></div>';renderTable([]);renderChart([])}function stationLabel(code){if(!code)return 'Pilih stasiun';const m=allStationOptions.find(s=>s.code===code);return m?m.label:code}function updateFetchButtonState(){fetchButton.disabled=!stationField.value}function closeMenu(){stationMenu.classList.remove('open')}function openMenu(){stationMenu.classList.add('open');stationSearchField.focus();stationSearchField.select()}function setStation(code){stationField.value=code||'';stationTrigger.textContent=stationLabel(stationField.value);updateFetchButtonState()}function renderStations(list){const selected=stationField.value,options=['<div class="select-empty">Silakan pilih salah satu stasiun.</div>'];list.forEach(st=>options.push('<button type="button" class="select-option'+(st.code===selected?' active':'')+'" data-code="'+e(st.code)+'">'+e(st.label)+'</button>'));if(!list.length)options.push('<div class="select-empty">Tidak ada stasiun yang cocok.</div>');stationOptions.innerHTML=options.join('');stationSearchInfo.textContent=stationSearchField.value.trim()?'Menampilkan '+list.length+' stasiun yang cocok.':'Pilih satu stasiun untuk mulai mengambil data.'}function filterStations(){const k=stationSearchField.value.trim().toLowerCase();if(!k)return renderStations(allStationOptions);renderStations(allStationOptions.filter(st=>st.code.toLowerCase().indexOf(k)!==-1||st.label.toLowerCase().indexOf(k)!==-1))}async function fetchTask(station,date,resolution){const url=new URL('<?= site_url('tides/fetch-day') ?>',window.location.origin);url.searchParams.set('station',station);url.searchParams.set('date',date);url.searchParams.set('resolution',resolution);const response=await fetch(url.toString(),{headers:{'Accept':'application/json'}}),payload=await response.json();if(!response.ok||!payload.success)throw new Error(payload.message||'Gagal mengambil data.');return payload}form.addEventListener('submit',async function(event){event.preventDefault();if(!stationField.value)return alert('Pilih stasiun terlebih dahulu.');if(!syncHiddenDate(dateFromField,dateFromDisplayField)||!syncHiddenDate(dateToField,dateToDisplayField))return alert('Gunakan format tanggal dd/mm/yyyy.');const from=dateFromField.value,to=dateToField.value,resolution=resolutionField.value;if(!from||!to)return alert('Tanggal mulai dan tanggal selesai wajib diisi.');if(from>to)return alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');if(days(from,to)>maxRangeDays)return alert('Rentang tanggal maksimal '+maxRangeDays+' hari untuk sekali fetch.');const selectedStations=[stationField.value],selectedDates=r(from,to),tasks=[];selectedStations.forEach(st=>selectedDates.forEach(date=>tasks.push({station:st,date:date})));resetView();fetchButton.disabled=true;summaryStatus.textContent='Jalan';const allRows=new Map();for(let i=0;i<tasks.length;i++){const task=tasks[i];let rateLimitWaitIndex=0;while(true){setProgress(i,tasks.length,'Mengambil data '+task.station+' tanggal '+task.date+' pada resolusi '+resolution+' menit...');try{const payload=await fetchTask(task.station,task.date,resolution),rows=payload.rows||[],actualPoints=payload.completeness?payload.completeness.actual_points:rows.filter(row=>!row.is_gap_fill).length;rows.forEach(row=>allRows.set(row.station_code+'|'+row.measured_at_utc,row));addLog({station:task.station,date:task.date,status:'success',message:actualPoints+' data asli diterima pada resolusi '+resolution+' menit',completeness:payload.completeness||null});summaryStatus.textContent='Jalan';break}catch(error){const errorMessage=error.message||'Terjadi kesalahan';if(String(errorMessage).includes('HTTP 429')&&rateLimitWaitIndex<4){rateLimitWaitIndex++;await waitForRateLimit(rateLimitWaitIndex,task.station,task.date);summaryStatus.textContent='Jalan';continue}addLog({station:task.station,date:task.date,status:'error',message:errorMessage});if(String(errorMessage).includes('HTTP 429')){summaryStatus.textContent='429';progressStatus.textContent='Batch dihentikan karena SRGI masih memberikan HTTP 429 setelah jeda 1, 2, 3, dan 4 menit.';updateFetchButtonState();return}break}}const rows=Array.from(allRows.values());summaryRows.textContent=String(rows.length);renderTable(rows);renderChart(rows);setProgress(i+1,tasks.length,'Selesai memproses '+(i+1)+' dari '+tasks.length+' task.')}summaryStatus.textContent='Selesai';progressStatus.textContent='Fetch selesai. Data yang berhasil diterima pada resolusi '+resolution+' menit sudah ditampilkan di tabel dan grafik.';updateFetchButtonState()});resetButton.addEventListener('click',function(){form.reset();dateFromField.value='<?= esc($defaultFrom) ?>';dateToField.value='<?= esc($today) ?>';syncDisplayDates();dateFromDisplayField.classList.remove('is-invalid');dateToDisplayField.classList.remove('is-invalid');resolutionField.value=String(defaultResolution);stationSearchField.value='';setStation('');renderStations(allStationOptions);closeMenu();resetView()});[dateFromDisplayField,dateToDisplayField].forEach(function(field){field.addEventListener('blur',function(){syncHiddenDate(field===dateFromDisplayField?dateFromField:dateToField,field)});field.addEventListener('input',function(){field.classList.remove('is-invalid')})});document.querySelectorAll('[data-picker-target]').forEach(function(trigger){trigger.addEventListener('click',function(){const target=this.getAttribute('data-picker-target');if(target==='date_from_display'&&fromPicker)fromPicker.open();if(target==='date_to_display'&&toPicker)toPicker.open()})});canvas.addEventListener('mousemove',function(event){if(!canvas._chartPoints||!canvas._chartPoints.length)return;const rect=canvas.getBoundingClientRect(),x=event.clientX-rect.left;let nearestIndex=null,nearestDistance=Infinity;canvas._chartPoints.forEach(function(point,index){if(!point)return;const distance=Math.abs(point.x-x);if(distance<nearestDistance){nearestDistance=distance;nearestIndex=index}});updateChartTooltip(nearestIndex)});canvas.addEventListener('mouseleave',function(){updateChartTooltip(null)});stationTrigger.addEventListener('click',function(){if(stationMenu.classList.contains('open'))return closeMenu();openMenu()});stationSearchField.addEventListener('input',filterStations);stationOptions.addEventListener('click',function(event){const option=event.target.closest('.select-option');if(!option)return;setStation(option.getAttribute('data-code')||'');filterStations();closeMenu()});document.addEventListener('click',function(event){if(event.target.closest('.searchable-select'))return;closeMenu()});document.addEventListener('keydown',function(event){if(event.key==='Escape')closeMenu()});fromPicker=setupDatePicker(dateFromDisplayField,dateFromField);toPicker=setupDatePicker(dateToDisplayField,dateToField);setStation('');renderStations(allStationOptions);syncDisplayDates();resetView()})();
</script>
<script>
(function(){
const exportCsvButton=document.getElementById('exportCsvButton');
const tableBody=document.getElementById('tableBody');
const stationField=document.getElementById('station');
const dateFromField=document.getElementById('date_from');
const dateToField=document.getElementById('date_to');
if(!exportCsvButton||!tableBody)return;
function csvEscape(value){return '"'+String(value ?? '').replace(/"/g,'""')+'"'}
function updateExportState(){exportCsvButton.disabled=tableBody.querySelectorAll('tr').length===0}
function exportTableToCsv(){
const rows=Array.from(tableBody.querySelectorAll('tr')).sort(function(left,right){
const leftTimestamp=left.querySelectorAll('td')[1]?.innerText.trim()||'';
const rightTimestamp=right.querySelectorAll('td')[1]?.innerText.trim()||'';
return leftTimestamp.localeCompare(rightTimestamp);
});
if(!rows.length)return;
const header=['station_code','measured_at_utc','measured_at_local','local_timezone','filter_timezone','prs1','enc1','rad1','water_level','water_level_source','source_status'];
const lines=[header.map(csvEscape).join(',')];
rows.forEach(function(row){
const cells=row.querySelectorAll('td');
if(cells.length<9)return;
lines.push([
cells[0].innerText.trim(),
cells[1].innerText.trim(),
cells[2].innerText.trim(),
 'Asia/Jakarta',
 'UTC',
cells[3].innerText.trim(),
cells[4].innerText.trim(),
cells[5].innerText.trim(),
cells[6].innerText.trim(),
cells[7].innerText.trim(),
cells[8].innerText.trim()
].map(csvEscape).join(','));
});
const blob=new Blob([lines.join('\r\n')],{type:'text/csv;charset=utf-8;'});
const url=URL.createObjectURL(blob);
const stationCode=stationField.value||'data';
const fileName='pasang-surut-'+stationCode+'-'+dateFromField.value+'-sampai-'+dateToField.value+'.csv';
const link=document.createElement('a');
link.href=url;
link.download=fileName;
document.body.appendChild(link);
link.click();
document.body.removeChild(link);
URL.revokeObjectURL(url);
}
new MutationObserver(updateExportState).observe(tableBody,{childList:true,subtree:true});
exportCsvButton.addEventListener('click',exportTableToCsv);
updateExportState();
})();
</script>
</body>
</html>



