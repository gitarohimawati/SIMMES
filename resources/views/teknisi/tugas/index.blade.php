@extends('layouts.technician')
@section('title', 'Tugas Teknisi')
@section('heading', 'Tugas Teknisi')
@section('sub', $tickets->count().' tugas')
@section('content')
<form method="GET" class="tcard" style="padding:10px 14px">
  <input type="hidden" name="f" value="{{ $filter }}">
  <input class="input" style="border:0;padding:6px" name="q" value="{{ request('q') }}" placeholder="Cari kode tiket, nama atau kode mesin...">
</form>

<div class="pills">
  <a id="pillTersedia" href="{{ route('teknisi.tugas.index', ['f' => 'tersedia']) }}" class="{{ $filter === 'tersedia' ? 'on' : '' }}" style="{{ $availableCount > 0 && $filter !== 'tersedia' ? 'border-color:var(--r500);color:var(--r600);font-weight:800' : '' }}">
    ⚡ Tersedia (<span id="availablePillCount">{{ $availableCount }}</span>)
  </a>
  <a href="{{ route('teknisi.tugas.index', ['f' => 'semua']) }}" class="{{ $filter === 'semua' ? 'on' : '' }}">Tugas Saya</a>
  <a href="{{ route('teknisi.tugas.index', ['f' => 'belum']) }}" class="{{ $filter === 'belum' ? 'on' : '' }}">Belum Mulai</a>
  <a href="{{ route('teknisi.tugas.index', ['f' => 'proses']) }}" class="{{ $filter === 'proses' ? 'on' : '' }}">Dikerjakan</a>
  <a href="{{ route('teknisi.tugas.index', ['f' => 'selesai']) }}" class="{{ $filter === 'selesai' ? 'on' : '' }}">Selesai</a>
</div>

<div id="availableTasksHero" class="tcard hero" style="background:linear-gradient(135deg,#fff1f3 0%,#ffe4e8 100%);border-color:var(--r300);margin-bottom:14px;{{ ($availableCount > 0 && $filter !== 'tersedia') ? '' : 'display:none;' }}">
  <div class="row between" style="gap:8px;flex-wrap:wrap;align-items:center">
    <div>
      <b id="availableTasksHeroText" style="color:var(--r700);font-size:14px">⚡ Ada {{ $availableCount }} tugas belum diambil teknisi</b>
      <div class="tiny" style="color:var(--ink2)">Siapa cepat dia dapat, segera ambil tugas sebelum diambil teknisi lain.</div>
    </div>
    <a class="btn primary" href="{{ route('teknisi.tugas.index', ['f' => 'tersedia']) }}" style="font-size:12px;padding:8px 12px;white-space:nowrap">
      Ambil Cepat
    </a>
  </div>
</div>

<div id="ticketsListContainer">
@forelse ($tickets as $t)
  <div id="ticket-item-{{ $t->id }}" data-ticket-id="{{ $t->id }}" @if ($t->technician_id === null) data-available-ticket-id="{{ $t->id }}" @endif class="tcard task {{ $t->urgency }}" style="{{ $t->technician_id === null ? 'border-left:5px solid #ea580c;' : '' }}">
    <div class="t1">
      <a href="{{ route('teknisi.tugas.show', $t) }}">{{ $t->code }} · {{ $t->machine->code }}</a>
      @if ($t->technician_id === null)
        <span class="chip" style="background:#ffedd5;color:#c2410c;font-weight:800">⚡ Belum Diambil</span>
      @else
        @include('partials.chip', ['v' => $t->display_status])
      @endif
    </div>

    <p style="margin:8px 0">
      <b>{{ $t->machine->name }}, {{ $t->machine->location }}</b><br>
      <span class="muted small">{{ $t->complaint }}</span>
    </p>

    <div class="row between" style="margin-bottom:12px">
      @include('partials.chip', ['v' => $t->urgency])
      <span class="tiny muted">Batas: {{ $t->due_at?->translatedFormat('d M H:i') ?? '-' }}</span>
    </div>

    @if ($t->technician_id === null)
      <div class="row" style="gap:8px">
        <form method="POST" action="{{ route('teknisi.tugas.claim', $t) }}" style="flex:1" onsubmit="const b=this.querySelector('button');if(b){b.disabled=true;b.innerHTML='⚡ Mengambil...'};">
          @csrf
          <button type="submit" class="btn primary block" style="background:linear-gradient(135deg,#ea580c 0%,#c2410c 100%);justify-content:center">
            ⚡ Ambil Tugas Ini
          </button>
        </form>
        <a class="btn block" style="padding:10px 14px" href="{{ route('teknisi.tugas.show', $t) }}">Lihat</a>
      </div>
    @elseif (in_array($t->status, ['ditugaskan', 'dilaporkan']))
      <form method="POST" action="{{ route('teknisi.tugas.start', $t) }}">
        @csrf
        <button class="btn primary block" style="justify-content:center">Mulai Proses Pengerjaan</button>
      </form>
    @else
      <a class="btn block" style="justify-content:center" href="{{ route('teknisi.tugas.show', $t) }}">Buka Detail & Progress</a>
    @endif
  </div>
@empty
@endforelse

<div class="tcard empty" id="emptyTersediaState" style="{{ $tickets->isEmpty() ? '' : 'display:none;' }}">
  @if ($filter === 'tersedia')
    Semua tugas perbaikan sudah diambil oleh teknisi. Tidak ada tugas terbuka saat ini 👍
  @else
    Tidak ada tugas pada filter ini.
  @endif
</div>
</div>
@endsection
