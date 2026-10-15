@php
    $seo = ['title' => 'Buffer · Administrácia', 'noindex' => true];
@endphp
@extends('layouts.admin')

@section('content')
    <x-pages.admin>
        <x-slot name="title">Buffer</x-slot>
        <x-slot name="lead">
            Na zverejnenie čaká {{ number_format($status['waiting'], 0, ',', ' ') }} príspevkov s dostupným videom,
            import prináša {{ number_format($status['inflow'], 1, ',', ' ') }} denne.
        </x-slot>

        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $filteredCanal = request('posts') ? $canals->firstWhere('id', (int) request('posts')) : null;
            @endphp

            {{-- Plán a kanály stáli až pod stránkovaním ~7 000 px dlhej mriežky
                 kariet, kam sa nikto nedostal. Plán je to, čo sa dnes stane,
                 preto ide prvý. Časy počíta App\Services\Buffer\PublishPlan zo
                 semienka podľa dátumu, takže sú počas dňa stále rovnaké. --}}
            <x-dashboard.panel title="Dnešný plán zverejňovania" class="mb-6">
                <x-slot name="note">
                    {{ $status['done_today'] }} z {{ count($status['plan']) }} vyšlo
                    @if ($status['archive_today'])
                        · {{ $status['archive_today'] }} zo staršieho buffera
                    @endif
                </x-slot>

                <ol class="ar-plan" aria-label="Časy zverejnenia dnes">
                    @forelse ($status['plan'] as $index => $slot)
                        <li @class([
                                'ar-plan__slot',
                                'ar-plan__slot--done' => $index < $status['done_today'],
                                'ar-plan__slot--next' => $index === $status['done_today'],
                            ])>
                            {{ $slot->format('H:i') }}
                            <span class="sr-only">
                                {{ $index < $status['done_today'] ? '— zverejnené' : ($index === $status['done_today'] ? '— najbližšie' : '') }}
                            </span>
                        </li>
                    @empty
                        <li class="text-sm text-[color:var(--ar-ink-soft)]">Na dnes nie je naplánované nič.</li>
                    @endforelse
                </ol>
            </x-dashboard.panel>

            <div class="grid gap-6 lg:grid-cols-12">
                <div class="min-w-0 lg:col-span-8">
                    @if ($filteredCanal)
                        <div class="mb-3 flex flex-wrap gap-2">
                            <a href="{{ route('admin.buffer.index') }}" class="ar-tab ar-tab--on" aria-pressed="true" title="Zrušiť filter">
                                Kanál: {{ $filteredCanal->title }}
                                <i class="ph ph-x text-xs opacity-70" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endif

                    <x-dashboard.panel title="Čakajú na zverejnenie" flush>
                        <x-slot name="note">{{ $num($posts->total()) }} vo výbere</x-slot>

                        @forelse ($posts as $post)
                            @php
                                $duration = $post->video_duration === '0:00' ? null : $post->video_duration;
                                $url = route('post.show', [$post->id, $post->slug]);
                            @endphp
                            <article class="ar-item" data-buffer-row>
                                <a href="{{ $url }}" class="ar-item__thumb" tabindex="-1" aria-hidden="true">
                                    @include('partials.thumb', ['model' => $post, 'alt' => '', 'class' => ''])
                                    @if ($duration)
                                        <span class="ar-item__time">{{ $duration }}</span>
                                    @endif
                                </a>

                                <div class="ar-item__body">
                                    <a href="{{ $url }}" class="ar-item__title ar-clamp-2">{{ $post->title }}</a>
                                    <div class="ar-item__meta">
                                        @if ($post->canal)
                                            <a class="ar-link" href="{{ route('admin.buffer.index', ['posts' => $post->canal_id]) }}"
                                               title="Len príspevky tohto kanála">{{ $post->canal->title }}</a>
                                        @endif
                                        <time datetime="{{ $post->created_at->toIso8601String() }}">v rade {{ $post->created_at->diffForHumans() }}</time>
                                        @if ($post->video_available !== null && ! $post->video_available)
                                            <span class="ar-badge ar-badge--warn">video nedostupné</span>
                                        @endif
                                    </div>
                                    {{-- Akcie pod textom, nie v stĺpci vpravo — v užšom stĺpci
                                         by inak názov ostal na pár slov na riadok. --}}
                                    <div class="mt-2">
                                        <post-publish-buttons :post="{{ json_encode(['id' => $post->id]) }}" />
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-dashboard.empty>V bufferi nič nečaká.</x-dashboard.empty>
                        @endforelse
                    </x-dashboard.panel>

                    @if ($posts->hasPages())
                        <div class="mt-6">{{ $posts->withQueryString()->links() }}</div>
                    @endif
                </div>

                {{-- Počet čakajúcich príspevkov ráta withCount v BufferController. --}}
                <aside class="min-w-0 lg:col-span-4">
                    <x-dashboard.panel title="Kanály s čakajúcimi príspevkami" flush>
                        <x-slot name="note">{{ $num($canals->count()) }}</x-slot>

                        <div class="ar-buffer-canals">
                            @forelse ($canals as $canal)
                                <a href="{{ route('admin.buffer.index', ['posts' => $canal->id]) }}"
                                   @class(['ar-row', 'ar-row--on' => $filteredCanal?->id === $canal->id])
                                   @if ($filteredCanal?->id === $canal->id) aria-current="true" @endif>
                                    <span class="ar-row__title min-w-0 truncate">{{ $canal->title }}</span>
                                    <span class="ar-row__value">{{ $num($canal->unpublished_posts_count) }}</span>
                                </a>
                            @empty
                                <x-dashboard.empty>Žiadny kanál nečaká.</x-dashboard.empty>
                            @endforelse
                        </div>
                    </x-dashboard.panel>
                </aside>
            </div>
        </x-slot>
    </x-pages.admin>
@endsection
