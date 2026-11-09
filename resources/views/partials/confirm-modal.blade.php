{{-- ======================================================== --}}
{{-- SIMMES CUSTOM CONFIRMATION MODAL (POPUP MERAH ELEGAN)     --}}
{{-- Menggantikan alert/confirm browser standar (127.0.0.1 says) --}}
{{-- ======================================================== --}}

<div id="simmesConfirmModal" class="quick-modal-backdrop" style="display:none;z-index:999999" onclick="if(event.target===this) window.closeSimmesConfirm(false)">
  <div class="quick-modal-card" style="width:min(440px, 92vw);padding:0;border-radius:20px;overflow:hidden;box-shadow:0 25px 60px -15px rgba(0,0,0,0.35);border:1px solid #fecdd3;animation:modalScaleIn .2s cubic-bezier(0.16,1,0.3,1)" onclick="event.stopPropagation()">
    
    {{-- Header Banner dengan Aksen Merah Brand --}}
    <div style="padding:22px 24px 18px;background:linear-gradient(135deg, #fff5f6 0%, #ffe9ec 100%);border-bottom:1px solid #fecdd3;display:flex;align-items:flex-start;gap:14px">
      <div id="simmesConfirmIconWrap" style="width:44px;height:44px;border-radius:12px;background:#fee2e2;color:#b1002c;display:grid;place-items:center;font-size:22px;flex-shrink:0;border:1px solid #fca5a5;box-shadow:0 4px 12px rgba(177,0,44,0.12)">
        <span id="simmesConfirmIcon">⚠️</span>
      </div>
      <div style="flex:1">
        <h3 id="simmesConfirmTitle" style="margin:0;font-size:16.5px;font-weight:800;color:#880022">Konfirmasi Tindakan</h3>
        <p id="simmesConfirmMessage" style="margin:6px 0 0;font-size:13.5px;color:#475569;line-height:1.5">
          Apakah Anda yakin ingin melanjutkan tindakan ini?
        </p>
      </div>
      <button type="button" onclick="window.closeSimmesConfirm(false)" aria-label="Tutup" style="width:30px;height:30px;border-radius:8px;border:1px solid #fecdd3;background:#fff;color:#880022;cursor:pointer;font-size:14px;display:grid;place-items:center;line-height:1;margin-top:-2px">✕</button>
    </div>

    {{-- Footer Tombol Aksi --}}
    <div style="padding:16px 24px;background:#ffffff;display:flex;justify-content:flex-end;align-items:center;gap:10px">
      <button type="button" id="simmesConfirmBtnCancel" onclick="window.closeSimmesConfirm(false)" class="btn" style="padding:9px 18px;font-size:13.5px;border:1px solid #cbd5e1;background:#fff;color:#475569;font-weight:600;border-radius:10px;cursor:pointer">
        Batal
      </button>
      <button type="button" id="simmesConfirmBtnOk" onclick="window.closeSimmesConfirm(true)" class="btn primary" style="padding:9px 22px;font-size:13.5px;background:#b1002c;border:1px solid #b1002c;color:#fff;font-weight:700;border-radius:10px;cursor:pointer;box-shadow:0 4px 12px rgba(177,0,44,0.25)">
        Ya, Lanjutkan
      </button>
    </div>

  </div>
</div>

<script>
(function() {
  let confirmCallback = null;

  window.showSimmesConfirm = function(opts) {
    opts = opts || {};
    const modal = document.getElementById('simmesConfirmModal');
    if (!modal) return;

    const titleEl = document.getElementById('simmesConfirmTitle');
    const msgEl   = document.getElementById('simmesConfirmMessage');
    const okBtn   = document.getElementById('simmesConfirmBtnOk');
    const cancelBtn = document.getElementById('simmesConfirmBtnCancel');
    const iconEl  = document.getElementById('simmesConfirmIcon');
    const iconWrap = document.getElementById('simmesConfirmIconWrap');

    const msg = opts.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    const isDelete = /hapus|delete|batal/i.test(msg);

    if (titleEl) titleEl.textContent = opts.title || (isDelete ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan');
    if (msgEl) msgEl.textContent = msg;

    if (okBtn) {
      okBtn.textContent = opts.confirmText || (isDelete ? 'Ya, Hapus' : 'Ya, Lanjutkan');
      if (isDelete) {
        okBtn.style.background = '#b1002c';
        okBtn.style.borderColor = '#b1002c';
      } else {
        okBtn.style.background = 'var(--brand, #b1002c)';
        okBtn.style.borderColor = 'var(--brand, #b1002c)';
      }
    }

    if (cancelBtn) cancelBtn.textContent = opts.cancelText || 'Batal';

    if (iconEl) iconEl.textContent = isDelete ? '🗑️' : '⚠️';
    if (iconWrap) {
      iconWrap.style.background = isDelete ? '#fee2e2' : '#fef3c7';
      iconWrap.style.borderColor = isDelete ? '#fca5a5' : '#fde68a';
      iconWrap.style.color = isDelete ? '#b1002c' : '#b45309';
    }

    confirmCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    if (okBtn) okBtn.focus();
  };

  window.closeSimmesConfirm = function(isConfirmed) {
    const modal = document.getElementById('simmesConfirmModal');
    if (modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    }
    if (isConfirmed && typeof confirmCallback === 'function') {
      const cb = confirmCallback;
      confirmCallback = null;
      cb();
    } else {
      confirmCallback = null;
    }
  };

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const modal = document.getElementById('simmesConfirmModal');
      if (modal && modal.style.display === 'flex') {
        window.closeSimmesConfirm(false);
      }
    }
  });

  // Intercept semua form yang menggunakan onsubmit="return confirm(...)" atau data-confirm
  document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.dataset.simmesConfirmed === 'true') {
      form.dataset.simmesConfirmed = '';
      return; // Izinkan submit berjalan
    }

    let msg = form.dataset.confirm;
    if (!msg) {
      const onsub = form.getAttribute('onsubmit');
      if (onsub && onsub.includes('confirm(')) {
        const match = onsub.match(/confirm\(\s*['"`](.*?)['"`]\s*\)/);
        if (match) msg = match[1];
      }
    }

    if (msg) {
      e.preventDefault();
      e.stopImmediatePropagation();
      window.showSimmesConfirm({
        message: msg,
        onConfirm: function() {
          form.dataset.simmesConfirmed = 'true';
          form.submit();
        }
      });
    }
  }, true);

  // Intercept tombol/link yang menggunakan onclick="return confirm(...)" atau data-confirm
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('button, a, input[type="submit"]');
    if (!btn) return;

    if (btn.dataset.simmesConfirmed === 'true') {
      btn.dataset.simmesConfirmed = '';
      return; // Izinkan aksi default berjalan
    }

    let msg = btn.dataset.confirm;
    if (!msg) {
      const onclickAttr = btn.getAttribute('onclick');
      if (onclickAttr && onclickAttr.includes('confirm(')) {
        const match = onclickAttr.match(/confirm\(\s*['"`](.*?)['"`]\s*\)/);
        if (match) msg = match[1];
      }
    }

    if (msg) {
      e.preventDefault();
      e.stopImmediatePropagation();
      window.showSimmesConfirm({
        message: msg,
        onConfirm: function() {
          btn.dataset.simmesConfirmed = 'true';
          if (btn.tagName === 'A' && btn.href && !btn.href.startsWith('javascript:')) {
            window.location.href = btn.href;
          } else if (btn.type === 'submit') {
            const form = btn.closest('form');
            if (form) {
              form.dataset.simmesConfirmed = 'true';
              if (btn.name) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = btn.name;
                hidden.value = btn.value;
                form.appendChild(hidden);
              }
              form.submit();
            }
          } else {
            btn.click();
          }
        }
      });
    }
  }, true);
})();
</script>
