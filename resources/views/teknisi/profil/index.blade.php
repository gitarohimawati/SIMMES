@extends('layouts.technician')
@section('title', 'Profil')
@section('heading', 'Profil')
@section('content')
<div class="tcard hero" style="text-align:center">
  <div class="avatar" style="width:74px;height:74px;font-size:26px;margin:0 auto 10px;background:var(--grad-brand);color:#fff">{{ $u->initials }}</div>
  <b style="font-size:19px">{{ $u->name }}</b>
  <div class="muted small">NIK {{ $u->nik }} · {{ $u->department }}</div>
  <div class="tstats" style="margin:16px 0 0">
    <div class="tstat hot"><b>{{ $total }}</b><span>Tiket selesai</span></div>
    <div class="tstat"><b>{{ $u->assignedTickets()->where('status','!=','selesai')->count() }}</b><span>Masih aktif</span></div>
  </div>
</div>

<div class="tcard">
  <h3 style="font-size:16px;font-weight:800;margin-bottom:6px">Notifikasi WhatsApp</h3>
  <p class="muted small" style="margin-bottom:14px;line-height:1.4">
    Nomor WhatsApp ini digunakan sistem SIMMES untuk mengirimkan notifikasi penugasan tiket perbaikan dan pengingat jadwal maintenance secara otomatis.
  </p>

  <form method="POST" action="{{ route('teknisi.profil.update') }}">
    @csrf
    @method('PUT')

    <label class="f" style="margin-top:0">Nama Pengguna</label>
    <input class="input" name="name" type="text" value="{{ old('name', $u->name) }}" placeholder="" required>
    @error('name')<div class="err">{{ $message }}</div>@enderror

    <label class="f" style="margin-top:14px">Nomor WhatsApp Aktif</label>
    <div style="position:relative">
      <input class="input" name="whatsapp" type="tel" value="{{ old('whatsapp', $u->whatsapp) }}" placeholder="Contoh: 08123456789" required>
    </div>

    <label class="f" style="margin-top:14px">Bagian / Unit Kerja</label>
    <input class="input" name="bagian" value="{{ old('bagian', $u->bagian) }}" placeholder="">

    <div class="profil-actions-centered" style="margin-top:22px">
      <button type="submit" class="btn primary btn-profil-save" style="min-width:240px;justify-content:center;padding:12px 24px;font-size:14px;font-weight:700;border-radius:12px">
        @include('partials.icon', ['n' => 'check']) Simpan Nama & Profil
      </button>
    </div>
  </form>

  <form id="testWaForm" method="POST" action="{{ route('teknisi.profil.testWa') }}" style="display:none">@csrf</form>

  <div class="profil-actions-centered" style="margin-top:12px">
    <form method="POST" action="{{ route('logout') }}" style="margin:0;width:auto">
      @csrf
      <button type="submit" class="btn btn-profil-logout" style="min-width:240px;justify-content:center;color:var(--r700);border-color:#fecdd3;background:#fff1f2;padding:11px 24px;font-size:13.5px;font-weight:600;border-radius:12px">
        @include('partials.icon', ['n' => 'logout']) Keluar (Logout)
      </button>
    </form>
  </div>

<script>
function testSystemAlert() {
  if (window.Notification) {
    Notification.requestPermission().then(perm => {
      if (perm === 'granted') {
        try {
          new Notification("⚡ SIMMES: Notifikasi Layar Ponsel", {
            body: "Notifikasi perbaikan mesin baru di layar ponsel Anda telah aktif!",
            icon: "/favicon.ico"
          });
        } catch(e) {}
      }
    });
  }

  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (AudioContext) {
      const ctx = new AudioContext();
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
    }
  } catch(e) {}
}
</script>
@endsection
