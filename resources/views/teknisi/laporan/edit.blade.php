@extends('layouts.technician')
@section('title', 'Edit Laporan '.$ticket->code)
@section('heading', 'Edit Laporan')
@section('sub', 'Ubah rincian laporan '.$ticket->code)
@section('back', route('teknisi.laporan.show', $ticket))

@section('content')
<form class="tcard" method="POST" action="{{ route('teknisi.laporan.update', $ticket) }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  @include('partials.machine-picker')

  <label class="f" style="margin-top:14px">Keluhan / Masalah Mesin</label>
  <textarea class="textarea" name="complaint" rows="3" required>{{ old('complaint', $ticket->complaint) }}</textarea>
  @error('complaint') <div class="err">{{ $message }}</div> @enderror

  <label class="f">Tingkat Urgensi</label>
  <div class="urg">
    @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $v => $l)
      <label>
        <input type="radio" name="urgency" value="{{ $v }}" @checked(old('urgency', $ticket->urgency) === $v)>
        <span>{{ $l }}</span>
      </label>
    @endforeach
  </div>
  @error('urgency') <div class="err">{{ $message }}</div> @enderror

  <label class="f" style="margin-top:14px">Status Pengerjaan (Sinkron ke Manajemen)</label>
  <select class="select" name="status">
    <option value="dilaporkan" @selected(old('status', $ticket->status) === 'dilaporkan')>Dilaporkan (Menunggu penanganan)</option>
    <option value="ditugaskan" @selected(old('status', $ticket->status) === 'ditugaskan')>Ditugaskan</option>
    <option value="diproses" @selected(old('status', $ticket->status) === 'diproses')>Sedang Dikerjakan (Diproses)</option>
    <option value="selesai" @selected(old('status', $ticket->status) === 'selesai')>Selesai (Perbaikan Tuntas)</option>
  </select>

  <label class="f" style="margin-top:14px">Tindakan Perbaikan (Opsional)</label>
  <input class="input" name="action" value="{{ old('action', $ticket->action) }}" placeholder="Contoh: Ganti bearing, pembersihan filter, pelumasan ulang">
  <span class="tiny" style="color:var(--ink3)">Tindakan ini akan tampil di laporan cetak Manager / HRD.</span>

  <label class="f" style="margin-top:14px">Suku Cadang / Sparepart (Opsional)</label>
  <input class="input" name="spareparts" value="{{ old('spareparts', $ticket->spareparts) }}" placeholder="Contoh: Bearing SKF 6205 (1 pcs), Seal O-Ring (2 pcs)">

  @if ($ticket->media->count())
    <label class="f" style="margin-top:16px">Lampiran Saat Ini ({{ $ticket->media->count() }})</label>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:8px">
      @foreach ($ticket->media as $m)
        <div style="position:relative;border-radius:12px;overflow:hidden;border:1px solid var(--line);aspect-ratio:1">
          @if ($m->kind === 'video')
            <video src="{{ $m->url }}" style="width:100%;height:100%;object-fit:cover"></video>
          @else
            <img src="{{ $m->url }}" alt="Lampiran" style="width:100%;height:100%;object-fit:cover">
          @endif
          <button type="submit" form="del-media-{{ $m->id }}" style="position:absolute;top:4px;right:4px;width:24px;height:24px;border-radius:50%;background:rgba(200,16,46,.9);color:#fff;border:0;cursor:pointer;font-weight:700;line-height:1;display:grid;place-items:center;font-size:12px" title="Hapus foto ini">✕</button>
        </div>
      @endforeach
    </div>
  @endif

  <label class="f" style="margin-top:14px">Tambah Foto / Video Baru</label>
  <label class="dash" for="edit-file-input" style="min-height:90px;cursor:pointer">
    <span id="file-label">📷 Pilih file baru untuk ditambahkan</span>
    <input id="edit-file-input" type="file" name="files[]" accept="image/*,video/*" multiple hidden>
  </label>
  <span class="tiny" style="color:var(--ink3);margin-top:4px;display:block">Format: JPG, PNG, WEBP, MP4. Maksimal 30MB per file.</span>
  <div id="edit-preview-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px"></div>
  @error('files.*') <div class="err">{{ $message }}</div> @enderror

  <div class="form-actions-end" style="margin-top:24px;display:flex;justify-content:flex-end;align-items:center;gap:8px">
    <a class="btn" href="{{ route('teknisi.laporan.show', $ticket) }}" style="padding:10px 18px;font-size:13px;border-color:var(--line);color:var(--ink2);margin:0!important">Batal</a>
    <button class="btn primary btn-compact" type="submit" style="padding:10px 22px;font-size:13px;font-weight:700;margin:0!important">Simpan Perubahan</button>
  </div>
</form>

{{-- Form terpisah untuk hapus media per file --}}
@foreach ($ticket->media as $m)
  <form id="del-media-{{ $m->id }}" method="POST" action="{{ route('teknisi.laporan.media.destroy', $m) }}" data-confirm="Hapus lampiran ini?" style="display:none">
    @csrf
    @method('DELETE')
  </form>
@endforeach

@push('scripts')
<script>
const fileInput = document.getElementById('edit-file-input');
const fileLabel = document.getElementById('file-label');
const previewGrid = document.getElementById('edit-preview-grid');

if (fileInput) {
  fileInput.addEventListener('change', function () {
    previewGrid.innerHTML = '';
    const files = Array.from(this.files || []);
    if (files.length === 0) {
      fileLabel.textContent = '📷 Pilih file baru untuk ditambahkan';
      return;
    }
    fileLabel.textContent = '✔ ' + files.length + ' file baru dipilih (klik untuk ganti)';

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
