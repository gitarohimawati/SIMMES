@php
  $technicians = $technicians ?? \App\Models\User::where('role', 'teknisi')->where('status', 'aktif')->orderBy('name')->get();
  $machines = $machines ?? \App\Models\Machine::orderBy('category')->orderBy('name')->get(['id', 'code', 'name', 'category', 'model', 'location']);
@endphp
<button type="button" class="btn primary" onclick="openQuickScheduleModal()">@include('partials.icon', ['n' => 'plus']) Tambah Jadwal</button>

<div id="quickScheduleModal" class="quick-modal-backdrop" onclick="if(event.target===this) closeQuickScheduleModal()">
  <form method="POST" action="{{ route('manajemen.jadwal.store') }}" class="quick-modal-card" style="width:min(640px, 100%)" onclick="event.stopPropagation()">
    @csrf
    <div style="padding:18px 24px 14px;background:#fff6f7;border-bottom:1px solid var(--rose200);display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
      <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
          <span class="chip-dot" style="width:8px;height:8px;background:var(--r700)"></span>
          <h3 style="margin:0;font-size:17px;font-weight:800">Tambah Jadwal Maintenance</h3>
        </div>
        <span class="tiny muted">Jadwalkan pemeliharaan mesin berkala tanpa meninggalkan halaman.</span>
      </div>
      <button type="button" onclick="closeQuickScheduleModal()" aria-label="Tutup form jadwal" style="width:34px;height:34px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);cursor:pointer;font-size:16px;display:grid;place-items:center;line-height:1">×</button>
    </div>

    <div style="padding:20px 24px;display:grid;gap:14px">
      <input type="hidden" name="machine_id" id="quick_sched_machine_id">
      <div style="position:relative">
        <label class="f" style="margin:0 0 6px">Nama Mesin <span style="color:var(--r600)">*</span></label>
        <input class="input" name="machine_name" id="quick_sched_machine_name" value="{{ old('machine_name') }}" placeholder="Ketik nama mesin (misal: AC, Press, Pompa)..." autocomplete="off" required>
        <div id="quick_sched_machine_results" class="quick-suggest-popup" style="display:none"></div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Kode Mesin</label>
          <input class="input" name="machine_code" id="quick_sched_machine_code" value="{{ old('machine_code') }}" placeholder="Opsional">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Lokasi Mesin</label>
          <input class="input" name="location" id="quick_sched_location" value="{{ old('location') }}" placeholder="Opsional">
        </div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Jenis / Tugas Perawatan <span style="color:var(--r600)">*</span></label>
          <input class="input" name="task" value="{{ old('task') }}" placeholder="Contoh: Pelumasan bearing & cek suhu" required>
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Tipe Pemeliharaan</label>
          <select class="select" name="type">
            <option value="rutin" @selected(old('type', 'rutin') == 'rutin')>Rutin (Preventive)</option>
            <option value="insiden" @selected(old('type') == 'insiden')>Insiden / Korektif</option>
          </select>
        </div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Interval Siklus (bulan)</label>
          <input class="input" type="number" min="1" max="60" name="interval_months" value="{{ old('interval_months', 3) }}" placeholder="3">
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Tanggal Jatuh Tempo <span style="color:var(--r600)">*</span></label>
          <input class="input" type="date" name="due_date" value="{{ old('due_date', now()->format('Y-m-d')) }}" required>
        </div>
      </div>

      <div class="grid g2" style="gap:12px">
        <div>
          <label class="f" style="margin:0 0 6px">Waktu / Jam Pelaksanaan</label>
          <input class="input" type="time" name="due_time" value="{{ old('due_time', '09:00') }}" required>
        </div>
        <div>
          <label class="f" style="margin:0 0 6px">Tugaskan Teknisi</label>
          <select class="select" name="technician_id">
            <option value="">Belum ditugaskan (Bisa nanti)</option>
            @foreach ($technicians as $u)
              <option value="{{ $u->id }}" @selected(old('technician_id') == $u->id)>
                {{ $u->name }} {{ $u->whatsapp ? '| WA: '.$u->whatsapp : '' }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="form-actions-end" style="padding:16px 24px 20px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:10px">
      <button type="button" class="btn" onclick="closeQuickScheduleModal()">Batal</button>
      <button class="btn primary btn-compact" type="submit">💾 Simpan Jadwal</button>
    </div>
  </form>
</div>

<script>
function openQuickScheduleModal() {
  const m = document.getElementById('quickScheduleModal');
  if (m) {
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
      const inp = document.getElementById('quick_sched_machine_name');
      if (inp) inp.focus();
    }, 100);
  }
}

function closeQuickScheduleModal() {
  const m = document.getElementById('quickScheduleModal');
  if (m) {
    m.style.display = 'none';
    document.body.style.overflow = '';
  }
}

(function() {
  const machines = @json($machines ?? []);
  const nameInput = document.getElementById('quick_sched_machine_name');
  const codeInput = document.getElementById('quick_sched_machine_code');
  const locInput = document.getElementById('quick_sched_location');
  const idInput = document.getElementById('quick_sched_machine_id');
  const resultsBox = document.getElementById('quick_sched_machine_results');

  if (nameInput && resultsBox) {
    nameInput.addEventListener('input', function() {
      if (idInput) idInput.value = '';
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
          if (idInput) idInput.value = m.id || '';
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
      closeQuickScheduleModal();
    }
  });
})();
</script>
