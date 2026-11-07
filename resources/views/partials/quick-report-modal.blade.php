@php
  $machines = $machines ?? \App\Models\Machine::orderBy('category')->orderBy('name')->get(['id', 'code', 'name', 'category', 'model', 'location']);
@endphp
@if (!isset($hideTrigger) || !$hideTrigger)
<button type="button" class="{{ $triggerClass ?? 'btn primary' }}" onclick="openQuickReportModal()">
  @include('partials.icon', ['n' => 'plus']) <span>{{ $triggerLabel ?? 'Lapor' }}</span>
</button>
@endif

<div id="quickReportModal" class="quick-modal-backdrop" onclick="if(event.target===this) closeQuickReportModal()">
  <form method="POST" action="{{ route('manajemen.lapor.store') }}" enctype="multipart/form-data" class="quick-modal-card" style="width:min(580px, 100%)" onclick="event.stopPropagation()" autocomplete="off">
    @csrf
    <div style="padding:18px 24px 14px;background:#fff6f7;border-bottom:1px solid var(--rose200);display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
      <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
          <span class="chip-dot" style="width:8px;height:8px;background:var(--r700)"></span>
          <h3 id="quickReportTitle" style="margin:0;font-size:17px;font-weight:800">Lapor Kerusakan</h3>
        </div>
        <span class="tiny muted">Buat laporan baru tanpa meninggalkan halaman.</span>
      </div>
      <button type="button" onclick="closeQuickReportModal()" aria-label="Tutup form laporan" style="width:34px;height:34px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);cursor:pointer;font-size:16px;display:grid;place-items:center;line-height:1">×</button>
    </div>

    <div style="padding:20px 24px;display:grid;gap:14px">
      <div style="position:relative">
        <label class="f" style="margin:0 0 6px">Nama Mesin <span style="color:var(--r600)">*</span></label>
        <input class="input" name="machine_name" id="quick_machine_name" value="{{ old('machine_name') }}" placeholder="Ketik nama mesin (misal: AC, Press, Pompa)..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" required>
        <div id="quick_machine_results" class="quick-suggest-popup" style="display:none"></div>
      </div>
      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Kode Mesin</label>
          <input class="input" name="machine_code" id="quick_machine_code" value="{{ old('machine_code') }}" placeholder="Opsional" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Lokasi</label>
          <input class="input" name="location" id="quick_location" value="{{ old('location') }}" placeholder="Opsional" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true">
        </div>
      </div>
      <div>
        <label class="f" style="margin:0 0 6px">Keluhan <span style="color:var(--r600)">*</span></label>
        <textarea class="textarea" name="complaint" rows="3" placeholder="Jelaskan masalah mesin..." required>{{ old('complaint') }}</textarea>
      </div>
      <div>
        <label class="f" style="margin:0 0 6px">Tingkat Urgensi</label>
        <div class="urg">
          @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $value => $label)
            <label><input type="radio" name="urgency" value="{{ $value }}" @checked(old('urgency', 'sedang') === $value)><span>{{ $label }}</span></label>
          @endforeach
        </div>
      </div>
      <div>
        <label class="f" style="margin:0 0 6px">Foto atau Video <span class="tiny muted">(opsional)</span></label>
        <input class="input" type="file" name="files[]" accept="image/*,video/*" multiple style="padding:9px 12px">
      </div>
    </div>

    <div class="form-actions-end" style="padding:16px 24px 20px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:10px">
      <button type="button" class="btn" onclick="closeQuickReportModal()">Batal</button>
      <button class="btn primary btn-compact" type="submit">Kirim Laporan</button>
    </div>
  </form>
</div>

<script>
function openQuickReportModal() {
  const m = document.getElementById('quickReportModal');
  if (m) {
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
      const inp = document.getElementById('quick_machine_name');
      if (inp) inp.focus();
    }, 100);
  }
}

function closeQuickReportModal() {
  const m = document.getElementById('quickReportModal');
  if (m) {
    m.style.display = 'none';
    document.body.style.overflow = '';
  }
}

(function() {
  const machines = @json($machines ?? []);
  const nameInput = document.getElementById('quick_machine_name');
  const codeInput = document.getElementById('quick_machine_code');
  const locInput = document.getElementById('quick_location');
  const resultsBox = document.getElementById('quick_machine_results');

  if (nameInput && resultsBox) {
    nameInput.addEventListener('input', function() {
      const q = this.value.trim().toLowerCase();
      if (q.length < 1) {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        return;
      }

      const filtered = machines.filter(m => {
        return (m.name || '').toLowerCase().includes(q) ||
               (m.code || '').toLowerCase().includes(q) ||
               (m.model || '').toLowerCase().includes(q) ||
               (m.category || '').toLowerCase().includes(q) ||
               (m.location || '').toLowerCase().includes(q);
      });

      if (filtered.length === 0) {
        resultsBox.style.display = 'none';
        return;
      }

      resultsBox.innerHTML = '';
      filtered.forEach(m => {
        const item = document.createElement('div');
        item.className = 'quick-suggest-item';
        item.innerHTML = `
          <div>
            <b style="color:var(--ink)">${m.name}</b>
            <div class="tiny muted" style="margin-top:2px">Kode: <b>${m.code}</b> · Lokasi: <b>${m.location || '-'}</b></div>
          </div>
          <span style="font-size:11.5px;color:var(--r700);font-weight:700">Pilih →</span>
        `;
        item.addEventListener('click', function(e) {
          e.stopPropagation();
          nameInput.value = m.name;
          if (codeInput) codeInput.value = m.code || '';
          if (locInput) locInput.value = m.location || '';
          resultsBox.style.display = 'none';
        });
        resultsBox.appendChild(item);
      });
      resultsBox.style.display = 'block';
    });

    document.addEventListener('click', function(e) {
      if (!nameInput.contains(e.target) && !resultsBox.contains(e.target)) {
        resultsBox.style.display = 'none';
      }
    });
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeQuickReportModal();
    }
  });
})();
</script>
