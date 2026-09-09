<x-dashboard.frame>
    <x-dashboard.header :heading="$title">
        <x-slot name="actions">{{ $title_right ?? '' }}</x-slot>
    </x-dashboard.header>
    <div class="ar-workspace__content min-w-0">{{ $page ?? $slot }}</div>
</x-dashboard.frame>
