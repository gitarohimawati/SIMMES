<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#b1002c">
<title>@yield('title') · {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600&display=swap" rel="stylesheet">
<style>
  :root {
    --red-900: #40000a;
    --red-800: #920022;
    --red-700: #b1002c;
    --red-600: #dc143c;
    --red-500: #ffb3b3;
    --red-100: #ffdad9;
    --red-50:  #fff1f0;
    --slate-900: #1b1c1c;
    --slate-800: #303030;
    --slate-700: #474646;
    --slate-600: #5f5e5e;
    --slate-400: #777575;
    --slate-300: #c8c6c5;
    --slate-200: #eae8e7;
    --slate-100: #efeded;
    --slate-50:  #f5f3f3;
  }
  *, *::before, *::after { box-sizing: border-box; }
  html, body { min-height: 100%; margin: 0; }
  body {
    background: #fbf9f8;
    color: var(--slate-900);
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    position: relative;
    overflow-x: hidden;
  }

  .auth {
    position: relative;
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 32px 20px;
    background: #ffffff;
  }

  /* SVG Artwork Latar Belakang Merah & Putih */
  .bg-art {
    position: absolute;
    pointer-events: none;
    z-index: 0;
  }
  .wave-top-left {
    top: 0;
    left: 0;
    width: clamp(260px, 45vw, 560px);
    height: clamp(220px, 40vh, 440px);
    animation: driftWaveA 14s ease-in-out infinite;
    filter: drop-shadow(0 12px 28px rgba(220, 38, 38, 0.16));
  }
  .wave-bottom-right {
    bottom: 0;
    right: 0;
    width: clamp(320px, 50vw, 700px);
    height: clamp(250px, 44vh, 520px);
    animation: driftWaveB 16s ease-in-out infinite;
    filter: drop-shadow(0 -10px 28px rgba(220, 38, 38, 0.14));
  }

  @keyframes driftWaveA {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-6px, 6px); }
  }
  @keyframes driftWaveB {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(6px, -6px); }
  }

  /* Aksen Dekoratif Geometris (Garis Diagonal & Titik-Titik) */
  .deco {
    position: absolute;
    pointer-events: none;
    z-index: 1;
  }
  .deco-tr-circle {
    top: 4%;
    right: 28%;
    width: clamp(50px, 6.5vw, 80px);
    height: clamp(50px, 6.5vw, 80px);
  }
  .deco-tr-dots {
    top: 6%;
    right: 4%;
    width: 90px;
    height: 45px;
  }
  .deco-bl-circle {
    bottom: 16%;
    left: 3%;
    width: clamp(60px, 7vw, 90px);
    height: clamp(60px, 7vw, 90px);
  }
  .deco-bl-dots {
    bottom: 4%;
    left: 4%;
    width: 90px;
    height: 45px;
  }
  .deco-bc-circle {
    bottom: 6%;
    left: 38%;
    width: 64px;
    height: 64px;
    opacity: 0.7;
  }

  /* Kotak Form Utama */
  .wrap {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 430px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 20px;
    padding: 38px 32px 34px;
    box-shadow: 0 20px 45px -12px rgba(220, 38, 38, 0.12),
                0 8px 24px -4px rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(226, 232, 240, 0.9);
  }

  /* Badge Brand SIMMES */
  .brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--red-50);
    color: var(--red-600);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .12em;
    padding: 5px 12px;
    border-radius: 999px;
    margin-bottom: 12px;
    border: 1px solid var(--red-100);
  }
  .brand-badge svg {
    width: 14px;
    height: 14px;
  }

  .title {
    margin: 0 0 22px;
    font-size: 26px;
    font-family: 'Poppins', 'Inter', sans-serif;
    font-weight: 600;
    color: var(--slate-900);
    letter-spacing: -0.025em;
  }

  /* Field & Elemen Form */
  .card { display: flex; flex-direction: column; gap: 16px; }
  .field { display: flex; flex-direction: column; gap: 6px; }
  .field label { font-size: 13px; font-weight: 600; color: var(--slate-700); }
  .field input, .field select {
    width: 100%;
    min-height: 46px;
    border: 1.5px solid var(--slate-200);
    border-radius: 10px;
    background: var(--slate-50);
    color: var(--slate-900);
    padding: 0 14px;
    font: inherit;
    font-size: 14.5px;
    transition: all .2s ease;
  }
  .field input::placeholder { color: var(--slate-400); }
  .field input:focus, .field select:focus {
    outline: none;
    background: #ffffff;
    border-color: var(--red-600);
    box-shadow: 0 0 0 3.5px rgba(220, 38, 38, 0.15);
  }
  .field select { cursor: pointer; }

  /* Tombol Lihat Sandi */
  .pw { position: relative; }
  .pw input { padding-right: 44px; }
  .pw button {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    border: 0;
    background: none;
    color: var(--slate-400);
    cursor: pointer;
    display: grid;
    place-items: center;
    border-radius: 8px;
    transition: color .15s;
  }
  .pw button:hover { color: var(--red-600); }
  .pw button:focus-visible, .btn:focus-visible, .alt a:focus-visible {
    outline: 2px solid var(--red-600);
    outline-offset: 2px;
  }
  .pw svg { width: 19px; height: 19px; }

  /* Notifikasi & Pesan Error */
  .alert {
    background: linear-gradient(135deg, #fff5f6 0%, #ffe9ec 100%);
    border: 1px solid #fecdd3;
    border-left: 4px solid #b1002c;
    color: #880022;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.5;
    font-weight: 600;
  }
  .alert p { margin: 0; }
  .notice {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
  }

  /* Tombol Utama */
  .btn {
    min-height: 48px;
    border: 0;
    border-radius: 10px;
    background: var(--red-700);
    color: #ffffff;
    font: inherit;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
    transition: all .2s ease;
    margin-top: 4px;
  }
  .btn:hover {
    background: var(--red-900);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45);
    transform: translateY(-1px);
  }
  .btn:active {
    transform: translateY(0);
  }

  /* Link Bawah */
  .alt { margin: 6px 0 0; text-align: center; font-size: 13.5px; color: var(--slate-600); }
  .alt a { color: var(--red-600); text-decoration: none; font-weight: 700; transition: color .15s; }
  .alt a:hover { color: var(--red-800); text-decoration: underline; }

  @media (max-width: 480px) {
    .wrap {
      padding: 30px 22px;
      border-radius: 16px;
    }
    .title {
      font-size: 22px;
      margin-bottom: 18px;
    }
  }
  @media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
  }
</style>
</head>
<body>

<!-- Definisi Pattern & Gradient SVG Bersama -->
<svg width="0" height="0" style="position:absolute;visibility:hidden" aria-hidden="true">
  <defs>
    <!-- Pattern Garis Diagonal Abu-abu -->
    <pattern id="diag-stripes" width="10" height="10" patternTransform="rotate(45 0 0)" patternUnits="userSpaceOnUse">
      <line x1="0" y1="0" x2="0" y2="10" stroke="#cbd5e1" stroke-width="2.5" />
    </pattern>

    <!-- Pattern Titik-Titik Abu-abu -->
    <pattern id="dot-grid" width="16" height="16" patternUnits="userSpaceOnUse">
      <circle cx="3" cy="3" r="1.8" fill="#cbd5e1" />
    </pattern>

    <!-- Pattern Titik-Titik Putih di atas Merah -->
    <pattern id="dot-grid-white" width="14" height="14" patternUnits="userSpaceOnUse">
      <circle cx="3" cy="3" r="1.5" fill="#ffffff" opacity="0.45" />
    </pattern>

    <!-- Gradient Merah Terang (Crimson ke Merah Menyala) -->
    <linearGradient id="redGradMain" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#b91c1c" />
      <stop offset="55%" stop-color="#dc2626" />
      <stop offset="100%" stop-color="#ef4444" />
    </linearGradient>

    <!-- Gradient Merah Gelap untuk Layer Bayangan -->
    <linearGradient id="redGradDark" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#7f1d1d" />
      <stop offset="100%" stop-color="#991b1b" />
    </linearGradient>

    <!-- Gradient Merah Bawah Kanan -->
    <linearGradient id="redGradBR" x1="0%" y1="100%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#dc2626" />
      <stop offset="100%" stop-color="#ef4444" />
    </linearGradient>
  </defs>
</svg>

<main class="auth">
  <!-- Gelombang Merah Kiri Atas (Top-Left Waves) -->
  <svg class="bg-art wave-top-left" viewBox="0 0 540 420" preserveAspectRatio="none" aria-hidden="true">
    <!-- Layer Merah Gelap di Belakang -->
    <path d="M0,0 L440,0 C390,75 350,120 300,140 C235,168 175,160 125,215 C75,270 50,335 0,390 Z" fill="url(#redGradDark)" />
    <!-- Layer Merah Menyala di Depan -->
    <path d="M0,0 L370,0 C325,60 285,95 245,112 C185,134 145,152 98,205 C50,258 28,305 0,350 Z" fill="url(#redGradMain)" />
    <!-- Matriks Titik Putih -->
    <rect x="70" y="32" width="84" height="42" fill="url(#dot-grid-white)" />
  </svg>

  <!-- Gelombang Merah & Abu-abu Kanan Bawah (Bottom-Right Waves) -->
  <svg class="bg-art wave-bottom-right" viewBox="0 0 680 500" preserveAspectRatio="none" aria-hidden="true">
    <!-- Layer Abu-abu Halus -->
    <path d="M680,160 C560,180 490,270 395,305 C295,345 200,355 105,430 C55,460 20,490 0,500 L680,500 Z" fill="#f1f5f9" />
    <!-- Layer Merah Gelap -->
    <path d="M680,250 C580,268 510,355 395,380 C290,405 230,430 175,500 L680,500 Z" fill="url(#redGradDark)" />
    <!-- Layer Merah Menyala -->
    <path d="M680,310 C590,328 530,405 435,420 C350,435 300,460 248,500 L680,500 Z" fill="url(#redGradBR)" />
    <!-- Lingkaran Bergaris Diagonal di dalam bidang merah -->
    <circle cx="585" cy="425" r="32" fill="url(#diag-stripes)" opacity="0.32" />
    <!-- Matriks Titik Putih -->
    <rect x="430" y="445" width="84" height="28" fill="url(#dot-grid-white)" />
  </svg>

  <!-- Elemen Dekoratif Geometris (Sesuai Referensi Gambar) -->
  <!-- Lingkaran Bergaris Diagonal Kanan Atas -->
  <svg class="deco deco-tr-circle" viewBox="0 0 80 80" aria-hidden="true">
    <circle cx="40" cy="40" r="38" fill="url(#diag-stripes)" />
  </svg>

  <!-- Matriks Titik Kanan Atas -->
  <svg class="deco deco-tr-dots" viewBox="0 0 96 48" aria-hidden="true">
    <rect width="96" height="48" fill="url(#dot-grid)" />
  </svg>

  <!-- Lingkaran Bergaris Diagonal Kiri Bawah -->
  <svg class="deco deco-bl-circle" viewBox="0 0 90 90" aria-hidden="true">
    <circle cx="45" cy="45" r="42" fill="url(#diag-stripes)" />
  </svg>

  <!-- Matriks Titik Kiri Bawah -->
  <svg class="deco deco-bl-dots" viewBox="0 0 96 48" aria-hidden="true">
    <rect width="96" height="48" fill="url(#dot-grid)" />
  </svg>

  <!-- Lingkaran Bergaris Diagonal Tengah Bawah -->
  <svg class="deco deco-bc-circle" viewBox="0 0 70 70" aria-hidden="true">
    <circle cx="35" cy="35" r="32" fill="url(#diag-stripes)" />
  </svg>

  <!-- Konten Kartu Autentikasi -->
  <div class="wrap">
    <div class="brand-badge">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
      </svg>
      SIMMES
    </div>
    <h1 class="title">@yield('heading')</h1>
    @yield('content')
  </div>
</main>

<script>
  // Tombol lihat/sembunyikan kata sandi
  const eye = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  const eyeOff = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.9 17.9A10.9 10.9 0 0112 19C5 19 1 12 1 12a18.7 18.7 0 015.1-5.9M9.9 5.1A10.4 10.4 0 0112 5c7 0 11 7 11 7a18.8 18.8 0 01-2.2 3.2M1 1l22 22"/></svg>';
  document.querySelectorAll('input[type="password"]').forEach(input => {
    const box = document.createElement('div');
    box.className = 'pw';
    input.parentNode.insertBefore(box, input);
    box.appendChild(input);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Tampilkan kata sandi');
    btn.innerHTML = eye;
    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? eyeOff : eye;
      btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
    box.appendChild(btn);
  });
</script>
</body>
</html>
