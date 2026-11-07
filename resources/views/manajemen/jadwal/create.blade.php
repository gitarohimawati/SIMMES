@extends('layouts.manager')
@section('title', 'Tambah Jadwal Maintenance')
@section('content')
<div style="max-width:820px;margin:0 auto">
  <div class="top">
    <div class="top-row">
      <h1>Tambah Jadwal Maintenance</h1>
      <a class="btn" href="{{ route('manajemen.jadwal.index') }}">← Kembali</a>
    </div>
    <p class="muted small">Tentukan mesin target dan atur siklus pemeliharaan berkala.</p>
  </div>

  <form class="card" style="max-width:820px;margin:0 auto" method="POST" action="{{ route('manajemen.jadwal.store') }}">
    @csrf

    {{-- Pencarian & Pemilihan Mesin Terpadu --}}
    @include('partials.machine-picker')

    <div class="grid g2" style="margin-top:16px">
      <div>
        <label class="f" style="margin-top:0">Jenis / Tugas Perawatan</label>
        <input class="input" name="task" value="{{ old('task') }}" placeholder="Contoh: Pelumasan bearing & cek suhu" required>
        @error('task') <div class="err">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="f" style="margin-top:0">Tipe Pemeliharaan</label>
        <select class="select" name="type">
          <option value="rutin" @selected(old('type', 'rutin') == 'rutin')>Rutin (Preventive)</option>
          <option value="insiden" @selected(old('type') == 'insiden')>Insiden / Korektif</option>
        </select>
        @error('type') <div class="err">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="f">Interval Siklus (bulan)</label>
        <input class="input" type="number" min="1" max="60" name="interval_months" value="{{ old('interval_months', 3) }}" placeholder="Contoh: 3">
        <span class="tiny muted">Pengulangan otomatis setelah jadwal diselesaikan.</span>
        @error('interval_months') <div class="err">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="f">Tanggal Pelaksanaan (Jatuh Tempo)</label>
        <input class="input" type="date" name="due_date" value="{{ old('due_date', now()->format('Y-m-d')) }}" required>
        @error('due_date') <div class="err">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="f">Waktu / Jam Pelaksanaan</label>
        <input class="input" type="time" name="due_time" value="{{ old('due_time', '09:00') }}" required>
        <span class="tiny muted">Jam pelaksanaan yang diinfokan ke teknisi.</span>
        @error('due_time') <div class="err">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="f">Tugaskan Teknisi</label>
        <select class="select" name="technician_id">
          <option value="">Belum ditugaskan (Bisa nanti)</option>
          @foreach ($technicians as $u)
            <option value="{{ $u->id }}" @selected(old('technician_id') == $u->id)>
              {{ $u->name }} {{ $u->whatsapp ? '| WA: '.$u->whatsapp : '(Belum ada WA)' }}
            </option>
          @endforeach
        </select>
        <span class="tiny muted">Teknisi penanggung jawab maintenance.</span>
        @error('technician_id') <div class="err">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="form-actions-end" style="display:flex;justify-content:flex-end;margin-top:24px">
      <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13.5px">
        💾 Simpan Jadwal Maintenance
      </button>
    </div>
  </form>
</div>
@endsection
