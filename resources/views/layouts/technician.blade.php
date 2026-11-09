@php
  $unclaimedTickets = \App\Models\Ticket::whereNull('technician_id')->where('status', '!=', 'selesai')->count();
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#b1002c">
  <title>@yield('title', 'Teknisi') · SIMMES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/simmes.css') }}?v={{ filemtime(public_path('css/simmes.css')) }}">
  <style>
    .nav-task-badge {
      position: absolute;
      top: 6px;
      right: 22%;
      background: #dc2626;
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      min-width: 17px;
      height: 17px;
      line-height: 17px;
      border-radius: 999px;
      text-align: center;
      padding: 0 4px;
      border: 1.5px solid #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body class="tech">
<div class="phone">
  <header class="thead">
    @hasSection('back')<a class="back" href="@yield('back')">@include('partials.icon', ['n' => 'back'])</a>@endif
    <h1>@yield('heading', 'SIMMES')</h1>
    @hasSection('sub')<div class="sub">@yield('sub')</div>@endif
  </header>


  <div class="tbody">
    @include('partials.flash')
    @yield('content')
  </div>

  @unless (View::hasSection('nonav'))
  <nav class="bottom">
    <a href="{{ route('teknisi.home') }}" class="{{ request()->routeIs('teknisi.home') ? 'on' : '' }}">@include('partials.icon', ['n' => 'home']) Beranda</a>
    <a href="{{ route('teknisi.tugas.index') }}" class="{{ request()->routeIs('teknisi.tugas.*') ? 'on' : '' }}" style="position:relative">
      @include('partials.icon', ['n' => 'list']) Tugas
      <span id="navTugasBadge" class="nav-task-badge" style="display:{{ $unclaimedTickets > 0 ? 'inline-flex' : 'none' }}">{{ $unclaimedTickets }}</span>
    </a>
    <a href="{{ route('teknisi.laporan.create') }}" class="fab" aria-label="Lapor">@include('partials.icon', ['n' => 'plus'])</a>
    <a href="{{ route('teknisi.laporan.index') }}" class="{{ request()->routeIs('teknisi.laporan.*') || request()->routeIs('teknisi.lapor*') ? 'on' : '' }}">@include('partials.icon', ['n' => 'file']) Laporan</a>
    <a href="{{ route('teknisi.profil') }}" class="{{ request()->routeIs('teknisi.profil*') ? 'on' : '' }}">@include('partials.icon', ['n' => 'user']) Profil</a>
  </nav>
  @endunless
</div>

<script>
// Real-time Phone Screen Notification Engine untuk Teknisi (Siapa Cepat Dia Dapat)
(function() {
  const pollUrl = "{{ route('teknisi.liveAlerts') }}";
  let knownMaxId = 0;
  let isInitialized = false;
  // Simpan referensi notifikasi layar ponsel aktif: map of ticketId -> Notification instance
  const activePhoneNotifications = new Map();

  // Minta izin notifikasi secara halus pada interaksi pertama jika belum ditentukan
  if (window.Notification && Notification.permission === 'default') {
    const askPerm = () => {
      Notification.requestPermission();
      document.removeEventListener('click', askPerm);
    };
    document.addEventListener('click', askPerm, { once: true });
  }

  window.enablePhoneNotifications = function() {
    if (window.Notification && Notification.permission !== 'granted') {
      Notification.requestPermission().then(perm => {
        if (perm === 'granted') {
          playChimeSound();
          try {
            new Notification("✅ Notifikasi Layar Ponsel Aktif!", {
              body: "Anda akan menerima notifikasi setiap kali ada perbaikan mesin baru (siapa cepat dia dapat).",
              icon: "/favicon.ico"
            });
          } catch(e) {}
        }
      });
    }
  };

  function playChimeSound() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(659.25, ctx.currentTime);
      gain.gain.setValueAtTime(0.2, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.35);
    } catch(e) {}
  }

  // Tampilkan Notifikasi di Layar Ponsel (System Notification)
  function triggerPhoneScreenNotification(ticket) {
    if (!window.Notification || Notification.permission !== 'granted') return;

    try {
      const notif = new Notification("⚡ SIMMES: Kerusakan Mesin Baru!", {
        body: `${ticket.machine} butuh perbaikan segera (Siapa cepat dia dapat)! Ketuk untuk ambil tugas.`,
        icon: "/favicon.ico",
        tag: "simmes-ticket-" + ticket.id,
        requireInteraction: false
      });

      notif.onclick = function() {
        window.focus();
        window.location.href = ticket.claim_url;
        notif.close();
      };

      activePhoneNotifications.set(ticket.id, notif);
      playChimeSound();
    } catch(e) {
      console.warn('Native notification tidak dapat dimunculkan:', e);
    }
  }

  // Polling cek status tiket terbuka & otomatis hilangkan notifikasi saat sudah diambil
  async function syncAlerts() {
    try {
      const res = await fetch(pollUrl, { credentials: 'same-origin' });
      if (!res.ok) return;
      const data = await res.json();
      const count = data.count || 0;
      const openIds = data.open_ids || [];
      const currentMaxId = data.max_id || 0;

      // Update badge di navbar Tugas
      const badge = document.getElementById('navTugasBadge');
      if (badge) {
        if (count > 0) {
          badge.style.display = 'inline-flex';
          badge.textContent = count;
        } else {
          badge.style.display = 'none';
        }
      }

      // Update pill count di halaman tugas jika ada
      const pillCount = document.getElementById('availablePillCount');
      if (pillCount) {
        pillCount.textContent = count;
      }

      // Update banner hero di Beranda / Dashboard Teknisi
      const homeHero = document.getElementById('availableTicketsHero');
      if (homeHero) {
        if (count > 0 && data.latest) {
          homeHero.style.display = 'block';
          const countEl = document.getElementById('availableTicketsCount');
          if (countEl) countEl.textContent = `${count} Laporan Terbuka (Belum Diambil)`;
          const textEl = document.getElementById('availableTicketsText');
          if (textEl) textEl.innerHTML = `Tiket <b>${data.latest.code}</b> (${data.latest.machine}) menunggu teknisi.`;
        } else {
          homeHero.style.display = 'none';
        }
      }

      // Update banner hero di Halaman Tugas
      const tasksHero = document.getElementById('availableTasksHero');
      if (tasksHero) {
        const isTersedia = new URLSearchParams(window.location.search).get('f') === 'tersedia';
        if (count > 0 && !isTersedia) {
          tasksHero.style.display = 'block';
          const heroText = document.getElementById('availableTasksHeroText');
          if (heroText) heroText.textContent = `⚡ Ada ${count} tugas belum diambil teknisi`;
        } else {
          tasksHero.style.display = 'none';
        }
      }

      // Jika sedang berada di tab filter "tersedia", otomatis hilangkan kartu tiket yang sudah diambil teknisi
      const isTersediaTab = new URLSearchParams(window.location.search).get('f') === 'tersedia';
      if (isTersediaTab) {
        document.querySelectorAll('[data-available-ticket-id]').forEach(el => {
          const tid = parseInt(el.getAttribute('data-available-ticket-id'), 10);
          if (!openIds.includes(tid)) {
            el.remove();
          }
        });
        const remainingCards = document.querySelectorAll('[data-available-ticket-id]');
        const emptyState = document.getElementById('emptyTersediaState');
        if (remainingCards.length === 0 && emptyState) {
          emptyState.style.display = 'block';
        }
      }

      // 1. KETIKA TIKET SUDAH DIAMBIL: Tutup notifikasi di layar ponsel secara otomatis
      activePhoneNotifications.forEach((notifObj, ticketId) => {
        if (!openIds.includes(ticketId) || count === 0) {
          try { notifObj.close(); } catch(e) {}
          activePhoneNotifications.delete(ticketId);
        }
      });

      // 2. KETIKA ADA TIKET BARU MASUK: Munculkan notifikasi di layar ponsel
      if (!isInitialized) {
        knownMaxId = currentMaxId;
        isInitialized = true;
      } else {
        if (currentMaxId > knownMaxId && data.latest) {
          triggerPhoneScreenNotification(data.latest);
          knownMaxId = currentMaxId;
        }
      }
    } catch(e) {}
  }

  // Inisialisasi awal & polling berkala setiap 15 detik
  syncAlerts();
  setInterval(syncAlerts, 15000);

  // Otomatis sinkronisasi saat kembali dari navigasi riwayat / BFCache
  window.addEventListener('pageshow', function() {
    syncAlerts();
  });
})();
</script>
@include('partials.confirm-modal')

@stack('scripts')
</body>
</html>
