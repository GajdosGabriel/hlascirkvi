@extends('layouts.app')

@section('title')
    <title>Vaše kanály</title>
@endsection

@section('body-class', 'ar-body')

@section('headerCSS')
    {{-- Rovnaké písmo ako verejná časť. Layout ho nenačítava globálne. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@php
    // Ikony k zaradeniu kanála. Updaters sú číselník, tu ide len o to, aby
    // štítok nebol holý text.
    $updaterIcon = [
        'front-user'      => 'fas fa-user',
        'catholic'        => 'fab fa-korvue',
        'evangelical'     => 'fab fa-product-hunt',
        'zive-vysielanie' => 'fas fa-church',
        'vzdelavanie'     => 'fas fa-graduation-cap',
    ];

    $plural = fn (int $n, string $one, string $few, string $many)
        => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
@endphp

@section('content')

    <div class="ar-dash mx-auto max-w-6xl px-4 py-8">
        {{-- min-w-0 na oboch stĺpcoch: bez neho dlhý názov kanála roztiahne
             bunku mriežky a stránka na mobile odchádza doprava. --}}
        <div class="grid gap-8 lg:grid-cols-12">

            <aside class="min-w-0 lg:col-span-3">
                <p class="ar-kicker mb-3">Váš profil</p>

                <nav class="ar-dash__menu" aria-label="Profil">
                    <x-navigation.aside-menu />
                </nav>
            </aside>

            <div class="min-w-0 lg:col-span-9">

                <header class="mb-6">
                    <h1 class="ar-display text-2xl font-extrabold sm:text-3xl">Vaše kanály</h1>

                    <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
                        {{ $organizations->total() }}
                        {{ $plural($organizations->total(), 'kanál', 'kanály', 'kanálov') }}
                        @if ($organizations->total())
                            · príspevky sa zapisujú do kanála s aktívnym prihlásením
                        @endif
                    </p>
                </header>

                {{-- Založenie kanála. Formulár nesie Vue komponent; podklad
                     a tlačidlá mu prekresľuje .ar-dash__new. --}}
                <div class="ar-dash__new mb-6">
                    <new-organization></new-organization>
                </div>

                {{-- Prepínače zodpovedajú kľúčom v App\Filters\OrganizationFilters. --}}
                <x-filters.bar class="mb-5"
                               :filters="['unpublished', 'deletedAt' => 'Zrušené']"
                               search="Hľadať kanál" />

                <div class="space-y-3">
                    @forelse ($organizations as $organization)
                        @php
                            $isActive = $organization->id === auth()->user()->org_id;
                            $days = $organization->updaters->where('type', 'dayOfWeek');
                            // `default` je zástupná položka číselníka, správcovi nič nehovorí.
                            $tags = $organization->updaters
                                ->where('type', '!=', 'dayOfWeek')
                                ->where('slug', '!=', 'default');
                        @endphp

                        <article @class([
                            'ar-card rounded-xl p-4 sm:p-5',
                            'ar-org--active' => $isActive,
                        ])>
                            <div class="flex flex-wrap items-start gap-4">

                                {{-- Iniciály ležia pod obrázkom, nie vedľa neho: kanál si avatar
                                     nesie len ako meno súboru a ten na disku chýbať môže. --}}
                                <span class="ar-org__avatar" aria-hidden="true">
                                    {{ $organization->initialName }}

                                    @if ($organization->avatar)
                                        <img src="{{ Storage::url('organizations/' . $organization->id . '/' . $organization->avatar) }}"
                                             alt="" loading="lazy" onerror="this.remove()">
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <a href="{{ route('profile.user.organization.show', [$user->id, $organization->id]) }}"
                                           class="ar-display ar-link text-base font-bold leading-snug">
                                            {{ $organization->title }}
                                        </a>

                                        @if ($isActive)
                                            <span class="ar-badge ar-badge--ok">Aktívne prihlásenie</span>
                                        @endif

                                        @if (! $organization->published)
                                            <span class="ar-badge ar-badge--warn">Nepublikované</span>
                                        @endif

                                        @if ($organization->trashed())
                                            <span class="ar-badge ar-badge--count">Zrušené</span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
                                        {{ collect([$organization->street, optional($organization->village)->fullname])->filter()->implode(', ') ?: 'Bez adresy' }}
                                    </p>

                                    @if ($tags->isNotEmpty() || $days->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            @foreach ($tags as $tag)
                                                <span class="ar-chip">
                                                    @isset($updaterIcon[$tag->slug])
                                                        <i class="{{ $updaterIcon[$tag->slug] }}" aria-hidden="true"></i>
                                                    @endisset
                                                    {{ $tag->title }}
                                                </span>
                                            @endforeach

                                            @if ($days->isNotEmpty())
                                                <span class="ar-chip ar-chip--muted">
                                                    Aktualizácia: {{ $days->pluck('title')->implode(', ') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($organization->users->isNotEmpty())
                                        <p class="mt-2 text-xs text-[color:var(--ar-ink-soft)]">
                                            {{ $plural($organization->users->count(), 'Správca', 'Správcovia', 'Správcovia') }}:
                                            {{ $organization->users->map->fullname->implode(', ') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex w-full shrink-0 flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                                    @if ($isActive)
                                        <span class="ar-btn ar-btn--quiet ar-btn--still">Prihlásený kanál</span>
                                    @else
                                        <form method="POST"
                                              action="{{ route('profile.user.organization.switch', [$user->id, $organization->id]) }}">
                                            @method('PUT') @csrf
                                            <button class="ar-btn ar-btn--accent">Prepnúť na kanál</button>
                                        </form>
                                    @endif

                                    <a class="ar-btn ar-btn--quiet"
                                       href="{{ route('profile.user.organization.edit', [$user->id, $organization->id]) }}">
                                        <i class="fas fa-pen text-[.7rem]" aria-hidden="true"></i>
                                        Upraviť
                                    </a>
                                </div>

                            </div>
                        </article>
                    @empty
                        <div class="ar-empty">
                            @if (request()->hasAny(['search', 'unpublished', 'deletedAt']))
                                Výberu nezodpovedá žiadny kanál.
                            @else
                                Zatiaľ nespravujete žiadny kanál. Založte si ho tlačidlom vyššie.
                            @endif
                        </div>
                    @endforelse
                </div>

                @if ($organizations->hasPages())
                    <div class="mt-8">
                        {{ $organizations->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
