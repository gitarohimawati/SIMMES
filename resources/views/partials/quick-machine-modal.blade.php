<button type="button" class="btn primary" onclick="openQuickMachineModal()">@include('partials.icon', ['n' => 'plus']) Tambah Mesin</button>

<div id="quickMachineModal" class="quick-modal-backdrop" onclick="if(event.target===this) closeQuickMachineModal()">
  <form method="POST" action="{{ route('manajemen.mesin.store') }}" enctype="multipart/form-data" class="quick-modal-card" style="width:min(680px, 100%)" onclick="event.stopPropagation()" autocomplete="off">
    @csrf
    <div style="padding:18px 24px 14px;background:#fff6f7;border-bottom:1px solid var(--rose200);display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
      <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
          <span class="chip-dot" style="width:8px;height:8px;background:var(--r700)"></span>
          <h3 style="margin:0;font-size:17px;font-weight:800">Tambah Data Mesin Baru</h3>
        </div>
        <span class="tiny muted">Daftarkan mesin baru langsung tanpa meninggalkan halaman Data Mesin.</span>
      </div>
      <button type="button" onclick="closeQuickMachineModal()" aria-label="Tutup form mesin" style="width:34px;height:34px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);cursor:pointer;font-size:16px;display:grid;place-items:center;line-height:1">×</button>
    </div>

    <div style="padding:20px 24px;display:grid;gap:14px">
      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Kode Mesin <span style="color:var(--r600)">*</span></label>
          <input class="input" name="code" value="{{ old('code') }}" placeholder="Contoh: MSN-015" required style="text-transform:uppercase">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Nama Mesin <span style="color:var(--r600)">*</span></label>
          <input class="input" name="name" value="{{ old('name') }}" placeholder="Contoh: Mesin Press Hidrolik 2" required>
        </div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Kategori</label>
          <input class="input" name="category" value="{{ old('category') }}" placeholder="Contoh: Mesin Produksi">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Tipe / Model</label>
          <input class="input" name="model" value="{{ old('model') }}" placeholder="Contoh: HP-50T">
        </div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Nomor Seri (S/N)</label>
          <input class="input" name="serial_number" value="{{ old('serial_number') }}" placeholder="Contoh: SN-2024-8891">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Tanggal Beli</label>
          <input class="input" type="date" name="purchase_date" value="{{ old('purchase_date') }}">
        </div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Servis Terakhir (Opsional)</label>
          <input class="input" type="date" name="last_service_at" value="{{ old('last_service_at') }}" placeholder="Tanggal servis sebelumnya">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Kondisi</label>
          <input class="input" name="condition" value="{{ old('condition', 'Baik') }}" placeholder="Contoh: Baik / Siap Pakai">
        </div>
      </div>

      <div>
        <label class="f" style="margin:0 0 6px">Lokasi Penempatan <span style="color:var(--r600)">*</span></label>
        <input class="input" name="location" value="{{ old('location') }}" placeholder="Contoh: Produksi A, Lantai 1" required>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Interval Maintenance (bulan) <span style="color:var(--r600)">*</span></label>
          <input class="input" type="number" min="1" max="60" name="maintenance_interval_months" value="{{ old('maintenance_interval_months', 3) }}" required>
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Foto / Video Dokumentasi Awal</label>
          <input class="input" type="file" name="media[]" multiple accept="image/*,video/*" style="padding:9px 12px">
        </div>
      </div>
    </div>

    <div class="form-actions-end" style="padding:16px 24px 20px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:10px">
      <button type="button" class="btn" onclick="closeQuickMachineModal()">Batal</button>
      <button class="btn primary btn-compact" type="submit">Simpan Mesin</button>
    </div>
  </form>
</div>

<script>
function openQuickMachineModal() {
  const m = document.getElementById('quickMachineModal');
  if (m) {
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

function closeQuickMachineModal() {
  const m = document.getElementById('quickMachineModal');
  if (m) {
    m.style.display = 'none';
    document.body.style.overflow = '';
  }
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeQuickMachineModal();
  }
});
</script>
