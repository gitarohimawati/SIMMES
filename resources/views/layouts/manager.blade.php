<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'SIMMES') · SIMMES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/simmes.css') }}?v={{ filemtime(public_path('css/simmes.css')) }}">
</head>
<body>
@php
  $pendingUsers = \App\Models\User::where('status', 'pending')->count();
  $nav = [
    ['manajemen.dashboard',      'Dashboard',          'dashboard', 'manajemen.dashboard'],
    ['manajemen.perbaikan.index','Perbaikan',          'wrench',    'manajemen.perbaikan.*'],
    ['manajemen.mesin.index',    'Data Mesin',         'cog',       'manajemen.mesin.*'],
    ['manajemen.teknisi.index',  'Teknisi',            'users',     'manajemen.teknisi.*'],
    ['manajemen.jadwal.index',   'Jadwal Maintenance', 'calendar',  'manajemen.jadwal.*'],
    ['manajemen.laporan.index',  'Laporan',            'file',      'manajemen.laporan.*'],
    ['manajemen.pengguna.index', 'Pengguna',           'user',      'manajemen.pengguna.*'],
  ];
@endphp
<div class="app">
  <!-- Mobile Topbar -->
  <header class="mobile-topbar">
    <div class="mobile-brand">
      <div class="brand-mark">S</div>
      <div><b>SIMMES</b><small>Portal Manager</small></div>
    </div>
    <button class="btn-burger" id="btnBurger" type="button" aria-label="Buka menu navigasi">
      <svg class="i" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
  </header>

  <div class="side-backdrop" id="sideBackdrop"></div>

  <aside class="side" id="sideMenu">
    <button class="side-close" id="sideClose" type="button" aria-label="Tutup menu">✕</button>
    <div class="brand">
      <div class="brand-mark">S</div>
      <div><b>SIMMES</b><small>Monitoring Mesin</small></div>
    </div>
    <div class="who">
      <div class="avatar">{{ auth()->user()->initials }}</div>
      <div>Portal Manager<br><b>{{ auth()->user()->name }}</b></div>
    </div>
    <nav class="nav">
      @foreach ($nav as [$route, $label, $icon, $pattern])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'on' : '' }}">
          @include('partials.icon', ['n' => $icon]) {{ $label }}
          @if ($route === 'manajemen.pengguna.index' && $pendingUsers)<span class="badge">{{ $pendingUsers }}</span>@endif
        </a>
      @endforeach
    </nav>
    <div class="side-foot">
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="logout">@include('partials.icon', ['n' => 'logout']) Keluar (Logout)</button>
      </form>
    </div>
  </aside>
  <main class="main">
    @include('partials.flash')
    @yield('content')
  </main>
</div>
<script>
  (function () {
    const burger = document.getElementById('btnBurger');
    const side = document.getElementById('sideMenu');
    const backdrop = document.getElementById('sideBackdrop');
    const closeBtn = document.getElementById('sideClose');

    function openMenu() {
      if (side) side.classList.add('open');
      if (backdrop) backdrop.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      if (side) side.classList.remove('open');
      if (backdrop) backdrop.classList.remove('active');
      document.body.style.overflow = '';
    }

    if (burger) burger.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (backdrop) backdrop.addEventListener('click', closeMenu);

    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024) closeMenu();
    });
  })();
</script>
@include('partials.confirm-modal')
@stack('scripts')
</body>
</html>
