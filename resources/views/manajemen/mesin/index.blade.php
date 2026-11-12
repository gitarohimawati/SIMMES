@extends('layouts.manager')
@section('title', 'Data Mesin')
@section('content')
<div class="top">
  <div class="top-row">
    <h1>Data Mesin</h1>
    <div class="actions">
      <form id="imp" method="POST" action="{{ route('manajemen.mesin.import') }}" enctype="multipart/form-data">@csrf
        <input type="file" name="file" accept=".csv,text/csv" hidden onchange="this.form.submit()">
        <button type="button" class="btn" onclick="this.previousElementSibling.click()" title="Format CSV: kode,nama,kategori,tipe,serial,tgl_beli,lokasi,interval_bulan">Import Excel (CSV)</button>
      </form>
      @include('partials.quick-machine-modal')
    </div>
  </div>
</div>
<form class="filters repair-filters machine-filters" method="GET" autocomplete="off">
  <div class="repair-search-control">
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode, nama, tipe, atau lokasi mesin" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true">
    <button class="btn primary" type="submit">Cari</button>
  </div>
  <select class="select" name="location" onchange="this.form.submit()">
    <option value="">Semua lokasi</option>
    @foreach ($locations as $l)<option @selected(request('location') === $l)>{{ $l }}</option>@endforeach
  </select>
  <select class="select" name="status" onchange="this.form.submit()">
    <option value="">Semua status</option>
    @foreach (['aktif','dilaporkan','diproses','nonaktif'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <select class="select" name="sort" aria-label="Urutkan mesin" onchange="this.form.submit()">
    <option value="code" @selected(request('sort', 'code') === 'code')>Urutkan: Kode</option>
    <option value="name" @selected(request('sort') === 'name')>Nama mesin</option>
    <option value="location" @selected(request('sort') === 'location')>Lokasi</option>
    <option value="service" @selected(request('sort') === 'service')>Servis terbaru</option>
  </select>
  @if (request()->hasAny(['q', 'location', 'status', 'sort']))
    <a class="btn repair-reset" href="{{ route('manajemen.mesin.index') }}">Reset</a>
  @endif
</form>
<div class="card tablewrap">
  <table>
    <thead><tr><th>Kode</th><th>Nama mesin</th><th>Lokasi</th><th>Servis terakhir</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
    <tbody>
    @forelse ($machines as $m)
      <tr>
        <td><b>{{ $m->code }}</b></td><td>{{ $m->name }}</td><td>{{ $m->location }}</td>
        <td>{{ $m->last_service_at?->translatedFormat('d M Y') ?? '-' }}</td>
        <td>@include('partials.chip', ['v' => $m->status])</td>
        <td style="text-align:right">
          <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:6px">
            <a class="btn-icon detail" href="{{ route('manajemen.mesin.show', $m) }}" title="Lihat detail mesin">
              @include('partials.icon', ['n' => 'eye'])
            </a>
            <a class="btn-icon edit" href="{{ route('manajemen.mesin.edit', $m) }}" title="Edit data mesin">
              @include('partials.icon', ['n' => 'edit'])
            </a>
            <form method="POST" action="{{ route('manajemen.mesin.destroy', $m) }}" data-confirm="Apakah Anda yakin ingin menghapus mesin {{ $m->code }} ({{ $m->name }})?" style="display:inline;margin:0">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-icon danger" title="Hapus data mesin">
                @include('partials.icon', ['n' => 'trash'])
              </button>
            </form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">Belum ada mesin.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $machines->links() }}</div>
@endsection
