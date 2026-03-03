@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Diascora" logo="{{ asset('icon-192.png') }}" {{ $attributes }} />
@else
    <flux:brand name="Diascora" logo="{{ asset('icon-192.png') }}" {{ $attributes }} />
@endif
