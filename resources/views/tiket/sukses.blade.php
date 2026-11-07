@extends(auth()->user()->isTeknisi() ? 'layouts.technician' : 'layouts.manager')
@section('title', 'Laporan terkirim')
@section('heading', 'Laporan Perbaikan')
@section('nonav', '1')
@section('content')
@php
  $role = auth()->user()->role;
  $role = $role instanceof \BackedEnum ? $role->value : $role;
@endphp
<div style="max-width:460px;margin:0 auto;text-align:center">
  <div class="okbig">@include('partials.icon', ['n' => 'check'])</div>
  <h2 style="font-size:26px;font-weight:800">Laporan terkirim</h2>
  <p class="muted" style="margin-bottom:22px">Teknisi akan segera melihat laporanmu.</p>

  @if (session('broadcast_status'))
    @if (session('broadcast_status.success'))
      <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;font-size:13px;text-align:left;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px">
        <span style="font-size:18px;line-height:1">✅</span>
        <div>
          <b style="color:#047857">Notifikasi WA Terkirim</b><br>
          Pemberitahuan tugas telah otomatis disiarkan ke <b>{{ session('broadcast_status.count') }}</b> teknisi aktif.
        </div>
      </div>
    @else
      <div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 16px;border-radius:10px;font-size:13px;text-align:left;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px">
        <span style="font-size:18px;line-height:1">⚠️</span>
        <div>
          <b style="color:#b45309">Gateway WhatsApp Belum Terhubung:</b><br>
          <span style="font-size:12px">{{ session('broadcast_status.message') }}</span><br>
        </div>
      </div>
    @endif
  @endif

  <div class="card" style="text-align:left;padding:18px 22px;margin-bottom:18px">
    <div class="row between"><span class="muted">Nomor tiket</span><b>{{ $ticket->code }}</b></div>
    <div class="row between" style="margin:8px 0"><span class="muted">Mesin</span><b>{{ $ticket->machine->label }}</b></div>
    <div class="row between"><span class="muted">Status</span>@include('partials.chip', ['v' => $ticket->status])</div>
  </div>
  @php
    $myWa = auth()->user()->whatsapp ?: config('services.whatsapp.target');
    $directMyWa = $myWa ? \App\Services\WhatsAppService::makeDirectLink($myWa, \App\Services\WhatsAppService::buildBroadcastMessage($ticket)) : null;
  @endphp
  
  
  <a class="btn primary block" href="{{ route($role.'.lapor') }}">Lapor Lagi</a>
  <a class="btn block" style="margin-top:10px;border-color:var(--r400);color:var(--r700)" href="{{ route($role === 'teknisi' ? 'teknisi.home' : 'manajemen.dashboard') }}">Keluar</a>
</div>
@endsection