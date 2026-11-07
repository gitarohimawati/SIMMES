<button type="button" class="btn primary" onclick="openQuickTechnicianModal()">
  @include('partials.icon', ['n' => 'plus']) Daftarkan Teknisi Baru
</button>

<div id="quickTechnicianModal" class="quick-modal-backdrop" onclick="if(event.target===this) closeQuickTechnicianModal()">
  <form method="POST" action="{{ route('manajemen.teknisi.store') }}" class="quick-modal-card" style="width:min(560px, 100%)" onclick="event.stopPropagation()" autocomplete="off">
    @csrf

    {{-- Header Modal --}}
    <div style="padding:18px 24px 16px;background:#fff6f7;border-bottom:1px solid var(--rose200);display:flex;align-items:center;justify-content:space-between;gap:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <span class="chip-dot" style="width:9px;height:9px;background:var(--r700);flex-shrink:0"></span>
        <div>
          <h2 style="margin:0;font-size:17.5px;font-weight:800;color:var(--ink)">Tambah Teknisi Baru</h2>
          <span class="tiny muted" style="display:block;margin-top:2px">Daftarkan akun teknisi yang dapat langsung aktif bertugas.</span>
        </div>
      </div>
      <button type="button" onclick="closeQuickTechnicianModal()" aria-label="Tutup form" style="width:34px;height:34px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);cursor:pointer;font-size:16px;display:grid;place-items:center;line-height:1">✕</button>
    </div>

    {{-- Form Body --}}
    <div style="padding:22px 24px;display:grid;gap:14px">
      {{-- Nama Lengkap --}}
      <div>
        <label class="f" style="margin:0 0 6px">Nama Lengkap <span style="color:#ef4444">*</span></label>
        <input class="input" name="name" id="modalTechName" required>
      </div>

      {{-- Email & NIK (Grid 2 Kolom) --}}
      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Email (Akun Login) <span style="color:#ef4444">*</span></label>
          <input class="input" type="email" name="email" required>
        </div>

        <div>
          <label class="f" style="margin:0 0 6px">Nomor NIK <span style="color:#ef4444">*</span></label>
          <input class="input" name="nik" value="{{ old('nik') }}" required>
          <span class="tiny muted" style="display:block;margin-top:3px">Diisi manual sesuai standar NIK perusahaan.</span>
        </div>
      </div>

      {{-- Password & WhatsApp (Grid 2 Kolom) --}}
      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Kata Sandi (Password) <span style="color:#ef4444">*</span></label>
          <div style="position:relative">
            <input class="input" id="modalPassInput" type="text" name="password" style="padding-right:75px" required>
            <button type="button" onclick="toggleModalPassVis()" class="btn-action" style="position:absolute;right:6px;top:6px;padding:4px 8px;font-size:11px" id="btnModalToggleVis">
              Sembunyi
            </button>
          </div>
          <span class="tiny muted" style="display:block;margin-top:3px">Kata sandi akun teknisi (min. 4 karakter).</span>
        </div>

        <div>
          <label class="f" style="margin:0 0 6px">Nomor WhatsApp</label>
          <input class="input" type="tel" name="whatsapp">
          <span class="tiny muted" style="display:block;margin-top:3px">Untuk notifikasi siaran WA.</span>
        </div>
      </div>

      {{-- Departemen & Bagian / Spesialisasi (Grid 2 Kolom) --}}
      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Departemen</label>
          <input class="input" name="department" value="Maintenance" placeholder="Maintenance" required>
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Bagian / Spesialisasi</label>
          <input class="input" name="bagian" placeholder="Pilih atau ketik" list="quickSpesialisasiList">
          <datalist id="quickSpesialisasiList">
            <option value="Mekanik">
            <option value="Kelistrikan & Otomasi">
            <option value="Mesin CNC / Bubut">
            <option value="Hydraulic & Pneumatic">
            <option value="Preventive Maintenance">
          </datalist>
        </div>
      </div>
    </div>

    {{-- Footer Aksi --}}
    <div class="form-actions-end" style="padding:16px 24px 18px;border-top:1px solid var(--line);background:#fafafa;display:flex;justify-content:flex-end;align-items:center;gap:10px">
      <button type="button" class="btn" onclick="closeQuickTechnicianModal()" style="padding:9px 18px;font-size:13px;border-color:var(--line);color:var(--ink2)">Batal</button>
      <button type="submit" class="btn primary btn-compact" style="padding:9px 22px;font-size:13.5px;font-weight:700">Simpan Teknisi</button>
    </div>
  </form>
</div>

<script>
function openQuickTechnicianModal() {
  const m = document.getElementById('quickTechnicianModal');
  if (m) {
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
      const inp = document.getElementById('modalTechName');
      if (inp) inp.focus();
    }, 100);
  }
}

function closeQuickTechnicianModal() {
  const m = document.getElementById('quickTechnicianModal');
  if (m) {
    m.style.display = 'none';
    document.body.style.overflow = '';
  }
}

function toggleModalPassVis() {
  const input = document.getElementById('modalPassInput');
  const btn = document.getElementById('btnModalToggleVis');
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = 'Sembunyi';
  } else {
    input.type = 'password';
    btn.textContent = 'Lihat';
  }
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeQuickTechnicianModal();
  }
});
</script>

