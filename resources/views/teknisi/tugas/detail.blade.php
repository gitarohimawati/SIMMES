@extends('layouts.technician')
@section('title', $ticket->code)
@section('heading', 'Detail Tiket')
@section('back', route('teknisi.tugas.index'))
@section('nonav', '1')
@section('content')
@php
  $hasReport = $ticket->mediaOf('report')->count() > 0;
  $hasBefore = $ticket->mediaOf('before')->count() > 0 || $hasReport;
  $hasAfter  = $ticket->mediaOf('after')->count() > 0;
  $started   = in_array($ticket->status, ['diproses', 'selesai']);
  $done      = $ticket->status === 'selesai';
  $isMine    = $ticket->technician_id === auth()->id();
@endphp
@if ($ticket->technician_id === null)
  <div class="tcard hero" style="background:linear-gradient(135deg,#fff7ed 0%,#ffedd5 100%);border:1.5px solid #fdba74;text-align:center;padding:18px;margin-bottom:14px">
    <div style="font-size:13px;font-weight:800;color:#c2410c">⚡ TUGAS TERBUKA (BELUM DIAMBIL)</div>
    <p class="small muted" style="margin:6px 0 14px">Tiket ini belum diambil oleh teknisi manapun. Ambil tugas ini sekarang agar resmi menjadi tanggung jawab Anda.</p>
    <form method="POST" action="{{ route('teknisi.tugas.claim', $ticket) }}" onsubmit="const b=this.querySelector('button');if(b){b.disabled=true;b.innerHTML='⚡ Mengambil...'};">
      @csrf
      <button class="btn primary block" style="background:linear-gradient(135deg,#ea580c 0%,#c2410c 100%);justify-content:center;font-size:15px;font-weight:800">
        ⚡ Ambil Tugas Perbaikan Ini
      </button>
    </form>
  </div>
@elseif ($isMine && !$done)
  <div class="tcard hero" style="background:#f0fdf4;border:1.5px solid #86efac;margin-bottom:14px;padding:14px 16px">
    <b style="color:#15803d;font-size:14px">✓ Tugas Resmi Anda Ambil</b>
    <div class="tiny" style="color:#166534;margin-top:2px">Tiket ini sedang dalam penanganan Anda. Lanjutkan dengan unggah foto sebelum dan proses perbaikan.</div>
  </div>
@elseif ($ticket->technician_id && !$isMine)
  <div class="tcard hero" style="background:#fff1f2;border:1.5px solid #fecdd3;margin-bottom:14px;padding:14px 16px">
    <b style="color:#be123c;font-size:14px">🔒 Tugas Sudah Diambil oleh {{ $ticket->technician?->name ?? 'Teknisi Lain' }}</b>
    <div class="tiny" style="color:#881337;margin-top:2px">Tiket perbaikan ini sudah diambil lebih dulu oleh rekan teknisi lain. Anda tidak perlu mengerjakan tiket ini.</div>
  </div>
@endif

<div class="tcard">
  <div class="row between">
    <b style="font-size:24px">{{ $ticket->code }}</b>
    @if ($ticket->technician_id === null)
      <span class="chip" style="background:#ffedd5;color:#c2410c;font-weight:800">Belum Diambil</span>
    @else
      @include('partials.chip', ['v' => $ticket->display_status])
    @endif
  </div>
  <div class="tiny" style="margin-top:10px">Mesin</div><b>{{ $ticket->machine->label }} · {{ $ticket->machine->location }}</b>
  <p class="muted" style="margin-top:6px">{{ $ticket->complaint }}</p>
  <div style="margin-top:6px">@include('partials.chip', ['v' => $ticket->urgency])</div>
</div>

<div class="tcard">
  <div class="row between"><b>Progress pekerjaan</b><b>{{ $ticket->progress }}%</b></div>
  <div class="progress" style="margin-top:8px"><i style="width:{{ $ticket->progress }}%"></i></div>
  <ul class="steps">
    <li class="{{ $hasBefore ? 'done' : '' }}"><i>✓</i> Foto sebelum</li>
    <li class="{{ $started ? 'done' : '' }}"><i>✓</i> Pengerjaan</li>
    <li class="{{ $hasAfter ? 'done' : '' }}"><i>✓</i> Foto sesudah</li>
    <li class="{{ $done ? 'done' : '' }}"><i>✓</i> Selesai</li>
  </ul>
</div>

@if ($hasReport)
<div class="tcard">
  <b>Foto / dokumentasi saat dilaporkan</b>
  <div class="row wrap" style="margin-top:10px">
    @foreach ($ticket->mediaOf('report') as $m)
      <div class="thumb">@if ($m->kind === 'video')<video src="{{ $m->url }}" controls></video>@else<img src="{{ $m->url }}" alt="" style="cursor:zoom-in" onclick="openPhotoZoom('{{ $m->url }}', 'Foto Saat Dilaporkan')">@endif</div>
    @endforeach
  </div>
</div>
@endif

@foreach (['before' => 'Foto / video sebelum', 'after' => 'Foto / video sesudah'] as $ph => $title)
<div class="tcard">
  <b>{{ $title }}</b>
  <div class="row wrap" style="margin-top:10px">
    @foreach ($ticket->mediaOf($ph) as $m)
      <div class="thumb">@if ($m->kind === 'video')<video src="{{ $m->url }}" controls></video>@else<img src="{{ $m->url }}" alt="" style="cursor:zoom-in" onclick="openPhotoZoom('{{ $m->url }}', '{{ $title }}')">@endif</div>
    @endforeach
    @if ($isMine && !$done)
    <form method="POST" action="{{ route('teknisi.tugas.upload', $ticket) }}" enctype="multipart/form-data">@csrf
      <input type="hidden" name="phase" value="{{ $ph }}">
      <label class="dash" style="width:110px;height:84px;min-height:0">+ Tambah
        <input type="file" name="files[]" accept="image/*,video/*" multiple hidden onchange="this.form.submit()">
      </label>
    </form>
    @endif
  </div>
  @if ($isMine && $ph === 'after' && !$hasAfter && !$done)<div class="tiny" style="margin-top:8px">Wajib diisi sebelum selesai.</div>@endif
</div>
@endforeach

<div class="tcard">
  <b>Riwayat status</b>
  <ul class="tl" style="margin-top:12px">
    @foreach ($ticket->logs as $l)<li><b>{{ $l->label }}</b><div class="tiny">{{ $l->created_at->translatedFormat('d M, H:i') }}{{ $l->note ? ' · '.$l->note : '' }}</div></li>@endforeach
  </ul>
  @if ($isMine && !$done)
  <form method="POST" action="{{ route('teknisi.tugas.progress', $ticket) }}" style="margin-top:14px">@csrf
    <input class="input" name="action" placeholder="Tindakan (mis. Ganti bearing)" value="{{ $ticket->action }}">
    <input class="input" name="spareparts" placeholder="Sparepart (opsional)" value="{{ $ticket->spareparts }}" style="margin-top:8px">
    <textarea class="textarea" name="note" rows="2" placeholder="Catatan progress…" style="margin-top:8px" required></textarea>
    <button class="btn ghost block" style="margin-top:10px">Tambahkan progress</button>
  </form>
  @endif
</div>

<div style="height:90px"></div>
@if ($isMine && !$done)
<div style="position:fixed;left:50%;transform:translateX(-50%);bottom:0;width:100%;max-width:480px;padding:12px 18px 18px;background:linear-gradient(transparent,var(--bg) 35%);z-index:10">
  @if (!$started)
    <form method="POST" action="{{ route('teknisi.tugas.start', $ticket) }}">@csrf<button class="btn primary block">Mulai Proses</button></form>
  @else
    <form method="POST" action="{{ route('teknisi.tugas.finish', $ticket) }}">@csrf<button class="btn primary block {{ $hasAfter ? '' : 'disabled' }}">Tandai Selesai</button></form>
  @endif
</div>
@endif

@include('partials.job-detail-modal')
@endsection
