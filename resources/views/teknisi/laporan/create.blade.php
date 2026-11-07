@extends('layouts.technician')
@section('title', 'Buat Laporan Perbaikan')
@section('heading', 'Buat Laporan')
@section('sub', 'Laporkan kendala mesin untuk penanganan segera')
@section('back', route('teknisi.laporan.index'))

@section('content')
<form class="tcard" method="POST" action="{{ route('teknisi.laporan.store') }}" enctype="multipart/form-data">
  @csrf

  @include('partials.machine-picker')

  <label class="f" style="margin-top:14px">Keluhan / Masalah Mesin</label>
  <textarea class="textarea" name="complaint" rows="4" placeholder="Jelaskan secara rinci gejala atau kerusakan yang terjadi…" required>{{ old('complaint') }}</textarea>
  @error('complaint') <div class="err">{{ $message }}</div> @enderror

  <label class="f">Tingkat Urgensi</label>
  <div class="urg">
    @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $v => $l)
      <label>
        <input type="radio" name="urgency" value="{{ $v }}" @checked(old('urgency', 'sedang') === $v)>
        <span>{{ $l }}</span>
      </label>
    @endforeach
  </div>
  @error('urgency') <div class="err">{{ $message }}</div> @enderror

  <label class="f" style="margin-top:14px">Foto / Video Dokumentasi (Opsional)</label>
  <label class="dash" for="file-input" style="min-height:90px;cursor:pointer">
    <span id="file-label">📷 Pilih foto atau video kendala</span>
    <input id="file-input" type="file" name="files[]" accept="image/*,video/*" multiple hidden>
  </label>
  <span class="tiny" style="color:var(--ink3);margin-top:4px;display:block">Format: JPG, PNG, WEBP, MP4. Maksimal 30MB per file.</span>
  <div id="preview-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px"></div>
  @error('files.*') <div class="err">{{ $message }}</div> @enderror

  <div class="form-actions-end" style="margin-top:24px;display:flex;justify-content:flex-end;align-items:center;gap:8px">
    <a class="btn" href="{{ route('teknisi.laporan.index') }}" style="padding:10px 18px;font-size:13px;border-color:var(--line);color:var(--ink2);margin:0!important">Batal</a>
    <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13px;font-weight:700;margin:0!important">Kirim Laporan</button>
  </div>
</form>

@push('scripts')
<script>
// File preview handler
const fileInput = document.getElementById('file-input');
const fileLabel = document.getElementById('file-label');
const previewGrid = document.getElementById('preview-grid');

if (fileInput) {
  fileInput.addEventListener('change', function () {
    previewGrid.innerHTML = '';
    const files = Array.from(this.files || []);
    if (files.length === 0) {
      fileLabel.textContent = '📷 Pilih foto atau video kendala';
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
