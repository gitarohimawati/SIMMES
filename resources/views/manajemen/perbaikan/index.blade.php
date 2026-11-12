@extends('layouts.manager')
@section('title', 'Perbaikan')
@section('content')
<div class="top">
  <div class="top-row">
    <h1>Perbaikan</h1>
    <div class="actions">
      @include('partials.quick-report-modal', ['machines' => $reportMachines])
    </div>
  </div>
</div>
<form class="filters repair-filters" method="GET" autocomplete="off">
  <div class="repair-search-control">
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode tiket, mesin, atau keluhan" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true">
    <button class="btn primary" type="submit">Cari</button>
  </div>
  <select class="select" name="status">
    <option value="">Semua status</option>
    @foreach (['dilaporkan','ditugaskan','diproses','selesai','terlambat'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <select class="select" name="urgency">
    <option value="">Semua urgensi</option>
    @foreach (['tinggi','sedang','rendah'] as $s)<option value="{{ $s }}" @selected(request('urgency') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <select class="select" name="location">
    <option value="">Semua lokasi</option>
    @foreach ($locations as $l)<option @selected(request('location') === $l)>{{ $l }}</option>@endforeach
  </select>
  <select class="select" name="sort" aria-label="Urutkan tiket">
    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Terbaru</option>
    <option value="oldest" @selected(request('sort') === 'oldest')>Terlama</option>
    <option value="urgency" @selected(request('sort') === 'urgency')>Urgensi tertinggi</option>
  </select>
  @if (request()->hasAny(['q', 'status', 'urgency', 'location', 'sort']))
    <a class="btn repair-reset" href="{{ route('manajemen.perbaikan.index') }}">Reset</a>
  @endif
</form>
<div class="card dashboard-card-section" style="margin-bottom:24px;padding:0;overflow:hidden;border-top:3.5px solid #be123c">
  <div class="row between dash-room-banner" style="padding:12px 20px;background:#fff1f2;border-bottom:1px solid #fecdd3;align-items:center">
    <div style="display:flex;align-items:center;gap:8px">
      <span class="chip-dot" style="background:#be123c"></span>
      <h3 style="margin:0;font-size:15px;color:#9f1239;font-weight:800">Antrian Tiket Perbaikan</h3>
    </div>
    <span class="tiny" style="color:#9f1239;font-weight:700">{{ $tickets->total() }} Tiket Tercatat</span>
  </div>

  {{-- Desktop Table View --}}
  <div class="dashboard-table-wrap" style="border:0;border-radius:0">
    <table class="dashboard-table">
      <thead>
        <tr style="background:linear-gradient(90deg, #fff1f2 0%, #ffe4e6 100%)">
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Kode & Tipe</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Mesin & Lokasi</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Waktu Pelaporan</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Urgensi</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Teknisi Bertugas</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Status</th>
          <th class="col-actions" style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important;text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($tickets as $t)
        @php
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
              'machine'        => ['name' => $t->machine->name, 'code' => $t->machine->code, 'location' => $t->machine->location],
              'status'         => $t->status,
              'display_status' => $t->display_status,
              'urgency'        => $t->urgency,
              'date_formatted' => $t->finished_at ? ('Selesai: ' . $t->finished_at->translatedFormat('d M Y')) : ($t->started_at ? ('Mulai: ' . $t->started_at->translatedFormat('d M Y')) : ('Dilaporkan: ' . $t->created_at->translatedFormat('d M Y'))),
              'complaint'      => $t->complaint,
              'action'         => $t->action,
              'spareparts'     => $t->spareparts,
              'technician'     => $t->technician ? ['name' => $t->technician->name, 'whatsapp' => $t->technician->whatsapp] : null,
              'photos'         => $tPhotos,
              'logs'           => $tLogs,
              'show_url'       => route('manajemen.perbaikan.show', $t),
          ];
        @endphp
        <tr>
          <td style="padding:14px">
            <span class="badge-code">{{ $t->code }}</span>
            <div class="tiny muted" style="margin-top:3px;text-transform:capitalize">{{ $t->type }}</div>
          </td>
          <td style="padding:14px">
            <div style="font-weight:700;font-size:14px;color:var(--ink)">{{ $t->machine->label }}</div>
            <div class="tiny muted" style="margin-top:2px">{{ $t->machine->location }}</div>
          </td>
          <td style="padding:14px">
            <div style="font-weight:700;color:var(--ink)">{{ $t->created_at->translatedFormat('d M Y') }}</div>
            @if ($t->finished_at)
              <div class="tiny" style="color:#047857;font-weight:700">Selesai: {{ $t->finished_at->translatedFormat('d M Y') }}</div>
            @elseif ($t->started_at)
              <div class="tiny muted">Mulai: {{ $t->started_at->translatedFormat('d M Y') }}</div>
            @endif
          </td>
          <td style="padding:14px">@include('partials.chip', ['v' => $t->urgency])</td>
          <td style="padding:14px">
            @if ($t->technician)
              <div style="display:flex;align-items:center;gap:8px">
                <div class="avatar" style="width:28px;height:28px;font-size:11px;background:#f3f4f6;color:var(--ink);border:1px solid #e5e7eb">
                  {{ $t->technician->initials }}
                </div>
                <div>
                  <div style="font-weight:700;font-size:13px">{{ $t->technician->name }}</div>
                  @if ($t->technician->whatsapp)
                    @php
                      $msg = \App\Services\WhatsAppService::buildTicketMessage($t, $t->technician);
                      $wa = \App\Services\WhatsAppService::makeDirectLink($t->technician->whatsapp, $msg);
                    @endphp
                    @if ($wa)
                      <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="font-size:10px;margin-top:2px">
                        @include('partials.icon', ['n' => 'whatsapp']) WA
                      </a>
                    @endif
                  @endif
                </div>
              </div>
            @else
              <span class="chip" style="background:#ffedd5;color:#c2410c;font-size:11px;font-weight:800">⚡ Belum Diambil</span>
            @endif
          </td>
          <td style="padding:14px">
            @include('partials.chip', ['v' => $t->display_status])
          </td>
          <td class="col-actions" style="padding:14px;text-align:right">
            <div class="table-actions-row">
              <button type="button" 
                      class="btn-icon detail" 
                      data-ticket="{{ json_encode($tPayload) }}" 
                      onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" 
                      title="Buka Ringkasan & Foto">
                @include('partials.icon', ['n' => 'eye'])
              </button>
              <a href="{{ route('manajemen.perbaikan.edit', $t) }}" 
                 class="btn-icon edit" 
                 title="Edit Tiket Perbaikan">
                @include('partials.icon', ['n' => 'edit'])
              </a>
              <form method="POST" action="{{ route('manajemen.perbaikan.destroy', $t) }}" data-confirm="Apakah Anda yakin ingin menghapus tiket {{ $t->code }}?" style="display:inline;margin:0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-icon danger" title="Hapus Tiket">
                  @include('partials.icon', ['n' => 'trash'])
                </button>
              </form>
              <a href="{{ route('manajemen.perbaikan.show', $t) }}" class="btn-icon" title="Buka Form Administrasi Penuh">
                @include('partials.icon', ['n' => 'external'])
              </a>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="empty" style="padding:36px 20px">Tidak ada tiket yang cocok.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile & Tablet Cards View (seperti pada dashboard teknisi / pusat aktivitas) --}}
  <div class="dashboard-mobile-list">
    @forelse ($tickets as $t)
      @php
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
            'machine'        => ['name' => $t->machine->name, 'code' => $t->machine->code, 'location' => $t->machine->location],
            'status'         => $t->status,
            'display_status' => $t->display_status,
            'urgency'        => $t->urgency,
            'date_formatted' => $t->finished_at ? ('Selesai: ' . $t->finished_at->translatedFormat('d M Y')) : ($t->started_at ? ('Mulai: ' . $t->started_at->translatedFormat('d M Y')) : ('Dilaporkan: ' . $t->created_at->translatedFormat('d M Y'))),
            'complaint'      => $t->complaint,
            'action'         => $t->action,
            'spareparts'     => $t->spareparts,
            'technician'     => $t->technician ? ['name' => $t->technician->name, 'whatsapp' => $t->technician->whatsapp] : null,
            'photos'         => $tPhotos,
            'logs'           => $tLogs,
            'show_url'       => route('manajemen.perbaikan.show', $t),
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
            @if ($t->technician)
              <b>{{ $t->technician->name }}</b>
              @if ($t->technician->whatsapp)
                @php
                  $msg = \App\Services\WhatsAppService::buildTicketMessage($t, $t->technician);
                  $wa = \App\Services\WhatsAppService::makeDirectLink($t->technician->whatsapp, $msg);
                @endphp
                @if ($wa)
                  <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="font-size:10px;padding:2px 6px">
                    @include('partials.icon', ['n' => 'whatsapp']) WA
                  </a>
                @endif
              @endif
            @else
              <span class="chip" style="background:#ffedd5;color:#c2410c;font-size:10.5px;padding:2px 7px;font-weight:800">⚡ Belum Diambil</span>
            @endif
          </div>
        </div>

        <div class="dash-m-actions">
          <span class="tiny muted" style="font-weight:600">Aksi:</span>
          <div class="dash-m-icon-group">
            <button type="button" class="btn-icon detail" data-ticket="{{ json_encode($tPayload) }}" onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" title="Buka Ringkasan & Foto">
              @include('partials.icon', ['n' => 'eye'])
            </button>
            <a href="{{ route('manajemen.perbaikan.edit', $t) }}" class="btn-icon edit" title="Edit Tiket Perbaikan">
              @include('partials.icon', ['n' => 'edit'])
            </a>
            <form method="POST" action="{{ route('manajemen.perbaikan.destroy', $t) }}" data-confirm="Apakah Anda yakin ingin menghapus tiket {{ $t->code }}?" style="display:inline;margin:0">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-icon danger" title="Hapus Tiket">
                @include('partials.icon', ['n' => 'trash'])
              </button>
            </form>
            <a href="{{ route('manajemen.perbaikan.show', $t) }}" class="btn-icon" title="Buka Detail Penuh">
              @include('partials.icon', ['n' => 'external'])
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="dash-m-empty">
        <div style="font-size:24px;margin-bottom:4px">🔧</div>
        <b style="font-size:14px;color:var(--ink)">Tidak ada tiket yang cocok</b>
      </div>
    @endforelse
  </div>
</div>
<div class="pagination">{{ $tickets->links() }}</div>

@include('partials.job-detail-modal')
@endsection
