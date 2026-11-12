@extends('layouts.manager')
@section('title', $machine->code)
@section('content')
@php
  $from = request('from');
  $backUrl = route('manajemen.mesin.index');

  if ($from === 'dashboard') {
      $backUrl = route('manajemen.dashboard');
  } elseif ($from === 'jadwal') {
      $backUrl = route('manajemen.jadwal.index');
  } elseif ($from === 'kalender') {
      $backUrl = route('manajemen.jadwal.kalender');
  } else {
      $prev = url()->previous();
      if ($prev && $prev !== url()->current()) {
          $dashPath = rtrim(parse_url(route('manajemen.dashboard'), PHP_URL_PATH) ?? '', '/');
          $prevPath = rtrim(parse_url($prev, PHP_URL_PATH) ?? '', '/');
          if ($dashPath && $prevPath === $dashPath) {
              $backUrl = route('manajemen.dashboard');
          } elseif ($prevPath === rtrim(parse_url(route('manajemen.jadwal.index'), PHP_URL_PATH) ?? '', '/')) {
              $backUrl = route('manajemen.jadwal.index');
          } elseif ($prevPath === rtrim(parse_url(route('manajemen.jadwal.kalender'), PHP_URL_PATH) ?? '', '/')) {
              $backUrl = route('manajemen.jadwal.kalender');
          }
      }
  }
@endphp
<div class="top">
  <div><h1>Detail Mesin</h1><p>{{ $machine->label }}</p></div>
  <div class="actions">
    @if ($machine->needs_service)<span class="chip c-diproses">Perlu servis</span>@endif
    <a class="btn" href="{{ route('manajemen.mesin.edit', $machine) }}" style="color:#0284c7;border-color:#bae6fd">
      @include('partials.icon', ['n' => 'edit']) Edit Mesin
    </a>
    <a class="btn" href="{{ $backUrl }}">← Kembali</a>
  </div>
</div>
<div class="card">
  <div class="kv">
    <div><span>Kategori</span><b>{{ $machine->category ?? '-' }}</b></div>
    <div><span>Tipe / model</span><b>{{ $machine->model ?? '-' }}</b></div>
    <div><span>Nomor seri</span><b>{{ $machine->serial_number ?? '-' }}</b></div>
    <div><span>Lokasi</span><b>{{ $machine->location }}</b></div>
    <div><span>Tanggal pembelian</span><b>{{ $machine->purchase_date?->translatedFormat('d M Y') ?? '-' }}</b></div>
    <div><span>Jadwal berikutnya</span><b style="color:var(--r600)">{{ $machine->nextSchedule?->due_date->translatedFormat('d M Y') ?? '-' }}</b></div>
  </div>
  <div class="small muted" style="margin:22px 0 8px">Foto / video awal</div>
  <div class="row wrap">
    @forelse ($machine->media ?? [] as $p)
      <div class="thumb">@if (preg_match('/\.(mp4|mov)$/i', $p))<video src="{{ Storage::url($p) }}" controls></video>@else<img src="{{ Storage::url($p) }}" alt="">@endif</div>
    @empty <span class="tiny">Belum ada dokumentasi.</span> @endforelse
  </div>
</div>
<div class="card dashboard-card-section" style="margin-top:26px;padding:0;overflow:hidden;border-top:3.5px solid var(--r700)">
  <div class="row between dash-room-banner" style="padding:12px 20px;background:#fff1f2;border-bottom:1px solid #fecdd3;align-items:center">
    <div style="display:flex;align-items:center;gap:8px">
      <span class="chip-dot" style="background:var(--r700)"></span>
      <h3 style="margin:0;font-size:15px;color:#9f1239;font-weight:800">Riwayat Pekerjaan (Sebelum & Sesudah)</h3>
    </div>
    <span class="tiny" style="color:#9f1239;font-weight:700">{{ $machine->tickets->count() }} Riwayat</span>
  </div>

  {{-- Desktop Table View --}}
  <div class="dashboard-table-wrap" style="border:0;border-radius:0">
    <table class="dashboard-table">
      <thead>
        <tr style="background:linear-gradient(90deg, #fff1f2 0%, #ffe4e6 100%)">
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Tanggal & Kode</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Jenis</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Foto Sebelum</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Foto Sesudah</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fecdd3;color:#881337!important">Status & Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($machine->tickets as $t)
        @php
          $b = $t->mediaOf('before')->first() ?? $t->mediaOf('report')->first();
          $a = $t->mediaOf('after')->first();
          $tPhotos = [];
          foreach ($t->media as $m) {
              $tPhotos[] = [
                  'url'   => $m->url,
                  'phase' => $m->phase,
                  'kind'  => $m->kind,
                  'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : 'Foto Sebelum / Awal'
              ];
          }
          $tLogs = [];
          foreach ($t->logs as $l) {
              $tLogs[] = [
                  'label' => $l->label,
                  'note'  => $l->note,
                  'time'  => $l->created_at ? $l->created_at->translatedFormat('d M Y, H:i') : '-'
              ];
          }
          $tPayload = [
              'code'           => $t->code,
              'machine'        => ['name' => $machine->name, 'code' => $machine->code, 'location' => $machine->location],
              'status'         => $t->status,
              'display_status' => $t->display_status,
              'urgency'        => $t->urgency,
              'date_formatted' => $t->created_at ? $t->created_at->translatedFormat('d M Y') : '-',
              'complaint'      => $t->complaint,
              'action'         => $t->action,
              'spareparts'     => $t->spareparts,
              'technician'     => $t->technician ? ['name' => $t->technician->name, 'whatsapp' => $t->technician->whatsapp] : null,
              'photos'         => $tPhotos,
              'logs'           => $tLogs,
              'show_url'       => route('manajemen.perbaikan.show', $t),
          ];
          $isUnclaimed = ($t->technician_id === null && $t->status !== 'selesai');
          $broadcastWaUrl = $isUnclaimed ? \App\Services\WhatsAppService::makeBroadcastShareLink($t) : null;
        @endphp
        <tr>
          <td style="padding:14px">
            <b>{{ $t->created_at ? $t->created_at->translatedFormat('d M Y') : '-' }}</b>
            @if ($t->finished_at)
              <div class="tiny" style="color:#047857;font-weight:700">Selesai: {{ $t->finished_at->translatedFormat('d M Y') }}</div>
            @elseif ($t->started_at)
              <div class="tiny muted">Mulai: {{ $t->started_at->translatedFormat('d M Y') }}</div>
            @endif
            <div class="tiny"><span class="badge-code">{{ $t->code }}</span></div>
          </td>
          <td style="padding:14px;text-transform:capitalize">{{ $t->type }}</td>
          <td style="padding:14px">
            <div style="width:130px;height:95px;border-radius:12px;overflow:hidden;border:1.5px solid var(--line);background:#1e293b;position:relative">
              @if ($b)
                <div onclick="openPhotoZoom('{{ $b->url }}', 'Foto Sebelum Perbaikan')" style="cursor:zoom-in;width:100%;height:100%" title="Klik untuk memperbesar">
                  <img src="{{ $b->url }}" alt="Sebelum" style="width:100%;height:100%;object-fit:cover;display:block">
                </div>
              @else
                <div style="width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-size:11px">Belum ada foto</div>
              @endif
            </div>
          </td>
          <td style="padding:14px">
            <div style="width:130px;height:95px;border-radius:12px;overflow:hidden;border:1.5px solid var(--line);background:#1e293b;position:relative">
              @if ($a)
                <div onclick="openPhotoZoom('{{ $a->url }}', 'Foto Sesudah Perbaikan')" style="cursor:zoom-in;width:100%;height:100%" title="Klik untuk memperbesar">
                  <img src="{{ $a->url }}" alt="Sesudah" style="width:100%;height:100%;object-fit:cover;display:block">
                </div>
              @else
                <div style="width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-size:11px">Belum ada foto</div>
              @endif
            </div>
          </td>
          <td style="padding:14px">
            @if ($isUnclaimed)
              <span class="chip" style="background:#ffedd5;color:#c2410c;font-size:11px;font-weight:800">⚡ Belum Diambil</span>
            @else
              @include('partials.chip', ['v' => $t->display_status])
            @endif

            <div style="margin-top:8px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
              <button type="button" 
                      class="btn-icon detail" 
                      data-ticket="{{ json_encode($tPayload) }}"
                      onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" 
                      title="Lihat Detail & Foto">
                @include('partials.icon', ['n' => 'eye'])
              </button>

              @if ($isUnclaimed && $broadcastWaUrl)
                <a href="{{ $broadcastWaUrl }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn-icon whatsapp" 
                   title="Siarkan Notifikasi Perbaikan ke WhatsApp Teknisi">
                  @include('partials.icon', ['n' => 'whatsapp'])
                </a>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty" style="padding:36px 20px">Belum ada riwayat.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile & Tablet Cards View --}}
  <div class="dashboard-mobile-list">
    @forelse ($machine->tickets as $t)
      @php
        $b = $t->mediaOf('before')->first() ?? $t->mediaOf('report')->first();
        $a = $t->mediaOf('after')->first();
        $tPhotos = [];
        foreach ($t->media as $m) {
            $tPhotos[] = [
                'url'   => $m->url,
                'phase' => $m->phase,
                'kind'  => $m->kind,
                'label' => $m->phase === 'after' ? 'Foto Sesudah Perbaikan' : 'Foto Sebelum / Awal'
            ];
        }
        $tLogs = [];
        foreach ($t->logs as $l) {
            $tLogs[] = [
                'label' => $l->label,
                'note'  => $l->note,
                'time'  => $l->created_at ? $l->created_at->translatedFormat('d M Y, H:i') : '-'
            ];
        }
        $tPayload = [
            'code'           => $t->code,
            'machine'        => ['name' => $machine->name, 'code' => $machine->code, 'location' => $machine->location],
            'status'         => $t->status,
            'display_status' => $t->display_status,
            'urgency'        => $t->urgency,
            'date_formatted' => $t->created_at ? $t->created_at->translatedFormat('d M Y') : '-',
            'complaint'      => $t->complaint,
            'action'         => $t->action,
            'spareparts'     => $t->spareparts,
            'technician'     => $t->technician ? ['name' => $t->technician->name, 'whatsapp' => $t->technician->whatsapp] : null,
            'photos'         => $tPhotos,
            'logs'           => $tLogs,
            'show_url'       => route('manajemen.perbaikan.show', $t),
        ];
        $isUnclaimed = ($t->technician_id === null && $t->status !== 'selesai');
        $broadcastWaUrl = $isUnclaimed ? \App\Services\WhatsAppService::makeBroadcastShareLink($t) : null;
      @endphp
      <div class="dash-m-card border-red">
        <div class="dash-m-head">
          <div class="dash-m-code-group">
            <span class="badge-code">{{ $t->code }}</span>
            <span class="tiny muted">{{ $t->created_at ? $t->created_at->translatedFormat('d M Y') : '-' }}</span>
          </div>
          <div class="dash-m-chips">
            @if ($isUnclaimed)
              <span class="chip" style="background:#ffedd5;color:#c2410c;font-size:10.5px;padding:2px 7px;font-weight:800">⚡ Belum Diambil</span>
            @else
              @include('partials.chip', ['v' => $t->display_status])
            @endif
          </div>
        </div>

        <div style="font-size:13px;color:var(--ink)">
          <b>Kendala:</b> {{ $t->complaint }}
        </div>

        {{-- Foto Sebelum & Sesudah --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:4px 0">
          <div>
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700">Sebelum:</span>
            <div style="height:90px;border-radius:10px;overflow:hidden;border:1px solid var(--line);background:#1e293b">
              @if ($b)
                <div onclick="openPhotoZoom('{{ $b->url }}', 'Foto Sebelum Perbaikan')" style="cursor:zoom-in;width:100%;height:100%" title="Klik zoom">
                  <img src="{{ $b->url }}" alt="Sebelum" style="width:100%;height:100%;object-fit:cover;display:block">
                </div>
              @else
                <div style="width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-size:10px">Belum ada</div>
              @endif
            </div>
          </div>
          <div>
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700">Sesudah:</span>
            <div style="height:90px;border-radius:10px;overflow:hidden;border:1px solid var(--line);background:#1e293b">
              @if ($a)
                <div onclick="openPhotoZoom('{{ $a->url }}', 'Foto Sesudah Perbaikan')" style="cursor:zoom-in;width:100%;height:100%" title="Klik zoom">
                  <img src="{{ $a->url }}" alt="Sesudah" style="width:100%;height:100%;object-fit:cover;display:block">
                </div>
              @else
                <div style="width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-size:10px">Belum ada</div>
              @endif
            </div>
          </div>
        </div>

        <div class="dash-m-actions">
          <span class="tiny muted" style="font-weight:600">Aksi:</span>
          <div class="dash-m-icon-group">
            <button type="button" 
                    class="btn-icon detail" 
                    data-ticket="{{ json_encode($tPayload) }}"
                    onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" 
                    title="Lihat Detail & Foto">
              @include('partials.icon', ['n' => 'eye'])
            </button>
            @if ($isUnclaimed && $broadcastWaUrl)
              <a href="{{ $broadcastWaUrl }}" 
                 target="_blank" 
                 rel="noopener noreferrer" 
                 class="btn-icon whatsapp" 
                 title="Siarkan Notifikasi ke WA Teknisi">
                @include('partials.icon', ['n' => 'whatsapp'])
              </a>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="dash-m-empty">
        <div style="font-size:24px;margin-bottom:4px">🔧</div>
        <b style="font-size:14px;color:var(--ink)">Belum ada riwayat</b>
      </div>
    @endforelse
  </div>
</div>

@include('partials.job-detail-modal')
@endsection
