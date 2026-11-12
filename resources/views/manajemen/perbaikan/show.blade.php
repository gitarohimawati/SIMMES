@extends('layouts.manager')
@section('title', $ticket->code)
@section('content')
@php
  $from = request('from');
  $backUrl = route('manajemen.perbaikan.index');
  if ($from === 'dashboard') {
      $backUrl = route('manajemen.dashboard');
  } else {
      $prev = url()->previous();
      if ($prev && $prev !== url()->current()) {
          $dashPath = rtrim(parse_url(route('manajemen.dashboard'), PHP_URL_PATH) ?? '', '/');
          $prevPath = rtrim(parse_url($prev, PHP_URL_PATH) ?? '', '/');
          if ($dashPath && $prevPath === $dashPath) {
              $backUrl = route('manajemen.dashboard');
          }
      }
  }
@endphp
<div class="top">
  <div><h1>{{ $ticket->code }}</h1><p>{{ $ticket->machine->label }} · {{ $ticket->machine->location }}</p></div>
  <div class="actions">
    @include('partials.chip', ['v' => $ticket->display_status])
    <a class="btn" href="{{ route('manajemen.perbaikan.edit', $ticket) }}" style="color:#0284c7;border-color:#bae6fd">
      @include('partials.icon', ['n' => 'edit']) Edit Tiket
    </a>
    <form method="POST" action="{{ route('manajemen.perbaikan.destroy', $ticket) }}" data-confirm="Apakah Anda yakin ingin menghapus tiket {{ $ticket->code }}?" style="display:inline;margin:0">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn" style="color:#e11d48;border-color:#fecdd3">
        @include('partials.icon', ['n' => 'trash']) Hapus
      </button>
    </form>
    <a class="btn" href="{{ $backUrl }}">← Kembali</a>
  </div>
</div>

@if (session('wa_link'))
  <div class="flash" style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:5px solid #16a34a;color:#14532d;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
    <div>
      <b style="font-size:15px">✓ Tiket Berhasil Ditugaskan ke {{ session('tech_name') }}!</b>
      <p style="margin:2px 0 0;font-size:13px;color:#166534">Kirim rincian tugas perbaikan langsung ke WhatsApp teknisi ({{ session('tech_phone') }}):</p>
    </div>
    <a href="{{ session('wa_link') }}" target="_blank" rel="noopener noreferrer" class="btn" style="background:#16a34a;color:#fff;border-color:transparent;font-weight:700;display:inline-flex;align-items:center;gap:8px;box-shadow:0 8px 18px -4px rgba(22,163,74,0.4)">
      <svg class="i" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
      Kirim Pesan WhatsApp Sekarang
    </a>
  </div>
  <script>
    setTimeout(function() {
      window.open(@json(session('wa_link')), '_blank');
    }, 400);
  </script>
@endif

<div class="grid g2">
  <div class="card">
    <h3>Informasi</h3>
    <div class="kv" style="grid-template-columns:1fr 1fr">
      <div><span>Keluhan</span><b>{{ $ticket->complaint }}</b></div>
      <div><span>Urgensi</span>@include('partials.chip', ['v' => $ticket->urgency])</div>
      <div><span>Pelapor</span><b>{{ $ticket->reporter?->name ?? '-' }}</b></div>
      <div><span>Tanggal Masuk</span><b>{{ $ticket->created_at->translatedFormat('d M Y, H:i') }}</b></div>
      <div><span>Mulai Perbaikan</span><b>{{ $ticket->started_at?->translatedFormat('d M Y, H:i') ?? 'Belum dimulai' }}</b></div>
      <div><span>Selesai Perbaikan</span><b style="{{ $ticket->finished_at ? 'color:#047857' : '' }}">{{ $ticket->finished_at?->translatedFormat('d M Y, H:i') ?? 'Belum selesai' }}</b></div>
      <div><span>Batas waktu</span><b>{{ $ticket->due_at?->translatedFormat('d M Y H:i') ?? '-' }}</b></div>
      <div><span>Tindakan</span><b>{{ $ticket->action ?? '-' }}</b></div>
      <div style="grid-column:1 / -1"><span>Sparepart</span><b>{{ $ticket->spareparts ?? '-' }}</b></div>
    </div>
    <div style="margin-top:20px"><span class="tiny">Progress pekerjaan · {{ $ticket->progress }}%</span>
      <div class="progress"><i style="width:{{ $ticket->progress }}%"></i></div></div>
  </div>

  <div class="card">
    <h3>Status Penanganan & Teknisi</h3>

    @if ($ticket->technician)
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:16px;margin-bottom:16px">
        <div class="row between wrap">
          <span class="chip c-selesai" style="font-size:12px;font-weight:800">✓ Sudah Diambil Teknisi</span>
          <span class="tiny muted">{{ $ticket->started_at ? 'Mulai: ' . $ticket->started_at->translatedFormat('d M Y, H:i') : 'Status: ' . ucfirst($ticket->status) }}</span>
        </div>
        <div style="margin-top:10px">
          <b style="font-size:17px;display:block">{{ $ticket->technician->name }}</b>
          <div class="small muted">NIK {{ $ticket->technician->nik }} · {{ $ticket->technician->bagian ?? $ticket->technician->department ?? 'Teknisi Lapangan' }}</div>
          <div class="small" style="margin-top:4px;color:#15803d;font-weight:700">WA: {{ $ticket->technician->whatsapp ?? 'Belum diisi' }}</div>
        </div>

        @if ($ticket->technician->whatsapp)
          @php
            $waMsg = \App\Services\WhatsAppService::buildTicketMessage($ticket, $ticket->technician);
            $waLink = \App\Services\WhatsAppService::makeDirectLink($ticket->technician->whatsapp, $waMsg);
          @endphp
          <div class="row" style="margin-top:12px;gap:8px">
            @if ($waLink)
              <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn" style="flex:1;font-size:12.5px;padding:8px;color:#15803d;border-color:#bbf7d0;background:#fff;justify-content:center;font-weight:700">
                <svg class="i" style="width:16px;height:16px" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                Hubungi Teknisi via WA
              </a>
            @endif
          </div>
        @endif
      </div>
    @else
      <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:16px;margin-bottom:16px">
        <span class="chip" style="background:#ffedd5;color:#c2410c;font-weight:800;font-size:12px">⚡ Menunggu Teknisi Mengambil Tugas</span>
        <p class="small muted" style="margin:8px 0 12px;line-height:1.4">
          Tiket ini berstatus terbuka. Teknisi akan mengambil tugas ini secara mandiri ("siapa cepat dia dapat"). Anda cukup memantau progres pengerjaannya di sini.
        </p>

        @php
          $broadcastLink = \App\Services\WhatsAppService::makeBroadcastShareLink($ticket);
        @endphp
        <div style="display:flex;flex-direction:column;gap:8px">
          <form method="POST" action="{{ route('manajemen.perbaikan.broadcastWa', $ticket) }}">
            @csrf
            <button type="submit" class="btn block" style="background:#0284c7;color:#fff;border-color:transparent;justify-content:center;font-size:12.5px;font-weight:700">
              ⚡ Kirim Ulang Notifikasi ke Semua HP Teknisi (Gateway WA)
            </button>
          </form>

          <a href="{{ $broadcastLink }}" target="_blank" rel="noopener noreferrer" class="btn block" style="background:#16a34a;color:#fff;border-color:transparent;justify-content:center;font-size:12.5px;font-weight:700">
            <svg class="i" style="width:16px;height:16px" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Bagikan ke Grup WhatsApp Teknisi
          </a>
        </div>
      </div>
    @endif

    <!-- Opsi pengalihan manual (Opsional jika darurat) -->
    <details style="margin-top:10px">
      <summary class="tiny muted" style="cursor:pointer;font-weight:700">⚙️ Opsi Tambahan: Tugaskan / Ganti Teknisi Manual</summary>
      <form method="POST" action="{{ route('manajemen.perbaikan.assign', $ticket) }}" style="margin-top:12px">@csrf
        <label class="f" style="margin-top:0">Pilih Teknisi Pengganti</label>
        <select class="select" name="technician_id" required>
          <option value="">Pilih teknisi</option>
          @foreach ($technicians as $t)
            <option value="{{ $t->id }}" @selected($ticket->technician_id === $t->id)>
              {{ $t->name }} (WA: {{ $t->whatsapp ? $t->whatsapp : 'Belum diisi' }})
            </option>
          @endforeach
        </select>
        <label class="f">Batas waktu</label>
        <input class="input" type="datetime-local" name="due_at" value="{{ $ticket->due_at?->format('Y-m-d\TH:i') }}">
        <button class="btn ghost block" style="margin-top:12px;justify-content:center">Simpan Penugasan Manual</button>
      </form>
    </details>
  </div>
</div>

<div class="grid g2" style="margin-top:18px">
  <div class="card">
    <h3>Foto / video</h3>
    @foreach (['report' => 'Laporan awal', 'before' => 'Sebelum', 'after' => 'Sesudah'] as $ph => $lbl)
      <div class="small muted" style="margin:14px 0 6px;font-weight:700">{{ $lbl }}</div>
      <div class="row wrap" style="gap:12px">
        @forelse ($ticket->mediaOf($ph) as $m)
          <div style="width:100%;max-width:220px;height:170px;border-radius:14px;overflow:hidden;border:1.5px solid var(--line);position:relative;background:#000;box-shadow:0 4px 12px rgba(0,0,0,0.06)">
            @if ($m->kind === 'video')
              <video src="{{ $m->url }}" controls style="width:100%;height:100%;object-fit:cover"></video>
            @else
              <div onclick="openPhotoZoom('{{ $m->url }}', 'Foto {{ $lbl }}')" style="cursor:zoom-in;width:100%;height:100%" title="Klik untuk memperbesar foto">
                <img src="{{ $m->url }}" alt="{{ $lbl }}" style="width:100%;height:100%;object-fit:cover;display:block">
              </div>
            @endif
          </div>
        @empty<span class="tiny">{{ $ph === 'after' ? 'Belum diunggah teknisi.' : 'Belum ada.' }}</span>@endforelse
      </div>
    @endforeach
  </div>
  <div class="card">
    <h3>Riwayat status</h3>
    <ul class="tl">
      @foreach ($ticket->logs as $l)
        <li><b>{{ $l->label }}</b><div class="tiny">{{ $l->created_at->translatedFormat('d M Y, H:i') }}{{ $l->note ? ' · '.$l->note : '' }}</div></li>
      @endforeach
    </ul>
  </div>
</div>

@include('partials.job-detail-modal')
@endsection
