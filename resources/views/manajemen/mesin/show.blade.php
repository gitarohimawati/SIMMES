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
<h3 style="margin:26px 0 12px;font-size:18px">Riwayat Pekerjaan (sebelum dan sesudah)</h3>
<div class="card tablewrap">
  <table>
    <thead><tr><th>Tanggal</th><th>Jenis</th><th>Foto sebelum</th><th>Foto sesudah</th><th>Status</th></tr></thead>
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
        <td>
          <b>{{ $t->created_at ? $t->created_at->translatedFormat('d M Y') : '-' }}</b>
          @if ($t->finished_at)
            <div class="tiny" style="color:#047857;font-weight:700">Selesai: {{ $t->finished_at->translatedFormat('d M Y') }}</div>
          @elseif ($t->started_at)
            <div class="tiny muted">Mulai: {{ $t->started_at->translatedFormat('d M Y') }}</div>
          @endif
          <div class="tiny muted">{{ $t->code }}</div>
        </td>
        <td>{{ ucfirst($t->type) }}</td>
        <td>
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
        <td>
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
        <td>
          @if ($isUnclaimed)
            <span class="chip" style="background:#ffedd5;color:#c2410c;font-size:11px;font-weight:800">⚡ Belum Diambil</span>
          @else
            @include('partials.chip', ['v' => $t->display_status])
          @endif

          <div style="margin-top:6px;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
            <button type="button" 
                    class="btn-action primary" 
                    data-ticket="{{ json_encode($tPayload) }}"
                    onclick="openTicketDetail(JSON.parse(this.dataset.ticket))" 
                    style="padding:4px 9px;font-size:11px">
              @include('partials.icon', ['n' => 'eye']) <span>Detail</span>
            </button>

            @if ($isUnclaimed && $broadcastWaUrl)
              <a href="{{ $broadcastWaUrl }}" 
                 target="_blank" 
                 rel="noopener noreferrer" 
                 class="btn-action" 
                 style="padding:4px 8px;font-size:11px;background:#16a34a;color:#fff;border-color:transparent" 
                 title="Siarkan Notifikasi Perbaikan ke WhatsApp Teknisi (Siapa Cepat Dia Dapat)">
                📲 Siarkan WA
              </a>
            @endif
          </div>
        </td>
      </tr>
    @empty <tr><td colspan="5" class="empty">Belum ada riwayat.</td></tr> @endforelse
    </tbody>
  </table>
</div>

@include('partials.job-detail-modal')
@endsection
