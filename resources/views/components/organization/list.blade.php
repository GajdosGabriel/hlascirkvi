@props(['organizations', 'user', 'admin' => false])

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

                    <div class="flex shrink-0 items-center justify-end">
                        <dropdown-slot>
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

                        </dropdown-slot>
                    </div>

                </div>
            </article>
        @empty
            <div class="ar-empty">
                @if (request()->hasAny(['search', 'unpublished', 'deletedAt']))
                    Výberu nezodpovedá žiadny kanál.
                @else
                    {{ $admin ? 'Zatiaľ nie sú žiadne kanály.' : 'Zatiaľ nespravujete žiadny kanál. Založte si ho tlačidlom vyššie.' }}
                @endif
            </div>
        @endforelse
    </div>
