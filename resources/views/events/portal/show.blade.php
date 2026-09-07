@extends('layouts.events')

@section('title')
    <title>{{ $event->title() }} | Hlas Cirkvi</title>
@endsection

@section('meta')
    <meta name="description" content="{{ $event->excerpt(160) }}">
    <link rel="canonical" href="{{ $event->url() }}">

    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $event->title() }}">
    <meta property="og:description" content="{{ $event->excerpt(200) }}">
    <meta property="og:url" content="{{ $event->url() }}">
    @if ($event->hasPoster())
        <meta property="og:image" content="{{ $event->poster() }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    {{-- Štruktúrované dáta pre vyhľadávače. Pole skladá RemoteEvent::schemaOrg()
         — kľúče ako "@context" by Blade v šablóne čítal ako direktívy. --}}
    <script type="application/ld+json">
        {!! json_encode($event->schemaOrg(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endsection

@section('content')

    {{-- Drobčeky --}}
    <div class="border-b border-[color:var(--ev-line)]">
        <div class="mx-auto max-w-6xl px-4 py-3 text-sm text-stone-500">
            <a href="{{ url('/') }}" class="hover:text-stone-800">Hlas Cirkvi</a>
            <span class="mx-2 text-stone-300">/</span>
            <a href="{{ route('akcie.index') }}" class="hover:text-stone-800">Podujatia</a>
            @if ($event->municipality())
                <span class="mx-2 text-stone-300">/</span>
                <a href="{{ route('akcie.index', ['municipality' => $event->municipalitySlug()]) }}"
                   class="hover:text-stone-800">{{ $event->municipality() }}</a>
            @endif
        </div>
    </div>

    {{-- Hlavička podujatia --}}
    <header class="border-b border-[color:var(--ev-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 md:py-10">

            <div class="mb-4 flex flex-wrap items-center gap-2">
                @if ($event->isOngoing())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                        <i class="fas fa-circle text-[6px]"></i> Práve prebieha
                    </span>
                @elseif ($event->isPast())
                    <span class="rounded-full bg-stone-200 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-stone-600">
                        Podujatie sa už uskutočnilo
                    </span>
                @elseif ($event->isToday())
                    <span class="rounded-full bg-amber-400 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-stone-900">
                        Dnes
                    </span>
                @endif

                @foreach (array_slice($event->tags(), 0, 5) as $tag)
                    <a href="{{ route('akcie.index', ['tags' => $tag['slug'] ?? '']) }}"
                       class="inline-flex items-center gap-1 rounded-full border border-[color:var(--ev-line)] px-2.5 py-1 text-xs text-stone-600 transition hover:border-amber-400 hover:text-amber-800">
                        @if (! empty($tag['emoji']))<span>{{ $tag['emoji'] }}</span>@endif
                        {{ $tag['name'] ?? '' }}
                    </a>
                @endforeach
            </div>

            <h1 class="ev-display max-w-4xl text-3xl font-bold leading-tight md:text-[2.5rem]">
                {{ $event->title() }}
            </h1>

            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-stone-600">
                @if ($event->startAt())
                    <span class="font-medium">
                        <i class="far fa-calendar mr-2 text-[color:var(--ev-accent)]"></i>
                        {{ ucfirst($event->dayName()) }}, {{ $event->longDate() }}
                        @if ($event->timeLabel())
                            <span class="text-stone-400">·</span> {{ $event->timeLabel() }}
                        @endif
                    </span>
                @endif

                @if ($event->address())
                    <span><i class="fas fa-map-marker-alt mr-2 text-[color:var(--ev-accent)]"></i>{{ $event->address() }}</span>
                @endif
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid gap-10 lg:grid-cols-12">

            {{-- Obsah --}}
            <article class="lg:col-span-8">

                @if ($event->hasPoster())
                    <a href="{{ $event->poster('original') ?: $event->poster() }}" target="_blank" rel="noopener"
                       class="mb-8 block overflow-hidden rounded-lg border border-[color:var(--ev-line)] bg-white">
                        <img src="{{ $event->poster() }}" alt="{{ $event->title() }}" class="w-full">
                    </a>
                @endif

                @if (trim(strip_tags($event->body())) !== '')
                    <div class="ev-prose max-w-none">
                        {!! $event->bodyHtml() !!}
                    </div>
                @else
                    <p class="text-stone-500">Organizátor zatiaľ nepridal podrobnejší popis.</p>
                @endif

                {{-- Ďalšie obrázky --}}
                @if ($event->gallery())
                    <div class="mt-8">
                        <h2 class="ev-rule ev-display mb-3 text-sm font-semibold uppercase tracking-wider text-stone-500">
                            Fotografie
                        </h2>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($event->gallery() as $image)
                                <a href="{{ $image['original_file_url'] ?? '#' }}" target="_blank" rel="noopener"
                                   class="block overflow-hidden rounded-md border border-[color:var(--ev-line)]">
                                    <img data-src="{{ $image['thumb_image_url'] ?? $image['original_file_url'] ?? '' }}"
                                         data-sizes="auto" alt="{{ $image['name'] ?? '' }}"
                                         class="lazyload h-32 w-full object-cover transition hover:scale-105">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Prílohy: program, prihláška, mapka --}}
                @if ($event->attachments())
                    <div class="mt-8">
                        <h2 class="ev-rule ev-display mb-3 text-sm font-semibold uppercase tracking-wider text-stone-500">
                            Na stiahnutie
                        </h2>
                        <ul class="space-y-2">
                            @foreach ($event->attachments() as $file)
                                <li>
                                    <a href="{{ $file['original_file_url'] ?? '#' }}" target="_blank" rel="noopener"
                                       class="flex items-center gap-3 rounded-md border border-[color:var(--ev-line)] bg-white p-3 text-sm transition hover:border-amber-400">
                                        <i class="far fa-file-alt text-lg text-stone-400"></i>
                                        <span class="flex-1 truncate">{{ $file['name'] ?? $file['original_name'] ?? 'Príloha' }}</span>
                                        <span class="text-xs uppercase text-stone-400">{{ $file['extension'] ?? '' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Ďalšie termíny tej istej série --}}
                @if ($event->seriesOccurrences())
                    <div class="mt-10">
                        <h2 class="ev-rule ev-display mb-3 text-sm font-semibold uppercase tracking-wider text-stone-500">
                            Ďalšie termíny
                        </h2>
                        <ul class="divide-y divide-[color:var(--ev-line)] overflow-hidden rounded-lg border border-[color:var(--ev-line)] bg-white">
                            @foreach ($event->seriesOccurrences() as $occurrence)
                                <li>
                                    <a href="{{ route('event.show', [$occurrence['id'], $occurrence['slug'] ?? 'podujatie']) }}"
                                       class="flex items-center justify-between gap-4 p-3 text-sm transition hover:bg-stone-50">
                                        <span class="truncate">{{ $occurrence['name'] ?? '' }}</span>
                                        <span class="shrink-0 text-xs text-stone-500">{{ $occurrence['date_range_label'] ?? '' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($event->sourceUrl())
                    <p class="mt-8 text-xs text-stone-400">
                        Zdroj informácií:
                        <a href="{{ $event->sourceUrl() }}" target="_blank" rel="noopener nofollow"
                           class="underline hover:text-stone-600">{{ parse_url($event->sourceUrl(), PHP_URL_HOST) }}</a>
                    </p>
                @endif
            </article>

            {{-- Bočný panel --}}
            <aside class="lg:col-span-4">
                <div class="space-y-4 lg:sticky lg:top-20">

                    {{-- Termín --}}
                    @if ($event->startAt())
                        <section class="rounded-lg border border-[color:var(--ev-line)] bg-white p-4">
                            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-stone-400">Termín</h2>

                            <div class="flex items-start gap-4">
                                <div class="ev-stamp flex w-16 shrink-0 flex-col items-center rounded-lg px-2 py-2">
                                    <span class="ev-display text-2xl font-bold leading-none">{{ $event->dayNumber() }}</span>
                                    <span class="text-[.6rem] uppercase tracking-widest text-stone-500">{{ $event->monthShort() }}</span>
                                </div>

                                <div class="text-sm">
                                    <div class="font-semibold">{{ ucfirst($event->dayName()) }}</div>
                                    <div class="text-stone-600">{{ $event->longDate() }}</div>
                                    @if ($event->timeLabel())
                                        <div class="mt-1 text-stone-600">
                                            <i class="far fa-clock mr-1 text-xs"></i>{{ $event->timeLabel() }}
                                        </div>
                                    @endif
                                    @if ($event->isMultiDay() && $event->endAt())
                                        <div class="mt-1 text-stone-500">
                                            koniec {{ $event->endAt()->locale('sk')->isoFormat('D. MMMM YYYY') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($event->calendarLinks() && ! $event->isPast())
                                <div class="mt-4 flex flex-wrap gap-2 border-t border-[color:var(--ev-line)] pt-3 text-xs">
                                    <span class="text-stone-400">Do kalendára:</span>
                                    @foreach (['google' => 'Google', 'outlook' => 'Outlook', 'download' => '.ics'] as $key => $label)
                                        @if (! empty($event->calendarLinks()[$key]))
                                            <a href="{{ $event->calendarLinks()[$key] }}" target="_blank" rel="noopener"
                                               class="rounded border border-[color:var(--ev-line)] px-2 py-1 transition hover:border-amber-400 hover:text-amber-800">
                                                {{ $label }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- Prihlásenie beží na portáli, nie u nás --}}
                    @if (! $event->isPast())
                        <section class="rounded-lg border border-[color:var(--ev-line)] bg-white p-4">
                            @if ($event->price())
                                <div class="mb-3 flex items-baseline justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Vstupné</span>
                                    <span class="ev-display text-lg font-semibold {{ $event->isFree() ? 'text-emerald-700' : '' }}">
                                        {{ $event->price() }}
                                    </span>
                                </div>
                            @endif

                            @if ($event->registrationDeadlineAt())
                                <p class="mb-3 text-xs text-stone-500">
                                    Prihlasovanie do
                                    {{ $event->registrationDeadlineAt()->locale('sk')->isoFormat('D. MMMM YYYY') }}.
                                </p>
                            @endif

                            @if ($event->ticketsEnabled())
                                <a href="{{ $event->portalUrl() }}" target="_blank" rel="noopener"
                                   class="flex w-full items-center justify-center gap-2 rounded-md bg-[color:var(--ev-accent)] px-4 py-2.5 font-semibold text-white transition hover:bg-amber-700">
                                    <i class="fas fa-ticket-alt"></i> Získať vstupenku
                                </a>
                            @elseif ($event->website())
                                <a href="{{ $event->website() }}" target="_blank" rel="noopener nofollow"
                                   class="flex w-full items-center justify-center gap-2 rounded-md bg-[color:var(--ev-accent)] px-4 py-2.5 font-semibold text-white transition hover:bg-amber-700">
                                    Prihlásiť sa <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            @else
                                <a href="{{ $event->portalUrl() }}" target="_blank" rel="noopener"
                                   class="flex w-full items-center justify-center gap-2 rounded-md border border-[color:var(--ev-line)] px-4 py-2.5 text-sm font-medium transition hover:bg-stone-50">
                                    Detail na portáli Event <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            @endif
                        </section>
                    @endif

                    {{-- Miesto --}}
                    @if ($event->address())
                        <section class="rounded-lg border border-[color:var(--ev-line)] bg-white p-4">
                            <h2 class="mb-2 text-xs font-semibold uppercase tracking-wider text-stone-400">Miesto</h2>
                            <div class="text-sm">
                                @if ($event->venue())
                                    <div class="font-semibold">{{ $event->venue() }}</div>
                                @endif
                                @if ($event->venueStreet())
                                    <div class="text-stone-600">{{ $event->venueStreet() }}</div>
                                @endif
                                @if ($event->municipality())
                                    <div class="text-stone-600">{{ $event->municipality() }}</div>
                                @endif
                            </div>

                            @if ($event->mapUrl())
                                <a href="{{ $event->mapUrl() }}" target="_blank" rel="noopener"
                                   class="mt-3 inline-flex items-center gap-2 text-sm text-[color:var(--ev-accent)] hover:underline">
                                    <i class="fas fa-directions"></i> Zobraziť na mape
                                </a>
                            @endif
                        </section>
                    @endif

                    {{-- Organizátor --}}
                    @if ($event->organizer())
                        <section class="rounded-lg border border-[color:var(--ev-line)] bg-white p-4">
                            <h2 class="mb-2 text-xs font-semibold uppercase tracking-wider text-stone-400">Organizátor</h2>
                            <div class="text-sm font-semibold">{{ $event->organizer() }}</div>
                            @if ($event->organizerWebsite())
                                <a href="{{ $event->organizerWebsite() }}" target="_blank" rel="noopener nofollow"
                                   class="mt-1 inline-block break-all text-sm text-[color:var(--ev-accent)] hover:underline">
                                    {{ parse_url($event->organizerWebsite(), PHP_URL_HOST) }}
                                </a>
                            @endif
                        </section>
                    @endif

                    {{-- Zdieľanie --}}
                    <section class="rounded-lg border border-[color:var(--ev-line)] bg-white p-4">
                        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-stone-400">Zdieľať</h2>
                        <div class="flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($event->url()) }}"
                               target="_blank" rel="noopener"
                               class="flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--ev-line)] text-stone-500 transition hover:border-blue-400 hover:text-blue-600"
                               title="Zdieľať na Facebooku">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($event->title() . ' ' . $event->url()) }}"
                               target="_blank" rel="noopener"
                               class="flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--ev-line)] text-stone-500 transition hover:border-green-400 hover:text-green-600"
                               title="Poslať cez WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="mailto:?subject={{ rawurlencode($event->title()) }}&body={{ rawurlencode($event->url()) }}"
                               class="flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--ev-line)] text-stone-500 transition hover:border-amber-400 hover:text-amber-700"
                               title="Poslať e-mailom">
                                <i class="far fa-envelope"></i>
                            </a>
                            <button type="button"
                                    class="js-copy-link flex h-9 flex-1 items-center justify-center gap-2 rounded-md border border-[color:var(--ev-line)] text-sm text-stone-500 transition hover:border-amber-400 hover:text-amber-700"
                                    data-url="{{ $event->url() }}">
                                <i class="far fa-copy"></i> Kopírovať odkaz
                            </button>
                        </div>
                    </section>
                </div>
            </aside>
        </div>

        {{-- Čo ešte je v okolí --}}
        @if ($related->isNotEmpty())
            <section class="mt-14">
                <h2 class="ev-rule ev-display mb-5 text-lg font-semibold">
                    Ďalšie podujatia {{ $event->municipality() ? 'v okolí: ' . $event->municipality() : '' }}
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $other)
                        <a href="{{ $other->url() }}" class="ev-card block overflow-hidden rounded-lg">
                            @if ($other->hasPoster())
                                <img data-src="{{ $other->thumb() ?: $other->poster() }}" data-sizes="auto"
                                     alt="{{ $other->title() }}" class="lazyload h-32 w-full object-cover">
                            @else
                                <div class="ev-noposter flex h-32 w-full items-center justify-center">
                                    <i class="far fa-calendar text-2xl"></i>
                                </div>
                            @endif
                            <div class="p-3">
                                <div class="mb-1 text-xs font-semibold text-[color:var(--ev-accent)]">
                                    {{ $other->dateRangeLabel() }}
                                </div>
                                <div class="ev-display text-sm font-semibold leading-snug">{{ $other->title() }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12 border-t border-[color:var(--ev-line)] pt-6">
            <a href="{{ route('akcie.index') }}" class="text-sm text-stone-500 hover:text-stone-800">
                <i class="fas fa-arrow-left mr-2"></i> Späť na všetky podujatia
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Kopírovanie odkazu. clipboard API funguje len cez https, na http
        // sa tlačidlo správa ako keby sa nič nestalo — preto krátky fallback.
        //
        // Poslucháča vešiame až po DOMContentLoaded: Vue mountuje na #app
        // a prekreslí obsah stránky, takže tlačidlo, ktoré tu je pri
        // parsovaní, o chvíľu v dokumente už nie je a klik by nikam neviedol.
        var initCopyLink = function () {
            document.querySelectorAll('.js-copy-link').forEach(function (button) {
                button.addEventListener('click', function () {
                    var url = button.dataset.url;
                    var done = function () {
                        var original = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check"></i> Skopírované';
                        setTimeout(function () { button.innerHTML = original; }, 2000);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(url).then(done);
                        return;
                    }

                    var field = document.createElement('input');
                    field.value = url;
                    document.body.appendChild(field);
                    field.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(field);
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCopyLink);
        } else {
            initCopyLink();
        }
    </script>
@endpush
