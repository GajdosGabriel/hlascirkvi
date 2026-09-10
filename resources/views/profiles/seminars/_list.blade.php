{{--
    Riadky výpisu seminárov v správe kanála. Vzhľad drží .ar-item, rovnako ako
    výpis článkov — obal (panel) patrí stránke, ktorá zoznam vkladá.

    Očakáva: $seminars (s withCount('posts')), $canal.
--}}
@forelse ($seminars as $seminar)

    <article class="ar-item">
        <div class="ar-item__body">
            <a href="{{ route('profile.canals.seminars.show', [$canal->id, $seminar->id]) }}"
               class="ar-item__title">
                <seminar-title :seminar="{{ $seminar }}"></seminar-title>
            </a>

            <div class="ar-item__meta">
                @php
                    $count = (int) ($seminar->posts_count ?? 0);
                @endphp

                <span>
                    <i class="far fa-newspaper"></i>
                    {{ $count }}
                    {{ $count === 1 ? 'článok' : ($count >= 2 && $count <= 4 ? 'články' : 'článkov') }}
                </span>

                <time datetime="{{ $seminar->created_at->toIso8601String() }}">
                    {{ $seminar->created_at->locale('sk')->isoFormat('D. M. YYYY') }}
                </time>
            </div>
        </div>

        @can('update', $seminar)
            <div class="ar-item__actions">
                <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'" />
            </div>
        @endcan
    </article>

@empty
    <x-dashboard.empty>Kanál zatiaľ nemá žiadny seminár.</x-dashboard.empty>
@endforelse
