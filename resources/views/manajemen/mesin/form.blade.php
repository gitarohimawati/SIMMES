@extends('layouts.manager')
@section('title', $machine->exists ? 'Edit Mesin' : 'Tambah Mesin')
@section('content')
<div style="max-width:820px;margin:0 auto">
  <div class="top">
    <div class="top-row">
      <h1>{{ $machine->exists ? 'Edit Data Mesin' : 'Tambah Mesin Baru' }}</h1>
      <a class="btn" href="{{ route('manajemen.mesin.index') }}">← Kembali</a>
    </div>
    <p class="muted small">Lengkapi informasi spesifikasi dan jadwal interval pemeliharaan mesin.</p>
  </div>

  <form class="card" style="max-width:820px;margin:0 auto" method="POST" enctype="multipart/form-data"
        action="{{ $machine->exists ? route('manajemen.mesin.update', $machine) : route('manajemen.mesin.store') }}">
    @csrf @if ($machine->exists) @method('PUT') @endif
    <div class="grid g2">
      @php $f = fn($k) => old($k, $machine->$k); @endphp
      <div><label class="f" style="margin-top:0">Kode mesin</label><input class="input" name="code" value="{{ $f('code') }}" placeholder="MSN-014" required></div>
      <div><label class="f" style="margin-top:0">Nama mesin</label><input class="input" name="name" value="{{ $f('name') }}" required></div>
      <div><label class="f">Kategori</label><input class="input" name="category" value="{{ $f('category') }}" placeholder="Mesin produksi"></div>
      <div><label class="f">Tipe / model</label><input class="input" name="model" value="{{ $f('model') }}"></div>
      <div><label class="f">Nomor seri (S/N)</label><input class="input" name="serial_number" value="{{ $f('serial_number') }}"></div>
      <div><label class="f">Tanggal beli</label><input class="input" type="date" name="purchase_date" value="{{ old('purchase_date', $machine->purchase_date?->format('Y-m-d')) }}"></div>
      <div><label class="f">Servis terakhir</label><input class="input" type="date" name="last_service_at" value="{{ old('last_service_at', $machine->last_service_at?->format('Y-m-d')) }}"></div>
      <div><label class="f">Lokasi</label><input class="input" name="location" value="{{ $f('location') }}" required></div>
      <div><label class="f">Kondisi</label><input class="input" name="condition" value="{{ $f('condition') }}"></div>
      <div><label class="f">Interval maintenance (bulan)</label><input class="input" type="number" min="1" name="maintenance_interval_months" value="{{ $f('maintenance_interval_months') }}" required></div>
      <div><label class="f">Foto / video mesin (dokumentasi awal)</label><input class="input" type="file" name="media[]" multiple accept="image/*,video/*"></div>
    </div>
    <div class="form-actions-end" style="display:flex;justify-content:flex-end;margin-top:24px">
      <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13.5px">Simpan Mesin</button>
    </div>
  </form>
</div>
@endsection
