@extends('layouts.technician')
@section('title', 'Detail Laporan '.$ticket->code)
@section('heading', 'Laporan '.$ticket->code)
@section('sub', 'Dilaporkan pada '.$ticket->created_at->translatedFormat('d M Y, H:i'))
@section('back', route('teknisi.laporan.index'))

@section('content')
{{-- Kartu Info Utama --}}
<div class="tcard hero" style="margin-bottom:14px">
  <div class="row between" style="margin-bottom:8px">
    <b style="font-size:18px;color:var(--r700)">{{ $ticket->machine->name }}</b>
    @include('partials.chip', ['v' => $ticket->display_status])
  </div>
  <div class="tiny" style="color:var(--ink2);margin-bottom:12px">
    Kode Mesin: <b>{{ $ticket->machine->code }}</b> · Lokasi: <b>{{ $ticket->machine->location }}</b>
  </div>

  <div class="row between" style="border-top:1px solid var(--line);padding-top:10px">
    <div>
      <span class="tiny" style="display:block">Tingkat Urgensi</span>
      @include('partials.chip', ['v' => $ticket->urgency])
    </div>
    <div style="text-align:right">
      <span class="tiny" style="display:block">Batas Waktu</span>
      <b>{{ $ticket->due_at ? $ticket->due_at->translatedFormat('d M Y') : '-' }}</b>
    </div>
  </div>
</div>

{{-- Kartu Keluhan & Deskripsi Masalah --}}
<div class="tcard" style="margin-bottom:14px">
  <h3 style="font-size:15px;margin-bottom:8px">Deskripsi Keluhan</h3>
  <p style="white-space:pre-line;color:var(--ink);line-height:1.6">{{ $ticket->complaint }}</p>
</div>

@if ($ticket->action || $ticket->spareparts)
<div class="tcard" style="margin-bottom:14px">
  <h3 style="font-size:15px;margin-bottom:8px">Hasil Perbaikan (Tersinkron ke Laporan Manager)</h3>
  @if ($ticket->action)
    <div style="margin-bottom:6px"><span class="tiny muted">Tindakan:</span> <b>{{ $ticket->action }}</b></div>
  @endif
  @if ($ticket->spareparts)
    <div><span class="tiny muted">Suku Cadang / Sparepart:</span> <b>{{ $ticket->spareparts }}</b></div>
  @endif
</div>
@endif

{{-- Lampiran Foto / Video --}}
<div class="tcard" style="margin-bottom:14px">
  <div class="row between" style="margin-bottom:10px">
    <h3 style="font-size:15px;margin:0">Dokumentasi Kendala ({{ $ticket->media->count() }})</h3>
  </div>

  @if ($ticket->media->count())
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
      @foreach ($ticket->media as $m)
        <div style="position:relative;border-radius:12px;overflow:hidden;border:1px solid var(--line);aspect-ratio:1">
          @if ($m->kind === 'video')
            <video src="{{ $m->url }}" controls style="width:100%;height:100%;object-fit:cover"></video>
          @else
            <div onclick="openPhotoZoom('{{ $m->url }}', 'Dokumentasi Kendala')" style="cursor:zoom-in;width:100%;height:100%" title="Klik untuk memperbesar foto">
              <img src="{{ $m->url }}" alt="Lampiran" style="width:100%;height:100%;object-fit:cover">
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @else
    <p class="tiny muted" style="margin:0">Tidak ada foto/video dokumentasi terlampir.</p>
  @endif
</div>

{{-- Riwayat Aktivitas / Logs --}}
<div class="tcard" style="margin-bottom:18px">
  <h3 style="font-size:15px;margin-bottom:12px">Riwayat Laporan</h3>
  <ul class="steps">
    @forelse ($ticket->logs as $log)
      <li class="done">
        <i>✓</i>
        <div style="flex:1">
          <b>{{ $log->label }}</b>
          @if ($log->note) <div class="tiny" style="color:var(--ink2)">{{ $log->note }}</div> @endif
          <div class="tiny" style="color:var(--ink3)">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</div>
        </div>
      </li>
    @empty
      <li class="tiny muted">Belum ada catatan log.</li>
    @endforelse
  </ul>
</div>

{{-- Tombol Aksi CRUD --}}
<div class="tcard" style="padding:14px">
  <div class="row" style="gap:10px;margin-bottom:10px">
    <a class="btn primary block" href="{{ route('teknisi.tugas.show', $ticket) }}" style="flex:1">
      @include('partials.icon', ['n' => 'wrench']) Tangani di Tugas
    </a>
    <a class="btn block" href="{{ route('teknisi.laporan.edit', $ticket) }}" style="flex:1;color:var(--warn);border-color:var(--rose200)">
      @include('partials.icon', ['n' => 'edit']) Edit Laporan
    </a>
  </div>

  <form method="POST" action="{{ route('teknisi.laporan.destroy', $ticket) }}" data-confirm="Apakah Anda yakin ingin menghapus laporan {{ $ticket->code }} secara permanen?" style="margin:0">
    @csrf
    @method('DELETE')
    <button class="btn block" type="submit" style="color:var(--r700);background:var(--rose50);border-color:var(--rose200);width:100%">
      @include('partials.icon', ['n' => 'trash']) Hapus Laporan Ini
    </button>
  </form>
</div>

@include('partials.job-detail-modal')
@endsection
