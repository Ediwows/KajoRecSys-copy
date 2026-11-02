@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 items-center justify-center">
            <img src="{{ asset('images/BSUB-logo.jpg') }}" alt="Benguet State University logo" class="size-8 object-contain">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 items-center justify-center">
            <img src="{{ asset('images/BSUB-logo.jpg') }}" alt="Benguet State University logo" class="size-8 object-contain">
        </x-slot>
    </flux:brand>
@endif
