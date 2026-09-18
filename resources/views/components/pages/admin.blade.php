<x-dashboard.frame label="Sekcie" navigation-label="Administrácia">
    <x-dashboard.header :heading="$title">
        <x-slot name="actions">{{ $title_right ?? '' }}</x-slot>
    </x-dashboard.header>

    @php
        $filterSelects = match (Route::currentRouteName()) {
            'admin.user.index' => [
                'status' => [
                    'label' => __('model_status.filter.label'),
                    'placeholder' => __('model_status.filter.all'),
                    'options' => collect(\App\Models\User::statusOptions())
                        ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                        ->put(\App\Filters\UserFilters::DELETED, __('model_status.filter.deleted'))
                        ->all(),
                ],
            ],
            default => [],
        };

        $filterOptions = match (Route::currentRouteName()) {
            'admin.user.index' => [],
            'admin.canal.index', 'admin.comment.index' => ['unpublished', 'deletedAt'],
            'admin.post.index' => ['unpublished', 'deletedAt', 'videoAvailable'],
            'admin.prayer.index' => ['fulfilled', 'deletedAt'],
            default => null,
        };
        // Filtre dlaždíc súhrnu — lišta ich musí pri hľadaní a výbere niesť ďalej.
        $filterKeep = match (Route::currentRouteName()) {
            'admin.user.index' => ['fresh', 'active', 'never', 'unverified', 'via'],
            'admin.canal.index' => ['fresh', 'orphans', 'silent', 'youtubeOff', 'month'],
            default => [],
        };
    @endphp
    @if ($filterOptions !== null)
        <x-filters.bar class="mb-5" :filters="$filterOptions" :selects="$filterSelects" :keep="$filterKeep" search="Hľadať" />
    @endif

    <div class="ar-admin__content min-w-0">
        {{ $page }}
    </div>
</x-dashboard.frame>
