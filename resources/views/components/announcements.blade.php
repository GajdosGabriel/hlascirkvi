@php
    /*
     * Farby oznamu držia triedy tu a nie v databáze ani v PHP triede: Tailwind
     * prehľadáva len `resources/**`, takže trieda zapísaná inde by sa do
     * zostaveného CSS nikdy nedostala a oznam by ostal nezafarbený.
     */
    $tones = [
        'info'    => ['bar' => 'bg-blue-800 text-blue-50',      'panel' => 'border-blue-200 bg-blue-50 text-blue-900',         'accent' => 'text-blue-800',    'icon' => 'fas fa-info-circle'],
        'success' => ['bar' => 'bg-emerald-800 text-emerald-50','panel' => 'border-emerald-200 bg-emerald-50 text-emerald-900','accent' => 'text-emerald-800', 'icon' => 'fas fa-check-circle'],
        'warning' => ['bar' => 'bg-amber-600 text-amber-50',    'panel' => 'border-amber-200 bg-amber-50 text-amber-900',      'accent' => 'text-amber-800',   'icon' => 'fas fa-exclamation-triangle'],
        'danger'  => ['bar' => 'bg-red-800 text-red-50',        'panel' => 'border-red-200 bg-red-50 text-red-900',            'accent' => 'text-red-800',     'icon' => 'fas fa-exclamation-circle'],
        'neutral' => ['bar' => 'bg-slate-800 text-slate-100',   'panel' => 'border-slate-300 bg-slate-100 text-slate-800',     'accent' => 'text-slate-800',   'icon' => 'far fa-bell'],
    ];

    // Pruh cez celú šírku, panel v obsahu, karta do bočného stĺpca.
    $shape = match ($placement) {
        'top', 'footer' => 'bar',
        'sidebar'       => 'card',
        default         => 'panel',
    };
@endphp

{{-- v-pre drží oznamy mimo Vue: text správcu môže obsahovať zložené zátvorky
     a Vue 2 by ich na tejto stránke skúsil vyhodnotiť ako výraz. --}}
<div class="ar-announce ar-announce--{{ $placement }}" v-pre>
    @foreach ($announcements as $item)
        @php
            $tone = $tones[$item->variant->value] ?? $tones['info'];
            $linkText = $item->link_text ?: 'Zistiť viac';
        @endphp

        @if ($shape === 'bar')
            <div class="{{ $tone['bar'] }} {{ $placement === 'footer' ? 'border-t border-black/10' : '' }}"
                 data-announcement="{{ $item->id }}">
                <div class="mx-auto flex max-w-6xl items-start gap-3 px-4 py-2.5 text-sm">
                    <i class="{{ $tone['icon'] }} mt-0.5 shrink-0 opacity-80" aria-hidden="true"></i>

                    <div class="min-w-0 flex-1">
                        <span class="font-semibold">{{ $item->title }}</span>
                        @if ($item->body)
                            <span class="opacity-90"> — {!! nl2br(e($item->body)) !!}</span>
                        @endif

                        @if ($item->link_url)
                            <a href="{{ $item->link_url }}"
                               class="ml-2 whitespace-nowrap font-semibold underline underline-offset-2 hover:no-underline">
                                {{ $linkText }} <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>

                    @if ($item->dismissible)
                        <button type="button" data-announcement-close
                                class="shrink-0 rounded px-1.5 text-base leading-none opacity-70 hover:opacity-100"
                                title="Zavrieť oznam" aria-label="Zavrieť oznam">&times;</button>
                    @endif
                </div>
            </div>

        @elseif ($shape === 'card')
            <section class="card" data-announcement="{{ $item->id }}">
                <div class="card_header">
                    <h4>{{ $item->title }}</h4>
                    <i class="{{ $tone['icon'] }} {{ $tone['accent'] }}" aria-hidden="true"></i>
                </div>
                <div class="card_body py-3 text-sm text-gray-600">
                    @if ($item->body)
                        <p>{!! nl2br(e($item->body)) !!}</p>
                    @endif

                    @if ($item->link_url)
                        <p class="mt-2">
                            <a href="{{ $item->link_url }}" class="{{ $tone['accent'] }} font-semibold">
                                {{ $linkText }} <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                            </a>
                        </p>
                    @endif
                </div>
            </section>

        @else
            <div class="mb-4 flex items-start gap-3 rounded-lg border px-4 py-3 {{ $tone['panel'] }}"
                 data-announcement="{{ $item->id }}">
                <i class="{{ $tone['icon'] }} mt-1 shrink-0 opacity-80" aria-hidden="true"></i>

                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $item->title }}</p>

                    @if ($item->body)
                        <p class="mt-1 text-sm opacity-90">{!! nl2br(e($item->body)) !!}</p>
                    @endif

                    @if ($item->link_url)
                        <p class="mt-2">
                            <a href="{{ $item->link_url }}" class="text-sm font-semibold underline underline-offset-2 hover:no-underline">
                                {{ $linkText }} <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                            </a>
                        </p>
                    @endif
                </div>

                @if ($item->dismissible)
                    <button type="button" data-announcement-close
                            class="shrink-0 rounded px-1.5 text-lg leading-none opacity-60 hover:opacity-100"
                            title="Zavrieť oznam" aria-label="Zavrieť oznam">&times;</button>
                @endif
            </div>
        @endif
    @endforeach
</div>

@once
    @push('scripts')
        <script>
            /*
             * Zatvorené oznamy si pamätá prehliadač návštevníka — na serveri
             * by to znamenalo tabuľku na niečo, čo sa nikde inde nepoužije.
             *
             * Beží v arReady: Vue prekresľuje celý #app a zahodilo by to aj
             * skryté uzly aj poslucháčov, keby sa naviazali skôr.
             */
            (function () {
                var KEY = 'hc.announcements.dismissed';

                var read = function () {
                    try {
                        var stored = JSON.parse(window.localStorage.getItem(KEY));
                        return Array.isArray(stored) ? stored : [];
                    } catch (e) {
                        // Súkromný režim aj plná pamäť hádžu — oznam sa vtedy
                        // len ukáže znova, nie je za čo zhodiť stránku.
                        return [];
                    }
                };

                var write = function (list) {
                    try {
                        window.localStorage.setItem(KEY, JSON.stringify(list));
                    } catch (e) {}
                };

                var apply = function () {
                    var closed = read();

                    document.querySelectorAll('[data-announcement]').forEach(function (box) {
                        var id = box.getAttribute('data-announcement');

                        if (closed.indexOf(id) !== -1) {
                            // Nie `hidden`: panel oznamu nesie triedu `flex`,
                            // ktorá by pravidlo prehliadača pre [hidden] prebila.
                            box.style.display = 'none';
                            return;
                        }

                        var button = box.querySelector('[data-announcement-close]');

                        if (!button) {
                            return;
                        }

                        // Priradenie onclick, nie addEventListener: apply() beží
                        // dvakrát (pred prekreslením Vue aj po ňom) a priradenie
                        // sa nezdvojí. Značka v atribúte by nepomohla — Vue si
                        // atribúty odnesie do šablóny a po prekreslení by
                        // vyzerali ako naviazané, hoci poslucháč je preč.
                        button.onclick = function () {
                            box.style.display = 'none';

                            var list = read();
                            if (list.indexOf(id) === -1) {
                                list.push(id);
                                write(list);
                            }
                        };
                    });
                };

                apply();

                if (typeof window.arReady === 'function') {
                    window.arReady(apply);
                }
            })();
        </script>
    @endpush
@endonce
