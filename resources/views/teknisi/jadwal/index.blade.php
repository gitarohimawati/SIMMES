@extends('layouts.technician')
@section('title', 'Jadwal')
@section('heading', 'Jadwal Maintenance')
@section('sub', 'Tugas rutin yang ditugaskan kepada Anda')
@section('content')
@forelse ($schedules as $s)
  <div class="tcard">
    <div class="row between"><b>{{ $s->machine->label }}</b>@include('partials.chip', ['v' => $s->display_status])</div>
    <div class="muted small" style="margin:4px 0 10px">{{ $s->task }}{{ $s->interval_label ? ' · tiap '.$s->interval_label : '' }} · jatuh tempo <b>{{ $s->due_date->translatedFormat('d M Y') }}{{ $s->due_time ? ' · Jam '.$s->due_time.' WIB' : '' }}</b></div>
    <div class="row"><div class="progress" style="flex:1"><i style="width:{{ $s->progress }}%"></i></div><b class="small">{{ $s->progress }}%</b></div>
    @if ($s->status !== 'selesai')
    <form method="POST" action="{{ route('teknisi.jadwal.progress', $s) }}" style="margin-top:12px">@csrf
      <div class="pills" style="padding-bottom:6px">
        @foreach ([25 => 'Foto sebelum', 50 => 'Pengerjaan', 75 => 'Foto sesudah', 100 => 'Selesai'] as $p => $l)
          <button name="progress" value="{{ $p }}" class="btn {{ $s->progress >= $p ? 'primary' : '' }}" style="padding:8px 12px;font-size:12.5px">{{ $p }}% {{ $l }}</button>
        @endforeach
      </div>
    </form>
    @endif
  </div>
@empty <div class="tcard empty">Belum ada jadwal untuk Anda.</div> @endforelse
@endsection
