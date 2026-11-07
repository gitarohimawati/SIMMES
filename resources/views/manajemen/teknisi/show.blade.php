@extends('layouts.manager')
@section('title', $tech->name)
@section('content')
<div class="top">
  <div>
    <h1>{{ $tech->name }}</h1>
    <p>NIK {{ $tech->nik }} · {{ $tech->department }} · WA: {{ $tech->whatsapp ?? '-' }}</p>
  </div>
  <div class="actions">
    @if ($tech->whatsapp)
      @php
        $directWa = \App\Services\WhatsAppService::makeDirectLink($tech->whatsapp, "Halo {$tech->name}, salam dari manajemen SIMMES.");
      @endphp
      @if ($directWa)
        <a href="{{ $directWa }}" target="_blank" rel="noopener noreferrer" class="btn" style="color:#15803d;border-color:#bbf7d0;background:#f0fdf4">
          <svg class="i" style="width:16px;height:16px" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          Chat WhatsApp
        </a>
      @endif
    @endif
    <a class="btn primary" href="{{ route('manajemen.pengguna.edit', $tech) }}">
      @include('partials.icon', ['n' => 'edit']) Ubah Nama & Profil
    </a>
    <a class="btn" href="{{ route('manajemen.teknisi.index') }}">← Kembali</a>
  </div>
</div>
<div class="card tablewrap"><h3>Pekerjaan terbaru</h3>
  <table><thead><tr><th>Tiket</th><th>Mesin</th><th>Progress</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead><tbody>
  @forelse ($tickets as $t)
    <tr>
      <td><a class="link" href="{{ route('manajemen.perbaikan.show', $t) }}"><b>{{ $t->code }}</b></a></td>
      <td>{{ $t->machine->label }}</td>
      <td><div class="progress"><i style="width:{{ $t->progress }}%"></i></div></td>
      <td>@include('partials.chip', ['v' => $t->display_status])</td>
      <td style="text-align:right">
        <a href="{{ route('manajemen.perbaikan.show', $t) }}" class="btn-icon detail" title="Lihat Detail Tiket">
          @include('partials.icon', ['n' => 'eye'])
        </a>
      </td>
    </tr>
  @empty <tr><td colspan="5" class="empty">Belum ada pekerjaan.</td></tr> @endforelse
  </tbody></table></div>
@endsection
