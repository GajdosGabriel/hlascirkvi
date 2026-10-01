@props(['canals', 'admin' => false])

@php
    $plural = fn (int $n, string $one, string $few, string $many)
        => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
@endphp


    {{-- Jeden formulár pre všetky tlačidlá „Prepnúť na kanál" (atribút form= a
         formaction=), namiesto samostatného formulára s CSRF tokenom na každý kanál. --}}
    <form id="canal-switch-form" method="POST" hidden>
        @csrf @method('PUT')
    </form>

    <div class="space-y-3">
        @forelse ($canals as $canal)
            @php
                $isActive = $canal->id === auth()->user()->canal_id;

                /*
                 * Štítky kanála. Do 9/2026 to boli updatery — jedna spojovacia
                 * tabuľka pre vierovyznanie, deň importu, zaradenie do zoznamov
                 * aj predný zoznam naraz. Dnes je každá z tých vecí vlastný
                 * stĺpec, takže sa dajú vypísať priamo.
                 */
                $importDay = $canal->import_day === null
                    ? null
                    : (\App\Models\Canal::IMPORT_DAYS[$canal->import_day] ?? null);
            @endphp

            <article @class([
                'ar-card rounded-xl p-4 sm:p-5',
                'ar-org--active' => $isActive,
            ])>
                <div class="flex flex-wrap items-start gap-4">

                    {{-- Iniciály ležia pod obrázkom, nie vedľa neho: kanál si avatar
                         nesie len ako meno súboru a ten na disku chýbať môže. --}}
                    <span class="ar-org__avatar" aria-hidden="true">
                        {{ $canal->initialName }}

                        @if ($canal->avatar)
                            <img src="{{ \App\Support\MediaUrl::canalAvatar($canal->id, $canal->avatar) }}"
                                 alt="" width="48" height="48" loading="lazy" data-img-hide>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <a href="{{ $admin ? route('admin.canal.show', $canal->id) : route('profile.canals.show', $canal->id) }}"
                               class="ar-display ar-link text-base font-bold leading-snug">
                                {{ $canal->title }}
                            </a>

                            @if ($isActive)
                                <span class="ar-badge ar-badge--ok">Aktívne prihlásenie</span>
                            @endif

                            @if (! $canal->published)
                                <span class="ar-badge ar-badge--warn">Nepublikované</span>
                            @endif

                            @if ($canal->trashed())
                                <span class="ar-badge ar-badge--count">Zrušené</span>
                            @endif
                        </div>

                        <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
                            {{ collect([$canal->street, optional($canal->village)->fullname])->filter()->implode(', ') ?: 'Bez adresy' }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            @if ($canal->front_listed_at)
                                <span class="ar-chip">
                                    <i class="ph ph-user" aria-hidden="true"></i>
                                    Predný zoznam
                                </span>
                            @endif

                            @if ($canal->denomination)
                                <span class="ar-chip">
                                    <i class="{{ $canal->denomination->icon() }}" aria-hidden="true"></i>
                                    {{ $canal->denomination->label() }}
                                </span>
                            @endif

                            @if ($canal->post_section === \App\Enums\CanalSection::Live)
                                <span class="ar-chip">
                                    <i class="ph ph-church" aria-hidden="true"></i>
                                    Nedeľné prenosy
                                </span>
                            @elseif ($canal->post_section === \App\Enums\CanalSection::Paused)
                                <span class="ar-chip ar-chip--muted">
                                    <i class="ph-fill ph-pause" aria-hidden="true"></i>
                                    Pozastavené
                                </span>
                            @endif

                            @if ($importDay)
                                <span class="ar-chip ar-chip--muted">Aktualizácia: {{ $importDay }}</span>
                            @endif
                        </div>

                        @if ($admin)
                            @include('components.canal.admin-details', ['canal' => $canal, 'plural' => $plural])
                        @elseif ($canal->users->isNotEmpty())
                            <p class="mt-2 text-xs text-[color:var(--ar-ink-soft)]">
                                {{ $plural($canal->users->count(), 'Správca', 'Správcovia', 'Správcovia') }}:
                                {{ $canal->users->map->fullname->implode(', ') }}
                            </p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center justify-end">
                        <dropdown-slot label="Možnosti kanála">
                            @if ($isActive)
                                <span class="ui-dropdown__item ui-dropdown__item--current">
                                    <i class="ph-fill ph-check-circle" aria-hidden="true"></i>
                                    Prihlásený kanál
                                </span>
                            @else
                                <button type="submit" form="canal-switch-form"
                                        formaction="{{ route('profile.canals.switch', $canal->id) }}"
                                        class="ui-dropdown__item--accent">
                                    <i class="ph ph-arrows-left-right" aria-hidden="true"></i>
                                    Prepnúť na kanál
                                </button>
                            @endif

                            <hr class="ui-dropdown__divider">

                            <a href="{{ route('profile.canals.edit', $canal->id) }}">
                                <i class="ph ph-pencil-simple" aria-hidden="true"></i>
                                Upraviť
                            </a>
                        </dropdown-slot>
                    </div>

                </div>
            </article>
        @empty
            <div class="ar-empty">
                @if (request()->hasAny(['search', 'unpublished', 'deletedAt', 'fresh', 'orphans', 'silent', 'youtubeOff', 'month']))
                    Výberu nezodpovedá žiadny kanál.
                @else
                    {{ $admin ? 'Zatiaľ nie sú žiadne kanály.' : 'Zatiaľ nespravujete žiadny kanál. Založte si ho tlačidlom vyššie.' }}
                @endif
            </div>
        @endforelse
    </div>
