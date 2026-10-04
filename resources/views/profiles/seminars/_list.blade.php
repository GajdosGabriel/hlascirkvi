{{--
    Riadky výpisu seminárov v správe kanála. Vzhľad drží .ar-item, rovnako ako
    výpis článkov — obal (panel) patrí stránke, ktorá zoznam vkladá.

    Očakáva: $seminars (s withCount('posts')), $canal.
--}}
@forelse ($seminars as $seminar)

    <article class="ar-item">
        <div class="ar-item__body">
            <a href="{{ route('profile.canals.seminars.show', [$seminar->canal_id, $seminar->id]) }}"
               class="ar-item__title">
                <seminar-title :seminar="{{ $seminar }}">{{ $seminar->title }}</seminar-title>
            </a>

            <div class="ar-item__meta">
                <span>{{ $seminar->kind_label }}</span>
                <span>{{ $seminar->published ? 'Zverejnené' : 'Koncept' }}</span>
                @php
                    $count = (int) ($seminar->posts_count ?? 0);
                @endphp

                @if ($showChannel ?? false)
                    <a class="ar-link" href="{{ route('profile.canals.seminars.index', $seminar->canal_id) }}">{{ $seminar->canal->title }}</a>
                @endif
                <span>
                    <i class="ph ph-newspaper"></i>
                    {{ $count }}
                    {{ $count === 1 ? 'článok' : ($count >= 2 && $count <= 4 ? 'články' : 'článkov') }}
                </span>

                @if ($seminar->created_at)
                <time datetime="{{ $seminar->created_at->toIso8601String() }}">
                    {{ $seminar->created_at->locale('sk')->isoFormat('D. M. YYYY') }}
                </time>
                @endif
            </div>
        </div>

        @can('update', $seminar)
            <div class="ar-item__actions">
                @include('seminars._actions')
            </div>
        @endcan
    </article>

@empty
    <x-dashboard.empty>Zatiaľ nemáte žiadne kolekcie. Vytvorte prvú a pridajte do nej videá.</x-dashboard.empty>
@endforelse
