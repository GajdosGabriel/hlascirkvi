<x-dashboard.frame>
    <x-dashboard.header :heading="$heading">
        <x-slot name="lead">{{ $lead ?? '' }}</x-slot>
        <x-slot name="actions">{{ $actions ?? '' }}</x-slot>
    </x-dashboard.header>

    {{ $slot }}
</x-dashboard.frame>
