@extends('layouts.manager')
@section('title', 'Daftar Teknisi')
@section('content')
<div class="top technician-page-header">
  <div class="top-row">
    <div class="technician-page-heading">
      <div class="technician-header-meta" style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="live-status-pill">
          <span class="pulse-dot"></span>
          {{ $technicians->count() }} Teknisi Aktif
        </span>
        <span class="tiny muted">Tim Pemeliharaan & Perbaikan Pabrik</span>
      </div>
      <h1 style="margin:0">Daftar Teknisi</h1>
    </div>
    @include('partials.quick-technician-modal')
  </div>
  <p style="margin:0" class="muted small">Pantau beban kerja teknisi dan daftarkan teknisi baru yang dapat langsung aktif bertugas.</p>
</div>

{{-- Banner Info Akun Baru Jika Baru Saja Ditambahkan --}}
@if (session('new_tech'))
  @php $nt = session('new_tech'); @endphp
  <div class="card" style="background:#f0fdf4;border:1.5px solid #86efac;margin-bottom:24px;padding:20px 24px">
    <div class="row between" style="align-items:flex-start">
      <div>
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-size:18px;color:#16a34a">✓</span>
          <h3 style="margin:0;color:#14532d;font-size:16px">Akun Teknisi Berhasil Didaftarkan & Langsung Aktif!</h3>
        </div>
        <p class="small" style="color:#166534;margin:4px 0 12px">
          Teknisi <b>{{ $nt['name'] }}</b> kini sudah dapat langsung login pada halaman login sistem.
        </p>
      </div>
      <span class="chip c-aktif"><span class="chip-dot"></span> Langsung Aktif</span>
    </div>

    <div style="background:#fff;border:1px solid #bbf7d0;border-radius:10px;padding:14px 18px;display:flex;flex-wrap:wrap;gap:18px;align-items:center;justify-content:space-between">
      <div style="display:flex;flex-wrap:wrap;gap:24px">
        <div>
          <span class="tiny muted" style="display:block">Nama Lengkap</span>
          <b style="font-size:14px">{{ $nt['name'] }}</b>
        </div>
        <div>
          <span class="tiny muted" style="display:block">Email (Akun Login)</span>
          <span style="font-size:13.5px;font-weight:700;color:var(--ink)">{{ $nt['email'] }}</span>
        </div>
        <div>
          <span class="tiny muted" style="display:block">NIK</span>
          <span class="badge-code" style="font-size:13px">{{ $nt['nik'] }}</span>
        </div>
        <div>
          <span class="tiny muted" style="display:block">Kata Sandi (Password)</span>
          <span class="badge-code" style="font-size:13px;background:#fef3c7;color:#92400e;border-color:#fde68a">{{ $nt['password'] }}</span>
        </div>
        @if (!empty($nt['wa']))
          <div>
            <span class="tiny muted" style="display:block">WhatsApp</span>
            <span style="font-size:13px;color:#047857;font-weight:600">{{ $nt['wa'] }}</span>
          </div>
        @endif
      </div>

      <div class="row" style="gap:8px">
        <button type="button" class="btn-action" onclick="copyCredentials('{{ $nt['email'] }}', '{{ $nt['nik'] }}', '{{ $nt['password'] }}')">
          @include('partials.icon', ['n' => 'check'])
          <span id="btnCopyText">Salin Info Login</span>
        </button>
        @if (!empty($nt['wa']))
          @php
            $waMsg = rawurlencode("Halo {$nt['name']}, akun teknisi SIMMES Anda telah aktif.\n\nData login Anda:\nURL: " . url('/login') . "\nEmail (Login): {$nt['email']}\nNIK: {$nt['nik']}\nPassword: {$nt['password']}\n\nSilakan langsung login menggunakan email & password Anda. Terima kasih!");
            $waPhone = preg_replace('/[^0-9]/', '', $nt['wa']);
          @endphp
          <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" rel="noopener noreferrer" class="btn-action primary" style="background:#16a34a;border-color:#16a34a">
            @include('partials.icon', ['n' => 'whatsapp'])
            <span>Kirim Akun via WA</span>
          </a>
        @endif
      </div>
    </div>
  </div>

  <script>
    function copyCredentials(email, nik, pass) {
      const text = `Halo, akun teknisi SIMMES Anda sudah aktif.\nURL Login: {{ url('/login') }}\nEmail: ${email}\nNIK: ${nik}\nPassword: ${pass}`;
      navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('btnCopyText');
        if (btn) {
          btn.innerText = '✓ Info Tersalin!';
          setTimeout(() => { btn.innerText = 'Salin Info Login'; }, 2500);
        }
      });
    }
  </script>
@endif

<div class="grid g3 technician-grid">
  @forelse ($technicians as $t)
    <div class="card technician-card" style="display:flex;flex-direction:column;justify-content:space-between;padding:22px">
      <div>
        <div style="display:flex;align-items:center;gap:12px">
          <div class="avatar" style="width:42px;height:42px;font-size:14px;background:#f3f4f6;color:var(--ink);border:1px solid #e5e7eb">
            {{ $t->initials }}
          </div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:15px;color:var(--ink)">{{ $t->name }}</div>
            <div class="row" style="gap:6px;margin-top:2px">
              <span class="badge-code">{{ $t->nik }}</span>
              <span class="tiny muted">{{ $t->department }}</span>
            </div>
          </div>
        </div>

        @if ($t->bagian)
          <div class="tiny" style="margin-top:10px;color:var(--ink2)">
            Spesialisasi: <b>{{ $t->bagian }}</b>
          </div>
        @endif

        <div class="grid g2" style="margin-top:16px;gap:10px">
          <div class="stat" style="padding:12px 14px;border-radius:10px">
            <span style="font-size:11.5px">Tiket Aktif</span>
            <b style="font-size:24px;margin-top:2px;color:var(--r700)">{{ $t->active_count }}</b>
          </div>
          <div class="stat s2" style="padding:12px 14px;border-radius:10px">
            <span style="font-size:11.5px">Selesai</span>
            <b style="font-size:24px;margin-top:2px;color:#047857">{{ $t->done_count }}</b>
          </div>
        </div>
      </div>

      <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <div>
          @if ($t->whatsapp)
            <span class="badge-wa" style="font-size:11px">
              @include('partials.icon', ['n' => 'whatsapp']) {{ $t->whatsapp }}
            </span>
          @else
            <span class="badge-wa-empty">No WA belum ada</span>
          @endif
        </div>
        <div style="display:inline-flex;align-items:center;gap:6px">
          <a class="btn-icon detail" href="{{ route('manajemen.teknisi.show', $t) }}" title="Lihat Profil & Riwayat Pekerjaan">
            @include('partials.icon', ['n' => 'eye'])
          </a>
          <a class="btn-icon edit" href="{{ route('manajemen.pengguna.edit', $t) }}" title="Ubah Akun / Nama Teknisi">
            @include('partials.icon', ['n' => 'edit'])
          </a>
          @if ($t->whatsapp)
            @php
              $directWa = \App\Services\WhatsAppService::makeDirectLink($t->whatsapp, "Halo {$t->name}, salam dari manajemen SIMMES.");
            @endphp
            @if ($directWa)
              <a href="{{ $directWa }}" target="_blank" rel="noopener noreferrer" class="btn-icon whatsapp" title="Chat WhatsApp Teknisi ({{ $t->whatsapp }})">
                @include('partials.icon', ['n' => 'whatsapp'])
              </a>
            @endif
          @endif
          @if (!$t->is(auth()->user()))
            <form method="POST" action="{{ route('manajemen.pengguna.destroy', $t) }}" style="display:inline;margin:0" data-confirm="Apakah Anda yakin ingin menghapus akun teknisi {{ $t->name }}?">
              @csrf
              @method('DELETE')
              <button class="btn-icon danger" type="submit" title="Hapus Akun Teknisi">
                @include('partials.icon', ['n' => 'trash'])
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="card empty" style="grid-column:1/-1;padding:48px 24px;text-align:center">
      <div style="font-weight:600;font-size:16px;color:var(--ink2)">Belum ada teknisi terdaftar</div>
      <p class="small muted" style="margin:6px 0 16px">Daftarkan teknisi pertama untuk mulai menugaskan perbaikan mesin.</p>
      <a class="btn primary" href="{{ route('manajemen.teknisi.create') }}">
        @include('partials.icon', ['n' => 'plus']) Daftarkan Teknisi Sekarang
      </a>
    </div>
  @endforelse
</div>
@endsection
