<div {{ $getExtraAttributeBag() }}>
    @php
        $options = json_decode($getState(), true) ?? [];
        $optionsCount = count($options);
    @endphp

    @if ($optionsCount === 1)
        <x-filament::badge>{{ $options[0] }}</x-filament::badge>
    @elseif ($optionsCount === 2)
        <x-filament::badge>{{ $options[0] }}</x-filament::badge>
        <x-filament::badge>{{ $options[1] }}</x-filament::badge>
    @elseif ($optionsCount > 2)
        <x-filament::badge>{{ $options[0] }}</x-filament::badge>
        <x-filament::badge>{{ $options[1] }}</x-filament::badge>
        + {{ $optionsCount - 2 }} more
    @else
        <span class="text-gray-500">No options</span>
    @endif
</div>
