@props(['canal'])
@php
    $active = (int) auth()->user()->canal_id === (int) $canal->id;
    $lastPost = $canal->posts_max_created_at ? \Illuminate\Support\Carbon::parse($canal->posts_max_created_at) : null;
    $hasSource = $canal->youtube_channel || $canal->youtube_playlist;
    $paused = $canal->post_section === \App\Enums\CanalSection::Paused;
    $web = preg_match('#^https?://#i', $canal->url_www ?? '') ? $canal->url_www : null;
@endphp
@include('partials.canal-overview-style')
<div class="co-overview">
    <section class="ar-panel co-hero">
        <div class="co-identity">
            <span class="ar-org__avatar co-avatar" aria-hidden="true">
                {{ \Illuminate\Support\Str::limit($canal->initialName, 3, '') }}
                @if ($canal->avatar)
                    <img src="{{ \App\Support\MediaUrl::canalAvatar($canal->id, $canal->avatar) }}" alt="" width="76" height="76" data-img-hide>
                @endif
            </span>
            <div class="co-heading">
                <p class="co-eyebrow">{{ $canal->identity_mode?->label() ?? 'Kanál' }} · #{{ $canal->id }}</p>
                <h2>{{ $canal->title }}</h2>
                <p class="co-muted">{{ collect([$canal->street, $canal->village?->fullname])->filter()->implode(', ') ?: 'Adresa nie je vyplnená' }}</p>
                <div class="co-actions">
                    @if ($canal->trashed())
                        <span class="ar-badge ar-badge--warn">Zrušený kanál</span>
                    @else
                        <span class="ar-badge {{ $canal->published ? 'ar-badge--ok' : 'ar-badge--warn' }}">{{ $canal->published ? 'Zverejnený' : 'Nezverejnený' }}</span>
                    @endif
                    @if ($active)<span class="ar-badge ar-badge--count">Aktívny kanál pre pridávanie obsahu</span>@endif
                </div>
            </div>
        </div>
        @unless ($canal->trashed())
            <div class="co-actions">
                <a class="ar-btn ar-btn--accent" href="{{ route('profile.canals.edit', $canal) }}"><i class="ph ph-pencil-simple" aria-hidden="true"></i> Upraviť profil</a>
                @if ($canal->published)
                    <a class="ar-btn" href="{{ route('organizations.show', $canal) }}">Verejný profil <i class="ph ph-arrow-square-out" aria-hidden="true"></i></a>
                @endif
            </div>
        @endunless
    </section>

    <div class="co-metrics" aria-label="Obsah a sledovanosť kanála">
        @foreach ([['Príspevky', $canal->posts_count, 'Nezmazaný obsah', 'file-text'], ['Modlitby', $canal->prayers_count, 'Modlitbové úmysly', 'hands-praying'], ['Podujatia', $canal->seminars_count, 'Všetky podujatia', 'calendar-blank'], ['Sledujúci', $canal->favorites_count, 'Kanál medzi obľúbenými', 'heart']] as [$label, $count, $hint, $icon])
            <section class="ar-panel co-metric">
                <div class="co-metric-label">{{ $label }} <i class="ph ph-{{ $icon }}" aria-hidden="true"></i></div>
                <strong>{{ number_format($count, 0, ',', ' ') }}</strong>
                <span class="co-muted">{{ $hint }}</span>
            </section>
        @endforeach
    </div>

    <div class="co-columns">
        <div class="co-stack">
            <section class="ar-panel co-panel">
                <h3>Obsah a aktivita</h3>
                <dl class="co-facts">
                    <div><dt>Zverejnené príspevky</dt><dd>{{ $canal->published_posts_count }}</dd></div>
                    <div><dt>Nezverejnené príspevky</dt><dd>{{ $canal->unpublished_posts_count }}</dd></div>
                    <div><dt>Príspevky v koši</dt><dd>{{ $canal->deleted_posts_count }}</dd></div>
                    <div><dt>Posledný pridaný príspevok</dt><dd>@if ($lastPost)<time datetime="{{ $lastPost->toIso8601String() }}">{{ $lastPost->format('j. n. Y') }}</time><small>{{ $lastPost->diffForHumans() }}</small>@else Zatiaľ žiadny @endif</dd></div>
                </dl>
                @unless ($canal->trashed())
                    <div class="co-actions co-divider">
                        @if ($active)
                            <a class="ar-btn" href="{{ route('profile.posts.index') }}">Spravovať príspevky</a>
                        @endif
                        <a class="ar-btn" href="{{ route('profile.canals.prayers.index', $canal) }}">Modlitby</a>
                        <a class="ar-btn" href="{{ route('profile.canals.seminars.index', $canal) }}">Podujatia</a>
                        @can('superadmin')
                            <a class="ar-btn" href="{{ route('admin.buffer.index', ['posts' => $canal->id]) }}">Čakajúce videá</a>
                        @endcan
                    </div>
                    @unless ($active)
                        <div class="co-switch">
                            <p>Pre pridávanie a správu príspevkov nastavte tento kanál ako aktívny.</p>
                            <form method="POST" action="{{ route('profile.canals.switch', $canal) }}">
                                @csrf @method('PUT')
                                <button class="ar-btn ar-btn--accent" type="submit">Prepnúť na tento kanál</button>
                            </form>
                        </div>
                    @endunless
                @endunless
            </section>
            <section class="ar-panel co-panel">
                <h3>O kanáli</h3>
                @if ($canal->description)
                    <div class="co-description">{!! $canal->description_html !!}</div>
                @else
                    <p class="co-muted">Popis zatiaľ nie je vyplnený. Predstavte návštevníkom kanál v úprave profilu.</p>
                @endif
                <dl class="co-facts co-divider">
                    <div><dt>Vierovyznanie</dt><dd>{{ $canal->denomination?->label() ?? 'Neuvedené' }}</dd></div>
                    <div><dt>Vytvorený</dt><dd>{{ $canal->created_at?->format('j. n. Y') ?? 'Neuvedené' }}</dd></div>
                    <div><dt>Posledná úprava profilu</dt><dd>{{ $canal->updated_at?->format('j. n. Y H:i') ?? 'Neuvedené' }}</dd></div>
                </dl>
            </section>
        </div>
        <div class="co-stack">
            <section class="ar-panel co-panel">
                <h3>Kontaktné údaje</h3>
                <dl class="co-facts">
                    <div><dt>E-mail</dt><dd>@if ($canal->email)<a class="ar-link" href="mailto:{{ $canal->email }}">{{ $canal->email }}</a>@else Neuvedený @endif</dd></div>
                    <div><dt>Telefón</dt><dd>@if ($canal->phone)<a class="ar-link" href="tel:{{ $canal->phone }}">{{ $canal->phone }}</a>@else Neuvedený @endif</dd></div>
                    <div><dt>Web</dt><dd>@if ($web)<a class="ar-link" href="{{ $web }}" target="_blank" rel="noopener noreferrer">{{ preg_replace('#^https?://#i', '', $web) }}</a>@else Neuvedený @endif</dd></div>
                </dl>
            </section>
            <section class="ar-panel co-panel">
                <h3>Import z YouTube</h3>
                <span class="ar-badge {{ $canal->youtube_disabled_at || $paused ? 'ar-badge--warn' : 'ar-badge--count' }}">{{ $paused ? 'Pozastavený' : ($canal->youtube_disabled_at ? 'Vypnutý' : ($hasSource ? 'Zdroj nastavený' : 'Bez zdroja')) }}</span>
                @if ($canal->youtube_disabled_at)
                    <p class="co-muted">Vypnutý {{ $canal->youtube_disabled_at->format('j. n. Y') }}. {{ $canal->youtube_disabled_reason }}</p>
                @endif
                <dl class="co-facts">
                    @if ($canal->youtube_channel)
                        <div><dt>YouTube kanál</dt><dd>@if (\App\Services\Youtube\ChannelId::isId($canal->youtube_channel))<a class="ar-link" href="https://www.youtube.com/channel/{{ $canal->youtube_channel }}" target="_blank" rel="noopener noreferrer">Otvoriť na YouTube</a>@else {{ $canal->youtube_channel }} @endif</dd></div>
                    @endif
                    @if ($canal->youtube_playlist)
                        <div><dt>Playlist</dt><dd><a class="ar-link" href="https://www.youtube.com/playlist?list={{ urlencode($canal->youtube_playlist) }}" target="_blank" rel="noopener noreferrer">Otvoriť playlist</a></dd></div>
                    @endif
                    <div><dt>Zaradenie videí</dt><dd>{{ $canal->post_section?->label() ?? 'Nenastavené' }}</dd></div>
                    <div><dt>Deň vyhľadávania kanála</dt><dd>{{ \App\Models\Canal::IMPORT_DAYS[$canal->import_day] ?? 'Nenastavený' }}</dd></div>
                </dl>
            </section>
            <section class="ar-panel co-panel">
                <h3>Správcovia <span class="ar-badge ar-badge--count">{{ $canal->users->count() }}</span></h3>
                <ul class="co-managers">
                    @forelse ($canal->users as $manager)
                        <li>
                            <span class="co-person" aria-hidden="true"><i class="ph ph-user"></i></span>
                            <div><strong>{{ trim($manager->first_name . ' ' . $manager->last_name) ?: 'Správca kanála' }}</strong>
                                @can('superadmin')<a class="ar-link" href="{{ route('admin.user.edit', $manager->id) }}">{{ $manager->email }}</a>@endcan
                            </div>
                        </li>
                    @empty
                        <li class="co-muted">Kanál zatiaľ nemá priradeného správcu.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</div>
