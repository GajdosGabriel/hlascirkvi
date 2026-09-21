@extends('layouts.admin')

@section('title')
    <title>Administrácia</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Administrácia</x-slot>
        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $compact = fn ($value) => (int) $value >= 1000000
                    ? number_format((int) $value / 1000000, 1, ',', ' ') . ' mil.'
                    : $num($value);
                $change = [\App\Services\Dashboard\AdminDashboardStats::class, 'change'];
                $postUrl = fn ($id, $slug) => route('post.show', [$id, $slug]);

                $dayNames = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];
                $dayNamesLong = ['v pondelok', 'v utorok', 'v stredu', 'vo štvrtok', 'v piatok', 'v sobotu', 'v nedeľu'];

                // Čo si žiada zásah. Zobrazí sa len to, čoho je viac ako nula;
                // každá položka vedie na výpis, ktorý tie záznamy ukáže.
                $todo = array_filter([
                    ['count' => $posts->broken, 'icon' => 'fas fa-video-slash', 'label' => 'videí už na YouTube nie je', 'href' => route('admin.post.index', ['videoAvailable' => 1])],
                    ['count' => $canals->youtube_off, 'icon' => 'fab fa-youtube', 'label' => 'kanálom sa zastavil import', 'href' => route('admin.canal.index', ['youtubeOff' => 1])],
                    ['count' => $canals->orphans, 'icon' => 'fas fa-user-slash', 'label' => 'kanálov nemá správcu', 'href' => route('admin.canal.index', ['orphans' => 1])],
                    ['count' => $comments->unpublished, 'icon' => 'far fa-comment-dots', 'label' => 'komentárov čaká na schválenie', 'href' => route('admin.comment.index', ['unpublished' => 1])],
                    ['count' => $users->blocked, 'icon' => 'fas fa-ban', 'label' => 'zablokovaných účtov', 'href' => route('admin.user.index', ['status' => 'blocked']), 'info' => true],
                    ['count' => $posts->waiting, 'icon' => 'far fa-clock', 'label' => 'článkov čaká v bufferi', 'href' => route('admin.buffer.index'), 'info' => true],
                ], fn ($item) => (int) $item['count'] > 0);

                $rhythmAlpha = fn ($value) => $rhythm->max > 0 ? round(sqrt($value / $rhythm->max), 3) : 0;
                $publishedMax = max(1, max($rhythm->published));

                $denominationColors = ['catholic' => '#b91c1c', 'evangelical' => '#1d4ed8', '' => '#d1d5db'];
                $denominationTotal = max(1, array_sum($denominations));

                $favoriteParts = [
                    ['label' => 'články', 'value' => $favorites->posts, 'color' => '#b91c1c'],
                    ['label' => 'modlitby', 'value' => $favorites->prayers, 'color' => '#c9a227'],
                    ['label' => 'kanály', 'value' => $favorites->canals, 'color' => '#101828'],
                    ['label' => 'komentáre', 'value' => $favorites->comments, 'color' => '#9ca3af'],
                ];
                $favoriteTotal = max(1, array_sum(array_column($favoriteParts, 'value')));

                // Pre porovnanie v zaujímavostiach: počet obyvateľov SR (ŠÚ SR, 2025).
                $slovakia = 5_420_000;
            @endphp

            {{-- ---- Dnes ---------------------------------------------------- --}}

            @if ($liturgy)
                <a href="{{ route('readings.show') }}" class="ar-liturgy mb-6" style="--ar-liturgy: {{ $liturgy->color->hex() }}">
                    <span class="ar-liturgy__dot" aria-hidden="true"></span>
                    <span class="ar-liturgy__title">{{ $liturgy->title }}</span>
                    <span>{{ $liturgy->season->label() }} · {{ $liturgy->rank->label() }} · liturgická farba {{ $liturgy->color->label() }}</span>
                    <span class="ml-auto text-xs">čítania na dnes <i class="fas fa-arrow-right ml-1"></i></span>
                </a>
            @endif

            <div class="mb-6">
                <p class="ar-kicker mb-3">
                    Celý web · {{ $now->format('j. n. Y') }} · údaje z {{ $now->format('H:i') }}
                    <a href="{{ route('admin.home.index', ['refresh' => 1]) }}" class="ml-1 underline" title="Prepočítať čísla hneď">Obnoviť</a>
                </p>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <x-dashboard.metric label="Zhliadnutia dnes" :value="$num($views->today)">
                        včera {{ $num($views->yesterday) }} · priemer {{ $num($views->average) }}/deň
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Zhliadnutia za 30 dní" :value="$compact($views->current)">
                        @include('admins.home._delta', ['value' => $views->change]) proti predošlým 30 dňom
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Nové články" :value="$num($posts->new)">
                        @include('admins.home._delta', ['value' => $change($posts->new, $posts->new_previous)]) za 30 dní · {{ $num($posts->published_today) }} dnes
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Nové komentáre" :value="$num($comments->new)">
                        @include('admins.home._delta', ['value' => $change($comments->new, $comments->new_previous)]) za 30 dní
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Používatelia" :value="$num($users->total)">
                        +{{ $num($users->new) }} za 30 dní · {{ $num($users->active) }} aktívnych
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Kanály" :value="$num($canals->total)">
                        {{ $num($canals->published) }} zverejnených · +{{ $num($canals->new) }} nových
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Zhliadnutia celkovo" :value="$compact($posts->views)">
                        {{ $num($posts->total) }} článkov v archíve
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Otvorené modlitby" :value="$num($prayers->open)">
                        {{ $num($prayers->fulfilled) }} vypočutých · +{{ $num($prayers->new) }} za 30 dní
                    </x-dashboard.metric>
                </div>
            </div>

            {{-- ---- Na čo sa pozrieť ---------------------------------------- --}}

            <div class="mb-6">
                <p class="ar-kicker mb-3">Na čo sa pozrieť</p>
                @if ($todo)
                    <div class="ar-todo">
                        @foreach ($todo as $item)
                            <a href="{{ $item['href'] }}" @class(['ar-todo__item', 'ar-todo__item--info' => $item['info'] ?? false])>
                                <span class="ar-todo__icon"><i class="{{ $item['icon'] }}"></i></span>
                                <span>{{ $item['label'] }}</span>
                                <span class="ar-todo__count">{{ $num($item['count']) }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="ar-todo">
                        <div class="ar-todo__item ar-todo__item--info">
                            <span class="ar-todo__icon"><i class="fas fa-check"></i></span>
                            <span>Všetko je v poriadku, nič nečaká na zásah.</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid gap-6 lg:grid-cols-12">

                {{-- ---- Ľavý stĺpec: vývoj a rebríčky ----------------------- --}}
                <div class="min-w-0 space-y-6 lg:col-span-8">

                    @include('dashboard._chart', [
                        'chartLabel' => 'Zhliadnutia článkov celého webu za posledných 30 dní',
                        'activityLabel' => 'Nové články po mesiacoch',
                    ])

                    <x-dashboard.panel title="Kedy sa číta" flush>
                        <x-slot name="note">
                            @if ($rhythm->bestDay !== null)
                                najviac {{ $dayNamesLong[$rhythm->bestDay] }} okolo {{ $rhythm->bestHour }}:00
                            @else
                                zatiaľ bez dát
                            @endif
                        </x-slot>

                        <div class="ar-panel__body ar-heat__wrap">
                            <div class="ar-heat" role="img"
                                 aria-label="Zhliadnutia podľa dňa v týždni a hodiny za posledné {{ \App\Services\Dashboard\AdminDashboardStats::RHYTHM_DAYS }} dni">
                                @foreach ($rhythm->grid as $day => $hours)
                                    <span class="ar-heat__day">{{ $dayNames[$day] }}</span>
                                    @foreach ($hours as $hour => $count)
                                        <span @class(['ar-heat__cell', 'ar-heat__cell--zero' => $count === 0])
                                              style="--a: {{ $rhythmAlpha($count) }}"
                                              title="{{ $dayNames[$day] }} {{ $hour }}:00 — {{ $num($count) }} zhliadnutí"></span>
                                    @endforeach
                                @endforeach

                                {{-- Pod mapou hodiny, keď články vychádzajú. --}}
                                <span class="ar-heat__day" title="Zverejnené články">zv.</span>
                                @foreach ($rhythm->published as $hour => $count)
                                    <span class="ar-heat__pub"
                                          style="height: {{ $count ? max(2, round($count / $publishedMax * 18)) : 0 }}px"
                                          title="{{ $hour }}:00 — zverejnených {{ $num($count) }}"></span>
                                @endforeach

                                <span></span>
                                @foreach (range(0, 23) as $hour)
                                    <span class="ar-heat__hour">{{ $hour % 3 === 0 ? $hour : '' }}</span>
                                @endforeach
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                <span class="ar-heat__legend">
                                    menej
                                    @foreach ([0.08, 0.3, 0.55, 0.8, 1] as $a)
                                        <span class="ar-heat__cell" style="--a: {{ $a }}"></span>
                                    @endforeach
                                    viac
                                </span>
                                <span class="ar-heat__legend">
                                    posledné {{ \App\Services\Dashboard\AdminDashboardStats::RHYTHM_DAYS }} dni · sivý pásik = kedy články vychádzajú
                                </span>
                            </div>
                        </div>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Najčítanejšie za 30 dní" flush>
                        <x-slot name="note"><a href="{{ route('admin.statistic.index', ['lastDays' => 30]) }}" class="ar-link">celá štatistika</a></x-slot>

                        @forelse ($topPosts as $index => $row)
                            <a href="{{ $postUrl($row->id, $row->slug) }}" class="ar-row">
                                <span class="ar-rank {{ $index === 0 ? 'ar-rank--first' : '' }}">{{ $index + 1 }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="ar-row__title ar-clamp-2">{{ $row->title }}</span>
                                    <span class="ar-row__meta block">{{ $row->canal ?? 'bez kanála' }} · {{ $num($row->count_view) }} celkovo</span>
                                </span>
                                <span class="ar-row__value">{{ $num($row->period_views) }}</span>
                            </a>
                        @empty
                            <x-dashboard.empty>Za posledných 30 dní nie sú namerané žiadne zhliadnutia.</x-dashboard.empty>
                        @endforelse
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Kanály, ktoré ťahajú" flush>
                        <x-slot name="note">zhliadnutia za 30 dní</x-slot>

                        @forelse ($risingCanals as $index => $row)
                            <a href="{{ route('organizations.show', [$row->id]) }}" class="ar-row">
                                <span class="ar-rank {{ $index === 0 ? 'ar-rank--first' : '' }}">{{ $index + 1 }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="ar-row__title ar-clamp-2">{{ $row->title }}</span>
                                    <span class="ar-row__meta block">
                                        @if ($row->previous > 0)
                                            predtým {{ $num($row->previous) }}
                                        @else
                                            v predošlom období bez zhliadnutí
                                        @endif
                                    </span>
                                </span>
                                <span class="ar-row__value">
                                    {{ $num($row->current) }}
                                    @include('admins.home._delta', ['value' => $row->change])
                                </span>
                            </a>
                        @empty
                            <x-dashboard.empty>Žiadny kanál zatiaľ nemá namerané zhliadnutia.</x-dashboard.empty>
                        @endforelse
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Posledné komentáre" flush>
                        <x-slot name="note">{{ $num($comments->total) }} spolu</x-slot>

                        @forelse ($latestComments as $row)
                            <a href="{{ $row->post_id ? $postUrl($row->post_id, $row->post_slug) . '#komentare' : route('admin.comment.index') }}" class="ar-row">
                                <span class="min-w-0 flex-1">
                                    <span class="ar-row__title ar-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($row->body), 180) }}</span>
                                    <span class="ar-row__meta block">
                                        @if ($row->youtube_comment_id)<i class="fab fa-youtube text-red-600"></i>@endif
                                        {{ $row->user_name ?: 'Anonym' }}
                                        · {{ \Carbon\Carbon::parse($row->created_at)->diffForHumans() }}
                                        @if ($row->post_title)
                                            · pod <strong class="font-semibold text-[color:var(--ar-ink-soft)]">{{ \Illuminate\Support\Str::limit($row->post_title, 60) }}</strong>
                                        @endif
                                    </span>
                                </span>
                            </a>
                        @empty
                            <x-dashboard.empty>Zatiaľ žiadne komentáre.</x-dashboard.empty>
                        @endforelse

                        <x-slot name="footer">
                            <a href="{{ route('admin.comment.index') }}" class="ar-link text-gray-500 hover:text-gray-900">Všetky komentáre <i class="fas fa-arrow-right ml-1"></i></a>
                        </x-slot>
                    </x-dashboard.panel>
                </div>

                {{-- ---- Pravý stĺpec: zaujímavosti a noví ------------------- --}}
                <div class="min-w-0 space-y-6 lg:col-span-4">

                    <x-dashboard.panel title="Viete, že…" flush>
                        @if ($posts->views > 0)
                            <div class="ar-fact">
                                <span class="ar-fact__icon"><i class="fas fa-eye"></i></span>
                                <p class="ar-fact__text">
                                    Články majú spolu <strong>{{ $compact($posts->views) }}</strong> zhliadnutí —
                                    to je, akoby si každý obyvateľ Slovenska pozrel
                                    <strong>{{ number_format($posts->views / $slovakia, 1, ',', ' ') }}×</strong> niektoré video.
                                </p>
                            </div>
                        @endif

                        @if ($posts->duration > 0)
                            @php $hoursOfVideo = intdiv((int) $posts->duration, 3600); @endphp
                            <div class="ar-fact">
                                <span class="ar-fact__icon"><i class="fas fa-film"></i></span>
                                <p class="ar-fact__text">
                                    V archíve je <strong>{{ $num($hoursOfVideo) }} hodín</strong> videa. Pozerať ho bez prestávky
                                    by trvalo <strong>{{ $num(round($hoursOfVideo / 24)) }} dní</strong>.
                                </p>
                            </div>
                        @endif

                        @if ($views->peak > 0)
                            <div class="ar-fact">
                                <span class="ar-fact__icon"><i class="fas fa-trophy"></i></span>
                                <p class="ar-fact__text">
                                    Najsilnejší deň za mesiac mal <strong>{{ $num($views->peak) }}</strong> zhliadnutí,
                                    to je {{ $views->average > 0 ? number_format($views->peak / $views->average, 1, ',', ' ') . '× priemer' : 'nad priemerom' }}.
                                </p>
                            </div>
                        @endif

                        @if ($posts->new > 0)
                            <div class="ar-fact">
                                <span class="ar-fact__icon"><i class="fas fa-seedling"></i></span>
                                <p class="ar-fact__text">
                                    Denne pribudne v priemere <strong>{{ number_format($posts->new / \App\Services\Dashboard\AdminDashboardStats::WINDOW, 1, ',', ' ') }}</strong> článku
                                    a <strong>{{ number_format($comments->new / \App\Services\Dashboard\AdminDashboardStats::WINDOW, 1, ',', ' ') }}</strong> komentára.
                                </p>
                            </div>
                        @endif

                        @if ($prayers->open + $prayers->fulfilled > 0)
                            <div class="ar-fact">
                                <span class="ar-fact__icon"><i class="fas fa-praying-hands"></i></span>
                                <p class="ar-fact__text">
                                    Za posledný mesiac prišlo <strong>{{ $num($prayers->new) }}</strong> prosieb o modlitbu,
                                    teda asi <strong>{{ $num(round($prayers->new / \App\Services\Dashboard\AdminDashboardStats::WINDOW)) }}</strong> denne.
                                </p>
                            </div>
                        @endif

                        @if ($users->total > 0)
                            <div class="ar-fact">
                                <span class="ar-fact__icon"><i class="fas fa-envelope-open-text"></i></span>
                                <p class="ar-fact__text">
                                    <strong>{{ round($users->unverified / $users->total * 100) }} %</strong> používateľov
                                    ({{ $num($users->unverified) }}) si ešte neoverilo e-mail.
                                </p>
                            </div>
                        @endif
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Kanály podľa zaradenia">
                        <div class="ar-split" role="img" aria-label="Podiel kanálov podľa cirkevného zaradenia">
                            @foreach ($denominations as $key => $count)
                                <span style="width: {{ round($count / $denominationTotal * 100, 2) }}%; background: {{ $denominationColors[$key] ?? '#9ca3af' }}"></span>
                            @endforeach
                        </div>
                        <div class="ar-split__legend">
                            @foreach ($denominations as $key => $count)
                                <span>
                                    <i style="background: {{ $denominationColors[$key] ?? '#9ca3af' }}"></i>
                                    {{ \App\Enums\Denomination::tryFrom($key)?->label() ?? 'nezaradené' }}
                                    <strong class="text-[color:var(--ar-ink)]">{{ $num($count) }}</strong>
                                </span>
                            @endforeach
                        </div>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Čo si ľudia obľúbili">
                        <x-slot name="note">{{ $num($favoriteTotal) }} srdiečok</x-slot>
                        <div class="ar-split" role="img" aria-label="Obľúbené podľa typu">
                            @foreach ($favoriteParts as $part)
                                <span style="width: {{ round($part['value'] / $favoriteTotal * 100, 2) }}%; background: {{ $part['color'] }}"></span>
                            @endforeach
                        </div>
                        <div class="ar-split__legend">
                            @foreach ($favoriteParts as $part)
                                <span><i style="background: {{ $part['color'] }}"></i>{{ $part['label'] }} <strong class="text-[color:var(--ar-ink)]">{{ $num($part['value']) }}</strong></span>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-[color:var(--ar-ink-soft)]">
                            <i class="far fa-bookmark mr-1"></i> {{ $num($favorites->saved) }} článkov uložených na neskôr
                        </p>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Obsah a automatika">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $num($buffer->queued) }}</span>
                                <span class="ar-stat__label">v rozvrhu bufferu</span>
                            </div>
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $buffer->next_at ? \Carbon\Carbon::parse($buffer->next_at)->format('H:i') : '—' }}</span>
                                <span class="ar-stat__label">{{ $buffer->next_at ? 'ďalší slot ' . \Carbon\Carbon::parse($buffer->next_at)->format('j. n.') : 'žiadny slot' }}</span>
                            </div>
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $num($posts->summarized) }}</span>
                                <span class="ar-stat__label">AI zhrnutí</span>
                            </div>
                            <div class="ar-stat">
                                <span class="ar-stat__value">${{ number_format((float) $ai->cost, 2, ',', ' ') }}</span>
                                <span class="ar-stat__label">AI tento mesiac · {{ $num($ai->calls) }}×</span>
                            </div>
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $num($comments->youtube) }}</span>
                                <span class="ar-stat__label">komentárov z YouTube</span>
                            </div>
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $num($comments->replies) }}</span>
                                <span class="ar-stat__label">odpovedí v diskusiách</span>
                            </div>
                        </div>
                        <x-slot name="footer">
                            <a href="{{ route('admin.buffer.index') }}" class="ar-link text-gray-500 hover:text-gray-900">Buffer</a>
                            <span class="mx-2 text-gray-300">·</span>
                            <a href="{{ route('admin.ai.index') }}" class="ar-link text-gray-500 hover:text-gray-900">AI zhrnutia</a>
                            <span class="mx-2 text-gray-300">·</span>
                            <a href="{{ route('admin.logs.index') }}" class="ar-link text-gray-500 hover:text-gray-900">Denník</a>
                        </x-slot>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Noví používatelia" flush>
                        @forelse ($newestUsers as $row)
                            <a href="{{ route('admin.user.edit', $row->id) }}" class="ar-row items-center">
                                <img class="ar-avatar" src="{{ $row->avatar ?: '/images/avatar.png' }}" alt="" loading="lazy" referrerpolicy="no-referrer">
                                <span class="min-w-0 flex-1">
                                    <span class="ar-row__title block truncate">{{ trim($row->first_name . ' ' . $row->last_name) ?: $row->email }}</span>
                                    <span class="ar-row__meta block">
                                        {{ \Carbon\Carbon::parse($row->created_at)->diffForHumans() }}
                                        @if ($row->last_login_via === 'google') · <i class="fab fa-google"></i> Google @endif
                                        @unless ($row->email_verified_at) · neoverený e-mail @endunless
                                    </span>
                                </span>
                            </a>
                        @empty
                            <x-dashboard.empty>Zatiaľ nikto.</x-dashboard.empty>
                        @endforelse
                        <x-slot name="footer">
                            <a href="{{ route('admin.user.index') }}" class="ar-link text-gray-500 hover:text-gray-900">Všetci používatelia <i class="fas fa-arrow-right ml-1"></i></a>
                        </x-slot>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Nové kanály" flush>
                        @forelse ($newestCanals as $row)
                            <a href="{{ route('organizations.show', [$row->id]) }}" class="ar-row">
                                <span class="min-w-0 flex-1">
                                    <span class="ar-row__title block truncate">{{ $row->title }}</span>
                                    <span class="ar-row__meta block">
                                        {{ \Carbon\Carbon::parse($row->created_at)->diffForHumans() }}
                                        @if ($row->youtube_channel) · <i class="fab fa-youtube"></i> YouTube @endif
                                        @unless ($row->published) · nezverejnený @endunless
                                    </span>
                                </span>
                            </a>
                        @empty
                            <x-dashboard.empty>Zatiaľ žiadne kanály.</x-dashboard.empty>
                        @endforelse
                        <x-slot name="footer">
                            <a href="{{ route('admin.canal.index') }}" class="ar-link text-gray-500 hover:text-gray-900">Všetky kanály <i class="fas fa-arrow-right ml-1"></i></a>
                        </x-slot>
                    </x-dashboard.panel>
                </div>
            </div>
        </x-slot>
    </x-pages.admin>
@endsection
