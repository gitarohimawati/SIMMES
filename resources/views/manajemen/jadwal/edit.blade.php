@extends('layouts.manager')
@section('title', 'Edit Jadwal Maintenance')
@section('content')
<div style="max-width:820px;margin:0 auto">
  <div class="top">
    <div class="top-row">
      <div>
        <h1>Edit Jadwal Maintenance</h1>
        <p class="muted small">Perbarui parameter siklus atau status pekerjaan pemeliharaan mesin.</p>
      </div>
      <div class="actions">
        <a class="btn" href="{{ route('manajemen.jadwal.index') }}">← Kembali</a>
      </div>
    </div>
  </div>

  <form class="card" style="max-width:820px;margin:0 auto" method="POST" action="{{ route('manajemen.jadwal.update', $jadwal) }}" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="row between wrap" style="background:#fff6f7;border:1px solid var(--rose200);border-radius:14px;padding:12px 18px;margin-bottom:20px;gap:12px">
      <div>
        <span class="tiny muted" style="display:block">Mesin Saat Ini</span>
        <b style="font-size:16px;color:var(--r800)">{{ $jadwal->machine->label }}</b>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Lokasi</span>
        <b>{{ $jadwal->machine->location }}</b>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Status Jadwal</span>
        @include('partials.chip', ['v' => $jadwal->display_status])
      </div>
    </div>

    <div class="grid g2">
      <div style="grid-column:1 / -1">
        <label class="f" style="margin-top:0">Pilih Mesin Target <span style="color:var(--r600)">*</span></label>
        <select class="select" name="machine_id" required>
          @foreach ($machines as $m)
            <option value="{{ $m->id }}" @selected(old('machine_id', $jadwal->machine_id) == $m->id)>
              {{ $m->name }} ({{ $m->code }}) — Lokasi: {{ $m->location }}
            </option>
          @endforeach
        </select>
        @error('machine_id') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Jenis / Tugas Perawatan <span style="color:var(--r600)">*</span></label>
        <input class="input" name="task" value="{{ old('task', $jadwal->task) }}" placeholder="Contoh: Pelumasan bearing & cek suhu" required>
        @error('task') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tipe Pemeliharaan <span style="color:var(--r600)">*</span></label>
        <select class="select" name="type" required>
          <option value="rutin" @selected(old('type', $jadwal->type) === 'rutin')>Rutin (Preventive)</option>
          <option value="insiden" @selected(old('type', $jadwal->type) === 'insiden')>Insiden / Korektif</option>
        </select>
        @error('type') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Interval Siklus (bulan)</label>
        <input class="input" type="number" min="1" max="60" name="interval_months" value="{{ old('interval_months', $jadwal->interval_months) }}" placeholder="Contoh: 3">
        <span class="tiny muted">Kosongkan jika bukan jadwal berulang.</span>
        @error('interval_months') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Status Pekerjaan <span style="color:var(--r600)">*</span></label>
        <select class="select" name="status" required>
          <option value="terjadwal" @selected(old('status', $jadwal->status) === 'terjadwal')>Terjadwal</option>
          <option value="dikerjakan" @selected(old('status', $jadwal->status) === 'dikerjakan')>Sedang Dikerjakan</option>
          <option value="selesai" @selected(old('status', $jadwal->status) === 'selesai')>Selesai</option>
        </select>
        @error('status') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tanggal Pelaksanaan (Jatuh Tempo) <span style="color:var(--r600)">*</span></label>
        <input class="input" type="date" name="due_date" value="{{ old('due_date', $jadwal->due_date?->format('Y-m-d')) }}" required>
        @error('due_date') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Waktu / Jam Pelaksanaan</label>
        <input class="input" type="time" name="due_time" value="{{ old('due_time', $jadwal->due_time ?: '09:00') }}" required>
        <span class="tiny muted">Jam pelaksanaan yang diinfokan ke teknisi.</span>
        @error('due_time') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tugaskan Teknisi</label>
        <select class="select" name="technician_id">
          <option value="">Belum Ditugaskan</option>
          @foreach ($technicians as $u)
            <option value="{{ $u->id }}" @selected(old('technician_id', $jadwal->technician_id) == $u->id)>
              {{ $u->name }} {{ $u->whatsapp ? '| WA: '.$u->whatsapp : '(Belum ada WA)' }}
            </option>
          @endforeach
        </select>
        <span class="tiny muted">Teknisi penanggung jawab maintenance.</span>
        @error('technician_id') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Progress Pengerjaan (%)</label>
        <input class="input" type="number" min="0" max="100" name="progress" value="{{ old('progress', $jadwal->progress) }}" placeholder="0 - 100">
        <span class="tiny muted">0: Belum mulai, 50: Pengerjaan, 100: Tuntas.</span>
        @error('progress') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div style="grid-column:1 / -1">
        <label class="f">Suku Cadang / Sparepart yang Digunakan</label>
        <input class="input" name="spareparts" value="{{ old('spareparts', $jadwal->spareparts) }}" placeholder="Contoh: Oli hidrolik ISO VG 46 (5 Liter), Seal Kit">
        @error('spareparts') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div style="grid-column:1 / -1">
        <label class="f">Catatan Hasil Pemeriksaan & Tindakan</label>
        <textarea class="input" name="notes" rows="3" placeholder="Catatan teknisi mengenai perawatan atau pengecekan mesin">{{ old('notes', $jadwal->notes) }}</textarea>
        @error('notes') <div class="err">{{ $message }}</div> @enderror
      </div>

      @if ($jadwal->before_url || $jadwal->after_url)
        <div style="grid-column:1 / -1;background:#fafafa;border:1px solid var(--line);border-radius:12px;padding:14px;margin-top:6px">
          <b class="small" style="display:block;margin-bottom:10px">Dokumentasi Foto Pengerjaan:</b>
          <div style="display:flex;gap:16px;flex-wrap:wrap">
            @if ($jadwal->before_url)
              <div>
                <span class="tiny muted" style="display:block;margin-bottom:4px">Foto Sebelum:</span>
                <a href="{{ $jadwal->before_url }}" target="_blank">
                  <img src="{{ $jadwal->before_url }}" alt="Foto Sebelum" style="width:140px;height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                </a>
              </div>
            @endif
            @if ($jadwal->after_url)
              <div>
                <span class="tiny muted" style="display:block;margin-bottom:4px">Foto Sesudah:</span>
                <a href="{{ $jadwal->after_url }}" target="_blank">
                  <img src="{{ $jadwal->after_url }}" alt="Foto Sesudah" style="width:140px;height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                </a>
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>

    <div class="form-actions-end" style="display:flex;justify-content:flex-end;gap:10px;margin-top:28px;padding-top:16px;border-top:1px solid var(--line)">
      <a class="btn" href="{{ route('manajemen.jadwal.index') }}">Batal</a>
      <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13.5px">
        💾 Simpan Perubahan Jadwal
      </button>
    </div>
  </form>
</div>
@endsection

