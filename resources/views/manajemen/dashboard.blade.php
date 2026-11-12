@extends('layouts.manager')
@section('title', 'Dashboard')
@section('content')
<div class="top dashboard-top" style="align-items:center;margin-bottom:24px">
  <div>
    <div class="dashboard-live-row" style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
      <span class="live-status-pill">
        <span class="pulse-dot"></span>
        Sistem Monitoring Aktif
      </span>
      <span class="tiny muted">{{ now()->translatedFormat('l, d F Y') }}</span>
    </div>
    <h1 style="margin:0 0 4px">Dashboard</h1>
    <p style="margin:0">Halo <b>{{ auth()->user()->name }}</b>, pantau kesiapan mesin, antrian pemeliharaan rutin, dan tiket perbaikan terkini.</p>
  </div>
  <div class="actions dashboard-top-actions">
    <a class="btn primary" href="{{ route('manajemen.jadwal.create') }}">
      @include('partials.icon', ['n' => 'plus']) <span>Buat Jadwal</span>
    </a>
    <a class="btn" href="{{ route('manajemen.perbaikan.index') }}" style="background:#fff">
      @include('partials.icon', ['n' => 'wrench']) <span>Lapor Kerusakan</span>
    </a>
    <a class="btn" href="{{ route('manajemen.laporan.index') }}" style="background:#fff">
      @include('partials.icon', ['n' => 'file']) <span>Rekap Laporan</span>
    </a>
  </div>
</div>

{{-- KPI STAT CARDS INTERAKTIF --}}
<div class="grid g4" style="margin-bottom:24px">
  {{-- Card 1: Mesin Aktif --}}
  <a href="{{ route('manajemen.mesin.index') }}" class="stat-card" title="Klik untuk mengelola seluruh data mesin">
    <div class="stat-card-head">
      <span class="stat-card-title">Mesin Aktif Beroperasi</span>
      <div class="stat-card-ic" style="background:#ecfdf5;color:#047857">
        @include('partials.icon', ['n' => 'cog'])
      </div>
    </div>
    <div class="stat-card-val">{{ $stats['aktif'] }}</div>
    <div class="stat-card-status" style="color:#047857">
      <span class="chip-dot" style="background:#10b981"></span> Siap Berproduksi
    </div>
    <div class="stat-card-foot">
      <span>Kelola data mesin</span>
      @include('partials.icon', ['n' => 'arrow-right'])
    </div>
  </a>

  {{-- Card 2: Sedang Diperbaiki --}}
  <a href="{{ route('manajemen.perbaikan.index') }}" class="stat-card" title="Klik untuk melihat tiket perbaikan yang sedang dikerjakan">
    <div class="stat-card-head">
      <span class="stat-card-title">Sedang Diperbaiki</span>
      <div class="stat-card-ic" style="background:#eff6ff;color:#1d4ed8">
        @include('partials.icon', ['n' => 'wrench'])
      </div>
    </div>
    <div class="stat-card-val">{{ $stats['diperbaiki'] }}</div>
    <div class="stat-card-status" style="color:#1d4ed8">
      <span class="chip-dot" style="background:#3b82f6"></span> Penanganan Teknisi
    </div>
    <div class="stat-card-foot">
      <span>Pantau perbaikan teknisi</span>
      @include('partials.icon', ['n' => 'arrow-right'])
    </div>
  </a>

  {{-- Card 3: Menunggu Teknisi --}}
  <a href="{{ route('manajemen.perbaikan.index') }}" class="stat-card" title="Klik untuk menugaskan teknisi pada tiket masuk">
    <div class="stat-card-head">
      <span class="stat-card-title">Menunggu Respon Teknisi</span>
      <div class="stat-card-ic" style="background:#fffbeb;color:#b45309">
        @include('partials.icon', ['n' => 'clock'])
      </div>
    </div>
    <div class="stat-card-val">{{ $stats['menunggu'] }}</div>
    <div class="stat-card-status" style="color:#b45309">
      <span class="chip-dot" style="background:#f59e0b"></span> Perlu Penugasan
    </div>
    <div class="stat-card-foot">
      <span>Tugaskan teknisi sekarang</span>
      @include('partials.icon', ['n' => 'arrow-right'])
    </div>
  </a>

  {{-- Card 4: Melewati Batas Waktu --}}
  <a href="{{ route('manajemen.perbaikan.index') }}" class="stat-card" title="Klik untuk mengevaluasi tiket yang melewati batas waktu">
    <div class="stat-card-head">
      <span class="stat-card-title">Melewati Batas Waktu</span>
      <div class="stat-card-ic" style="background:#fff1f2;color:#be123c">
        @include('partials.icon', ['n' => 'alert'])
      </div>
    </div>
    <div class="stat-card-val" style="@if($stats['terlambat'] > 0) color:#be123c @endif">{{ $stats['terlambat'] }}</div>
    <div class="stat-card-status" style="@if($stats['terlambat'] > 0) color:#be123c @else color:#047857 @endif">
      @if ($stats['terlambat'] > 0)
        <span class="chip-dot" style="background:#e11d48"></span> Butuh Evaluasi Segera
      @else
        <span class="chip-dot" style="background:#10b981"></span> Semua Sesuai Jadwal
      @endif
    </div>
    <div class="stat-card-foot">
      <span>Tinjau kendala tiket</span>
      @include('partials.icon', ['n' => 'arrow-right'])
    </div>
  </a>
</div>

{{-- PUSAT AKTIVITAS OPERASIONAL (TABBED UNIFIED VIEW) --}}
<div class="card dashboard-card-section" style="margin-bottom:24px;padding:0;overflow:hidden">
  {{-- Card Header with Tab Switcher --}}
  <div class="dash-card-header">
    <div class="dash-card-header-row">
      <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <h2 style="margin:0;font-size:18px;font-weight:800;color:var(--ink)">Pusat Aktivitas Operasional</h2>
          <span id="activeRoomBadge" class="chip" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;font-size:11.5px;font-weight:800;transition:all .2s ease">
            <span class="chip-dot" style="background:#dc2626"></span> Ruang: Tiket Perbaikan
          </span>
        </div>
        <p class="tiny muted" style="margin:4px 0 0">
          Pilih tab di bawah untuk melihat tiket perbaikan, jadwal pemeliharaan rutin, atau riwayat pekerjaan yang telah selesai.
        </p>
      </div>

      <div id="tabActionContainer">
        {{-- Tombol aksi cepat kontekstual sesuai tab aktif --}}
        <div id="action-tab-tickets" class="dash-action-group">
          @include('partials.quick-report-modal', [
            'machines' => \App\Models\Machine::orderBy('name')->get(['code', 'name', 'location']),
            'triggerClass' => 'btn-action primary',
            'triggerLabel' => 'Buat Tiket Baru'
          ])
        </div>
        <div id="action-tab-upcoming" class="dash-action-group" style="display:none">
          <a class="btn-action primary" href="{{ route('manajemen.jadwal.create') }}" style="background:#d97706;border-color:#d97706">
            @include('partials.icon', ['n' => 'plus'])
            <span>Tambah Jadwal</span>
          </a>
        </div>
        <div id="action-tab-completed" class="dash-action-group" style="display:none">
          <a class="btn-action primary" href="{{ route('manajemen.jadwal.index') }}" style="background:#059669!important;border-color:#059669!important;color:#fff!important;font-weight:700">
            <span style="color:#fff!important">Semua Riwayat Maintenance →</span>
          </a>
        </div>
      </div>
    </div>

    {{-- Segmented Tab Buttons Track (Warna Mencolok & Kontras Tinggi) --}}
    <div class="dashboard-tabs-track">
      <button type="button" class="dash-tab-btn tab-red active" id="btn-tab-tickets" onclick="switchDashboardTab('tab-tickets')">
        @include('partials.icon', ['n' => 'wrench'])
        <span class="tab-label-full">Tiket Kerusakan & Perbaikan</span>
        <span class="tab-label-short">Tiket</span>
        <span class="dash-tab-badge">{{ $tickets->count() }}</span>
      </button>

      <button type="button" class="dash-tab-btn tab-amber" id="btn-tab-upcoming" onclick="switchDashboardTab('tab-upcoming')">
        @include('partials.icon', ['n' => 'calendar'])
        <span class="tab-label-full">Jadwal Maintenance Mendatang</span>
        <span class="tab-label-short">Jadwal</span>
        <span class="dash-tab-badge">{{ $upcoming->count() }}</span>
      </button>

      <button type="button" class="dash-tab-btn tab-green" id="btn-tab-completed" onclick="switchDashboardTab('tab-completed')">
        @include('partials.icon', ['n' => 'check'])
        <span class="tab-label-full">Riwayat Selesai</span>
        <span class="tab-label-short">Selesai</span>
        <span class="dash-tab-badge">{{ $completedSchedules->count() }}</span>
      </button>
    </div>
  </div>

  {{-- TAB 1: TIKET KERUSAKAN & PERBAIKAN --}}
  <div id="pane-tab-tickets" class="dash-tab-pane" style="border-top:3.5px solid #be123c">
    <div class="row between dash-room-banner" style="padding:10px 20px;background:#fff1f2;border-bottom:1px solid #fecdd3;align-items:center;flex-wrap:wrap;gap:6px">
      <span style="font-size:12.5px;font-weight:700;color:#9f1239;display:flex;align-items:center;gap:7px">
        <span class="chip-dot" style="background:#be123c"></span> Ruang Operasional: Tiket Kerusakan & Perbaikan Aktif
      </span>
      <span class="tiny" style="color:#9f1239;font-weight:700">{{ $tickets->count() }} Laporan Butuh Penanganan</span>
    </div>

    {{-- TAMPILAN DESKTOP: TABEL KOMPREHENSIF (LAYAR > 768px) --}}
    <div class="dashboard-table-wrap" style="border:0;border-radius:0">
      <table class="dashboard-table">
        <thead>
          <tr style="background:linear-gradient(90deg, #fff1f2 0%, #ffe4e6 100%)">
            <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Tiket</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Mesin & Lokasi</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Kendala / Keluhan</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Urgensi</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Status Pengerjaan</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important;text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($tickets as $t)
          @php
            $ticketTech = $t->technician;
            $ticketWaLink = ($ticketTech && $ticketTech->whatsapp) 
                ? \App\Services\WhatsAppService::makeDirectLink($ticketTech->whatsapp, \App\Services\WhatsAppService::buildTicketMessage($t, $ticketTech)) 
                : null;

            $tPhotos = [];
            foreach ($t->media as $m) {
                $tPhotos[] = [
                    'url'   => $m->url,
                    'phase' => $m->phase,
                    'kind'  => $m->kind,
                    'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : ($m->phase === 'before' ? 'Foto Sebelum' : 'Foto Kendala Awal')
                ];
            }
            $tLogs = [];
            foreach ($t->logs as $l) {
                $tLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
            }
            $tPayload = [
                'code'           => $t->code,
                'machine'        => ['name' => $t->machine?->name ?? 'Mesin', 'code' => $t->machine?->code ?? '-', 'location' => $t->machine?->location ?? '-'],
                'status'         => $t->status,
                'display_status' => $t->display_status,
                'urgency'        => $t->urgency,
                'date_formatted' => $t->finished_at ? ('Selesai: ' . $t->finished_at->translatedFormat('d M Y, H:i')) : ($t->started_at ? ('Mulai: ' . $t->started_at->translatedFormat('d M Y, H:i')) : ('Masuk: ' . $t->created_at->translatedFormat('d M Y, H:i'))),
                'complaint'      => $t->complaint,
                'action'         => $t->action,
                'spareparts'     => $t->spareparts,
                'technician'     => $t->technician ? ['name' => $t->technician->name, 'whatsapp' => $t->technician->whatsapp] : null,
                'wa_url'         => $ticketWaLink,
                'photos'         => $tPhotos,
                'logs'           => $tLogs,
                'show_url'       => route('manajemen.perbaikan.show', [$t, 'from' => 'dashboard']),
            ];
          @endphp
          <tr>
            <td style="padding:14px">
              <div style="font-weight:700;font-size:13.5px;color:var(--ink)">{{ $t->code }}</div>
              <div class="tiny muted" style="margin-top:2px">{{ $t->created_at->diffForHumans() }}</div>
            </td>
            <td style="padding:14px">
              <div style="font-weight:700;color:var(--ink)">{{ $t->machine?->label ?? $t->machine?->name ?? 'Mesin' }}</div>
              <div class="row" style="gap:6px;margin-top:4px">
                <span class="badge-code">{{ $t->machine?->code ?? '-' }}</span>
                <span class="tiny muted">{{ $t->machine?->location ?? '-' }}</span>
              </div>
            </td>
            <td style="padding:14px">
              <div style="font-weight:500;color:var(--ink);max-width:280px;line-height:1.4" title="{{ $t->complaint }}">
                {{ Str::limit($t->complaint, 55) }}
              </div>
              @if ($t->technician)
                <div class="tiny muted" style="margin-top:3px;display:flex;align-items:center;gap:5px">
                  <span>Teknisi: <b>{{ $t->technician->name }}</b></span>
                  @if ($ticketWaLink)
                    <a href="{{ $ticketWaLink }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="font-size:10px;padding:1px 5px" title="Kirim Notifikasi Tiket Perbaikan via WA">WA</a>
                  @endif
                </div>
              @endif
            </td>
            <td style="padding:14px">
              @include('partials.chip', ['v' => $t->urgency])
            </td>
            <td style="padding:14px">
              @include('partials.chip', ['v' => $t->display_status])
            </td>
            <td style="padding:14px;text-align:right">
              <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:6px">
                <button type="button" class="btn-icon detail" data-ticket="{{ json_encode($tPayload) }}" onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" title="Lihat Detail & Foto">
                  @include('partials.icon', ['n' => 'eye'])
                </button>
                <a href="{{ route('manajemen.perbaikan.edit', $t) }}" class="btn-icon edit" title="Edit Tiket">
                  @include('partials.icon', ['n' => 'edit'])
                </a>
                <form method="POST" action="{{ route('manajemen.perbaikan.destroy', $t) }}" data-confirm="Apakah Anda yakin ingin menghapus tiket {{ $t->code }}?" style="display:inline;margin:0">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-icon danger" title="Hapus Tiket">
                    @include('partials.icon', ['n' => 'trash'])
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty" style="padding:36px 20px">
              <div style="font-weight:600;color:var(--ink2)">Belum ada tiket perbaikan</div>
              <div class="small muted" style="margin-top:4px">Tidak ada laporan kerusakan aktif yang tercatat saat ini.</div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- TAMPILAN MOBILE: KARTU SENTUH BERSIH & RAPI (LAYAR <= 768px) --}}
    <div class="dashboard-mobile-list">
      @forelse ($tickets as $t)
        @php
          $mTech = $t->technician;
          $mWaLink = ($mTech && $mTech->whatsapp) 
              ? \App\Services\WhatsAppService::makeDirectLink($mTech->whatsapp, \App\Services\WhatsAppService::buildTicketMessage($t, $mTech)) 
              : null;
          $mPhotos = [];
          foreach ($t->media as $m) {
              $mPhotos[] = [
                  'url'   => $m->url,
                  'phase' => $m->phase,
                  'kind'  => $m->kind,
                  'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : ($m->phase === 'before' ? 'Foto Sebelum' : 'Foto Kendala Awal')
              ];
          }
          $mLogs = [];
          foreach ($t->logs as $l) {
              $mLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
          }
          $mPayload = [
              'code'           => $t->code,
              'machine'        => ['name' => $t->machine?->name ?? 'Mesin', 'code' => $t->machine?->code ?? '-', 'location' => $t->machine?->location ?? '-'],
              'status'         => $t->status,
              'display_status' => $t->display_status,
              'urgency'        => $t->urgency,
              'date_formatted' => $t->finished_at ? ('Selesai: ' . $t->finished_at->translatedFormat('d M Y, H:i')) : ($t->started_at ? ('Mulai: ' . $t->started_at->translatedFormat('d M Y, H:i')) : ('Masuk: ' . $t->created_at->translatedFormat('d M Y, H:i'))),
              'complaint'      => $t->complaint,
              'action'         => $t->action,
              'spareparts'     => $t->spareparts,
              'technician'     => $mTech ? ['name' => $mTech->name, 'whatsapp' => $mTech->whatsapp] : null,
              'wa_url'         => $mWaLink,
              'photos'         => $mPhotos,
              'logs'           => $mLogs,
              'show_url'       => route('manajemen.perbaikan.show', [$t, 'from' => 'dashboard']),
          ];
        @endphp
        <div class="dash-m-card border-red">
          <div class="dash-m-head">
            <div class="dash-m-code-group">
              <span class="badge-code">{{ $t->code }}</span>
              <span class="tiny muted">{{ $t->created_at->diffForHumans() }}</span>
            </div>
            <div class="dash-m-chips">
              @include('partials.chip', ['v' => $t->urgency])
              @include('partials.chip', ['v' => $t->display_status])
            </div>
          </div>

          <div class="dash-m-machine">
            <div class="dash-m-machine-title">{{ $t->machine?->name ?? 'Mesin' }}</div>
            <div class="dash-m-machine-sub">
              <span class="badge-code" style="font-size:10px">{{ $t->machine?->code ?? '-' }}</span> · {{ $t->machine?->location ?? '-' }}
            </div>
          </div>

          <div class="dash-m-desc-box">
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700">Kendala:</span>
            {{ $t->complaint }}
          </div>

          <div class="dash-m-meta-row">
            <div class="dash-m-tech">
              <span class="muted">Teknisi:</span>
              @if ($mTech)
                <b>{{ $mTech->name }}</b>
                @if ($mWaLink)
                  <a href="{{ $mWaLink }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="font-size:10px;padding:2px 6px">
                    @include('partials.icon', ['n' => 'whatsapp']) WA
                  </a>
                @endif
              @else
                <span class="chip c-ditugaskan" style="font-size:10.5px;padding:2px 7px">Belum Ada Teknisi</span>
              @endif
            </div>
          </div>

          <div class="dash-m-actions">
            <span class="tiny muted" style="font-weight:600">Aksi:</span>
            <div class="dash-m-icon-group">
              <button type="button" class="btn-icon detail" data-ticket="{{ json_encode($mPayload) }}" onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" title="Lihat Detail & Foto">
                @include('partials.icon', ['n' => 'eye'])
              </button>
              <a href="{{ route('manajemen.perbaikan.edit', $t) }}" class="btn-icon edit" title="Edit Tiket">
                @include('partials.icon', ['n' => 'edit'])
              </a>
              <form method="POST" action="{{ route('manajemen.perbaikan.destroy', $t) }}" data-confirm="Apakah Anda yakin ingin menghapus tiket {{ $t->code }}?" style="display:inline;margin:0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-icon danger" title="Hapus Tiket">
                  @include('partials.icon', ['n' => 'trash'])
                </button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="dash-m-empty">
          <div style="font-size:24px;margin-bottom:4px">🔧</div>
          <b style="font-size:14px;color:var(--ink)">Belum ada tiket perbaikan</b>
          <div class="tiny muted" style="margin-top:2px">Tidak ada laporan kerusakan aktif yang tercatat saat ini.</div>
        </div>
      @endforelse
    </div>

    <div class="dash-card-footer" style="padding:12px 20px;background:#fcfcfb;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
      <span class="tiny muted">Tiket kerusakan yang baru dilaporkan atau sedang ditangani teknisi.</span>
      <a href="{{ route('manajemen.perbaikan.index') }}" class="link small" style="font-weight:700">Lihat Semua Tiket di Menu Perbaikan →</a>
    </div>
  </div>

  {{-- TAB 2: ANTRIAN JADWAL MAINTENANCE --}}
  <div id="pane-tab-upcoming" class="dash-tab-pane" style="display:none;border-top:3.5px solid #d97706">
    <div class="row between dash-room-banner" style="padding:10px 20px;background:#fffbeb;border-bottom:1px solid #fde68a;align-items:center">
      <span style="font-size:12.5px;font-weight:700;color:#92400e;display:flex;align-items:center;gap:7px">
        <span class="chip-dot" style="background:#d97706"></span> Ruang Operasional: Antrian Jadwal Maintenance Mendatang
      </span>
      <span class="tiny" style="color:#92400e;font-weight:700">{{ $upcoming->count() }} Jadwal Terencana</span>
    </div>

    <div class="dashboard-table-wrap" style="border:0;border-radius:0">
      <table class="dashboard-table">
        <thead>
          <tr style="background:linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%)">
            <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Mesin & Lokasi</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Tugas & Jenis</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Waktu Pelaksanaan</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Teknisi Bertugas</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Status</th>
            <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important;text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($upcoming as $s)
          @php
            $tech = $s->technician;
            $kindReminder = $s->display_status === 'terlambat' ? 'Terlambat' : ($s->due_date->isToday() ? 'Hari-H' : 'Pengingat');
            $schedWaLink = ($tech && $tech->whatsapp) ? \App\Services\WhatsAppService::makeScheduleShareLink($s, $kindReminder) : null;

            $latestTicket = $s->ticket ?? $s->machine->tickets->first();
            $sPhotos = [];
            if ($latestTicket) {
                foreach ($latestTicket->media as $m) {
                    $sPhotos[] = [
                        'url'   => $m->url,
                        'phase' => $m->phase,
                        'kind'  => $m->kind,
                        'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : 'Foto Sebelum / Awal'
                    ];
                }
            }
            $sLogs = [];
            if ($latestTicket) {
                foreach ($latestTicket->logs as $l) {
                    $sLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
                }
            }
            $sPayload = [
                'type'           => $s->type,
                'task'           => $s->task,
                'status'         => $s->status,
                'display_status' => $s->display_status,
                'formatted_date' => $s->due_date->translatedFormat('d M Y') . ($s->due_time ? ' Jam '.$s->due_time.' WIB' : ''),
                'machine'        => ['name' => $s->machine->name, 'code' => $s->machine->code, 'location' => $s->machine->location],
                'technician'     => $s->technician ? ['name' => $s->technician->name, 'whatsapp' => $s->technician->whatsapp] : null,
                'wa_url'         => $schedWaLink,
                'ticket'         => $latestTicket ? ['action' => $latestTicket->action, 'spareparts' => $latestTicket->spareparts] : null,
                'photos'         => $sPhotos,
                'logs'           => $sLogs,
                'show_url'       => route('manajemen.mesin.show', [$s->machine, 'from' => 'dashboard']),
            ];
          @endphp
          <tr>
            <td style="padding:14px">
              <div style="font-weight:700;font-size:14px;color:var(--ink)">{{ $s->machine->label }}</div>
              <div class="row" style="gap:6px;margin-top:4px">
                <span class="badge-code">{{ $s->machine->code }}</span>
                <span class="tiny muted">{{ $s->machine->location }}</span>
              </div>
            </td>
            <td style="padding:14px">
              <div style="font-weight:600;color:var(--ink)">{{ $s->task }}</div>
              <div class="tiny muted" style="margin-top:3px">
                <span style="text-transform:capitalize">{{ $s->type }}</span>{{ $s->interval_label ? ' · Siklus '.$s->interval_label : '' }}
              </div>
            </td>
            <td style="padding:14px">
              <div style="font-weight:700;color:var(--ink)">{{ $s->due_date->translatedFormat('d M Y') }}</div>
              <div style="margin-top:3px">
                @if ($s->due_time)
                  <span class="badge-time">
                    @include('partials.icon', ['n' => 'clock']) Jam {{ $s->due_time }} WIB
                  </span>
                @else
                  <span class="tiny muted">Fleksibel</span>
                @endif
              </div>
            </td>
            <td style="padding:14px">
              @if ($tech)
                <div style="display:flex;align-items:center;gap:8px">
                  <div class="avatar" style="width:30px;height:30px;font-size:11px;background:#f3f4f6;color:var(--ink);border:1px solid #e5e7eb">
                    {{ $tech->initials }}
                  </div>
                  <div>
                    <div style="font-weight:700;font-size:13px">{{ $tech->name }}</div>
                    @if ($schedWaLink)
                      <a href="{{ $schedWaLink }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="margin-top:2px" title="Kirim Notifikasi Jadwal Maintenance ke WhatsApp Teknisi">
                        @include('partials.icon', ['n' => 'whatsapp']) {{ $tech->whatsapp }}
                      </a>
                    @elseif ($tech->whatsapp)
                      <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tech->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="margin-top:2px" title="Chat WhatsApp teknisi">
                        @include('partials.icon', ['n' => 'whatsapp']) {{ $tech->whatsapp }}
                      </a>
                    @else
                      <span class="badge-wa-empty">No WA belum diisi</span>
                    @endif
                  </div>
                </div>
              @else
                <span class="chip c-ditugaskan" style="font-size:11px;padding:2px 8px">
                  <span class="chip-dot"></span> Belum Ditugaskan
                </span>
              @endif
            </td>
            <td style="padding:14px">
              @include('partials.chip', ['v' => $s->display_status])
            </td>
            <td style="padding:14px;text-align:right">
              <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:6px">
                <button type="button" class="btn-icon detail" data-schedule="{{ json_encode($sPayload) }}" onclick="openScheduleDetail(JSON.parse(this.dataset.schedule))" title="Lihat Detail & Foto">
                  @include('partials.icon', ['n' => 'eye'])
                </button>
                <a href="{{ route('manajemen.jadwal.edit', $s) }}" class="btn-icon edit" title="Edit Jadwal">
                  @include('partials.icon', ['n' => 'edit'])
                </a>
                <form method="POST" action="{{ route('manajemen.jadwal.destroy', $s) }}" data-confirm="Apakah Anda yakin ingin menghapus jadwal ini?" style="display:inline;margin:0">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-icon danger" title="Hapus Jadwal">
                    @include('partials.icon', ['n' => 'trash'])
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty" style="padding:36px 20px">
              <div style="font-weight:600;color:var(--ink2)">Tidak ada maintenance aktif untuk dikerjakan</div>
              <div class="small muted" style="margin-top:4px">Jadwal perawatan mesin akan muncul otomatis di sini.</div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile View List for Tab 2 --}}
    <div class="dashboard-mobile-list">
      @forelse ($upcoming as $s)
        @php
          $mTech = $s->technician;
          $mKindReminder = $s->display_status === 'terlambat' ? 'Terlambat' : ($s->due_date->isToday() ? 'Hari-H' : 'Pengingat');
          $mSchedWaLink = ($mTech && $mTech->whatsapp) ? \App\Services\WhatsAppService::makeScheduleShareLink($s, $mKindReminder) : null;

          $mLatestTicket = $s->ticket ?? $s->machine?->tickets?->first();
          $mPhotos = [];
          if ($mLatestTicket) {
              foreach ($mLatestTicket->media as $m) {
                  $mPhotos[] = [
                      'url'   => $m->url,
                      'phase' => $m->phase,
                      'kind'  => $m->kind,
                      'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : 'Foto Sebelum / Awal'
                  ];
              }
          }
          $mLogs = [];
          if ($mLatestTicket) {
              foreach ($mLatestTicket->logs as $l) {
                  $mLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
              }
          }
          $mPayload = [
              'type'           => $s->type,
              'task'           => $s->task,
              'status'         => $s->status,
              'display_status' => $s->display_status,
              'formatted_date' => $s->due_date->translatedFormat('d M Y') . ($s->due_time ? ' Jam '.$s->due_time.' WIB' : ''),
              'machine'        => ['name' => $s->machine?->name ?? 'Mesin', 'code' => $s->machine?->code ?? '-', 'location' => $s->machine?->location ?? '-'],
              'technician'     => $mTech ? ['name' => $mTech->name, 'whatsapp' => $mTech->whatsapp] : null,
              'wa_url'         => $mSchedWaLink,
              'ticket'         => $mLatestTicket ? ['action' => $mLatestTicket->action, 'spareparts' => $mLatestTicket->spareparts] : null,
              'photos'         => $mPhotos,
              'logs'           => $mLogs,
              'show_url'       => $s->machine ? route('manajemen.mesin.show', [$s->machine, 'from' => 'dashboard']) : '#',
          ];
        @endphp
        <div class="dash-m-card border-amber">
          <div class="dash-m-head">
            <div class="dash-m-code-group">
              <span class="badge-code" style="background:#fef3c7;color:#92400e;border-color:#fde68a;text-transform:capitalize">{{ $s->type }}</span>
              <span class="tiny muted">{{ $s->interval_label ? 'Siklus ' . $s->interval_label : 'Perawatan Rutin' }}</span>
            </div>
            <div class="dash-m-chips">
              @include('partials.chip', ['v' => $s->display_status])
            </div>
          </div>

          <div class="dash-m-machine">
            <div class="dash-m-machine-title">{{ $s->machine?->name ?? 'Mesin' }}</div>
            <div class="dash-m-machine-sub">
              <span class="badge-code" style="font-size:10px">{{ $s->machine?->code ?? '-' }}</span> · {{ $s->machine?->location ?? '-' }}
            </div>
          </div>

          <div class="dash-m-desc-box" style="background:#fffbeb;border-color:#fde68a">
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700;color:#92400e">Rencana Tugas:</span>
            <b style="color:var(--ink)">{{ $s->task }}</b>
          </div>

          <div class="dash-m-meta-row" style="flex-direction:column;align-items:flex-start;gap:6px">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;flex-wrap:wrap">
              <span class="muted">Jatuh Tempo:</span>
              <b style="color:var(--ink)">{{ $s->due_date->translatedFormat('d M Y') }}</b>
              @if ($s->due_time)
                <span class="badge-time" style="font-size:10px;padding:1px 6px">Jam {{ $s->due_time }} WIB</span>
              @endif
            </div>
            <div class="dash-m-tech">
              <span class="muted">Teknisi:</span>
              @if ($mTech)
                <b>{{ $mTech->name }}</b>
                @if ($mSchedWaLink)
                  <a href="{{ $mSchedWaLink }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="font-size:10px;padding:2px 6px">
                    @include('partials.icon', ['n' => 'whatsapp']) WA
                  </a>
                @endif
              @else
                <span class="chip c-ditugaskan" style="font-size:10.5px;padding:2px 7px">Belum Ditugaskan</span>
              @endif
            </div>
          </div>

          <div class="dash-m-actions">
            <span class="tiny muted" style="font-weight:600">Aksi:</span>
            <div class="dash-m-icon-group">
              <button type="button" class="btn-icon detail" data-schedule="{{ json_encode($mPayload) }}" onclick="openScheduleDetail(JSON.parse(this.dataset.schedule))" title="Lihat Detail & Foto">
                @include('partials.icon', ['n' => 'eye'])
              </button>
              <a href="{{ route('manajemen.jadwal.edit', $s) }}" class="btn-icon edit" title="Edit Jadwal">
                @include('partials.icon', ['n' => 'edit'])
              </a>
              <form method="POST" action="{{ route('manajemen.jadwal.destroy', $s) }}" data-confirm="Apakah Anda yakin ingin menghapus jadwal ini?" style="display:inline;margin:0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-icon danger" title="Hapus Jadwal">
                  @include('partials.icon', ['n' => 'trash'])
                </button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="dash-m-empty">
          <div style="font-size:24px;margin-bottom:4px">📅</div>
          <b style="font-size:14px;color:var(--ink)">Tidak ada maintenance aktif</b>
          <div class="tiny muted" style="margin-top:2px">Jadwal perawatan mesin akan muncul otomatis di sini.</div>
        </div>
      @endforelse
    </div>

    <div class="dash-card-footer" style="padding:12px 20px;background:#fcfcfb;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
      <span class="tiny muted">Jadwal perawatan berkala untuk menjaga keandalan mesin operasional.</span>
      <a href="{{ route('manajemen.jadwal.index') }}" class="link small" style="font-weight:700">Buka Kalender & Semua Jadwal Maintenance →</a>
    </div>
  </div>

  {{-- TAB 3: RIWAYAT MAINTENANCE SELESAI --}}
  <div id="pane-tab-completed" class="dash-tab-pane" style="display:none;border-top:3.5px solid #059669">
    <div class="row between dash-room-banner" style="padding:10px 20px;background:#ecfdf5;border-bottom:1px solid #a7f3d0;align-items:center">
      <span style="font-size:12.5px;font-weight:700;color:#065f46;display:flex;align-items:center;gap:7px">
        <span class="chip-dot" style="background:#059669"></span> Ruang Operasional: Riwayat Pekerjaan Selesai
      </span>
      <span class="tiny" style="color:#065f46;font-weight:700">{{ $completedSchedules->count() }} Pekerjaan Tuntas</span>
    </div>

    <div class="dashboard-table-wrap" style="border:0;border-radius:0">
      <table class="dashboard-table">
        <thead>
          <tr style="background:linear-gradient(90deg, #ecfdf5 0%, #d1fae5 100%)">
            <th style="padding:12px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Mesin & Lokasi</th>
            <th style="padding:12px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Tugas & Siklus</th>
            <th style="padding:12px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Selesai Dikerjakan</th>
            <th style="padding:12px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Teknisi</th>
            <th style="padding:12px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Status</th>
            <th style="padding:12px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important;text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($completedSchedules as $schedule)
          @php
            $cTech = $schedule->technician;
            $cTicket = $schedule->ticket ?? $schedule->machine->tickets->first();
            $cPhotos = [];
            if ($cTicket) {
                foreach ($cTicket->media as $m) {
                    $cPhotos[] = [
                        'url'   => $m->url,
                        'phase' => $m->phase,
                        'kind'  => $m->kind,
                        'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : 'Foto Sebelum / Awal'
                    ];
                }
            }
            $cLogs = [];
            if ($cTicket) {
                foreach ($cTicket->logs as $l) {
                    $cLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
                }
            }
            $cPayload = [
                'type'           => $schedule->type,
                'task'           => $schedule->task,
                'status'         => $schedule->status,
                'display_status' => $schedule->display_status,
                'formatted_date' => ($schedule->completed_at ? $schedule->completed_at->translatedFormat('d M Y') : $schedule->due_date->translatedFormat('d M Y')) . ($schedule->due_time ? ' Jam '.$schedule->due_time.' WIB' : ''),
                'machine'        => ['name' => $schedule->machine->name, 'code' => $schedule->machine->code, 'location' => $schedule->machine->location],
                'technician'     => $cTech ? ['name' => $cTech->name, 'whatsapp' => $cTech->whatsapp] : null,
                'wa_url'         => null,
                'ticket'         => $cTicket ? ['action' => $cTicket->action, 'spareparts' => $cTicket->spareparts] : null,
                'photos'         => $cPhotos,
                'logs'           => $cLogs,
                'show_url'       => route('manajemen.mesin.show', [$schedule->machine, 'from' => 'dashboard']),
            ];
          @endphp
          <tr>
            <td style="padding:14px">
              <div style="font-weight:700;font-size:14px;color:var(--ink)">{{ $schedule->machine->label }}</div>
              <div class="row" style="gap:6px;margin-top:4px">
                <span class="badge-code">{{ $schedule->machine->code }}</span>
                <span class="tiny muted">{{ $schedule->machine->location }}</span>
              </div>
            </td>
            <td style="padding:14px">
              <div style="font-weight:600;color:var(--ink)">{{ $schedule->task }}</div>
              <div class="tiny muted" style="margin-top:3px">{{ ucfirst($schedule->type) }}{{ $schedule->interval_label ? ' · Siklus '.$schedule->interval_label : '' }}</div>
            </td>
            <td style="padding:14px">
              <div style="font-weight:700;color:var(--ink)">{{ $schedule->completed_at?->translatedFormat('d M Y') ?? '-' }}</div>
              <div class="tiny muted" style="margin-top:3px">Jadwal awal: {{ $schedule->due_date->translatedFormat('d M Y') }}</div>
            </td>
            <td style="padding:14px">{{ $schedule->technician?->name ?? 'Belum ditugaskan' }}</td>
            <td style="padding:14px">@include('partials.chip', ['v' => 'selesai'])</td>
            <td style="padding:14px;text-align:right">
              <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:6px">
                <button type="button" class="btn-icon detail" data-schedule="{{ json_encode($cPayload) }}" onclick="openScheduleDetail(JSON.parse(this.dataset.schedule))" title="Lihat Detail & Foto">
                  @include('partials.icon', ['n' => 'eye'])
                </button>
                <a href="{{ route('manajemen.jadwal.edit', $schedule) }}" class="btn-icon edit" title="Edit Riwayat Jadwal">
                  @include('partials.icon', ['n' => 'edit'])
                </a>
                <form method="POST" action="{{ route('manajemen.jadwal.destroy', $schedule) }}" data-confirm="Apakah Anda yakin ingin menghapus riwayat jadwal ini?" style="display:inline;margin:0">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-icon danger" title="Hapus Riwayat Jadwal">
                    @include('partials.icon', ['n' => 'trash'])
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty" style="padding:36px 20px">
              <div style="font-weight:600;color:var(--ink2)">Belum ada maintenance yang selesai</div>
              <div class="small muted" style="margin-top:4px">Riwayat akan tampil otomatis setelah pekerjaan ditandai selesai.</div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile View List for Tab 3 --}}
    <div class="dashboard-mobile-list">
      @forelse ($completedSchedules as $schedule)
        @php
          $cTech = $schedule->technician;
          $cTicket = $schedule->ticket ?? $schedule->machine?->tickets?->first();
          $cPhotos = [];
          if ($cTicket) {
              foreach ($cTicket->media as $m) {
                  $cPhotos[] = [
                      'url'   => $m->url,
                      'phase' => $m->phase,
                      'kind'  => $m->kind,
                      'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : 'Foto Sebelum / Awal'
                  ];
              }
          }
          $cLogs = [];
          if ($cTicket) {
              foreach ($cTicket->logs as $l) {
                  $cLogs[] = ['label' => $l->label, 'note' => $l->note, 'time' => $l->created_at->translatedFormat('d M Y, H:i')];
              }
          }
          $cPayload = [
              'type'           => $schedule->type,
              'task'           => $schedule->task,
              'status'         => $schedule->status,
              'display_status' => $schedule->display_status,
              'formatted_date' => ($schedule->completed_at ? $schedule->completed_at->translatedFormat('d M Y') : $schedule->due_date->translatedFormat('d M Y')) . ($schedule->due_time ? ' Jam '.$schedule->due_time.' WIB' : ''),
              'machine'        => ['name' => $schedule->machine?->name ?? 'Mesin', 'code' => $schedule->machine?->code ?? '-', 'location' => $schedule->machine?->location ?? '-'],
              'technician'     => $cTech ? ['name' => $cTech->name, 'whatsapp' => $cTech->whatsapp] : null,
              'wa_url'         => null,
              'ticket'         => $cTicket ? ['action' => $cTicket->action, 'spareparts' => $cTicket->spareparts] : null,
              'photos'         => $cPhotos,
              'logs'           => $cLogs,
              'show_url'       => $schedule->machine ? route('manajemen.mesin.show', [$schedule->machine, 'from' => 'dashboard']) : '#',
          ];
        @endphp
        <div class="dash-m-card border-green">
          <div class="dash-m-head">
            <div class="dash-m-code-group">
              <span class="badge-code" style="background:#ecfdf5;color:#065f46;border-color:#a7f3d0;text-transform:capitalize">{{ $schedule->type }}</span>
              <span class="tiny muted">{{ $schedule->interval_label ? 'Siklus ' . $schedule->interval_label : 'Perawatan Berkala' }}</span>
            </div>
            <div class="dash-m-chips">
              @include('partials.chip', ['v' => 'selesai'])
            </div>
          </div>

          <div class="dash-m-machine">
            <div class="dash-m-machine-title">{{ $schedule->machine?->name ?? 'Mesin' }}</div>
            <div class="dash-m-machine-sub">
              <span class="badge-code" style="font-size:10px">{{ $schedule->machine?->code ?? '-' }}</span> · {{ $schedule->machine?->location ?? '-' }}
            </div>
          </div>

          <div class="dash-m-desc-box" style="background:#ecfdf5;border-color:#a7f3d0">
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700;color:#065f46">Tugas Diselesaikan:</span>
            <b style="color:var(--ink)">{{ $schedule->task }}</b>
          </div>

          <div class="dash-m-meta-row" style="flex-direction:column;align-items:flex-start;gap:6px">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;flex-wrap:wrap">
              <span class="muted">Tuntas:</span>
              <b style="color:#059669">{{ $schedule->completed_at?->translatedFormat('d M Y') ?? '-' }}</b>
              <span class="tiny muted">(Jadwal: {{ $schedule->due_date->translatedFormat('d M Y') }})</span>
            </div>
            <div class="dash-m-tech">
              <span class="muted">Teknisi:</span>
              <b>{{ $cTech?->name ?? 'Belum Ditugaskan' }}</b>
            </div>
          </div>

          <div class="dash-m-actions">
            <span class="tiny muted" style="font-weight:600">Aksi:</span>
            <div class="dash-m-icon-group">
              <button type="button" class="btn-icon detail" data-schedule="{{ json_encode($cPayload) }}" onclick="openScheduleDetail(JSON.parse(this.dataset.schedule))" title="Lihat Detail & Foto">
                @include('partials.icon', ['n' => 'eye'])
              </button>
              <a href="{{ route('manajemen.jadwal.edit', $schedule) }}" class="btn-icon edit" title="Edit Riwayat Jadwal">
                @include('partials.icon', ['n' => 'edit'])
              </a>
              <form method="POST" action="{{ route('manajemen.jadwal.destroy', $schedule) }}" data-confirm="Apakah Anda yakin ingin menghapus riwayat jadwal ini?" style="display:inline;margin:0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-icon danger" title="Hapus Riwayat Jadwal">
                  @include('partials.icon', ['n' => 'trash'])
                </button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="dash-m-empty">
          <div style="font-size:24px;margin-bottom:4px">✅</div>
          <b style="font-size:14px;color:var(--ink)">Belum ada maintenance yang selesai</b>
          <div class="tiny muted" style="margin-top:2px">Riwayat akan tampil otomatis setelah pekerjaan ditandai selesai.</div>
        </div>
      @endforelse
    </div>

    <div class="dash-card-footer" style="padding:12px 20px;background:#fcfcfb;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
      <span class="tiny muted">Pekerjaan perawatan berkala yang telah tuntas diselesaikan teknisi.</span>
      <a href="{{ route('manajemen.jadwal.index') }}" class="link small" style="font-weight:700">Lihat Riwayat Lengkap di Menu Jadwal →</a>
    </div>
  </div>
</div>

<script>
function switchDashboardTab(tabId) {
  document.querySelectorAll('.dash-tab-pane').forEach(function(pane) {
    pane.style.display = 'none';
  });
  document.querySelectorAll('.dash-tab-btn').forEach(function(btn) {
    btn.classList.remove('active');
  });
  document.querySelectorAll('.dash-action-group').forEach(function(act) {
    act.style.display = 'none';
  });

  const activePane = document.getElementById('pane-' + tabId);
  const activeBtn = document.getElementById('btn-' + tabId);
  const activeAction = document.getElementById('action-' + tabId);
  const roomBadge = document.getElementById('activeRoomBadge');

  if (activePane) activePane.style.display = 'block';
  if (activeBtn) activeBtn.classList.add('active');
  if (activeAction) activeAction.style.display = 'block';

  if (roomBadge) {
    if (tabId === 'tab-tickets') {
      roomBadge.style.background = '#fee2e2';
      roomBadge.style.color = '#991b1b';
      roomBadge.style.borderColor = '#fca5a5';
      roomBadge.innerHTML = '<span class="chip-dot" style="background:#dc2626"></span> Ruang: Tiket Perbaikan';
    } else if (tabId === 'tab-upcoming') {
      roomBadge.style.background = '#fef3c7';
      roomBadge.style.color = '#92400e';
      roomBadge.style.borderColor = '#fcd34d';
      roomBadge.innerHTML = '<span class="chip-dot" style="background:#d97706"></span> Ruang: Jadwal Maintenance';
    } else if (tabId === 'tab-completed') {
      roomBadge.style.background = '#d1fae5';
      roomBadge.style.color = '#065f46';
      roomBadge.style.borderColor = '#6ee7b7';
      roomBadge.innerHTML = '<span class="chip-dot" style="background:#059669"></span> Ruang: Riwayat Selesai';
    }
  }
}
</script>

{{-- SECTION 3: BOTTOM WIDGETS (GRAFIK & PENGINGAT WA) --}}
<div class="grid g2" style="margin-top:24px">
  {{-- Grafik Tren --}}
  <div class="card dashboard-trend-widget" style="padding:22px">
    <div class="row between trend-widget-header" style="margin-bottom:14px;align-items:flex-start">
      <div>
        <h3 style="margin:0;font-size:16px">Tren Tiket Perbaikan (6 Bulan Terakhir)</h3>
        <div class="small muted" style="margin-top:4px">Volume laporan kerusakan bulanan dari seluruh area pabrik.</div>
      </div>
    </div>
    @php
      $totalTrend = $trend->sum('count');
      $maxTrendCount = max(1, (int) $trend->max('count'));
      $averageTrend = $trend->count() ? $totalTrend / $trend->count() : 0;
      $peakMonth = $trend->sortByDesc('count')->first();
      $segments = $trend->values();
    @endphp
    <div class="trend-summary-grid">
      <div class="trend-summary-card trend-summary-total"><span>Total tiket</span><b>{{ number_format($totalTrend, 0, ',', '.') }}</b></div>
      <div class="trend-summary-card"><span>Rata-rata per bulan</span><b>{{ number_format($averageTrend, 1, ',', '.') }}</b></div>
      <div class="trend-summary-card"><span>Bulan tertinggi</span><b>{{ $totalTrend ? $peakMonth['label'].' · '.$peakMonth['count'] : 'Belum ada' }}</b></div>
    </div>
    <div class="trend-horizontal-chart" role="img" aria-label="Jumlah tiket per bulan; skala batang sampai {{ $maxTrendCount }} tiket">
      <div class="trend-scale"><span>Jumlah tiket per bulan</span><span>Skala 0–{{ $maxTrendCount }}</span></div>
      <div class="trend-month-list">
        @foreach ($segments as $segment)
          <div class="trend-month-row" aria-label="{{ $segment['label'] }}: {{ $segment['count'] }} tiket">
            <span class="trend-month-label">{{ $segment['label'] }}</span>
            <div class="trend-month-track">
              <span style="width:{{ round($segment['count'] / $maxTrendCount * 100, 1) }}%"></span>
            </div>
            <b class="trend-month-count">{{ number_format($segment['count'], 0, ',', '.') }}</b>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Status Otomasi WhatsApp --}}
  <div class="card dashboard-wa-widget" style="padding:22px;display:flex;flex-direction:column;justify-content:space-between">
    <div>
      <div class="row between dashboard-wa-header" style="margin-bottom:14px;align-items:flex-start">
        <div>
          <h3 style="margin:0;font-size:16px">Integrasi Notifikasi WhatsApp Gateway</h3>
          <div class="small muted" style="margin-top:4px">Sistem notifikasi perbaikan & pengingat berkala ke nomor teknisi.</div>
        </div>
        <a class="link small" href="{{ route('manajemen.jadwal.index') }}" style="font-weight:700">Kelola Jadwal →</a>
      </div>

      <div class="dashboard-wa-status-box" style="background:#f0fdf4;padding:12px 14px;border-radius:10px;border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div style="display:flex;align-items:center;gap:8px">
          <span class="pulse-dot"></span>
          <span style="font-weight:700;color:#166534;font-size:13px">Gateway Notifikasi Aktif & Terhubung</span>
        </div>
        <span class="badge-wa">Online 24/7</span>
      </div>

      <div class="dashboard-wa-list" style="display:grid;gap:10px;font-size:12.5px;color:var(--ink2)">
        <div class="dashboard-wa-item" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#fafaf9;border-radius:8px;border:1px solid var(--line)">
          <div style="display:flex;align-items:center;gap:8px">
            <span style="color:#ef4444">🚨</span>
            <span>Notifikasi Tiket Perbaikan Kerusakan & Siaran Cepat</span>
          </div>
          <span class="chip" style="font-size:10.5px;padding:2px 8px;background:#fee2e2;color:#991b1b;border-color:#fca5a5">Instan</span>
        </div>
        <div class="dashboard-wa-item" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#fafaf9;border-radius:8px;border:1px solid var(--line)">
          <div style="display:flex;align-items:center;gap:8px">
            <span style="color:#10b981">🗓️</span>
            <span>Pengingat Sebelum Jatuh Tempo (H-7 dan H-1)</span>
          </div>
          <span class="chip" style="font-size:10.5px;padding:2px 8px;background:#ecfdf5;color:#047857;border-color:#a7f3d0">Otomatis</span>
        </div>
        <div class="dashboard-wa-item" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#fafaf9;border-radius:8px;border:1px solid var(--line)">
          <div style="display:flex;align-items:center;gap:8px">
            <span style="color:#10b981">🚀</span>
            <span>Pengingat Saat Hari-H Pelaksanaan Maintenance</span>
          </div>
          <span class="chip" style="font-size:10.5px;padding:2px 8px;background:#ecfdf5;color:#047857;border-color:#a7f3d0">Sesuai Jam</span>
        </div>
        <div class="dashboard-wa-item" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#fafaf9;border-radius:8px;border:1px solid var(--line)">
          <div style="display:flex;align-items:center;gap:8px">
            <span style="color:#ef4444">⚠️</span>
            <span>Peringatan Keterlambatan Pengerjaan (Eskalasi)</span>
          </div>
          <span class="chip" style="font-size:10.5px;padding:2px 8px;background:#fff1f2;color:#be123c;border-color:#fecdd3">Prioritas</span>
        </div>
      </div>
    </div>

    <div class="dashboard-wa-foot" style="margin-top:16px;padding-top:12px;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
      <span class="tiny muted">Notifikasi dikirim langsung ke WhatsApp teknisi terdaftar.</span>
      <div class="dashboard-wa-actions" style="display:inline-flex;align-items:center;gap:6px">
        <form method="POST" action="{{ route('manajemen.wa.testGateway') }}" style="margin:0">
          @csrf
          <button type="submit" class="btn-action primary" style="font-size:12px;padding:6px 12px;background:#16a34a;border-color:#16a34a" title="Kirim pesan uji coba ke WhatsApp Anda">
            @include('partials.icon', ['n' => 'whatsapp'])
            <span>Tes Gateway WA</span>
          </button>
        </form>
        <form method="POST" action="{{ route('manajemen.jadwal.runReminders') }}" style="margin:0">
          @csrf
          <button type="submit" class="btn-action" style="font-size:12px;padding:6px 10px" title="Jalankan pengecekan pengingat hari ini">
            @include('partials.icon', ['n' => 'clock'])
            <span>Cek Pengingat</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@include('partials.job-detail-modal')
@endsection
