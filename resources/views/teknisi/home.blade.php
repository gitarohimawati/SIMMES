@extends('layouts.technician')
@section('title', 'Beranda')
@section('heading', 'Halo, '.explode(' ', auth()->user()->name)[0].' 👋')
@section('sub', 'Dashboard Teknisi · '.now()->translatedFormat('l, d F Y'))
@section('content')
<div id="availableTicketsHero" class="tcard hero" style="background:linear-gradient(135deg,#fff1f3 0%,#ffe4e8 100%);border:1.5px solid var(--r400);margin-bottom:14px;box-shadow:0 8px 20px -8px rgba(220,38,38,0.25);{{ $availableCount > 0 ? '' : 'display:none;' }}">
  <div class="row between" style="gap:10px;flex-wrap:wrap;align-items:center">
    <div style="flex:1;min-width:200px">
      <div style="display:flex;align-items:center;gap:6px">
        <span class="chip" style="background:#ea580c;color:#fff;font-size:11px;padding:2px 8px;font-weight:800">⚡ TERBUKA</span>
        <b id="availableTicketsCount" style="color:var(--r800);font-size:14.5px">{{ $availableCount }} Laporan Terbuka (Belum Diambil)</b>
      </div>
      <div id="availableTicketsText" class="tiny" style="color:var(--ink2);margin-top:4px">
        @if (isset($latestAvailable) && $latestAvailable)
          <span>Tiket <b>{{ $latestAvailable->code }}</b> ({{ $latestAvailable->machine?->name ?? 'Mesin' }}) menunggu teknisi.</span>
        @else
          <span>Siapa cepat dia dapat. Ambil tugas sekarang sebelum teknisi lain.</span>
        @endif
      </div>
    </div>
    <a class="btn primary" href="{{ route('teknisi.tugas.index', ['f' => 'tersedia']) }}" style="font-size:12.5px;padding:9px 14px;white-space:nowrap">
      Ambil Tugas
    </a>
  </div>
</div>

<div class="tstats">
  <a class="tstat hot" href="{{ route('teknisi.tugas.index', ['f' => 'belum']) }}"><b>{{ $stats['baru'] }}</b><span>Tugas baru</span></a>
  <a class="tstat" href="{{ route('teknisi.tugas.index', ['f' => 'proses']) }}"><b>{{ $stats['proses'] }}</b><span>Sedang dikerjakan</span></a>
  <a class="tstat" href="{{ route('teknisi.tugas.index', ['f' => 'selesai']) }}"><b>{{ $stats['selesai'] }}</b><span>Selesai bulan ini</span></a>
  <div class="tstat"><b style="color:var(--r600)">{{ $stats['terlambat'] }}</b><span>Melewati batas</span></div>
</div>

<div class="row" style="gap:10px;margin-bottom:14px">
  <a class="btn primary block" href="{{ route('teknisi.laporan.create') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;font-size:13px;padding:12px 10px">
    @include('partials.icon', ['n' => 'plus']) Buat Laporan
  </a>
  <a class="btn block" href="{{ route('teknisi.jadwal') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;font-size:13px;padding:12px 10px">
    @include('partials.icon', ['n' => 'calendar']) Jadwal Perawatan
  </a>
</div>

@if ($reminders->count())
<div class="tcard hero">
  <div class="row between"><b>🔔 Pengingat jadwal</b></div>
  @foreach ($reminders as $r)
    <div class="reminder">
      <span class="rem-tag {{ $r['tone'] === 'late' ? 'late' : '' }}">{{ $r['label'] }}</span>
      <div>
        <b class="small">{{ $r['s']->task }} · {{ $r['s']->machine->code }}</b>
        <div class="tiny">{{ $r['s']->machine->name }} · {{ $r['s']->due_date->translatedFormat('d M') }}{{ $r['s']->due_time ? ' · Jam '.$r['s']->due_time : '' }}</div>
      </div>
    </div>
  @endforeach
</div>
@endif

<div class="sect"><h2>Tugas Perbaikan Saya</h2><a class="link small" href="{{ route('teknisi.tugas.index') }}">Semua</a></div>
@forelse ($priority as $t)
  <a class="tcard task {{ $t->urgency }}" href="{{ route('teknisi.tugas.show', $t) }}">
    <div class="t1"><span>{{ $t->code }} · {{ $t->machine->code }}</span>@include('partials.chip', ['v' => $t->display_status])</div>
    <p><b>{{ $t->machine->name }}, {{ $t->machine->location }}</b><br><span class="muted small">{{ \Illuminate\Support\Str::limit($t->complaint, 70) }}</span></p>
    <div class="row between">@include('partials.chip', ['v' => $t->urgency])<div class="progress" style="width:90px"><i style="width:{{ $t->progress }}%"></i></div></div>
  </a>
@empty
  <div class="tcard empty">Tidak ada tugas perbaikan saat ini.</div>
@endforelse

<div class="sect"><h2>Jadwal maintenance saya</h2><a class="link small" href="{{ route('teknisi.jadwal') }}">Semua</a></div>
<div class="tcard">
  @forelse ($schedules as $s)
    <div class="reminder">
      <div style="flex:1">
        <b class="small">{{ $s->machine->label }}</b>
        <div class="tiny">{{ $s->task }} · {{ $s->due_date->translatedFormat('d M Y') }}{{ $s->due_time ? ' · Jam '.$s->due_time : '' }}</div>
      </div>
      @include('partials.chip', ['v' => $s->display_status])
    </div>
  @empty <div class="empty">Belum ada jadwal maintenance.</div> @endforelse
</div>

<div class="sect"><h2>Aktivitas 7 hari</h2></div>
<div class="tcard">
  @php $mx = max(1, $week->max('n')); @endphp
  <div class="minibars">@foreach ($week as $w)<div><i style="height:{{ max(7, $w['n'] / $mx * 100) }}%"></i>{{ $w['d'] }}</div>@endforeach</div>
</div>

@if ($recent->count())
<div class="sect"><h2>Baru selesai</h2></div>
<div class="tcard">
  @foreach ($recent as $t)<div class="reminder"><div style="flex:1"><b class="small">{{ $t->code }} · {{ $t->machine->name }}</b><div class="tiny">{{ $t->finished_at?->diffForHumans() }}</div></div>@include('partials.chip', ['v' => 'selesai'])</div>@endforeach
</div>
@endif
@endsection
