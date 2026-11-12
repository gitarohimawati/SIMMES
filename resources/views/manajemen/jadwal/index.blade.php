@extends('layouts.manager')
@section('title', 'Jadwal Maintenance')
@section('content')
<div class="top">
  <div>
    <h1>Jadwal Maintenance</h1>
    <p class="muted small">Antrian jadwal pemeliharaan mesin (terbaru dimasukkan berada di atas).</p>
  </div>
</div>
@include('manajemen.jadwal._tabs')

<div class="card" style="margin-bottom:18px;padding:14px 22px">
  <b>Progress:</b> <span class="muted" style="margin-left:10px">25% Foto sebelum · 50% Pengerjaan · 75% Foto sesudah · 100% Selesai</span>
</div>

<div class="card dashboard-card-section" style="margin-bottom:24px;padding:0;overflow:hidden;border-top:3.5px solid #d97706">
  <div class="row between dash-room-banner" style="padding:12px 20px;background:#fffbeb;border-bottom:1px solid #fde68a;align-items:center">
    <div style="display:flex;align-items:center;gap:8px">
      <span class="chip-dot" style="background:#d97706"></span>
      <h3 style="margin:0;font-size:15px;color:#92400e;font-weight:800">Antrian Jadwal Maintenance</h3>
      <span class="chip" style="background:#fef3c7;color:#92400e;border-color:#fde68a;font-size:11px;font-weight:700">Terbaru di Atas</span>
    </div>
    <span class="tiny" style="color:#92400e;font-weight:700">{{ $schedules->count() }} Jadwal Terencana</span>
  </div>

  {{-- Desktop Table View --}}
  <div class="dashboard-table-wrap" style="border:0;border-radius:0">
    <table class="dashboard-table">
      <thead>
        <tr style="background:linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%)">
          <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Mesin & Lokasi</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Tugas & Jenis</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Waktu Pelaksanaan</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Teknisi Bertugas</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Progress</th>
          <th style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important">Status</th>
          <th class="col-actions" style="padding:12px 14px;border-bottom:1px solid #fde68a;color:#78350f!important;text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($schedules as $s)
        @php
          $tech = $s->technician;
          $kindReminder = $s->display_status === 'terlambat' ? 'Terlambat' : ($s->due_date->isToday() ? 'Hari-H' : 'Pengingat');
          $schedWaLink = ($tech && $tech->whatsapp) ? \App\Services\WhatsAppService::makeScheduleShareLink($s, $kindReminder) : null;

          $latestTicket = $s->ticket ?? $s->machine->tickets->first();
          $sPhotos = [];
          if ($s->before_url) {
              $sPhotos[] = [
                  'url'   => $s->before_url,
                  'phase' => 'before',
                  'kind'  => 'image',
                  'label' => 'Foto Sebelum Maintenance'
              ];
          }
          if ($s->after_url) {
              $sPhotos[] = [
                  'url'   => $s->after_url,
                  'phase' => 'after',
                  'kind'  => 'image',
                  'label' => 'Foto Sesudah Maintenance'
              ];
          }
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
              'ticket'         => [
                  'action'     => $s->notes ?: ($latestTicket ? $latestTicket->action : null),
                  'spareparts' => $s->spareparts ?: ($latestTicket ? $latestTicket->spareparts : null),
              ],
              'notes'          => $s->notes,
              'spareparts'     => $s->spareparts,
              'photos'         => $sPhotos,
              'logs'           => $sLogs,
              'show_url'       => route('manajemen.mesin.show', [$s->machine, 'from' => 'jadwal']),
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
                    <a href="{{ $schedWaLink }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="margin-top:2px" title="Kirim Notifikasi via WA">
                      @include('partials.icon', ['n' => 'whatsapp']) {{ $tech->whatsapp }}
                    </a>
                  @elseif ($tech->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tech->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="margin-top:2px">
                      @include('partials.icon', ['n' => 'whatsapp']) {{ $tech->whatsapp }}
                    </a>
                  @else
                    <span class="badge-wa-empty">No WA belum diisi</span>
                  @endif
                </div>
              </div>
            @else
              <form class="row" method="POST" action="{{ route('manajemen.jadwal.assign', $s) }}" style="margin:0">
                @csrf
                <select class="select" name="technician_id" style="padding:4px 8px;font-size:11.5px;min-width:110px" onchange="this.form.submit()" required>
                  <option value="">Tugaskan…</option>
                  @foreach (\App\Models\User::where('role','teknisi')->where('status','aktif')->get() as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </form>
            @endif
          </td>
          <td style="padding:14px;min-width:130px">
            <div class="row" style="gap:8px;align-items:center">
              <div class="progress" style="flex:1"><i style="width:{{ $s->progress }}%"></i></div>
              <b class="small">{{ $s->progress }}%</b>
            </div>
            <div class="tiny muted" style="margin-top:3px">
              {{ $s->progress == 100 ? 'Bukti lengkap' : ($s->progress ? 'Sedang dikerjakan' : 'Belum mulai') }}
            </div>
          </td>
          <td style="padding:14px">
            @include('partials.chip', ['v' => $s->display_status])
          </td>
          <td class="col-actions" style="padding:14px;text-align:right">
            <div class="table-actions-row">
              <button type="button" class="btn-icon detail" data-schedule="{{ json_encode($sPayload) }}" onclick="openScheduleDetail(JSON.parse(this.dataset.schedule))" title="Lihat Detail Jadwal">
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
              <a href="{{ route('manajemen.mesin.show', [$s->machine, 'from' => 'jadwal']) }}" class="btn-icon" title="Lihat Profil Mesin Lengkap">
                @include('partials.icon', ['n' => 'external'])
              </a>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="empty" style="padding:36px 20px">
            <div style="font-weight:600;color:var(--ink2)">Belum ada jadwal maintenance</div>
            <div class="small muted" style="margin-top:4px">Jadwal perawatan mesin akan muncul di sini.</div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile & Tablet Cards View (seperti pada dashboard teknisi / pusat aktivitas) --}}
  <div class="dashboard-mobile-list">
    @forelse ($schedules as $s)
      @php
        $tech = $s->technician;
        $kindReminder = $s->display_status === 'terlambat' ? 'Terlambat' : ($s->due_date->isToday() ? 'Hari-H' : 'Pengingat');
        $schedWaLink = ($tech && $tech->whatsapp) ? \App\Services\WhatsAppService::makeScheduleShareLink($s, $kindReminder) : null;

        $latestTicket = $s->ticket ?? $s->machine->tickets->first();
        $sPhotos = [];
        if ($s->before_url) {
            $sPhotos[] = ['url' => $s->before_url, 'phase' => 'before', 'kind' => 'image', 'label' => 'Foto Sebelum Maintenance'];
        }
        if ($s->after_url) {
            $sPhotos[] = ['url' => $s->after_url, 'phase' => 'after', 'kind' => 'image', 'label' => 'Foto Sesudah Maintenance'];
        }
        if ($latestTicket) {
            foreach ($latestTicket->media as $m) {
                $sPhotos[] = ['url' => $m->url, 'phase' => $m->phase, 'kind' => $m->kind, 'label' => $m->phase === 'after' ? 'Foto Sesudah' : 'Foto Awal'];
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
            'ticket'         => [
                'action'     => $s->notes ?: ($latestTicket ? $latestTicket->action : null),
                'spareparts' => $s->spareparts ?: ($latestTicket ? $latestTicket->spareparts : null),
            ],
            'notes'          => $s->notes,
            'spareparts'     => $s->spareparts,
            'photos'         => $sPhotos,
            'logs'           => $sLogs,
            'show_url'       => route('manajemen.mesin.show', [$s->machine, 'from' => 'jadwal']),
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

        <div style="margin:2px 0 4px">
          <div class="row between" style="margin-bottom:4px">
            <span class="tiny muted">Progress: <b>{{ $s->progress }}%</b></span>
            <span class="tiny muted">
              @if ($s->status === 'selesai')
                <span style="color:#16a34a;font-weight:700">✓ Tuntas</span>
              @elseif ($s->progress >= 75)
                Foto sesudah
              @elseif ($s->progress >= 25)
                Foto awal
              @else
                Belum mulai
              @endif
            </span>
          </div>
          <div class="progress" style="height:6px"><i style="width:{{ $s->progress }}%"></i></div>
        </div>

        <div class="dash-m-meta-row" style="flex-direction:column;align-items:flex-start;gap:6px">
          <div style="display:flex;align-items:center;gap:6px;font-size:12px;flex-wrap:wrap">
            <span class="muted">Jatuh Tempo:</span>
            <b style="color:var(--ink)">{{ $s->due_date ? $s->due_date->translatedFormat('d M Y') : '-' }}</b>
            @if ($s->due_time)
              <span class="badge-time" style="font-size:10px;padding:1px 6px">Jam {{ $s->due_time }} WIB</span>
            @endif
          </div>
          <div class="dash-m-tech" style="width:100%;justify-content:space-between;flex-wrap:wrap;gap:6px">
            <div style="display:flex;align-items:center;gap:6px">
              <span class="muted">Teknisi:</span>
              @if ($tech)
                <b>{{ $tech->name }}</b>
                @if ($schedWaLink)
                  <a href="{{ $schedWaLink }}" target="_blank" rel="noopener noreferrer" class="badge-wa" style="font-size:10px;padding:2px 6px">
                    @include('partials.icon', ['n' => 'whatsapp']) WA
                  </a>
                @endif
              @else
                <span class="chip c-ditugaskan" style="font-size:10.5px;padding:2px 7px">Belum Ditugaskan</span>
              @endif
            </div>
            @if (!$tech)
              <form method="POST" action="{{ route('manajemen.jadwal.assign', $s) }}" style="margin:0">
                @csrf
                <select class="select" name="technician_id" style="padding:3px 6px;font-size:11px" onchange="this.form.submit()" required>
                  <option value="">+ Tugaskan…</option>
                  @foreach (\App\Models\User::where('role','teknisi')->where('status','aktif')->get() as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </form>
            @endif
          </div>
        </div>

        <div class="dash-m-actions">
          <span class="tiny muted" style="font-weight:600">Aksi:</span>
          <div class="dash-m-icon-group">
            <button type="button" class="btn-icon detail" data-schedule="{{ json_encode($sPayload) }}" onclick="openScheduleDetail(JSON.parse(this.dataset.schedule))" title="Lihat Detail Jadwal">
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
            <a href="{{ route('manajemen.mesin.show', [$s->machine, 'from' => 'jadwal']) }}" class="btn-icon" title="Lihat Profil Mesin">
              @include('partials.icon', ['n' => 'external'])
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="dash-m-empty">
        <div style="font-size:24px;margin-bottom:4px">📅</div>
        <b style="font-size:14px;color:var(--ink)">Belum ada jadwal maintenance</b>
        <div class="tiny muted" style="margin-top:2px">Jadwal perawatan mesin akan muncul di sini.</div>
      </div>
    @endforelse
  </div>
</div>

{{-- Riwayat Servis Terakhir --}}
<div class="card dashboard-card-section" style="margin-top:18px;padding:0;overflow:hidden;border-top:3.5px solid #059669">
  <div class="row between dash-room-banner" style="padding:10px 18px;background:#ecfdf5;border-bottom:1px solid #a7f3d0;align-items:center">
    <div style="display:flex;align-items:center;gap:7px">
      <span class="chip-dot" style="background:#059669"></span>
      <h3 style="margin:0;font-size:14px;color:#065f46;font-weight:800">Riwayat Servis Terakhir</h3>
    </div>
    <span class="tiny" style="color:#065f46;font-weight:700">{{ $history->count() }} Riwayat Tercatat</span>
  </div>

  <div class="dashboard-table-wrap" style="border:0;border-radius:0">
    <table class="dashboard-table">
      <thead>
        <tr style="background:linear-gradient(90deg, #ecfdf5 0%, #d1fae5 100%)">
          <th style="padding:10px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Mesin</th>
          <th style="padding:10px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Tindakan</th>
          <th style="padding:10px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Sparepart</th>
          <th style="padding:10px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Teknisi</th>
          <th style="padding:10px 14px;border-bottom:1px solid #a7f3d0;color:#064e3b!important">Tanggal Selesai</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($history as $h)
        <tr>
          <td style="padding:12px 14px">
            <span class="badge-code">{{ $h->machine->code }}</span>
            <div style="font-weight:700;font-size:13px;margin-top:2px">{{ $h->machine->name }}</div>
          </td>
          <td style="padding:12px 14px;font-weight:600">{{ $h->task }}</td>
          <td style="padding:12px 14px">{{ $h->spareparts ?? '-' }}</td>
          <td style="padding:12px 14px">{{ $h->technician?->name ?? '-' }}</td>
          <td style="padding:12px 14px"><b>{{ $h->completed_at?->translatedFormat('d M Y') ?? '-' }}</b></td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty" style="padding:28px 14px">Belum ada riwayat.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="dashboard-mobile-list" style="padding:10px;gap:8px">
    @forelse ($history as $h)
      <div class="dash-m-card border-green" style="padding:10px 12px;gap:6px">
        <div class="row between">
          <span class="badge-code">{{ $h->machine->code }}</span>
          <b class="tiny" style="color:#059669">{{ $h->completed_at?->translatedFormat('d M Y') ?? '-' }}</b>
        </div>
        <div style="font-weight:700;font-size:13px">{{ $h->task }}</div>
        <div class="tiny muted">Teknisi: <b>{{ $h->technician?->name ?? '-' }}</b> · Part: {{ $h->spareparts ?? '-' }}</div>
      </div>
    @empty
      <div class="tiny muted" style="padding:14px;text-align:center">Belum ada riwayat.</div>
    @endforelse
  </div>
</div>

@include('partials.job-detail-modal')
@endsection
