@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand
        :name="config('app.name', 'My Finance')"
        {{ $attributes }}
        class="gap-3"
    >
        <x-slot name="logo">
            <div class="flex size-11 items-center justify-center">
                <img
                    src="{{ asset('images/MyFinance.png') }}"
                    alt="My Finance"
                    class="h-20 w-20 object-contain"
                >
            </div>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand
        :name="config('app.name', 'My Finance')"
        {{ $attributes }}
    >
        <x-slot name="logo">
            <img
                src="{{ asset('images/MyFinance.png') }}"
                alt="My Finance"
                class="h-20 w-20 object-contain"
            >
        </x-slot>
    </flux:brand>
@endif