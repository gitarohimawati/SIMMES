@extends('layouts.manager')
@section('title', 'Laporan')
@section('content')
<div class="top dashboard-top" style="align-items:center;margin-bottom:20px">
  <div>
    <h1 style="margin:0 0 4px">Laporan Bulanan</h1>
    <p class="muted small" style="margin:0">Rekapitulasi pekerjaan dan antrian jadwal pemeliharaan siap cetak untuk HRD / Manajemen.</p>
  </div>
  <div class="actions noprint dashboard-top-actions report-top-actions">
    <form method="GET" style="margin:0">
      <input class="input" type="month" name="bulan" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" style="padding:9px 12px;font-weight:700">
    </form>
    <button class="btn primary" onclick="window.print()">
      @include('partials.icon', ['n' => 'printer']) Cetak / PDF
    </button>
  </div>
</div>

<article class="paper report-paper">
  <div class="report-header-row" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
    <div>
      <h1 style="margin:0 0 4px;font-size:22px">Laporan Perbaikan dan Maintenance Mesin</h1>
      <div class="muted small">Monitoring Mesin · Untuk HRD / Manager</div>
    </div>
    <div class="report-meta" style="text-align:right">
      <b>Periode: {{ $month->translatedFormat('F Y') }}</b>
      <div class="muted small">Dicetak: {{ now()->translatedFormat('d M Y') }} · oleh {{ auth()->user()->name }}</div>
    </div>
  </div>

  <div class="sumbox">
    <div><b>{{ number_format($summary['total'], 0, ',', '.') }}</b><span>Total pekerjaan</span></div>
    <div><b style="color:var(--ok)">{{ number_format($summary['selesai'], 0, ',', '.') }}</b><span>Selesai</span></div>
    <div><b style="color:var(--warn)">{{ number_format($summary['dikerjakan'], 0, ',', '.') }}</b><span>Sedang dikerjakan</span></div>
    <div><b style="color:var(--r600)">{{ number_format($summary['terlambat'], 0, ',', '.') }}</b><span>Terlambat</span></div>
  </div>

  <h3 style="margin:22px 0 10px;font-size:16px">Riwayat Pekerjaan</h3>

  {{-- Desktop & Print Table View --}}
  <div class="report-table-wrap">
    <table>
      <thead><tr><th>Mesin</th><th>Sebelum</th><th>Tindakan</th><th>Sesudah</th><th>Status</th></tr></thead>
      <tbody>
      @forelse ($jobs as $j)
        @php $b = $j->mediaOf('before')->first() ?? $j->mediaOf('report')->first(); $a = $j->mediaOf('after')->first(); @endphp
        <tr>
          <td><div class="tiny">{{ $j->created_at->translatedFormat('d M Y') }} · {{ $j->code }}</div><b>{{ $j->machine->label }}</b><div class="muted small">{{ $j->machine->location }}</div>@include('partials.chip', ['v' => $j->type])</td>
          <td><div class="thumb ba" style="width:84px;height:66px">@if ($b)<img src="{{ $b->url }}" alt="">@else Sebelum @endif</div><div class="tiny">{{ \Illuminate\Support\Str::limit($j->complaint, 40) }}</div></td>
          <td><b>{{ $j->action ?? ($j->status === 'selesai' ? 'Selesai' : 'Sedang dikerjakan') }}</b><div class="tiny">Sparepart: {{ $j->spareparts ?? '-' }}</div></td>
          <td>@if ($a)<div class="thumb" style="width:84px;height:66px"><img src="{{ $a->url }}" alt=""></div>@else<span class="tiny">Belum diunggah</span>@endif</td>
          <td>@include('partials.chip', ['v' => $j->display_status])</td>
        </tr>
      @empty <tr><td colspan="5" class="empty">Tidak ada pekerjaan pada periode ini.</td></tr> @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile & Tablet Cards View --}}
  <div class="report-mobile-list noprint">
    @forelse ($jobs as $j)
      @php
        $b = $j->mediaOf('before')->first() ?? $j->mediaOf('report')->first();
        $a = $j->mediaOf('after')->first();
      @endphp
      <div class="dash-m-card border-red" style="padding:12px;gap:8px">
        <div class="dash-m-head">
          <div class="dash-m-code-group">
            <span class="badge-code">{{ $j->code }}</span>
            <span class="tiny muted">{{ $j->created_at->translatedFormat('d M Y') }}</span>
          </div>
          <div class="dash-m-chips">
            @include('partials.chip', ['v' => $j->type])
            @include('partials.chip', ['v' => $j->display_status])
          </div>
        </div>

        <div class="dash-m-machine">
          <div class="dash-m-machine-title">{{ $j->machine->name }}</div>
          <div class="dash-m-machine-sub">
            <span class="badge-code" style="font-size:10px">{{ $j->machine->code }}</span> · {{ $j->machine->location }}
          </div>
        </div>

        {{-- Foto Sebelum & Sesudah --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:4px 0">
          <div>
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700">Foto Sebelum:</span>
            <div style="height:90px;border-radius:8px;overflow:hidden;border:1px solid var(--line);background:#1e293b">
              @if ($b)
                <img src="{{ $b->url }}" alt="Sebelum" style="width:100%;height:100%;object-fit:cover;display:block">
              @else
                <div style="width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-size:10px">Belum ada</div>
              @endif
            </div>
            <div class="tiny muted" style="margin-top:2px">{{ \Illuminate\Support\Str::limit($j->complaint, 35) }}</div>
          </div>
          <div>
            <span class="tiny muted" style="display:block;margin-bottom:3px;font-weight:700">Foto Sesudah:</span>
            <div style="height:90px;border-radius:8px;overflow:hidden;border:1px solid var(--line);background:#1e293b">
              @if ($a)
                <img src="{{ $a->url }}" alt="Sesudah" style="width:100%;height:100%;object-fit:cover;display:block">
              @else
                <div style="width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-size:10px">Belum ada</div>
              @endif
            </div>
          </div>
        </div>

        <div class="dash-m-desc-box" style="padding:8px 10px;font-size:12px">
          <b>Tindakan:</b> {{ $j->action ?? ($j->status === 'selesai' ? 'Selesai' : 'Sedang dikerjakan') }}
          <div class="tiny muted" style="margin-top:2px">Sparepart: {{ $j->spareparts ?? '-' }}</div>
        </div>
      </div>
    @empty
      <div class="dash-m-empty" style="padding:24px 14px">
        <div class="tiny muted">Tidak ada pekerjaan pada periode ini.</div>
      </div>
    @endforelse
  </div>

  <h3 style="margin:22px 0 10px;font-size:16px">Jadwal Perbaikan Berikutnya</h3>

  {{-- Desktop & Print Table View --}}
  <div class="report-table-wrap">
    <table>
      <thead><tr><th>Mesin</th><th>Jenis perawatan</th><th>Interval</th><th>Jatuh tempo</th></tr></thead>
      <tbody>
      @forelse ($upcoming as $u)
        <tr>
          <td><b>{{ $u->machine->label }}</b></td>
          <td>{{ $u->task }}</td>
          <td>{{ $u->interval_label ?? '-' }}</td>
          <td><b style="color:var(--r600)">{{ $u->due_date->translatedFormat('d M Y') }}</b></td>
        </tr>
      @empty
        <tr><td colspan="4" class="empty">Tidak ada jadwal mendatang pada periode ini.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile & Tablet View --}}
  <div class="report-mobile-list noprint" style="gap:8px">
    @forelse ($upcoming as $u)
      <div class="dash-m-card border-amber" style="padding:10px 12px;gap:4px">
        <div class="row between">
          <div style="font-weight:700;font-size:13px">{{ $u->machine->label }}</div>
          <b class="tiny" style="color:var(--r600)">{{ $u->due_date->translatedFormat('d M Y') }}</b>
        </div>
        <div class="tiny muted">{{ $u->task }} · Siklus: {{ $u->interval_label ?? '-' }}</div>
      </div>
    @empty
      <div class="tiny muted" style="padding:14px;text-align:center">Tidak ada jadwal mendatang pada periode ini.</div>
    @endforelse
  </div>

  <div class="sign" style="margin-top:28px">
    <div>Disiapkan oleh (Teknisi)<br><span class="tiny">[Nama] · [Tanggal]</span></div>
    <div>Diketahui (Manager / HRD)<br><span class="tiny">[Nama] · [Tanggal]</span></div>
  </div>
  <p class="tiny" style="margin-top:22px">Dibuat otomatis dari data pekerjaan. Jadwal berikutnya dihitung dari interval maintenance tiap mesin.</p>
</article>
@endsection
