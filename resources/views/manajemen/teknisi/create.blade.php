@extends('layouts.manager')
@section('title', 'Tambah Teknisi')
@section('content')
<div style="min-height:calc(100vh - 160px);display:flex;flex-direction:column;justify-content:center;align-items:center;padding:24px 12px">

  {{-- Card Timbul di Tengah (Elevated Compact Modal-Like Card) --}}
  <div class="card" style="width:100%;max-width:560px;padding:0;border-radius:20px;box-shadow:0 20px 50px -10px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);border:1px solid #e2e8f0;overflow:hidden;background:#fff;animation:modalScaleIn .2s cubic-bezier(0.16,1,0.3,1)">
    
    {{-- Header Modal/Card --}}
    <div style="padding:18px 24px 16px;background:#fff6f7;border-bottom:1px solid var(--rose200);display:flex;align-items:center;justify-content:space-between;gap:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <span class="chip-dot" style="width:9px;height:9px;background:var(--r700);flex-shrink:0"></span>
        <div>
          <h2 style="margin:0;font-size:17.5px;font-weight:800;color:var(--ink)">Tambah Teknisi Baru</h2>
          <span class="tiny muted" style="display:block;margin-top:2px">Daftarkan akun teknisi yang dapat langsung aktif bertugas.</span>
        </div>
      </div>
      <a href="{{ route('manajemen.teknisi.index') }}" title="Kembali ke Daftar Teknisi" style="width:34px;height:34px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--ink2);cursor:pointer;font-size:16px;display:grid;place-items:center;text-decoration:none;transition:all .15s">✕</a>
    </div>

    {{-- Form Body --}}
    <form method="POST" action="{{ route('manajemen.teknisi.store') }}" autocomplete="off" style="margin:0">
      @csrf

      <div style="padding:22px 24px;display:grid;gap:14px">
        {{-- Nama Lengkap --}}
        <div>
          <label class="f" style="margin:0 0 6px">Nama Lengkap <span style="color:#ef4444">*</span></label>
          <input class="input" name="name" value="{{ old('name') }}" required autofocus>
          @error('name')<div class="err">{{ $message }}</div>@enderror
        </div>

        {{-- Email & NIK (Grid 2 Kolom) --}}
        <div class="grid g2" style="gap:12px">
          <div>
            <label class="f" style="margin:0 0 6px">Email (Akun Login) <span style="color:#ef4444">*</span></label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div>
            <label class="f" style="margin:0 0 6px">Nomor NIK <span style="color:#ef4444">*</span></label>
            <input class="input" id="nikInput" name="nik" value="{{ old('nik') }}" required>
            <span class="tiny muted" style="display:block;margin-top:3px">Diisi manual sesuai standar NIK perusahaan.</span>
            @error('nik')<div class="err">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Password & WhatsApp (Grid 2 Kolom) --}}
        <div class="grid g2" style="gap:12px">
          <div>
            <label class="f" style="margin:0 0 6px">Kata Sandi (Password) <span style="color:#ef4444">*</span></label>
            <div style="position:relative">
              <input class="input" id="passInput" type="text" name="password" value="{{ old('password') }}" style="padding-right:75px" required>
              <button type="button" onclick="togglePassVis()" class="btn-action" style="position:absolute;right:6px;top:6px;padding:4px 8px;font-size:11px" id="btnToggleVis">
                Sembunyi
              </button>
            </div>
            <span class="tiny muted" style="display:block;margin-top:3px">Kata sandi akun teknisi (min. 4 karakter).</span>
            @error('password')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div>
            <label class="f" style="margin:0 0 6px">Nomor WhatsApp</label>
            <input class="input" type="tel" name="whatsapp" value="{{ old('whatsapp') }}">
            <span class="tiny muted" style="display:block;margin-top:3px">Untuk notifikasi siaran WA.</span>
            @error('whatsapp')<div class="err">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Departemen & Bagian / Spesialisasi (Grid 2 Kolom) --}}
        <div class="grid g2" style="gap:12px">
          <div>
            <label class="f" style="margin:0 0 6px">Departemen</label>
            <input class="input" name="department" value="{{ old('department', 'Maintenance') }}" placeholder="Maintenance" required>
          </div>
          <div>
            <label class="f" style="margin:0 0 6px">Bagian / Spesialisasi</label>
            <input class="input" name="bagian" value="{{ old('bagian') }}" placeholder="Pilih atau ketik" list="spesialisasiList">
            <datalist id="spesialisasiList">
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
        <a class="btn" href="{{ route('manajemen.teknisi.index') }}" style="padding:9px 18px;font-size:13px;border-color:var(--line);color:var(--ink2)">Batal</a>
        <button type="submit" class="btn primary btn-compact" style="padding:9px 22px;font-size:13.5px;font-weight:700">Simpan Teknisi</button>
      </div>
    </form>
  </div>
</div>

<script>
  function togglePassVis() {
    const input = document.getElementById('passInput');
    const btn = document.getElementById('btnToggleVis');
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = 'Sembunyi';
    } else {
      input.type = 'password';
      btn.textContent = 'Lihat';
    }
  }
</script>
@endsection
