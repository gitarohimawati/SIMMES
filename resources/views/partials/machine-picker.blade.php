{{-- Partial Komponen Pencarian & Pemilihan Mesin Berdasarkan Kategori & Tipe --}}
@php
  $mModel = $ticket->machine ?? ($schedule->machine ?? ($machine ?? null));
  $initId   = old('machine_id', $currentMachineId ?? ($mModel->id ?? ''));
  $initName = old('machine_name', $currentMachineName ?? ($mModel->name ?? ''));
  $initCode = old('machine_code', $currentMachineCode ?? ($mModel->code ?? ''));
  $initLoc  = old('location', $currentMachineLoc ?? ($mModel->location ?? ''));
  $initCat  = $currentMachineCat ?? ($mModel->category ?? '');
  $initType = $currentMachineType ?? ($mModel->model ?? '');
@endphp

<div class="machine-picker-container" id="machine-picker-root" style="position:relative">
  <input type="hidden" name="machine_id" id="machine_id" value="{{ $initId }}">
  <div class="row between" style="margin-bottom:6px">
    <label class="f" style="margin:0;font-weight:700">Nama Mesin</label>
  </div>

  <div style="position:relative">
    <div style="position:relative;display:flex;align-items:center">
      <input class="input" 
             id="machine_name" 
             name="machine_name" 
             value="{{ $initName }}" 
             placeholder="Cari nama mesin"
             autocomplete="off" 
             required
             style="padding-right:38px">
      <button type="button" 
              id="btn-clear-machine" 
              style="position:absolute;right:10px;background:none;border:0;color:var(--ink3);cursor:pointer;display:none;padding:4px;font-size:16px;line-height:1"
              title="Hapus pencarian">✕</button>
    </div>

    {{-- Dropdown Hasil Pencarian Berkategori --}}
    <div id="machine-dropdown-menu" 
         class="machine-dropdown-card"
         style="display:none;position:absolute;left:0;right:0;top:calc(100% + 6px);background:#fff;border:1.5px solid var(--r300);border-radius:16px;box-shadow:0 14px 34px -8px rgba(0,0,0,0.18);z-index:999;max-height:340px;overflow-y:auto;padding:10px">
      
      {{-- Bar Kategori / Filter Cepat --}}
      <div id="machine-category-chips" style="display:flex;gap:6px;overflow-x:auto;padding-bottom:8px;margin-bottom:8px;border-bottom:1px solid var(--line);scrollbar-width:none">
        <button type="button" class="btn-cat-chip on" data-cat="all">Semua Kategori</button>
      </div>

      {{-- Petunjuk Ketik Sebelum Kata Kunci Dimasukkan --}}
      <div id="machine-search-hint" style="padding:18px 14px;text-align:center;color:var(--ink2)">
        <div style="font-size:24px;margin-bottom:4px">⌨️</div>
        <b style="font-size:13.5px;color:var(--ink);display:block">Masukkan Kata Kunci Mesin</b>
        <span class="tiny muted">Ketik minimal 1 karakter untuk menampilkan nama mesin beserta tipe & kategorinya.</span>
      </div>

      {{-- Wadah Daftar Mesin Berkategori --}}
      <div id="machine-results-list" style="display:none"></div>

      {{-- Pesan Jika Mesin Baru (Tidak Ada di Database) --}}
      <div id="machine-not-found" style="display:none;padding:14px;background:#fef2f2;border-radius:12px;border:1px solid #fecaca;margin-top:4px">
        <div style="display:flex;align-items:flex-start;gap:8px">
          <span style="font-size:16px;color:#dc2626">✨</span>
          <div style="flex:1">
            <b style="font-size:13px;color:#991b1b;display:block">Mesin Belum Terdaftar di Database</b>
            <div class="tiny" style="color:#7f1d1d;margin-top:2px">
              Mesin <b id="not-found-term"></b> akan otomatis dibuat sebagai mesin baru di sistem.
            </div>
          </div>
          <button type="button" class="btn-action primary" id="btn-use-new-machine" style="font-size:11.5px;padding:4px 10px">
            Gunakan Nama Ini
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Badge Info Mesin yang Sedang Terpilih --}}
  <div id="selected-machine-badge" 
       style="{{ $initName ? 'display:flex' : 'display:none' }};align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:8px 12px;margin-top:8px">
    <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#166534">
      <span style="font-weight:800;color:#15803d">✓ Mesin Terpilih:</span>
      <span id="badge-machine-name" style="font-weight:700">{{ $initName }}</span>
      <span id="badge-machine-type" class="badge-code" style="font-size:11px;background:#dcfce7;color:#15803d;border-color:#bbf7d0">
        {{ $initType ? 'Tipe: '.$initType : ($initCat ? 'Kategori: '.$initCat : 'Siap Lapor') }}
      </span>
    </div>
    <button type="button" id="btn-change-machine" style="background:none;border:0;color:#15803d;font-size:11.5px;font-weight:700;cursor:pointer;text-decoration:underline">
      Ganti
    </button>
  </div>

  @error('machine_name') <div class="err">{{ $message }}</div> @enderror

  {{-- Input Kode Mesin & Lokasi --}}
  <div class="row" style="gap:10px;margin-top:12px">
    <div style="flex:1">
      <label class="f" style="margin-top:0">Kode Mesin (Opsional)</label>
      <input class="input" id="machine_code" name="machine_code" value="{{ $initCode }}" placeholder="MSN-014">
      @error('machine_code') <div class="err">{{ $message }}</div> @enderror
    </div>
    <div style="flex:1">
      <label class="f" style="margin-top:0">Lokasi Mesin</label>
      <input class="input" id="location" name="location" value="{{ $initLoc }}" placeholder="Produksi">
      @error('location') <div class="err">{{ $message }}</div> @enderror
    </div>
  </div>
</div>

<style>
.btn-cat-chip{
  padding:5px 11px;
  border-radius:20px;
  border:1px solid var(--line);
  background:#f9fafb;
  font-size:11.5px;
  font-weight:600;
  color:var(--ink2);
  white-space:nowrap;
  cursor:pointer;
  transition:all .15s ease;
}
.btn-cat-chip:hover{
  background:var(--rose50);
  color:var(--r700);
  border-color:var(--r300);
}
.btn-cat-chip.on{
  background:var(--r700);
  color:#fff;
  border-color:var(--r700);
}
.machine-group-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:6px 10px;
  margin:6px 0 3px;
  font-size:11.5px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.5px;
  color:var(--r800);
  background:var(--rose50);
  border-radius:8px;
}
.machine-item-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:9px 12px;
  border-radius:10px;
  cursor:pointer;
  transition:background .15s ease;
  margin-bottom:2px;
}
.machine-item-row:hover{
  background:#f3f4f6;
}
.machine-item-row.active{
  background:var(--rose100);
}
#machine-category-chips,#machine-search-hint{display:none!important}
</style>

<script>
(function() {
  const machinesData = @json($machines ?? []);
  const idInput = document.getElementById('machine_id');
  const input = document.getElementById('machine_name');
  const codeInput = document.getElementById('machine_code');
  const locInput = document.getElementById('location');
  const dropdown = document.getElementById('machine-dropdown-menu');
  const resultsList = document.getElementById('machine-results-list');
  const hint = document.getElementById('machine-search-hint');
  const notFound = document.getElementById('machine-not-found');
  const notFoundTerm = document.getElementById('not-found-term');
  const btnClear = document.getElementById('btn-clear-machine');
  const btnUseNew = document.getElementById('btn-use-new-machine');
  const badge = document.getElementById('selected-machine-badge');
  const badgeName = document.getElementById('badge-machine-name');
  const badgeType = document.getElementById('badge-machine-type');
  const btnChange = document.getElementById('btn-change-machine');

  if (!input || !dropdown) return;

  // 2. Tampilkan dropdown ketika input diklik / fokus
  input.addEventListener('focus', function() {
    if (this.value.trim().length >= 2) {
      dropdown.style.display = 'block';
      renderResults();
    }
  });

  // Tombol ganti
  if (btnChange) {
    btnChange.addEventListener('click', function() {
      input.focus();
      input.select();
      if (input.value.trim().length >= 2) {
        dropdown.style.display = 'block';
        renderResults();
      }
    });
  }

  // Input listener
  input.addEventListener('input', function() {
    if (idInput) idInput.value = '';
    if (btnClear) {
      btnClear.style.display = this.value.trim() ? 'block' : 'none';
    }
    if (this.value.trim().length >= 2) {
      dropdown.style.display = 'block';
      renderResults();
    } else {
      dropdown.style.display = 'none';
      resultsList.style.display = 'none';
      notFound.style.display = 'none';
    }
  });

  if (btnClear) {
    btnClear.addEventListener('click', function() {
      input.value = '';
      if (idInput) idInput.value = '';
      if (codeInput) codeInput.value = '';
      if (locInput) locInput.value = '';
      this.style.display = 'none';
      if (badge) badge.style.display = 'none';
      input.focus();
      dropdown.style.display = 'none';
    });
  }

  // Tombol gunakan mesin baru
  if (btnUseNew) {
    btnUseNew.addEventListener('click', function(e) {
      e.preventDefault();
      if (idInput) idInput.value = '';
      dropdown.style.display = 'none';
      updateBadge(input.value.trim(), 'Mesin Baru Ditambahkan');
    });
  }

  // Tutup dropdown jika klik di luar
  document.addEventListener('click', function(e) {
    if (!document.getElementById('machine-picker-root').contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

  // Render hasil pencarian dengan sorting & grouping kategori
  function renderResults() {
    const q = input.value.trim().toLowerCase();

    // Sesuai permintaan user:
    // "ketika di sorting, saya ingin ketika shotingnya itu tidak semua tampil yang ada di database,
    // tapi ketika memasukkan kata kunci baru keluar nama mesin dengan tipe yang sesuai bisa di buat perkategori"
    if (q.length < 2) {
      resultsList.style.display = 'none';
      notFound.style.display = 'none';
      return;
    }

    hint.style.display = 'none';

    // Filter mesin sesuai query & kategori
    const filtered = machinesData.filter(m => {
      const nameMatch = (m.name || '').toLowerCase().includes(q);
      const codeMatch = (m.code || '').toLowerCase().includes(q);
      const modelMatch = (m.model || '').toLowerCase().includes(q);
      const catMatch = (m.category || '').toLowerCase().includes(q);
      const locMatch = (m.location || '').toLowerCase().includes(q);

      return nameMatch || codeMatch || modelMatch || catMatch || locMatch;
    });

    if (filtered.length === 0) {
      resultsList.style.display = 'none';
      notFound.style.display = 'block';
      notFoundTerm.textContent = input.value.trim();
      return;
    }

    notFound.style.display = 'none';
    resultsList.style.display = 'block';
    resultsList.innerHTML = '';

    // Kelompokkan hasil per kategori
    const groups = {};
    filtered.forEach(m => {
      const cat = (m.category && m.category.trim()) ? m.category.trim() : 'Umum / Lainnya';
      if (!groups[cat]) groups[cat] = [];
      groups[cat].push(m);
    });

    // Render masing-masing grup kategori
    Object.keys(groups).sort().forEach(cat => {
      const groupHeader = document.createElement('div');
      groupHeader.className = 'machine-group-header';
      groupHeader.innerHTML = `<span>📁 ${cat}</span><span>${groups[cat].length} mesin</span>`;
      resultsList.appendChild(groupHeader);

      groups[cat].forEach(m => {
        const row = document.createElement('div');
        row.className = 'machine-item-row';
        const typeInfo = m.model ? `<span class="badge-code" style="color:var(--r700);border-color:var(--rose200);background:var(--rose50)">Tipe: ${m.model}</span>` : '';
        
        row.innerHTML = `
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              <b style="font-size:13.5px;color:var(--ink)">${m.name}</b>
              ${typeInfo}
            </div>
            <div class="tiny muted" style="margin-top:2px">
              Kode: <b>${m.code}</b> · Lokasi: <b>${m.location || '-'}</b>
            </div>
          </div>
          <span style="font-size:12px;font-weight:700;color:var(--r700);margin-left:10px">Pilih →</span>
        `;

        row.addEventListener('click', function(e) {
          e.stopPropagation();
          selectMachine(m);
        });

        resultsList.appendChild(row);
      });
    });
  }

  function selectMachine(m) {
    if (idInput) idInput.value = m.id || '';
    input.value = m.name;
    if (codeInput) codeInput.value = m.code || '';
    if (locInput) locInput.value = m.location || '';
    if (btnClear) btnClear.style.display = 'block';
    dropdown.style.display = 'none';

    const typeDesc = m.model ? `Tipe: ${m.model}` : (m.category ? `Kategori: ${m.category}` : `Kode: ${m.code}`);
    updateBadge(m.name, typeDesc);
  }

  function updateBadge(name, type) {
    if (!badge) return;
    badgeName.textContent = name;
    badgeType.textContent = type;
    badge.style.display = 'flex';
  }
})();
</script>
