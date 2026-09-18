{{--
    Podrobnosti kanála len pre administrátora (admin/canal). Počty a posledný
    príspevok prichádzajú z withCount/withMax v Admin\CanalController —
    bez nich sa blok nezobrazuje s nulami, ale s pomlčkou.
--}}
@php
    $num = fn ($value) => number_format((int) $value, 0, ',', ' ');

    $registered = $canal->created_at;
    $lastPost = $canal->posts_max_created_at ? \Illuminate\Support\Carbon::parse($canal->posts_max_created_at) : null;
    $daysSilent = $lastPost ? (int) $lastPost->diffInDays(now()) : null;

    // Aktivita podľa posledného príspevku. Hranica „utíchol“ je rovnaká ako
    // pri filtri silent, aby sa štítok a dlaždica nad výpisom nerozchádzali.
    [$activityLabel, $activityClass] = match (true) {
        $lastPost === null => ['Bez príspevkov', 'ar-badge--count'],
        $daysSilent <= 30 => ['Aktívny', 'ar-badge--ok'],
        $daysSilent <= \App\Filters\CanalFilters::SILENT_DAYS => ['Stíchnutý', 'ar-badge--count'],
        default => ['Utíchol', 'ar-badge--warn'],
    };

    $isNew = $registered && $registered->gt(now()->subDays(\App\Filters\CanalFilters::FRESH_DAYS));

    $youtubeUrl = match (true) {
        \App\Services\Youtube\ChannelId::isId($canal->youtube_channel) => 'https://www.youtube.com/channel/' . $canal->youtube_channel,
        (bool) $canal->youtube_playlist => 'https://www.youtube.com/playlist?list=' . $canal->youtube_playlist,
        default => null,
    };

    $followers = $canal->relationLoaded('favorites') ? $canal->favorites->count() : null;
@endphp

<div class="ar-canal-admin mt-3">
    <dl class="ar-canal-admin__facts">
        <div>
            <dt>Registrovaný</dt>
            <dd>
                @if ($registered)
                    <time datetime="{{ $registered->toIso8601String() }}" title="{{ $registered->format('j. n. Y H:i') }}">
                        {{ $registered->format('j. n. Y') }}
                    </time>
                    <span class="ar-canal-admin__sub">
                        {{ $registered->diffForHumans() }}
                        @if ($isNew)<span class="ar-badge ar-badge--ok ml-1">Nový</span>@endif
                    </span>
                @else
                    —
                @endif
            </dd>
        </div>

        <div>
            <dt>Posledný príspevok</dt>
            <dd>
                @if ($lastPost)
                    <time datetime="{{ $lastPost->toIso8601String() }}" title="{{ $lastPost->format('j. n. Y H:i') }}">
                        {{ $lastPost->format('j. n. Y') }}
                    </time>
                    <span class="ar-canal-admin__sub">{{ $lastPost->diffForHumans() }}</span>
                @else
                    —
                @endif
                <span class="ar-badge {{ $activityClass }} mt-1">{{ $activityLabel }}</span>
            </dd>
        </div>

        <div>
            <dt>Obsah</dt>
            <dd>
                @isset($canal->posts_count)
                    {{ $num($canal->posts_count) }}
                    <span class="ar-canal-admin__sub">
                        {{ $plural((int) $canal->posts_count, 'príspevok', 'príspevky', 'príspevkov') }}
                        · {{ $num($canal->prayers_count) }} {{ $plural((int) $canal->prayers_count, 'modlitba', 'modlitby', 'modlitieb') }}
                        · {{ $num($canal->seminars_count) }} {{ $plural((int) $canal->seminars_count, 'seminár', 'semináre', 'seminárov') }}
                    </span>
                @else
                    —
                @endif
            </dd>
        </div>

        <div>
            <dt>Sledujúci</dt>
            <dd>
                {{ $followers === null ? '—' : $num($followers) }}
                <span class="ar-canal-admin__sub">ID #{{ $canal->id }} · upravený {{ optional($canal->updated_at)->diffForHumans() ?? '—' }}</span>
            </dd>
        </div>
    </dl>

    <div class="mt-3 flex flex-wrap items-center gap-1.5">
        @if ($canal->email)
            <a href="mailto:{{ $canal->email }}" class="ar-chip ar-link">
                <i class="fas fa-envelope" aria-hidden="true"></i>{{ $canal->email }}
            </a>
        @endif

        @if ($canal->phone)
            <a href="tel:{{ $canal->phone_numeric ?: $canal->phone }}" class="ar-chip ar-link">
                <i class="fas fa-phone" aria-hidden="true"></i>{{ $canal->phone }}
            </a>
        @endif

        @if ($canal->url_www)
            <a href="{{ $canal->url_www }}" target="_blank" rel="noopener nofollow" class="ar-chip ar-link">
                <i class="fas fa-globe" aria-hidden="true"></i>{{ \Illuminate\Support\Str::limit(preg_replace('#^https?://(www\.)?#', '', $canal->url_www), 32) }}
            </a>
        @endif

        @if ($youtubeUrl)
            <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener" class="ar-chip ar-link">
                <i class="fab fa-youtube" aria-hidden="true"></i>YouTube
            </a>
        @elseif ($canal->youtube_channel)
            <span class="ar-chip ar-chip--muted">
                <i class="fab fa-youtube" aria-hidden="true"></i>{{ $canal->youtube_channel }}
            </span>
        @endif

        @if ($canal->youtube_disabled_at)
            <span class="ar-badge ar-badge--warn"
                  title="{{ $canal->youtube_disabled_reason }}">
                Import vypnutý {{ $canal->youtube_disabled_at->format('j. n. Y') }}@if ($canal->youtube_disabled_reason): {{ \Illuminate\Support\Str::limit($canal->youtube_disabled_reason, 60) }}@endif
            </span>
        @endif

        @if ($canal->trashed())
            <span class="ar-badge ar-badge--count">Zrušený {{ $canal->deleted_at->format('j. n. Y') }}</span>
        @endif

        @if (! $canal->email && ! $canal->phone && ! $canal->url_www && ! $canal->youtube_channel && ! $canal->youtube_playlist)
            <span class="ar-chip ar-chip--muted">Bez kontaktu</span>
        @endif
    </div>

    <p class="mt-2 text-xs text-[color:var(--ar-ink-soft)]">
        @if ($canal->users->isNotEmpty())
            {{ $plural($canal->users->count(), 'Správca', 'Správcovia', 'Správcovia') }}:
            @foreach ($canal->users as $user)
                <a href="{{ route('admin.user.edit', $user->id) }}" class="ar-link" title="{{ $user->email }}">{{ $user->fullname }}</a>@if (! $loop->last), @endif
            @endforeach
        @else
            <span class="ar-badge ar-badge--warn">Bez správcu</span>
        @endif
    </p>
</div>
