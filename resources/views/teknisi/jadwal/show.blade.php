@extends('layouts.technician')
@section('title', 'Perawatan ' . $jadwal->machine->code)
@section('heading', 'Perawatan Mesin')
@section('back', route('teknisi.jadwal'))
@section('nonav', '1')
@section('content')

@php
  $isDone = $jadwal->status === 'selesai';
  $hasBefore = !empty($jadwal->before_path);
  $hasAfter  = !empty($jadwal->after_path);
@endphp

{{-- Banner Pemberitahuan Status Selesai / Kirim WA --}}
@if ($isDone)
  <div class="tcard hero" style="background:#f0fdf4;border:1.5px solid #86efac;margin-bottom:14px;padding:16px">
    <div class="row between" style="gap:10px;align-items:flex-start">
      <div>
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:16px;color:#16a34a">✓</span>
          <b style="color:#14532d;font-size:15px">Pemeliharaan Selesai Dikerjakan!</b>
        </div>
        <p class="small" style="color:#166534;margin:4px 0 0">
          Diselesaikan pada <b>{{ $jadwal->completed_at ? $jadwal->completed_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</b>. Status mesin telah diperbarui menjadi aktif.
        </p>
      </div>
      <span class="chip c-selesai"><span class="chip-dot"></span> Selesai</span>
    </div>

    @if (!empty($waCompletedLink))
      <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #bbf7d0">
        <span class="tiny muted" style="display:block;margin-bottom:6px">Kirimkan konfirmasi penyelesaian pekerjaan ini ke WhatsApp Manajemen:</span>
        <a href="{{ $waCompletedLink }}" target="_blank" rel="noopener noreferrer" class="btn primary block" style="background:#16a34a;border-color:#16a34a;justify-content:center;gap:8px;font-size:13.5px;padding:10px 14px">
          @include('partials.icon', ['n' => 'whatsapp']) Kirim Laporan Selesai ke WA Manajemen
        </a>
      </div>
    @endif
  </div>
@elseif ($jadwal->display_status === 'terlambat')
  <div class="tcard hero" style="background:#fff1f2;border:1.5px solid #fecdd3;margin-bottom:14px;padding:14px 16px">
    <b style="color:#be123c;font-size:14px">⚠️ Melewati Tanggal Jatuh Tempo</b>
    <div class="tiny" style="color:#881337;margin-top:2px">Jadwal perawatan ini telah melewati tanggal jatuh tempo. Harap segera laksanakan pemeriksaan mesin.</div>
  </div>
@endif

{{-- Kartu Info Mesin & Tugas --}}
<div class="tcard">
  <div class="row between">
    <b style="font-size:20px;color:var(--ink)">{{ $jadwal->task }}</b>
    @include('partials.chip', ['v' => $jadwal->display_status])
  </div>

  <div style="margin-top:12px;display:grid;gap:6px">
    <div class="row" style="gap:8px">
      <span class="tiny muted" style="width:90px;flex-shrink:0">Mesin</span>
      <b class="small" style="color:var(--ink)">{{ $jadwal->machine->name }} ({{ $jadwal->machine->code }})</b>
    </div>
    <div class="row" style="gap:8px">
      <span class="tiny muted" style="width:90px;flex-shrink:0">Lokasi</span>
      <span class="small" style="color:var(--ink2)">{{ $jadwal->machine->location }}</span>
    </div>
    <div class="row" style="gap:8px">
      <span class="tiny muted" style="width:90px;flex-shrink:0">Waktu Jadwal</span>
      <span class="small" style="color:var(--ink)">
        <b>{{ $jadwal->due_date ? $jadwal->due_date->translatedFormat('l, d F Y') : '-' }}</b>
        @if ($jadwal->due_time) · Pukul {{ $jadwal->due_time }} WIB @endif
      </span>
    </div>
    <div class="row" style="gap:8px">
      <span class="tiny muted" style="width:90px;flex-shrink:0">Siklus Servis</span>
      <span class="small" style="color:var(--ink2)">
        {{ $jadwal->interval_label ? 'Tiap ' . $jadwal->interval_label : ($jadwal->interval_months ? 'Tiap ' . $jadwal->interval_months . ' bulan' : 'Rutin Berkala') }}
      </span>
    </div>
  </div>
</div>

{{-- Stepper Progress --}}
<div class="tcard">
  <div class="row between">
    <b>Progress Pengerjaan</b>
    <b style="color:var(--brand)">{{ $jadwal->progress }}%</b>
  </div>
  <div class="progress" style="margin-top:8px"><i style="width:{{ $jadwal->progress }}%"></i></div>
  <ul class="steps" style="margin-top:12px">
    <li class="{{ $hasBefore || $jadwal->progress >= 25 ? 'done' : '' }}"><i>✓</i> Foto Sebelum</li>
    <li class="{{ $jadwal->progress >= 50 ? 'done' : '' }}"><i>✓</i> Pengerjaan</li>
    <li class="{{ $hasAfter || $jadwal->progress >= 75 ? 'done' : '' }}"><i>✓</i> Foto Sesudah</li>
    <li class="{{ $isDone ? 'done' : '' }}"><i>✓</i> Selesai</li>
  </ul>
</div>

{{-- Dokumentasi Foto Bukti (Sebelum & Sesudah) --}}
<div class="tcard">
  <div class="row between">
    <b>Dokumentasi Foto Bukti</b>
    <span class="tiny muted">Sebelum & Sesudah</span>
  </div>

  <div class="grid g2" style="gap:12px;margin-top:14px">
    {{-- Foto Sebelum --}}
    <div style="border:1px dashed var(--line);border-radius:12px;padding:12px;text-align:center;background:#fafafa">
      <span class="tiny muted" style="display:block;margin-bottom:8px;font-weight:700">1. Foto Sebelum Perawatan</span>
      @if ($hasBefore)
        <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--line);background:#fff;margin-bottom:8px">
          <img src="{{ $jadwal->before_url }}" alt="Foto Sebelum" style="width:100%;height:130px;object-fit:cover;display:block">
        </div>
        <span class="chip c-selesai" style="font-size:10.5px">✓ Sudah Diunggah</span>
        @if (!$isDone)
          <form method="POST" action="{{ route('teknisi.jadwal.upload', $jadwal) }}" enctype="multipart/form-data" style="margin-top:8px">
            @csrf
            <input type="hidden" name="phase" value="before">
            <label class="btn block" style="font-size:11px;padding:6px;cursor:pointer">
              Ganti Foto
              <input type="file" name="photo" accept="image/*" hidden onchange="this.form.submit()">
            </label>
          </form>
        @endif
      @else
        <div style="padding:16px 8px">
          <span style="font-size:24px;display:block;margin-bottom:4px">📷</span>
          <span class="tiny muted" style="display:block;margin-bottom:10px">Foto kondisi mesin sebelum mulai</span>
          @if (!$isDone)
            <form method="POST" action="{{ route('teknisi.jadwal.upload', $jadwal) }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="phase" value="before">
              <label class="btn primary block" style="font-size:11.5px;padding:8px;cursor:pointer">
                + Ambil / Upload Foto
                <input type="file" name="photo" accept="image/*" hidden onchange="this.form.submit()">
              </label>
            </form>
          @else
            <span class="tiny muted">Tidak ada foto</span>
          @endif
        </div>
      @endif
    </div>

    {{-- Foto Sesudah --}}
    <div style="border:1px dashed var(--line);border-radius:12px;padding:12px;text-align:center;background:#fafafa">
      <span class="tiny muted" style="display:block;margin-bottom:8px;font-weight:700">2. Foto Sesudah Perawatan</span>
      @if ($hasAfter)
        <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--line);background:#fff;margin-bottom:8px">
          <img src="{{ $jadwal->after_url }}" alt="Foto Sesudah" style="width:100%;height:130px;object-fit:cover;display:block">
        </div>
        <span class="chip c-selesai" style="font-size:10.5px">✓ Sudah Diunggah</span>
        @if (!$isDone)
          <form method="POST" action="{{ route('teknisi.jadwal.upload', $jadwal) }}" enctype="multipart/form-data" style="margin-top:8px">
            @csrf
            <input type="hidden" name="phase" value="after">
            <label class="btn block" style="font-size:11px;padding:6px;cursor:pointer">
              Ganti Foto
              <input type="file" name="photo" accept="image/*" hidden onchange="this.form.submit()">
            </label>
          </form>
        @endif
      @else
        <div style="padding:16px 8px">
          <span style="font-size:24px;display:block;margin-bottom:4px">📸</span>
          <span class="tiny muted" style="display:block;margin-bottom:10px">Foto hasil akhir perawatan mesin</span>
          @if (!$isDone)
            <form method="POST" action="{{ route('teknisi.jadwal.upload', $jadwal) }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="phase" value="after">
              <label class="btn primary block" style="font-size:11.5px;padding:8px;cursor:pointer">
                + Ambil / Upload Foto
                <input type="file" name="photo" accept="image/*" hidden onchange="this.form.submit()">
              </label>
            </form>
          @else
            <span class="tiny muted">Tidak ada foto</span>
          @endif
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Form Penyelesaian Maintenance (Jika Belum Selesai) --}}
@if (!$isDone)
  <form class="tcard" method="POST" action="{{ route('teknisi.jadwal.selesai', $jadwal) }}" enctype="multipart/form-data">
    @csrf

    <b style="font-size:15px;color:var(--ink);display:block;margin-bottom:4px">Laporan Hasil Pengerjaan</b>
    <span class="tiny muted" style="display:block;margin-bottom:14px">Isi suku cadang dan catatan hasil perawatan untuk memberitahu manajemen.</span>

    <div style="display:grid;gap:12px">
      <div>
        <label class="f" style="margin:0 0 6px">Suku Cadang / Bahan yang Digunakan (Opsional)</label>
        <input class="input" name="spareparts" value="{{ old('spareparts', $jadwal->spareparts) }}" placeholder="Contoh: Oli Shell Tellus 46 (5L), Seal O-Ring">
        <span class="tiny muted" style="display:block;margin-top:3px">Daftar part atau pelumas yang diganti/ditambahkan.</span>
      </div>

      <div>
        <label class="f" style="margin:0 0 6px">Catatan Hasil Pemeriksaan & Tindakan</label>
        <textarea class="input" name="notes" rows="3" placeholder="Contoh: Rantai telah dilumasi, bearing dibersihkan, kebocoran oli nihil, mesin berjalan normal saat uji beban.">{{ old('notes', $jadwal->notes) }}</textarea>
        <span class="tiny muted" style="display:block;margin-top:3px">Jelaskan kondisi mesin setelah Anda periksa/rawat.</span>
      </div>

      @if (!$hasAfter)
        <div>
          <label class="f" style="margin:0 0 6px">Unggah Foto Sesudah (Bukti Akhir)</label>
          <input class="input" type="file" name="after_photo" accept="image/*">
          <span class="tiny muted" style="display:block;margin-top:3px">Bisa diunggah langsung sekarang atau melalui tombol di atas.</span>
        </div>
      @endif
    </div>

    <div style="margin-top:18px">
      <button type="submit" class="btn primary block" style="justify-content:center;font-size:14px;padding:12px;font-weight:700" onclick="return confirm('Apakah pekerjaan maintenance ini telah selesai dan siap dilaporkan?')">
        ✓ Tandai Selesai & Beritahu Manajemen
      </button>
    </div>
  </form>
@else
  {{-- Detail Hasil Pekerjaan Yang Sudah Selesai --}}
  <div class="tcard">
    <b style="font-size:15px;color:var(--ink);display:block;margin-bottom:12px">Hasil Pengerjaan Teknisi</b>

    <div style="display:grid;gap:10px">
      <div>
        <span class="tiny muted" style="display:block">Suku Cadang / Bahan:</span>
        <b class="small" style="color:var(--ink)">{{ $jadwal->spareparts ?: 'Tidak ada pergantian suku cadang' }}</b>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Catatan Hasil Perawatan:</span>
        <p class="small" style="margin:2px 0 0;color:var(--ink);line-height:1.4">{{ $jadwal->notes ?: 'Pekerjaan rutin selesai sesuai checklist standar.' }}</p>
      </div>
      <div>
        <span class="tiny muted" style="display:block">Tanggal Selesai:</span>
        <span class="small" style="color:var(--ink)">{{ $jadwal->completed_at ? $jadwal->completed_at->translatedFormat('d F Y') : '-' }}</span>
      </div>
    </div>
  </div>
@endif

<div style="margin:20px 0 40px;text-align:center">
  <a class="btn" href="{{ route('teknisi.jadwal') }}" style="background:#fff;border:1px solid var(--line);font-size:13px;padding:9px 18px">
    ← Kembali ke Daftar Jadwal
  </a>
</div>

@endsection

