@extends('layouts.technician')
@section('title', 'Jadwal Maintenance')
@section('heading', 'Jadwal Maintenance')
@section('sub', 'Pemeliharaan rutin preventif mesin pabrik')
@section('content')

{{-- Tab Navigasi Aktif vs Selesai --}}
<div class="pills" style="margin-bottom:14px;background:#fff;padding:6px;border-radius:12px;border:1px solid var(--line);display:flex;gap:6px">
  <a href="{{ route('teknisi.jadwal', ['tab' => 'aktif']) }}" class="btn {{ $tab === 'aktif' ? 'primary' : '' }}" style="flex:1;justify-content:center;font-size:12.5px;padding:8px 10px;border-radius:8px">
    Perlu Dikerjakan ({{ $countActive }})
  </a>
  <a href="{{ route('teknisi.jadwal', ['tab' => 'selesai']) }}" class="btn {{ $tab === 'selesai' ? 'primary' : '' }}" style="flex:1;justify-content:center;font-size:12.5px;padding:8px 10px;border-radius:8px">
    Riwayat Selesai ({{ $countDone }})
  </a>
</div>

@forelse ($schedules as $s)
  @php
    $payload = [
      'id'             => $s->id,
      'task'           => $s->task,
      'type'           => $s->type,
      'status'         => $s->status,
      'display_status' => $s->display_status,
      'progress'       => (int) $s->progress,
      'machine_name'   => $s->machine?->name ?? 'Mesin',
      'machine_code'   => $s->machine?->code ?? '-',
      'location'       => $s->machine?->location ?? '-',
      'due_date'       => $s->due_date ? $s->due_date->translatedFormat('d M Y') : '-',
      'due_time'       => $s->due_time ?: '',
      'interval'       => $s->interval_label ?: ($s->interval_months ? $s->interval_months.' bulan' : 'Rutin'),
      'spareparts'     => $s->spareparts ?: '',
      'notes'          => $s->notes ?: '',
      'before_url'     => $s->before_url ?: '',
      'after_url'      => $s->after_url ?: '',
      'completed_date' => $s->completed_at ? $s->completed_at->translatedFormat('d F Y') : '',
      'upload_url'     => route('teknisi.jadwal.upload', $s),
      'complete_url'   => route('teknisi.jadwal.selesai', $s),
      'wa_link'        => \App\Services\WhatsAppService::makeScheduleCompletedLink($s, $managerPhone ?? null),
    ];
  @endphp

  <script>
    window.schedulesData = window.schedulesData || {};
    window.schedulesData[{{ $s->id }}] = @json($payload);
  </script>

  <div class="tcard" style="margin-bottom:12px;border-left:4px solid {{ $s->status === 'selesai' ? '#16a34a' : ($s->display_status === 'terlambat' ? '#ef4444' : 'var(--brand)') }}">
    <div class="row between" style="align-items:flex-start;gap:8px">
      <div>
        <span class="badge-code" style="font-size:11px">{{ $s->machine?->code ?? '-' }}</span>
        <b style="font-size:15px;display:block;margin-top:4px;color:var(--ink)">{{ $s->task }}</b>
        <div class="tiny muted" style="margin-top:2px">{{ $s->machine?->name ?? 'Mesin' }} · {{ $s->machine?->location ?? '-' }}</div>
      </div>
      @include('partials.chip', ['v' => $s->display_status])
    </div>

    <div style="margin:10px 0;background:#f9fafb;border-radius:8px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
      <div class="tiny">
        <span class="muted">Jatuh Tempo:</span>
        <b style="color:var(--ink)">{{ $s->due_date ? $s->due_date->translatedFormat('d M Y') : '-' }}{{ $s->due_time ? ' ('.$s->due_time.' WIB)' : '' }}</b>
      </div>
      <div class="tiny">
        <span class="muted">Siklus:</span>
        <span style="color:var(--ink2);font-weight:600">{{ $payload['interval'] }}</span>
      </div>
    </div>

    <div class="row between" style="margin-bottom:6px">
      <span class="tiny muted">Progress: <b>{{ $s->progress }}%</b></span>
      <span class="tiny muted">
        @if ($s->status === 'selesai')
          <span style="color:#16a34a;font-weight:700">✓ Tuntas dikerjakan</span>
        @elseif ($s->progress >= 75)
          Foto sesudah terunggah
        @elseif ($s->progress >= 25)
          Foto awal terunggah
        @else
          Belum dimulai
        @endif
      </span>
    </div>
    <div class="progress" style="margin-bottom:12px"><i style="width:{{ $s->progress }}%"></i></div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      @if ($s->status !== 'selesai')
        <button type="button" class="btn primary block btn-open-schedule" data-schedule-id="{{ $s->id }}" data-schedule="{{ json_encode($payload) }}" onclick="openScheduleById({{ $s->id }})" style="justify-content:center;font-size:12.5px;padding:8px 14px;cursor:pointer">
          🛠️ Kerjakan & Laporkan Hasil
        </button>
      @else
        <button type="button" class="btn block btn-open-schedule" data-schedule-id="{{ $s->id }}" data-schedule="{{ json_encode($payload) }}" onclick="openScheduleById({{ $s->id }})" style="justify-content:center;font-size:12.5px;padding:8px 14px;background:#fff;border-color:var(--line);cursor:pointer">
          👁️ Lihat Detail & Hasil
        </button>
      @endif
    </div>
  </div>
@empty
  <div class="tcard empty" style="padding:36px 16px;text-align:center">
    <div style="font-size:28px;margin-bottom:8px">🗓️</div>
    <b style="color:var(--ink)">
      @if ($tab === 'selesai')
        Belum ada riwayat maintenance yang selesai.
      @else
        Tidak ada jadwal maintenance aktif untuk Anda saat ini.
      @endif
    </b>
    <p class="tiny muted" style="margin:4px 0 0">
      Jadwal yang ditugaskan oleh manajemen akan otomatis muncul di sini.
    </p>
  </div>
@endforelse

@if ($schedules->hasPages())
  <div style="margin-top:14px">
    {{ $schedules->appends(['tab' => $tab])->links() }}
  </div>
@endif

{{-- ======================================================== --}}
{{-- MODAL POPUP PENGERJAAN & LAPORAN MAINTENANCE (TIMBUL DI TENGAH) --}}
{{-- ======================================================== --}}
<div id="maintenanceModal" class="quick-modal-backdrop" style="display:none;z-index:999999" onclick="if(event.target===this) closeMaintenanceModal()">
  <div class="quick-modal-card" style="width:min(580px, 100%)" onclick="event.stopPropagation()">
    
    {{-- Header Modal --}}
    <div style="padding:16px 20px;background:#fff6f7;border-bottom:1px solid var(--rose200);display:flex;align-items:center;justify-content:space-between;gap:12px;border-top-left-radius:22px;border-top-right-radius:22px">
      <div style="display:flex;align-items:center;gap:10px">
        <span class="chip-dot" style="width:9px;height:9px;background:var(--r700);flex-shrink:0"></span>
        <div>
          <h3 id="modalTaskTitle" style="margin:0;font-size:16px;font-weight:800;color:var(--ink)">Pengerjaan Maintenance</h3>
          <span id="modalMachineSubtitle" class="tiny muted" style="display:block;margin-top:2px">Mesin · Lokasi</span>
        </div>
      </div>
      <button type="button" onclick="closeMaintenanceModal()" aria-label="Tutup modal" style="width:32px;height:32px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);cursor:pointer;font-size:15px;display:grid;place-items:center;line-height:1">✕</button>
    </div>

    {{-- Body Modal --}}
    <div style="padding:18px 20px;display:grid;gap:14px;max-height:calc(85vh - 70px);overflow-y:auto">
      
      {{-- Banner Selesai (Jika Sudah Selesai) --}}
      <div id="modalDoneBanner" style="display:none;background:linear-gradient(135deg,#fff5f6 0%,#ffe9ec 100%);border:1.5px solid var(--rose200);border-left:5px solid var(--brand);border-radius:14px;padding:14px 16px;box-shadow:0 4px 14px -4px rgba(177,0,44,0.15)">
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:16px;color:var(--brand)">✓</span>
          <b style="color:var(--r800);font-size:14.5px">Pemeliharaan Selesai Dikerjakan!</b>
        </div>
        <p id="modalDoneDateText" class="tiny" style="color:var(--ink2);margin:4px 0 10px">Diselesaikan dan status mesin telah aktif kembali.</p>
        <a id="modalWaBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn primary block" style="background:#16a34a;border-color:#16a34a;justify-content:center;gap:8px;font-size:12.5px;padding:9px 12px">
          @include('partials.icon', ['n' => 'whatsapp']) Kirim Laporan Selesai ke WA Manajemen
        </a>
      </div>

      {{-- Detail Informasi Singkat --}}
      <div style="background:#f9fafb;border:1px solid var(--line);border-radius:12px;padding:12px 14px;display:grid;gap:6px">
        <div class="row between" style="gap:8px">
          <span class="tiny muted">Status Pengerjaan:</span>
          <span id="modalStatusBadge"></span>
        </div>
        <div class="row between" style="gap:8px">
          <span class="tiny muted">Jatuh Tempo:</span>
          <b id="modalDueDate" class="tiny" style="color:var(--ink)">-</b>
        </div>
        <div class="row between" style="gap:8px">
          <span class="tiny muted">Siklus Servis:</span>
          <span id="modalInterval" class="tiny" style="color:var(--ink2);font-weight:600">-</span>
        </div>
      </div>

      {{-- Progress Bar & Stepper --}}
      <div>
        <div class="row between" style="margin-bottom:6px">
          <span class="tiny" style="font-weight:700;color:var(--ink)">Progres Pengerjaan</span>
          <b id="modalProgressText" style="color:var(--brand);font-size:12.5px">0%</b>
        </div>
        <div class="progress" style="margin-bottom:8px"><i id="modalProgressBar" style="width:0%"></i></div>
        <ul class="steps">
          <li id="stepBefore"><i>✓</i> Foto Sebelum</li>
          <li id="stepWork"><i>✓</i> Pengerjaan</li>
          <li id="stepAfter"><i>✓</i> Foto Sesudah</li>
          <li id="stepDone"><i>✓</i> Selesai</li>
        </ul>
      </div>

      {{-- Dokumentasi Foto Sebelum & Sesudah (2 Kolom) --}}
      <div>
        <b style="font-size:13px;color:var(--ink);display:block;margin-bottom:8px">Dokumentasi Foto Bukti</b>
        <div class="grid g2" style="gap:10px">
          
          {{-- Foto Sebelum --}}
          <div style="border:1px dashed var(--line);border-radius:12px;padding:10px;text-align:center;background:#fafafa">
            <span class="tiny muted" style="display:block;margin-bottom:6px;font-weight:700">1. Foto Sebelum</span>
            
            <div id="beforePreviewWrap" style="display:none;margin-bottom:6px">
              <img id="beforeImg" src="" alt="Foto Sebelum" style="width:100%;height:105px;object-fit:cover;border-radius:8px;border:1px solid var(--line);display:block">
            </div>

            <form id="beforeUploadForm" method="POST" action="" enctype="multipart/form-data" style="margin:0">
              @csrf
              <input type="hidden" name="phase" value="before">
              <label id="beforeUploadBtn" class="btn primary block" style="font-size:11px;padding:7px;cursor:pointer;justify-content:center;margin:0">
                <span id="beforeUploadText">📷 Unggah Foto</span>
                <input type="file" id="beforeFileInput" name="photo" accept="image/*" style="display:none" onchange="submitMaintenancePhoto(this, 'beforeUploadText')">
              </label>
            </form>
          </div>

          {{-- Foto Sesudah --}}
          <div style="border:1px dashed var(--line);border-radius:12px;padding:10px;text-align:center;background:#fafafa">
            <span class="tiny muted" style="display:block;margin-bottom:6px;font-weight:700">2. Foto Sesudah</span>
            
            <div id="afterPreviewWrap" style="display:none;margin-bottom:6px">
              <img id="afterImg" src="" alt="Foto Sesudah" style="width:100%;height:105px;object-fit:cover;border-radius:8px;border:1px solid var(--line);display:block">
            </div>

            <form id="afterUploadForm" method="POST" action="" enctype="multipart/form-data" style="margin:0">
              @csrf
              <input type="hidden" name="phase" value="after">
              <label id="afterUploadBtn" class="btn primary block" style="font-size:11px;padding:7px;cursor:pointer;justify-content:center;margin:0">
                <span id="afterUploadText">📸 Unggah Foto</span>
                <input type="file" id="afterFileInput" name="photo" accept="image/*" style="display:none" onchange="submitMaintenancePhoto(this, 'afterUploadText')">
              </label>
            </form>
          </div>
        </div>
      </div>

      {{-- Form Pengerjaan & Selesaikan (Tampil Jika Belum Selesai) --}}
      <form id="modalCompleteForm" method="POST" action="" enctype="multipart/form-data" style="margin:0;display:grid;gap:12px">
        @csrf
        <div>
          <label class="f" style="margin:0 0 6px">Suku Cadang / Bahan yang Digunakan (Opsional)</label>
          <input class="input" id="modalInputParts" name="spareparts" placeholder="Contoh: Oli Shell Tellus 46 (5L), Seal O-Ring">
          <span class="tiny muted" style="display:block;margin-top:3px">Daftar part atau pelumas yang diganti/ditambahkan.</span>
        </div>

        <div>
          <label class="f" style="margin:0 0 6px">Catatan Hasil Pemeriksaan & Tindakan</label>
          <textarea class="input" id="modalInputNotes" name="notes" rows="3" placeholder="Contoh: Rantai telah dilumasi, bearing dibersihkan, kebocoran nihil, mesin beroperasi normal saat uji beban."></textarea>
          <span class="tiny muted" style="display:block;margin-top:3px">Jelaskan kondisi mesin setelah Anda periksa/rawat.</span>
        </div>

        <div style="background:#fff5f6;border:1px dashed var(--rose200);border-radius:12px;padding:10px 12px">
          <span class="tiny" style="font-weight:700;color:var(--r800);display:block;margin-bottom:6px">Unggah Foto Bersamaan Saat Selesai (Opsional)</span>
          <div class="grid g2" style="gap:8px">
            <div>
              <label class="f" style="margin:0 0 4px;font-size:11px">Foto Sebelum</label>
              <input class="input" type="file" name="before_photo" accept="image/*" style="padding:6px;font-size:11px">
            </div>
            <div>
              <label class="f" style="margin:0 0 4px;font-size:11px">Foto Sesudah</label>
              <input class="input" type="file" name="after_photo" accept="image/*" style="padding:6px;font-size:11px">
            </div>
          </div>
        </div>

        <div style="padding-top:6px;display:flex;justify-content:flex-end;gap:8px">
          <button type="button" class="btn" onclick="closeMaintenanceModal()" style="padding:9px 16px;font-size:13px;border-color:var(--line);color:var(--ink2)">Batal</button>
          <button type="submit" class="btn primary btn-compact" style="padding:9px 20px;font-size:13px;font-weight:700" data-confirm="Apakah pekerjaan maintenance ini telah selesai dan siap dilaporkan?">
            ✓ Tandai Selesai & Laporkan
          </button>
        </div>
      </form>

      {{-- Ringkasan Selesai (Tampil Jika Status Selesai) --}}
      <div id="modalCompletedSummary" style="display:none;background:#f9fafb;border:1px solid var(--line);border-radius:12px;padding:12px 14px;display:grid;gap:8px">
        <div>
          <span class="tiny muted" style="display:block">Suku Cadang / Bahan:</span>
          <b id="summaryParts" class="tiny" style="color:var(--ink)">-</b>
        </div>
        <div>
          <span class="tiny muted" style="display:block">Catatan Hasil Perawatan:</span>
          <p id="summaryNotes" class="tiny" style="margin:2px 0 0;color:var(--ink);line-height:1.4">-</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
window.schedulesData = window.schedulesData || {};
let currentSchedule = null;

// Pindahkan modal ke level document.body saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('maintenanceModal');
  if (modal && modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }
});

function openScheduleById(id) {
  if (window.schedulesData && window.schedulesData[id]) {
    openMaintenanceModal(window.schedulesData[id]);
    return;
  }
  const btn = document.querySelector(`[data-schedule-id="${id}"]`);
  if (btn && btn.dataset.schedule) {
    try {
      const data = typeof btn.dataset.schedule === 'string' ? JSON.parse(btn.dataset.schedule) : btn.dataset.schedule;
      openMaintenanceModal(data);
      return;
    } catch(e) {}
  }
  console.warn('Jadwal data tidak ditemukan untuk ID:', id);
}

// Event delegation click untuk memastikan tombol jadwal selalu merespon
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn-open-schedule, [data-schedule-id]');
  if (btn && btn.dataset.schedule) {
    e.preventDefault();
    try {
      const item = typeof btn.dataset.schedule === 'string' ? JSON.parse(btn.dataset.schedule) : btn.dataset.schedule;
      openMaintenanceModal(item);
    } catch(err) {
      console.error('Gagal parsing data schedule:', err);
    }
  }
});

function submitMaintenancePhoto(input, textId) {
  if (input.files && input.files.length > 0) {
    const form = input.form;
    if (currentSchedule && currentSchedule.upload_url) {
      form.action = currentSchedule.upload_url;
    }
    const textEl = document.getElementById(textId);
    if (textEl) textEl.textContent = '⏳ Mengunggah...';
    form.submit();
  }
}

function openMaintenanceModal(item) {
  if (!item) return;
  currentSchedule = item;
  const modal = document.getElementById('maintenanceModal');
  if (!modal) return;

  // Header
  const titleEl = document.getElementById('modalTaskTitle');
  const subEl   = document.getElementById('modalMachineSubtitle');
  if (titleEl) titleEl.textContent = item.task || 'Pengerjaan Maintenance';
  if (subEl)   subEl.textContent = `${item.machine_name || 'Mesin'} (${item.machine_code || '-'}) · ${item.location || '-'}`;

  // Info
  const dueEl  = document.getElementById('modalDueDate');
  const intEl  = document.getElementById('modalInterval');
  if (dueEl) dueEl.textContent = `${item.due_date || '-'} ${item.due_time ? '· Jam ' + item.due_time + ' WIB' : ''}`;
  if (intEl) intEl.textContent = item.interval || 'Rutin';

  // Status Chip
  const badgeWrap = document.getElementById('modalStatusBadge');
  if (badgeWrap) {
    const chipClass = item.status === 'selesai' ? 'c-selesai' : (item.display_status === 'terlambat' ? 'c-terlambat' : 'c-terjadwal');
    const chipLabel = item.status === 'selesai' ? 'Selesai' : (item.display_status === 'terlambat' ? 'Terlambat' : (item.status === 'dikerjakan' ? 'Dikerjakan' : 'Terjadwal'));
    badgeWrap.innerHTML = `<span class="chip ${chipClass}"><span class="chip-dot"></span> ${chipLabel}</span>`;
  }

  // Progress Bar
  const prog = parseInt(item.progress, 10) || 0;
  const progText = document.getElementById('modalProgressText');
  const progBar  = document.getElementById('modalProgressBar');
  if (progText) progText.textContent = `${prog}%`;
  if (progBar)  progBar.style.width = `${prog}%`;

  // Stepper
  const stepBefore = document.getElementById('stepBefore');
  const stepWork   = document.getElementById('stepWork');
  const stepAfter  = document.getElementById('stepAfter');
  const stepDone   = document.getElementById('stepDone');

  if (stepBefore) stepBefore.className = (item.before_url || prog >= 25) ? 'done' : '';
  if (stepWork)   stepWork.className   = (prog >= 50) ? 'done' : '';
  if (stepAfter)  stepAfter.className  = (item.after_url || prog >= 75) ? 'done' : '';
  if (stepDone)   stepDone.className   = (item.status === 'selesai') ? 'done' : '';

  // Foto Sebelum
  const beforeForm = document.getElementById('beforeUploadForm');
  const beforeWrap = document.getElementById('beforePreviewWrap');
  const beforeImg  = document.getElementById('beforeImg');
  const beforeText = document.getElementById('beforeUploadText');
  const beforeBtn  = document.getElementById('beforeUploadBtn');
  if (beforeForm) beforeForm.action = item.upload_url || '';

  if (item.before_url) {
    if (beforeImg) beforeImg.src = item.before_url;
    if (beforeWrap) beforeWrap.style.display = 'block';
    if (beforeText) beforeText.textContent = 'Ganti Foto';
    if (beforeBtn) beforeBtn.className = 'btn block';
  } else {
    if (beforeWrap) beforeWrap.style.display = 'none';
    if (beforeText) beforeText.textContent = '📷 Unggah Foto';
    if (beforeBtn) beforeBtn.className = 'btn primary block';
  }

  // Foto Sesudah
  const afterForm = document.getElementById('afterUploadForm');
  const afterWrap = document.getElementById('afterPreviewWrap');
  const afterImg  = document.getElementById('afterImg');
  const afterText = document.getElementById('afterUploadText');
  const afterBtn  = document.getElementById('afterUploadBtn');
  if (afterForm) afterForm.action = item.upload_url || '';

  if (item.after_url) {
    if (afterImg) afterImg.src = item.after_url;
    if (afterWrap) afterWrap.style.display = 'block';
    if (afterText) afterText.textContent = 'Ganti Foto';
    if (afterBtn) afterBtn.className = 'btn block';
  } else {
    if (afterWrap) afterWrap.style.display = 'none';
    if (afterText) afterText.textContent = '📸 Unggah Foto';
    if (afterBtn) afterBtn.className = 'btn primary block';
  }

  // Selesai vs Belum Selesai View
  const doneBanner = document.getElementById('modalDoneBanner');
  const completeForm = document.getElementById('modalCompleteForm');
  const summaryBox = document.getElementById('modalCompletedSummary');
  const waBtn = document.getElementById('modalWaBtn');

  if (item.status === 'selesai') {
    if (doneBanner) doneBanner.style.display = 'block';
    const dateEl = document.getElementById('modalDoneDateText');
    if (dateEl) dateEl.textContent = `Diselesaikan pada ${item.completed_date || 'Hari ini'}. Status mesin telah aktif kembali.`;
    if (waBtn) waBtn.href = item.wa_link || '#';

    if (completeForm) completeForm.style.display = 'none';
    if (summaryBox) summaryBox.style.display = 'grid';
    const partsEl = document.getElementById('summaryParts');
    const notesEl = document.getElementById('summaryNotes');
    if (partsEl) partsEl.textContent = item.spareparts || 'Tidak ada pergantian suku cadang';
    if (notesEl) notesEl.textContent = item.notes || 'Pekerjaan rutin selesai sesuai standar.';

    // Matikan upload jika sudah selesai
    if (beforeForm) beforeForm.style.display = 'none';
    if (afterForm) afterForm.style.display = 'none';
  } else {
    if (doneBanner) doneBanner.style.display = 'none';
    if (completeForm) {
      completeForm.style.display = 'grid';
      completeForm.action = item.complete_url || '';
    }
    if (summaryBox) summaryBox.style.display = 'none';

    const pIn = document.getElementById('modalInputParts');
    const nIn = document.getElementById('modalInputNotes');
    if (pIn) pIn.value = item.spareparts || '';
    if (nIn) nIn.value = item.notes || '';

    if (beforeForm) beforeForm.style.display = 'block';
    if (afterForm) afterForm.style.display = 'block';
  }

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeMaintenanceModal() {
  const modal = document.getElementById('maintenanceModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeMaintenanceModal();
  }
});

// Auto open modal jika ada parameter open di URL
@if (isset($openSchedule) && $openSchedule)
  document.addEventListener('DOMContentLoaded', function() {
    @php
      $openPayload = [
        'id'             => $openSchedule->id,
        'task'           => $openSchedule->task,
        'type'           => $openSchedule->type,
        'status'         => $openSchedule->status,
        'display_status' => $openSchedule->display_status,
        'progress'       => (int) $openSchedule->progress,
        'machine_name'   => $openSchedule->machine?->name ?? 'Mesin',
        'machine_code'   => $openSchedule->machine?->code ?? '-',
        'location'       => $openSchedule->machine?->location ?? '-',
        'due_date'       => $openSchedule->due_date ? $openSchedule->due_date->translatedFormat('d M Y') : '-',
        'due_time'       => $openSchedule->due_time ?: '',
        'interval'       => $openSchedule->interval_label ?: ($openSchedule->interval_months ? $openSchedule->interval_months.' bulan' : 'Rutin'),
        'spareparts'     => $openSchedule->spareparts ?: '',
        'notes'          => $openSchedule->notes ?: '',
        'before_url'     => $openSchedule->before_url ?: '',
        'after_url'      => $openSchedule->after_url ?: '',
        'completed_date' => $openSchedule->completed_at ? $openSchedule->completed_at->translatedFormat('d F Y') : '',
        'upload_url'     => route('teknisi.jadwal.upload', $openSchedule),
        'complete_url'   => route('teknisi.jadwal.selesai', $openSchedule),
        'wa_link'        => \App\Services\WhatsAppService::makeScheduleCompletedLink($openSchedule, $managerPhone ?? null),
      ];
    @endphp
    openMaintenanceModal(@json($openPayload));
  });
@endif
</script>

@endsection
