<x-dashboard.frame label="Administrácia" navigation-label="Administrácia">
    <x-dashboard.header :heading="$title">
        <x-slot name="actions">{{ $title_right ?? '' }}</x-slot>
    </x-dashboard.header>

    @php
        $filterOptions = match (Route::currentRouteName()) {
            'admin.user.index' => ['banned', 'deletedAt'],
            'admin.organization.index', 'admin.comment.index' => ['unpublished', 'deletedAt'],
            'admin.post.index' => ['unpublished', 'deletedAt', 'videoAvailable'],
            'admin.prayer.index' => ['fulfilled', 'deletedAt'],
            default => null,
        };
    @endphp
    @if ($filterOptions !== null)
        <x-filters.bar class="mb-5" :filters="$filterOptions" search="Hľadať" />
    @endif

    <div class="ar-admin__content min-w-0">
        {{ $page }}
    </div>
</x-dashboard.frame>
