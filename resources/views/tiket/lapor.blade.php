@extends(auth()->user()->isTeknisi() ? 'layouts.technician' : 'layouts.manager')
@section('title', 'Laporan Perbaikan')
@section('heading', 'Laporan Perbaikan')
@section('content')
@php
  $user = auth()->user();
  $roleStr = $user->isTeknisi() ? 'teknisi' : 'manajemen';
@endphp
@if (!$user->isTeknisi())
<div style="max-width:680px;margin:0 auto">
  <div class="top" style="margin-bottom:20px">
    <div class="top-row">
      <h1 style="font-size:26px">Laporan Perbaikan</h1>
      <a class="btn" href="{{ route('manajemen.perbaikan.index') }}">← Kembali</a>
    </div>
    <p class="muted small" style="margin-top:2px">Laporkan kerusakan atau kendala mesin untuk ditangani teknisi</p>
  </div>
@endif
<form class="{{ $user->isTeknisi() ? 'tcard' : 'card' }}" style="{{ !$user->isTeknisi() ? 'width:100%;margin:0 auto' : '' }}" method="POST" action="{{ route($roleStr.'.lapor.store') }}" enctype="multipart/form-data">@csrf
  @include('partials.machine-picker')

  <label class="f" style="margin-top:14px">Keluhan</label>
  <textarea class="textarea" name="complaint" rows="4" placeholder="Jelaskan masalah mesin…" required>{{ old('complaint') }}</textarea>

  <label class="f">Tingkat urgensi</label>
  <div class="urg">
    @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $v => $l)
      <label><input type="radio" name="urgency" value="{{ $v }}" @checked(old('urgency', 'sedang') === $v)><span>{{ $l }}</span></label>
    @endforeach
  </div>

  <label class="f">Foto atau video</label>
  <label class="dash" for="lapor-file-input" style="min-height:90px;cursor:pointer">
    <span id="lapor-file-label">📷 Tambah foto / video</span>
    <input id="lapor-file-input" type="file" name="files[]" accept="image/*,video/*" multiple hidden>
  </label>
  <div id="lapor-preview-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px"></div>
  <div class="form-actions-end" style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-top:22px">
    <a class="btn" href="{{ $user->isTeknisi() ? route('teknisi.home') : route('manajemen.perbaikan.index') }}" style="padding:10px 18px;font-size:13px;border-color:var(--line);color:var(--ink2);margin:0!important">Batal</a>
    <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13.5px;margin:0!important">Kirim Laporan</button>
  </div>
</form>
@if (!$user->isTeknisi())
</div>
@endif
@push('scripts')
<script>
const fileInput = document.getElementById('lapor-file-input');
const fileLabel = document.getElementById('lapor-file-label');
const previewGrid = document.getElementById('lapor-preview-grid');

if (fileInput) {
  fileInput.addEventListener('change', function () {
    previewGrid.innerHTML = '';
    const files = Array.from(this.files || []);
    if (files.length === 0) {
      fileLabel.textContent = '📷 Tambah foto / video';
      return;
    }
    fileLabel.textContent = '✔ ' + files.length + ' file dipilih (klik untuk ganti)';

    files.forEach((file) => {
      const box = document.createElement('div');
      box.style.cssText = 'position:relative;border-radius:12px;overflow:hidden;border:1.5px solid var(--line);aspect-ratio:1;background:#faf6f6;display:flex;align-items:center;justify-content:center;';

      const url = URL.createObjectURL(file);
      if (file.type.startsWith('video/')) {
        const video = document.createElement('video');
        video.src = url;
        video.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        video.controls = false;
        box.appendChild(video);
      } else {
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        box.appendChild(img);
      }
      previewGrid.appendChild(box);
    });
  });
}
</script>
@endpush
@endsection
