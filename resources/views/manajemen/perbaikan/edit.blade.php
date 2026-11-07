@extends('layouts.manager')
@section('title', 'Edit Tiket ' . $ticket->code)
@section('content')
<div style="max-width:820px;margin:0 auto">
  <div class="top">
    <div class="top-row">
      <div>
        <h1>Edit Tiket {{ $ticket->code }}</h1>
        <p class="muted small">Perbarui data laporan kendala, penugasan teknisi, atau status perbaikan.</p>
      </div>
      <div class="actions">
        <a class="btn" href="{{ url()->previous() ?: route('manajemen.perbaikan.index') }}">← Kembali</a>
      </div>
    </div>
  </div>

  <form class="card" style="max-width:820px;margin:0 auto" method="POST" action="{{ route('manajemen.perbaikan.update', $ticket) }}" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="row between wrap" style="background:#fff6f7;border:1px solid var(--rose200);border-radius:14px;padding:12px 18px;margin-bottom:20px;gap:12px">
      <div>
        <span class="tiny muted" style="display:block">Nomor Tiket</span>
        <b style="font-size:16px;color:var(--r800)">{{ $ticket->code }}</b>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Pelapor</span>
        <b>{{ $ticket->reporter?->name ?? 'Sistem / Anonim' }}</b>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Tanggal Masuk</span>
        <b>{{ $ticket->created_at->translatedFormat('d M Y, H:i') }}</b>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Status Terkini</span>
        @include('partials.chip', ['v' => $ticket->display_status])
      </div>
    </div>

    <div class="grid g2">
      <div style="grid-column:1 / -1">
        <label class="f" style="margin-top:0">Mesin Terkait <span style="color:var(--r600)">*</span></label>
        <select class="select" name="machine_id" required>
          @foreach ($machines as $m)
            <option value="{{ $m->id }}" @selected(old('machine_id', $ticket->machine_id) == $m->id)>
              {{ $m->name }} ({{ $m->code }}) — Lokasi: {{ $m->location }}
            </option>
          @endforeach
        </select>
        @error('machine_id') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div style="grid-column:1 / -1">
        <label class="f">Keluhan / Masalah Mesin <span style="color:var(--r600)">*</span></label>
        <textarea class="textarea" name="complaint" rows="3" placeholder="Deskripsikan masalah kerusakan mesin..." required>{{ old('complaint', $ticket->complaint) }}</textarea>
        @error('complaint') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tingkat Urgensi <span style="color:var(--r600)">*</span></label>
        <select class="select" name="urgency" required>
          <option value="rendah" @selected(old('urgency', $ticket->urgency) === 'rendah')>Rendah</option>
          <option value="sedang" @selected(old('urgency', $ticket->urgency) === 'sedang')>Sedang</option>
          <option value="tinggi" @selected(old('urgency', $ticket->urgency) === 'tinggi')>Tinggi (Prioritas)</option>
        </select>
        @error('urgency') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Status Pengerjaan <span style="color:var(--r600)">*</span></label>
        <select class="select" name="status" required>
          <option value="dilaporkan" @selected(old('status', $ticket->status) === 'dilaporkan')>Dilaporkan (Menunggu Teknisi)</option>
          <option value="ditugaskan" @selected(old('status', $ticket->status) === 'ditugaskan')>Ditugaskan</option>
          <option value="diproses" @selected(old('status', $ticket->status) === 'diproses')>Diproses / Sedang Dikerjakan</option>
          <option value="selesai" @selected(old('status', $ticket->status) === 'selesai')>Selesai</option>
        </select>
        @error('status') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Teknisi Penanggung Jawab</label>
        <select class="select" name="technician_id">
          <option value="">Belum Ditugaskan (Terbuka untuk siapa saja)</option>
          @foreach ($technicians as $tech)
            <option value="{{ $tech->id }}" @selected(old('technician_id', $ticket->technician_id) == $tech->id)>
              {{ $tech->name }} (NIK: {{ $tech->nik ?? '-' }}) {{ $tech->whatsapp ? '| WA: '.$tech->whatsapp : '' }}
            </option>
          @endforeach
        </select>
        <span class="tiny muted">Kosongkan jika ingin sistem "siapa cepat dia dapat".</span>
        @error('technician_id') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Batas Waktu Pengerjaan (Due Date)</label>
        <input class="input" type="datetime-local" name="due_at" value="{{ old('due_at', $ticket->due_at?->format('Y-m-d\TH:i')) }}">
        <span class="tiny muted">Estimasi batas maksimal perbaikan tuntas.</span>
        @error('due_at') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tanggal Mulai Perbaikan</label>
        <input class="input" type="datetime-local" name="started_at" value="{{ old('started_at', $ticket->started_at?->format('Y-m-d\TH:i')) }}">
        <span class="tiny muted">Waktu teknisi mulai melakukan perbaikan.</span>
        @error('started_at') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tanggal Selesai Perbaikan</label>
        <input class="input" type="datetime-local" name="finished_at" value="{{ old('finished_at', $ticket->finished_at?->format('Y-m-d\TH:i')) }}">
        <span class="tiny muted">Waktu mesin dinyatakan selesai & normal.</span>
        @error('finished_at') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Progress Perbaikan (%)</label>
        <input class="input" type="number" min="0" max="100" name="progress" value="{{ old('progress', $ticket->progress) }}" placeholder="0 - 100">
        <span class="tiny muted">0: Belum mulai, 25: Foto sebelum, 50: Pengerjaan, 75: Foto sesudah, 100: Selesai.</span>
        @error('progress') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="f">Tindakan / Solusi yang Dilakukan</label>
        <input class="input" name="action" value="{{ old('action', $ticket->action) }}" placeholder="Contoh: Penggantian bearing roda dan pelumasan oli">
        @error('action') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div style="grid-column:1 / -1">
        <label class="f">Suku Cadang / Sparepart yang Diganti</label>
        <input class="input" name="spareparts" value="{{ old('spareparts', $ticket->spareparts) }}" placeholder="Contoh: Bearing 6204-2RS (2 pcs), Fan Belt A-34">
        @error('spareparts') <div class="err">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="form-actions-end" style="display:flex;justify-content:space-between;align-items:center;margin-top:28px;padding-top:16px;border-top:1px solid var(--line)">
      <a href="{{ route('manajemen.perbaikan.show', $ticket) }}" class="link">Lihat Detail Lengkap & Riwayat Foto →</a>
      <div style="display:flex;gap:10px">
        <a class="btn" href="{{ url()->previous() ?: route('manajemen.perbaikan.index') }}">Batal</a>
        <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13.5px">
          💾 Simpan Perubahan
        </button>
      </div>
    </div>
  </form>
</div>
@endsection

