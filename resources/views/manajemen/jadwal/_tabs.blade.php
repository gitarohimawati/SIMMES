<div class="tabs-bar maintenance-tabs-bar">
  <div class="tabs">
    <a href="{{ route('manajemen.jadwal.index') }}" class="{{ request()->routeIs('manajemen.jadwal.index') ? 'on' : '' }}">Daftar</a>
    <a href="{{ route('manajemen.jadwal.kalender') }}" class="{{ request()->routeIs('manajemen.jadwal.kalender') ? 'on' : '' }}">Kalender Bulanan</a>
  </div>
  <div class="actions">
    @include('partials.quick-schedule-modal')
  </div>
</div>
