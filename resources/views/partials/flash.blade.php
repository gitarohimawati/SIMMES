@if (session('ok'))
  <div class="flash flashm">
    <span style="font-size:16px;line-height:1;color:var(--brand)">✓</span>
    <span style="flex:1">{{ session('ok') }}</span>
  </div>
@endif
@if (session('error'))
  <div class="flash bad flashm">
    <span style="font-size:16px;line-height:1">⚠️</span>
    <span style="flex:1">{{ session('error') }}</span>
  </div>
@endif
@if (isset($errors) && $errors->any())
  <div class="flash bad flashm">
    <span style="font-size:16px;line-height:1">⚠️</span>
    <div style="flex:1">
      @foreach ($errors->all() as $e)
        <div>{{ $e }}</div>
      @endforeach
    </div>
  </div>
@endif

