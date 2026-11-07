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
<form class="filters" method="GET" autocomplete="off">
  <input class="input" name="q" value="{{ request('q') }}" placeholder="Cari kode tiket atau mesin" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true">
  <select class="select" name="status" onchange="this.form.submit()">
    <option value="">Semua status</option>
    @foreach (['dilaporkan','ditugaskan','diproses','selesai','terlambat'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <select class="select" name="urgency" onchange="this.form.submit()">
    <option value="">Semua urgensi</option>
    @foreach (['tinggi','sedang','rendah'] as $s)<option value="{{ $s }}" @selected(request('urgency') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <select class="select" name="location" onchange="this.form.submit()">
    <option value="">Semua lokasi</option>
    @foreach ($locations as $l)<option @selected(request('location') === $l)>{{ $l }}</option>@endforeach
  </select>
</form>
<div class="card tablewrap">
  <table>
    <thead><tr><th>Kode/Tipe</th><th>Mesin</th><th>Dilaporkan</th><th>Urgensi</th><th>Teknisi</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
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
        <td><b>{{ $t->code }}</b><div class="tiny">{{ ucfirst($t->type) }}</div></td>
        <td>{{ $t->machine->label }}</td>
        <td>
          <b>{{ $t->created_at->translatedFormat('d M Y') }}</b>
          @if ($t->finished_at)
            <div class="tiny" style="color:#047857;font-weight:700">Selesai: {{ $t->finished_at->translatedFormat('d M Y') }}</div>
          @elseif ($t->started_at)
            <div class="tiny muted">Mulai: {{ $t->started_at->translatedFormat('d M Y') }}</div>
          @endif
        </td>
        <td>@include('partials.chip', ['v' => $t->urgency])</td>
        <td>
          @if ($t->technician)
            <b>{{ $t->technician->name }}</b>
          @else
            <span class="chip" style="background:#ffedd5;color:#c2410c;font-size:11px;font-weight:800">⚡ Belum Diambil</span>
          @endif
        </td>
        <td>
          @include('partials.chip', ['v' => $t->display_status])
        </td>
        <td style="text-align:right">
          <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:5px;flex-wrap:wrap">
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
            <form method="POST" action="{{ route('manajemen.perbaikan.destroy', $t) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tiket {{ $t->code }}?');" style="display:inline;margin:0">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-icon danger" title="Hapus Tiket">
                @include('partials.icon', ['n' => 'trash'])
              </button>
            </form>
            <a href="{{ route('manajemen.perbaikan.show', $t) }}" class="btn-icon" title="Buka Form Administrasi Penuh">
              @include('partials.icon', ['n' => 'external'])
            </a>
            @if ($t->technician?->whatsapp)
              @php
                $msg = \App\Services\WhatsAppService::buildTicketMessage($t, $t->technician);
                $wa = \App\Services\WhatsAppService::makeDirectLink($t->technician->whatsapp, $msg);
              @endphp
              @if ($wa)
                <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="btn-icon whatsapp" title="Hubungi WA {{ $t->technician->name }}">
                  @include('partials.icon', ['n' => 'whatsapp'])
                </a>
              @endif
            @elseif ($t->technician_id === null && $t->status !== 'selesai')
              @php
                $waBroadcast = \App\Services\WhatsAppService::makeBroadcastShareLink($t);
              @endphp
              <a href="{{ $waBroadcast }}" target="_blank" rel="noopener noreferrer" class="btn-icon whatsapp" title="Siarkan Notifikasi Perbaikan ke Grup WA Teknisi (Siapa Cepat Dia Dapat)">
                @include('partials.icon', ['n' => 'whatsapp'])
              </a>
            @endif
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="empty">Tidak ada tiket yang cocok.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $tickets->links() }}</div>

@include('partials.job-detail-modal')
@endsection
