@extends('layouts.technician')
@section('title', 'Laporan Perbaikan')
@section('heading', 'Laporan Perbaikan')
@section('sub', 'Kelola & pantau status laporan mesin Anda')
@section('back', route('teknisi.home'))

@section('content')
{{-- Statistik Ringkas --}}
<div class="tstats" style="grid-template-columns:repeat(3,1fr)">
  <div class="tstat">
    <b>{{ $stats['total'] }}</b>
    <span>Total</span>
  </div>
  <div class="tstat hot">
    <b>{{ $stats['aktif'] }}</b>
    <span>Aktif</span>
  </div>
  <div class="tstat">
    <b style="color:var(--ok)">{{ $stats['selesai'] }}</b>
    <span>Selesai</span>
  </div>
</div>

{{-- Tombol Tambah Laporan Baru --}}
<div style="margin-bottom:14px">
  <a class="btn primary block" href="{{ route('teknisi.laporan.create') }}" style="display:flex;align-items:center;justify-content:center;gap:8px">
    @include('partials.icon', ['n' => 'plus']) Buat Laporan Perbaikan
  </a>
</div>

{{-- Pencarian & Filter Status --}}
<form class="tcard" method="GET" action="{{ route('teknisi.laporan.index') }}" style="padding:12px;margin-bottom:14px">
  <input type="hidden" name="f" value="{{ $filter }}">
  <div class="row" style="gap:8px">
    <input class="input" type="search" name="q" value="{{ $search }}" placeholder="Cari kode tiket, mesin, atau keluhan…" style="padding:9px 14px;border-radius:12px">
    <button class="btn" style="padding:9px 14px;border-radius:12px" type="submit">Cari</button>
  </div>
</form>

<div class="pills" style="margin-bottom:12px">
  <a href="{{ route('teknisi.laporan.index', ['f' => 'semua', 'q' => $search]) }}" class="{{ $filter === 'semua' ? 'on' : '' }}">Semua</a>
  <a href="{{ route('teknisi.laporan.index', ['f' => 'dilaporkan', 'q' => $search]) }}" class="{{ $filter === 'dilaporkan' ? 'on' : '' }}">Dilaporkan</a>
  <a href="{{ route('teknisi.laporan.index', ['f' => 'diproses', 'q' => $search]) }}" class="{{ $filter === 'diproses' ? 'on' : '' }}">Diproses</a>
  <a href="{{ route('teknisi.laporan.index', ['f' => 'selesai', 'q' => $search]) }}" class="{{ $filter === 'selesai' ? 'on' : '' }}">Selesai</a>
</div>

{{-- Daftar Laporan --}}
@forelse ($tickets as $t)
  @php
    $tPhotos = [];
    foreach ($t->media as $m) {
        $tPhotos[] = [
            'url'   => $m->url,
            'phase' => $m->phase,
            'kind'  => $m->kind,
            'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : ($m->phase === 'before' ? 'Foto Sebelum' : 'Foto Kendala Awal')
        ];
    }
    $tLogs = [];
    foreach ($t->logs as $l) {
        $tLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
    }
    $tPayload = [
        'code'           => $t->code,
        'machine'        => ['name' => $t->machine->name, 'code' => $t->machine->code, 'location' => $t->machine->location],
        'status'         => $t->status,
        'display_status' => $t->display_status,
        'urgency'        => $t->urgency,
        'date_formatted' => $t->created_at->translatedFormat('d M Y, H:i'),
        'complaint'      => $t->complaint,
        'action'         => $t->action,
        'spareparts'     => $t->spareparts,
        'technician'     => $t->technician ? ['name' => $t->technician->name, 'whatsapp' => $t->technician->whatsapp] : null,
        'photos'         => $tPhotos,
        'logs'           => $tLogs,
        'show_url'       => route('teknisi.laporan.show', $t),
    ];
  @endphp
  <div class="tcard task {{ $t->urgency }}" style="margin-bottom:12px">
    <div class="t1">
      <span style="font-weight:800;color:var(--r700)">{{ $t->code }} · {{ $t->machine->code }}</span>
      @include('partials.chip', ['v' => $t->display_status])
    </div>

    <p style="margin:8px 0 6px">
      <b>{{ $t->machine->name }}</b> <span class="tiny">({{ $t->machine->location }})</span><br>
      <span class="muted small">{{ \Illuminate\Support\Str::limit($t->complaint, 85) }}</span>
    </p>

    <div class="row between" style="margin-bottom:10px">
      <div class="row" style="gap:6px">
        @include('partials.chip', ['v' => $t->urgency])
        @if ($t->media->count())
          <span class="tiny" style="color:var(--ink3);display:inline-flex;align-items:center;gap:3px">
            📷 {{ $t->media->count() }} media
          </span>
        @endif
      </div>
      <span class="tiny">{{ $t->created_at->translatedFormat('d M Y, H:i') }}</span>
    </div>

    {{-- Tombol Aksi CRUD (Icon Modern) --}}
    <div class="row between" style="border-top:1px solid var(--line);padding-top:10px;margin-top:4px;align-items:center">
      <span class="tiny muted" style="font-weight:600">Aksi Laporan:</span>
      <div style="display:inline-flex;align-items:center;gap:6px">
        <button type="button" class="btn-icon detail" data-ticket="{{ json_encode($tPayload) }}" onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" title="Lihat Detail & Foto">
          @include('partials.icon', ['n' => 'eye'])
        </button>
        <a class="btn-icon edit" href="{{ route('teknisi.laporan.edit', $t) }}" title="Edit Laporan Perbaikan">
          @include('partials.icon', ['n' => 'edit'])
        </a>
        <form method="POST" action="{{ route('teknisi.laporan.destroy', $t) }}" data-confirm="Apakah Anda yakin ingin menghapus laporan {{ $t->code }}?" style="margin:0">
          @csrf
          @method('DELETE')
          <button class="btn-icon danger" type="submit" title="Hapus Laporan Perbaikan">
            @include('partials.icon', ['n' => 'trash'])
          </button>
        </form>
      </div>
    </div>
  </div>
@empty
  <div class="tcard empty">
    <p style="margin-bottom:12px">Belum ada laporan perbaikan yang ditemukan.</p>
    <a class="btn primary small" href="{{ route('teknisi.laporan.create') }}">
      @include('partials.icon', ['n' => 'plus']) Buat Laporan Sekarang
    </a>
  </div>
@endforelse

<div class="pagination">
  {{ $tickets->links() }}
</div>

@include('partials.job-detail-modal')
@endsection
