<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Akses Sistem R-PASOET | PT. Eser Geosurvey Indonesia') ?></title>
  
  <!-- Tailwind CSS & Google Fonts -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .bg-ocean-mesh {
      background-color: #0b1329;
      background-image: 
        radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.22) 0px, transparent 50%),
        radial-gradient(at 100% 0%, rgba(20, 184, 166, 0.18) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(30, 58, 138, 0.35) 0px, transparent 50%),
        radial-gradient(at 0% 100%, rgba(2, 132, 199, 0.25) 0px, transparent 50%);
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }
    .glow-cyan {
      box-shadow: 0 0 40px -10px rgba(14, 165, 233, 0.35);
    }
  </style>
</head>
<body class="bg-ocean-mesh min-h-screen flex items-center justify-center p-4 antialiased text-slate-800">

  <div class="w-full max-w-md flex flex-col gap-6">

    <!-- Card Container -->
    <div class="glass-card rounded-3xl p-8 sm:p-10 shadow-2xl border border-white/60 glow-cyan">
      
      <!-- Logo & Branding -->
      <div class="flex flex-col items-center text-center mb-7">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 via-cyan-600 to-teal-500 flex items-center justify-center text-white text-2xl shadow-lg shadow-sky-600/30 mb-4 ring-4 ring-sky-100/60">
          <i class="fa-solid fa-water"></i>
        </div>
        
        <div class="flex items-center gap-2 mb-1">
          <span class="text-2xl font-black tracking-tight text-slate-900">R-PASOET</span>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 border border-sky-200">v2.1</span>
        </div>
        
        <div class="text-xs font-bold uppercase tracking-wider text-sky-700">PT. ESER GEOSURVEY INDONESIA</div>
        <p class="text-xs text-slate-500 mt-1">Hydro-Oceanography & Marine Survey Division</p>
      </div>

      <!-- Flash Notifications -->
      <?php if (!empty($authError)): ?>
        <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5 animate-in fade-in duration-200">
          <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm mt-0.5 shrink-0"></i>
          <div>
            <strong class="font-bold">Akses Ditolak:</strong>
            <div class="mt-0.5 leading-relaxed"><?= esc($authError) ?></div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($authSuccess)): ?>
        <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-2.5 animate-in fade-in duration-200">
          <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
          <div>
            <strong class="font-bold">Berhasil:</strong>
            <div class="mt-0.5 leading-relaxed"><?= esc($authSuccess) ?></div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($authInfo)): ?>
        <div class="mb-5 p-3.5 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-start gap-2.5 animate-in fade-in duration-200">
          <i class="fa-solid fa-circle-info text-sky-600 text-sm mt-0.5 shrink-0"></i>
          <div>
            <strong class="font-bold">Informasi:</strong>
            <div class="mt-0.5 leading-relaxed"><?= esc($authInfo) ?></div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form action="<?= site_url('login') ?>" method="POST" class="flex flex-col gap-4">
        <?= csrf_field() ?>

        <!-- Surveyor Name Field -->
        <div>
          <label for="user_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
            <span>Nama Surveyor / Personel</span>
            <span class="text-[10px] text-slate-400 font-normal">Opsional</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-user text-xs"></i>
            </span>
            <input 
              type="text" 
              name="user_name" 
              id="user_name" 
              value="<?= esc(old('user_name', '')) ?>" 
              placeholder="Contoh: Surveyor Hidrografi" 
              class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition shadow-xs"
            >
          </div>
          <p class="text-[10px] text-slate-400 mt-1">Nama ini akan tercantum di lembar pengesahan laporan resmi A4.</p>
        </div>

        <!-- Passcode Field -->
        <div>
          <label for="passcode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
            <span>Passcode Akses Kantor</span>
            <span class="text-[10px] text-rose-500 font-semibold">*Wajib</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-key text-xs"></i>
            </span>
            <input 
              type="password" 
              name="passcode" 
              id="passcode" 
              required 
              autofocus
              placeholder="Masukkan passcode kantor..." 
              class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition shadow-xs font-mono"
            >
            <button 
              type="button" 
              id="togglePasswordBtn" 
              class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
              title="Tampilkan / Sembunyikan Passcode"
            >
              <i class="fa-solid fa-eye text-xs" id="togglePasswordIcon"></i>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          id="submitLoginBtn"
          class="mt-2 w-full bg-gradient-to-r from-sky-600 via-cyan-600 to-sky-700 hover:from-sky-700 hover:to-sky-800 text-white font-bold text-sm py-3 px-4 rounded-xl shadow-lg shadow-sky-600/30 hover:shadow-sky-600/40 transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
          <i class="fa-solid fa-unlock-keyhole"></i>
          <span>Buka Akses Sistem</span>
        </button>

      </form>

      <!-- Security Notice -->
      <div class="mt-6 pt-5 border-t border-slate-100 flex items-start gap-2.5 text-[11px] text-slate-500 leading-relaxed">
        <i class="fa-solid fa-shield-halved text-sky-600 mt-0.5 shrink-0"></i>
        <span>Sistem ini diproteksi untuk operasional internal hydro-oceanography PT. Eser Geosurvey Indonesia. Gunakan passcode resmi kantor.</span>
      </div>

    </div>

    <!-- Footer Copyright -->
    <div class="text-center text-xs text-slate-400">
      &copy; <?= date('Y') ?> <strong>PT. Eser Geosurvey Indonesia</strong>. All Rights Reserved.
    </div>

  </div>

  <script>
    // Toggle Password Visibility
    const passcodeField = document.getElementById('passcode');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && passcodeField && toggleIcon) {
      toggleBtn.addEventListener('click', function() {
        const isPassword = passcodeField.type === 'password';
        passcodeField.type = isPassword ? 'text' : 'password';
        toggleIcon.className = isPassword ? 'fa-solid fa-eye-slash text-xs' : 'fa-solid fa-eye text-xs';
      });
    }

    // Submit state
    const submitBtn = document.getElementById('submitLoginBtn');
    if (submitBtn) {
      submitBtn.closest('form').addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Memverifikasi Passcode...';
      });
    }
  </script>
</body>
</html>
