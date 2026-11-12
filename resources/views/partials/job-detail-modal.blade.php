{{-- Modal Form Kotak Kecil: Riwayat Pekerjaan & Foto Besar --}}
<div id="jobDetailModal" class="job-modal-backdrop" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.62);backdrop-filter:blur(6px);z-index:99999;align-items:center;justify-content:center;padding:16px;-webkit-overflow-scrolling:touch" role="dialog" aria-modal="true" aria-labelledby="modalJobTitle">
  <div class="job-modal-box" style="background:#fff;border-radius:22px;box-shadow:0 25px 60px -15px rgba(0,0,0,0.35);width:100%;max-width:540px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;border:1px solid #e2e8f0;position:relative">
    
    {{-- Header Modal Kotak Kecil --}}
    <div style="padding:18px 22px 14px;background:#fafaf9;border-bottom:1px solid var(--line);display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
      <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
          <span id="modalHeaderDot" class="chip-dot" style="background:#b1002c;width:8px;height:8px"></span>
          <h3 id="modalJobTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--ink)">Riwayat Pekerjaan</h3>
          <span id="modalJobBadgeCode" class="badge-code" style="font-size:11.5px">TK-0000</span>
        </div>
        <div id="modalMachineMeta" class="tiny muted" style="font-weight:600">
          Mesin Press 2 · MSN-014 · Area Produksi A
        </div>
      </div>
      <button type="button" 
              onclick="closeJobDetailModal()" 
              aria-label="Tutup form riwayat"
              style="width:34px;height:34px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);display:grid;place-items:center;cursor:pointer;font-size:16px;line-height:1;transition:all .15s ease;flex-shrink:0"
              onmouseover="this.style.background='#f3f4f6';this.style.color='#000'"
              onmouseout="this.style.background='#fff';this.style.color='var(--ink2)'">
        ✕
      </button>
    </div>

    {{-- Konten Riwayat Pekerjaan (Scrollable) --}}
    <div style="padding:18px 22px;overflow-y:auto;display:flex;flex-direction:column;gap:16px">
      
      {{-- Baris Status, Tanggal, & Teknisi --}}
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:12px 14px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <span class="tiny muted" style="display:block">Status Pekerjaan</span>
          <div id="modalStatusContainer" style="margin-top:3px"></div>
        </div>
        <div>
          <span class="tiny muted" style="display:block">Waktu / Tanggal</span>
          <b id="modalDateVal" style="font-size:13px;color:var(--ink);display:block;margin-top:3px">-</b>
        </div>
        <div style="grid-column:1/-1;border-top:1px dashed #e2e8f0;padding-top:8px">
          <span class="tiny muted" style="display:block">Teknisi Penanggung Jawab</span>
          <div id="modalTechVal" style="font-size:13.5px;font-weight:700;color:var(--ink);margin-top:2px;display:flex;align-items:center;gap:6px">
            -
          </div>
        </div>
      </div>

      {{-- Bagian Tugas / Keluhan Kerusakan --}}
      <div>
        <span id="modalComplaintLabel" class="tiny" style="font-weight:700;color:var(--r800);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px">
          Rincian Keluhan / Tugas:
        </span>
        <div id="modalComplaintBox" style="background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;padding:12px 14px;font-size:13.5px;line-height:1.5;color:#881337">
          -
        </div>
      </div>

      {{-- Tindakan & Suku Cadang (Jika Ada) --}}
      <div id="modalActionContainer" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 14px">
        <span class="tiny" style="font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px">
          Tindakan Yang Telah Dilakukan:
        </span>
        <div id="modalActionText" style="font-size:13.5px;font-weight:700;color:#14532d"></div>
        <div id="modalSparepartsRow" class="tiny" style="color:#166534;margin-top:4px;display:none">
          Suku cadang / Sparepart: <b id="modalSparepartsText">-</b>
        </div>
      </div>

      {{-- FOTO DOKUMENTASI PEKERJAAN (LEBIH BESAR & JELAS) --}}
      <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <span class="tiny" style="font-weight:800;color:var(--ink);text-transform:uppercase;letter-spacing:.5px">
            📷 Foto Dokumentasi Pekerjaan
          </span>
        </div>

        {{-- Wadah Foto Berukuran Besar --}}
        <div id="modalPhotosContainer" style="display:flex;flex-direction:column;gap:14px">
          {{-- Diisi secara dinamis oleh JavaScript --}}
        </div>

        {{-- Jika Tidak Ada Foto --}}
        <div id="modalNoPhotos" style="display:none;text-align:center;padding:24px 16px;background:#f9fafb;border:1.5px dashed var(--line);border-radius:14px;color:var(--ink3)">
          <div style="font-size:26px;margin-bottom:4px">📷</div>
          <div style="font-size:13px;font-weight:600">Belum ada foto dokumentasi pekerjaan</div>
          <span class="tiny muted">Foto kendala atau hasil perbaikan belum diunggah oleh teknisi.</span>
        </div>
      </div>

      {{-- Riwayat Status / Log Aktivitas --}}
      <div>
        <span class="tiny" style="font-weight:800;color:var(--ink);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:8px">
          ⏱️ Riwayat Perubahan Status:
        </span>
        <ul id="modalLogsList" class="tl" style="margin:0;padding-left:22px">
          {{-- Diisi dinamis --}}
        </ul>
      </div>

    </div>

    {{-- Footer Modal Kotak Kecil --}}
    <div style="padding:14px 22px;background:#fafaf9;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:10px">
      <div id="modalExtraLinkContainer"></div>
      <button type="button" class="btn primary" onclick="closeJobDetailModal()" style="font-size:13px;padding:9px 18px">
        Tutup Form
      </button>
    </div>

  </div>
</div>

{{-- Modal Fullscreen Zoom Foto (Klik di area hitam mana saja untuk kembali ke form kecil) --}}
<div id="photoZoomModal" 
     class="photo-zoom-backdrop" 
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.93);backdrop-filter:blur(8px);z-index:100050;flex-direction:column;align-items:center;justify-content:center;padding:16px;cursor:zoom-out;-webkit-overflow-scrolling:touch"
     role="dialog"
     aria-modal="true"
     aria-label="Tinjauan Foto Diperbesar"
     onclick="if (event.target === this) closePhotoZoom()">

  {{-- Bar Atas Melayang --}}
  <div style="position:absolute;top:16px;left:20px;right:20px;display:flex;align-items:center;justify-content:space-between;pointer-events:none;z-index:100052">
    <div style="display:flex;align-items:center;gap:10px;pointer-events:auto">
      <span id="photoZoomBadgeLabel" class="chip" style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:12.5px;padding:6px 14px;backdrop-filter:blur(8px);font-weight:700">
        📷 Foto Dokumentasi
      </span>
    </div>
    
   
  </div>

  {{-- Kontainer Gambar Terpusat: Klik di luar gambar (area hitam) otomatis kembali ke form kecil --}}
  <div style="max-width:94vw;max-height:86vh;display:flex;align-items:center;justify-content:center;position:relative;cursor:default" onclick="event.stopPropagation()">
    <img id="photoZoomImg" 
         src="" 
         alt="Perbesar Foto" 
         style="max-width:92vw;max-height:84vh;width:auto;height:auto;object-fit:contain;border-radius:12px;box-shadow:0 30px 80px rgba(0,0,0,0.95);display:block;border:1.5px solid rgba(255,255,255,0.2)">
  </div>


<style>
.photo-zoom-backdrop {
  cursor: zoom-out !important;
}
.photo-big-card-img-wrap {
  cursor: zoom-in;
  display: block;
  overflow: hidden;
  position: relative;
}
.photo-big-card{
  border-radius:16px;
  overflow:hidden;
  border:1.5px solid var(--line);
  background:#1e293b;
  position:relative;
  box-shadow:0 4px 14px rgba(0,0,0,0.08);
}
.photo-big-label{
  position:absolute;
  top:10px;
  left:10px;
  background:rgba(15,23,42,0.85);
  color:#fff;
  padding:4px 10px;
  border-radius:8px;
  font-size:11.5px;
  font-weight:700;
  backdrop-filter:blur(4px);
  z-index:2;
}
.photo-big-img{
  width:100%;
  height:240px;
  object-fit:cover;
  display:block;
  transition:transform .2s ease;
  cursor:pointer;
}
.photo-big-card:hover .photo-big-img{
  transform:scale(1.02);
}
.photo-big-video{
  width:100%;
  max-height:260px;
  display:block;
  background:#000;
}
.photo-big-zoom-btn{
  position:absolute;
  bottom:10px;
  right:10px;
  background:rgba(0,0,0,0.75);
  color:#fff;
  padding:4px 9px;
  border-radius:8px;
  font-size:11px;
  font-weight:600;
  text-decoration:none;
  display:inline-flex;
  align-items:center;
  gap:4px;
  z-index:2;
}
.photo-big-zoom-btn:hover{
  background:var(--r700);
  color:#fff;
}
</style>

<script>
// Fungsi Global Buka Modal Detail Tiket Perbaikan
window.openTicketDetail = function(t) {
  try {
    if (!t) return;
    if (typeof t === 'string') {
      try { t = JSON.parse(t); } catch (e) { console.error('Gagal parse tiket payload:', e); }
    }
    const modal = document.getElementById('jobDetailModal');
    if (!modal) return;

    const elTitle = document.getElementById('modalJobTitle');
    const elCode = document.getElementById('modalJobBadgeCode');
    const elMeta = document.getElementById('modalMachineMeta');
    const elDot  = document.getElementById('modalHeaderDot');
    const cLabel = document.getElementById('modalComplaintLabel');
    const elComplaint = document.getElementById('modalComplaintBox');

    if (elDot) elDot.style.background = '#b1002c';
    if (elTitle) elTitle.textContent = 'Riwayat Perbaikan Mesin (Kerusakan)';
    if (elCode) {
      elCode.textContent = t.code || '-';
      elCode.style.background = '#ffe4e6';
      elCode.style.color = '#9f1239';
    }
    
    const mName = t.machine ? (t.machine.name || '') : (t.machine_name || 'Mesin');
    const mCode = t.machine ? (t.machine.code || '') : (t.machine_code || '');
    const mLoc  = t.machine ? (t.machine.location || '') : (t.location || '-');
    if (elMeta) elMeta.textContent = `${mName} ${mCode ? '· ' + mCode : ''} · Lokasi: ${mLoc || '-'}`;

    // Status Chip
    const statusBox = document.getElementById('modalStatusContainer');
    if (statusBox) {
      const statusText = t.display_status || t.status || 'dilaporkan';
      let chipClass = 'c-diproses';
      if (statusText === 'selesai') chipClass = 'c-selesai';
      else if (statusText === 'terlambat') chipClass = 'c-terlambat';
      else if (statusText === 'ditugaskan') chipClass = 'c-ditugaskan';
      else if (statusText === 'dilaporkan') chipClass = 'c-dilaporkan';

      statusBox.innerHTML = `<span class="chip ${chipClass}"><span class="chip-dot"></span> <span style="text-transform:capitalize">${statusText}</span></span>`;
    }

    // Waktu
    const elDate = document.getElementById('modalDateVal');
    if (elDate) elDate.textContent = t.date_formatted || t.created_at || '-';

    // Teknisi & Tombol WA Perbaikan
    const techBox = document.getElementById('modalTechVal');
    if (techBox) {
      if (t.technician && t.technician.name) {
        const waLink = t.wa_url || (t.technician.whatsapp ? `https://wa.me/${String(t.technician.whatsapp).replace(/[^0-9]/g, '')}` : null);
        techBox.innerHTML = `<span>👤 <b>${t.technician.name}</b></span>` + 
          (waLink ? `<a href="${waLink}" target="_blank" class="badge-wa" style="font-size:11px;margin-left:6px;background:#22c55e;color:#fff" title="Chat WhatsApp Format Perbaikan">📱 WA Tiket</a>` : '');
      } else if (t.technician_name) {
        techBox.innerHTML = `<span>👤 <b>${t.technician_name}</b></span>`;
      } else {
        techBox.innerHTML = `<span class="chip" style="background:#ffedd5;color:#c2410c;font-size:11.5px;font-weight:800"><span class="chip-dot" style="background:#ea580c"></span> ⚡ Belum Diambil (Siapa Cepat Dia Dapat)</span>`;
      }
    }

    // Keluhan Kerusakan (Merah/Alert)
    if (cLabel) {
      cLabel.textContent = '🚨 Rincian Keluhan / Gejala Kerusakan:';
      cLabel.style.color = '#9f1239';
    }
    if (elComplaint) {
      elComplaint.textContent = t.complaint || 'Tidak ada catatan keluhan.';
      elComplaint.style.background = '#fff1f2';
      elComplaint.style.borderColor = '#fecdd3';
      elComplaint.style.color = '#881337';
    }

    // Tindakan & Sparepart
    const actionContainer = document.getElementById('modalActionContainer');
    const actionText = document.getElementById('modalActionText');
    const sparepartsRow = document.getElementById('modalSparepartsRow');
    const sparepartsText = document.getElementById('modalSparepartsText');

    if (actionContainer) {
      if (t.action || t.spareparts) {
        actionContainer.style.display = 'block';
        if (actionText) actionText.textContent = t.action || (t.status === 'selesai' ? 'Perbaikan selesai dilaksanakan.' : 'Sedang dalam penanganan.');
        if (sparepartsRow && sparepartsText) {
          if (t.spareparts) {
            sparepartsRow.style.display = 'block';
            sparepartsText.textContent = t.spareparts;
          } else {
            sparepartsRow.style.display = 'none';
          }
        }
      } else {
        actionContainer.style.display = 'none';
      }
    }

    // Foto-foto lebih besar
    renderLargePhotos(t.photos || []);

    // Riwayat Status (Logs)
    renderLogs(t.logs || []);

    // Extra link ke show jika tersedia
    const extraLinkBox = document.getElementById('modalExtraLinkContainer');
    if (extraLinkBox) {
      if (t.show_url) {
        extraLinkBox.innerHTML = `<a href="${t.show_url}" class="link tiny" style="font-weight:700">Buka Halaman Lengkap →</a>`;
      } else {
        extraLinkBox.innerHTML = '';
      }
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  } catch (err) {
    console.error('Error saat membuka form detail pekerjaan:', err);
    alert('Terjadi kendala saat membuka rincian pekerjaan: ' + err.message);
  }
};

// Fungsi Global Buka Modal Detail Antrian Jadwal Maintenance
window.openScheduleDetail = function(s) {
  try {
    if (!s) return;
    if (typeof s === 'string') {
      try { s = JSON.parse(s); } catch (e) { console.error('Gagal parse jadwal payload:', e); }
    }
    const modal = document.getElementById('jobDetailModal');
    if (!modal) return;

    const elTitle = document.getElementById('modalJobTitle');
    const elCode = document.getElementById('modalJobBadgeCode');
    const elMeta = document.getElementById('modalMachineMeta');
    const elDot  = document.getElementById('modalHeaderDot');
    const cLabel = document.getElementById('modalComplaintLabel');
    const elComplaint = document.getElementById('modalComplaintBox');

    if (elDot) elDot.style.background = '#16a34a';
    if (elTitle) elTitle.textContent = 'Agenda Pemeliharaan Preventif';
    if (elCode) {
      elCode.textContent = s.type ? s.type.toUpperCase() : 'MAINTENANCE';
      elCode.style.background = '#dcfce7';
      elCode.style.color = '#166534';
    }

    const mName = s.machine ? (s.machine.name || '') : (s.machine_name || 'Mesin');
    const mCode = s.machine ? (s.machine.code || '') : (s.machine_code || '');
    const mLoc  = s.machine ? (s.machine.location || '') : (s.location || '-');
    if (elMeta) elMeta.textContent = `${mName} ${mCode ? '· ' + mCode : ''} · Lokasi: ${mLoc || '-'}`;

    // Status Chip
    const statusBox = document.getElementById('modalStatusContainer');
    if (statusBox) {
      const statusText = s.display_status || s.status || 'terjadwal';
      let chipClass = 'c-terjadwal';
      if (statusText === 'selesai') chipClass = 'c-selesai';
      else if (statusText === 'terlambat') chipClass = 'c-terlambat';
      else if (statusText === 'diproses') chipClass = 'c-diproses';

      statusBox.innerHTML = `<span class="chip ${chipClass}"><span class="chip-dot"></span> <span style="text-transform:capitalize">${statusText}</span></span>`;
    }

    // Waktu
    const elDate = document.getElementById('modalDateVal');
    if (elDate) elDate.textContent = s.formatted_date || s.due_date || '-';

    // Teknisi & Tombol WA Maintenance
    const techBox = document.getElementById('modalTechVal');
    if (techBox) {
      if (s.technician && s.technician.name) {
        const waLink = s.wa_url || (s.technician.whatsapp ? `https://wa.me/${String(s.technician.whatsapp).replace(/[^0-9]/g, '')}` : null);
        techBox.innerHTML = `<span>👤 <b>${s.technician.name}</b></span>` + 
          (waLink ? `<a href="${waLink}" target="_blank" class="badge-wa" style="font-size:11px;margin-left:6px;background:#16a34a;color:#fff" title="Kirim Notifikasi Jadwal Maintenance ke WA">📱 WA Jadwal</a>` : '');
      } else if (s.technician_name) {
        techBox.innerHTML = `<span>👤 <b>${s.technician_name}</b></span>`;
      } else {
        techBox.innerHTML = `<span class="chip c-ditugaskan" style="font-size:11px"><span class="chip-dot"></span> Belum Ditugaskan</span>`;
      }
    }

    // Tugas Maintenance (Hijau/Biru Harmonis)
    if (cLabel) {
      cLabel.textContent = '🛠️ Item Tugas Pemeliharaan Berkala:';
      cLabel.style.color = '#166534';
    }
    if (elComplaint) {
      elComplaint.textContent = s.task ? `Tugas: ${s.task}` : 'Pemeliharaan rutin terjadwal.';
      elComplaint.style.background = '#f0fdf4';
      elComplaint.style.borderColor = '#bbf7d0';
      elComplaint.style.color = '#14532d';
    }

    // Tindakan
    const actionContainer = document.getElementById('modalActionContainer');
    if (actionContainer) {
      if (s.ticket && s.ticket.action) {
        actionContainer.style.display = 'block';
        const actionText = document.getElementById('modalActionText');
        if (actionText) actionText.textContent = s.ticket.action;
        const sparepartsRow = document.getElementById('modalSparepartsRow');
        const sparepartsText = document.getElementById('modalSparepartsText');
        if (sparepartsRow && sparepartsText) {
          sparepartsRow.style.display = s.ticket.spareparts ? 'block' : 'none';
          sparepartsText.textContent = s.ticket.spareparts || '';
        }
      } else {
        actionContainer.style.display = 'none';
      }
    }

    // Foto dari jadwal / tiket terkait
    const photos = s.photos || (s.ticket ? (s.ticket.photos || []) : []);
    renderLargePhotos(photos);

    // Logs
    const logs = s.logs || (s.ticket ? (s.ticket.logs || []) : []);
    renderLogs(logs);

    const extraLinkBox = document.getElementById('modalExtraLinkContainer');
    if (extraLinkBox) {
      if (s.show_url) {
        extraLinkBox.innerHTML = `<a href="${s.show_url}" class="link tiny" style="font-weight:700">Detail Mesin →</a>`;
      } else {
        extraLinkBox.innerHTML = '';
      }
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  } catch (err) {
    console.error('Error saat membuka modal jadwal:', err);
  }
};

function renderLargePhotos(photos) {
  const container = document.getElementById('modalPhotosContainer');
  const noPhotos = document.getElementById('modalNoPhotos');
  if (!container || !noPhotos) return;
  container.innerHTML = '';

  if (!photos || photos.length === 0) {
    noPhotos.style.display = 'block';
    return;
  }

  noPhotos.style.display = 'none';

  photos.forEach(p => {
    const card = document.createElement('div');
    card.className = 'photo-big-card';

    const url = (typeof p === 'string') ? p : (p.url || '');
    if (!url) return;

    const phase = (typeof p === 'object' && p.phase) ? p.phase : '';
    const labelText = (typeof p === 'object' && p.label) ? p.label : (phase === 'before' ? 'Foto Sebelum (Awal)' : (phase === 'after' ? 'Foto Sesudah (Perbaikan)' : 'Foto Dokumentasi'));
    const isVideo = (typeof p === 'object' && p.kind === 'video') || url.match(/\.(mp4|mov)$/i);

    if (isVideo) {
      card.innerHTML = `
        <span class="photo-big-label">${labelText}</span>
        <video src="${url}" controls class="photo-big-video"></video>
      `;
    } else {
      const safeUrl = url.replace(/'/g, "\\'");
      const safeLabel = (labelText || 'Foto Dokumentasi').replace(/'/g, "\\'").replace(/"/g, '&quot;');
      card.innerHTML = `
        <span class="photo-big-label">${labelText}</span>
        <div class="photo-big-card-img-wrap" onclick="openPhotoZoom('${safeUrl}', '${safeLabel}')" title="Klik foto untuk memperbesar">
          <img src="${url}" alt="${labelText}" class="photo-big-img" loading="lazy">
        </div>
        <button type="button" class="photo-big-zoom-btn" onclick="openPhotoZoom('${safeUrl}', '${safeLabel}')" title="Klik untuk memperbesar foto">
          🔍 Perbesar Foto
        </button>
      `;
    }

    container.appendChild(card);
  });
}

window.openPhotoZoom = function(url, label) {
  if (!url) return;
  const zoomModal = document.getElementById('photoZoomModal');
  const zoomImg = document.getElementById('photoZoomImg');
  const badgeLabel = document.getElementById('photoZoomBadgeLabel');

  if (zoomImg) zoomImg.src = url;
  if (badgeLabel) badgeLabel.textContent = label ? `📷 ${label}` : '📷 Foto Dokumentasi';
  if (zoomModal) {
    zoomModal.style.display = 'flex';
  }
};

window.closePhotoZoom = function() {
  const zoomModal = document.getElementById('photoZoomModal');
  const zoomImg = document.getElementById('photoZoomImg');
  if (zoomModal) {
    zoomModal.style.display = 'none';
  }
  if (zoomImg) {
    zoomImg.src = '';
  }
};

function renderLogs(logs) {
  const list = document.getElementById('modalLogsList');
  if (!list) return;
  list.innerHTML = '';

  if (!logs || logs.length === 0) {
    list.innerHTML = '<li class="tiny muted" style="list-style:none">Belum ada catatan riwayat pekerjaan.</li>';
    return;
  }

  logs.forEach(l => {
    const li = document.createElement('li');
    const label = (typeof l === 'string') ? l : (l.label || '-');
    const time = (typeof l === 'object') ? (l.time || l.created_at || '') : '';
    const note = (typeof l === 'object' && l.note) ? '· ' + l.note : '';
    li.innerHTML = `
      <b style="color:var(--ink);font-size:13px">${label}</b>
      <div class="tiny muted">${time} ${note}</div>
    `;
    list.appendChild(li);
  });
}

window.closeJobDetailModal = function() {
  closePhotoZoom();
  const modal = document.getElementById('jobDetailModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
};

// Event listener ESC dan backdrop click
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const zoomModal = document.getElementById('photoZoomModal');
    if (zoomModal && zoomModal.style.display === 'flex') {
      e.preventDefault();
      closePhotoZoom();
      return;
    }
    closeJobDetailModal();
  }
});

document.addEventListener('click', function(e) {
  // 1. Klik area hitam pada photo zoom modal -> kembali ke form kecil
  const zoomModal = document.getElementById('photoZoomModal');
  if (zoomModal && zoomModal.style.display === 'flex') {
    if (e.target === zoomModal) {
      closePhotoZoom();
      return;
    }
  }

  // 2. Klik backdrop luar job detail modal -> tutup form
  const modal = document.getElementById('jobDetailModal');
  if (modal && e.target === modal) {
    closeJobDetailModal();
  }
});
</script>
