{{-- Mapa podujatí. Leaflet + dlaždice OpenStreetMap — bez kľúča a bez
     registrácie, rovnako ako na portáli Event. --}}

@php
    /*
     * Koľko podujatí z tejto strany sa na mapu nedostalo. Miesto bez
     * súradníc je pri importovanom katalógu bežné („Celé Slovensko",
     * adresa len ako text), takže mapa takmer vždy ukazuje menej než
     * zoznam — bez tejto vety to vyzerá ako chyba.
     */
    $mapped = collect($mapPoints)->sum(fn ($point) => count($point['events']));
    $missing = max(0, $items->count() - $mapped);
@endphp

<div class="overflow-hidden rounded-lg border border-[color:var(--ev-line)] bg-white">

    @if ($mapPoints)
        <div id="ev-map" class="h-[30rem] w-full md:h-[34rem]"></div>
    @else
        <div class="flex h-64 flex-col items-center justify-center px-6 text-center">
            <i class="fas fa-map-marked-alt mb-3 text-3xl text-stone-300"></i>
            <p class="ev-display mb-1 font-semibold">Na mape nie je čo ukázať</p>
            <p class="text-sm text-stone-500">
                Žiadne z podujatí v tomto výbere nemá zadané súradnice miesta.
            </p>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[color:var(--ev-line)] px-4 py-2.5 text-xs text-stone-500">
        <span>
            <i class="fas fa-map-pin mr-1 text-[color:var(--ev-accent)]"></i>
            {{ $mapped }} z {{ $items->count() }} podujatí na
            {{ count($mapPoints) }} {{ count($mapPoints) === 1 ? 'mieste' : 'miestach' }}
        </span>

        @if ($missing > 0)
            <span>{{ $missing }} bez súradníc miesta — na mape ich nenájdete.</span>
        @endif

        @if ($events->hasPages())
            <span>Mapa ukazuje túto stranu výsledkov.</span>
        @endif
    </div>
</div>

@once
    @push('head')
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"
              integrity="sha512-Zcn6bjR/8RZbLEpLIeOwNtzREBAJnUKESxces60Mpoj+2okopSAcSUIUOseddDm0cxnGQzxIR7vJgsLZbdLE3w=="
              crossorigin="anonymous" referrerpolicy="no-referrer">

        <style>
            /* Vlastná značka namiesto predvolenej modrej kvapky — sadne
               palete výpisu a rovno nesie počet podujatí na mieste. */
            .ev-pin {
                background: var(--ev-accent);
                color: #fff;
                border: 2px solid #fff;
                border-radius: 999px;
                box-shadow: 0 2px 8px rgba(28, 25, 23, .45);
                font: 600 .75rem/1 Roboto, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
            }
            .ev-popup .leaflet-popup-content-wrapper {
                border-radius: .5rem;
                font-family: Roboto, sans-serif;
            }
            .ev-popup .leaflet-popup-content { margin: .75rem .9rem; }
            .leaflet-container { background: var(--ev-paper-deep); }
        </style>
    @endpush
@endonce

@if ($mapPoints)
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"
                integrity="sha512-puJW3E/qXDqYp9IfhAI54BJEaWIfloJ7JWs7OeD5i6ruC9JZL1gERT1wjtwXFlh7CjE7ZJ+/vcRZRkIYIb6p4g=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script>
            (function () {
                var points = @json($mapPoints);

                /*
                 * Vue mountuje na #app a pritom prekreslí celý obsah stránky.
                 * Keby sa mapa vytvorila hneď pri parsovaní, Leaflet by ju
                 * postavil na element, ktorý Vue vzápätí zahodí: mapa by potom
                 * merala 0×0, natiahla len štyri dlaždice a ostala prázdna.
                 * DOMContentLoaded nastane až po spustení app.js, teda po
                 * mountnutí Vue — vtedy je element v stránke už ten konečný.
                 */
                var init = function () {
                    var element = document.getElementById('ev-map');

                    if (!element || !window.L || !points.length) {
                        return;
                    }

                    var map = L.map(element, {
                        // Koliesko myši necháme stránke — inak sa pri rolovaní
                        // okolo mapy zrazu približuje mapa namiesto posunu stránky.
                        scrollWheelZoom: false,
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; prispievatelia <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 18,
                    }).addTo(map);

                    var escapeHtml = function (value) {
                        var div = document.createElement('div');
                        div.textContent = value == null ? '' : String(value);
                        return div.innerHTML;
                    };

                    var bounds = [];

                    points.forEach(function (point) {
                        var count = point.events.length;
                        var size = count > 9 ? 34 : 28;

                        var marker = L.marker([point.lat, point.lng], {
                            icon: L.divIcon({
                                className: '',
                                html: '<div class="ev-pin">' + count + '</div>',
                                iconSize: [size, size],
                                iconAnchor: [size / 2, size / 2],
                                popupAnchor: [0, -size / 2],
                            }),
                            title: point.venue || point.municipality || '',
                        });

                        // `venue` je null, keď na tom istom bode sedí viac rôznych
                        // miest — vtedy je nadpisom obec a názov miesta sa vypíše
                        // pri každom podujatí zvlášť.
                        var mixedVenues = !point.venue;
                        var heading = point.venue || point.municipality || 'Miesto';

                        var html = '<div style="max-width:16rem">'
                            + '<div style="font-weight:600;margin-bottom:.35rem">' + escapeHtml(heading) + '</div>';

                        if (point.municipality && point.municipality !== heading) {
                            html += '<div style="color:#78716c;font-size:.75rem;margin-bottom:.5rem">'
                                + escapeHtml(point.municipality) + '</div>';
                        }

                        html += '<ul style="margin:0;padding:0;list-style:none">';

                        // Dlhý zoznam by bublinu roztiahol cez celú mapu; zvyšok
                        // sa dá dopozerať vo výpise.
                        point.events.slice(0, 6).forEach(function (event) {
                            html += '<li style="margin-bottom:.5rem">'
                                + (event.date
                                    ? '<div style="color:#b45309;font-size:.7rem">' + escapeHtml(event.date) + '</div>'
                                    : '')
                                + '<a href="' + escapeHtml(event.url) + '" style="color:#1c1917;font-size:.8rem">'
                                + escapeHtml(event.title) + '</a>'
                                + (mixedVenues && event.venue
                                    ? '<div style="color:#78716c;font-size:.7rem">' + escapeHtml(event.venue) + '</div>'
                                    : '')
                                + '</li>';
                        });

                        html += '</ul>';

                        if (count > 6) {
                            html += '<div style="color:#78716c;font-size:.7rem">a ďalších '
                                + (count - 6) + ' na tomto mieste</div>';
                        }

                        marker.bindPopup(html + '</div>', { className: 'ev-popup' });
                        marker.addTo(map);
                        bounds.push([point.lat, point.lng]);
                    });

                    var fit = function () {
                        if (bounds.length === 1) {
                            map.setView(bounds[0], 13);
                        } else {
                            map.fitBounds(bounds, { padding: [40, 40] });
                        }
                    };

                    fit();

                    // Po dopočítaní rozložení (webové písma, obrázky) a pri
                    // zmene šírky okna nech si Leaflet premeria kontajner znova.
                    var refit = function () {
                        map.invalidateSize();
                        fit();
                    };

                    window.addEventListener('load', refit);
                    window.addEventListener('resize', refit);

                    // Priblíženie kolieskom až po kliknutí do mapy — vtedy s ňou
                    // návštevník naozaj pracuje.
                    map.once('click', function () {
                        map.scrollWheelZoom.enable();
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
        </script>
    @endpush
@endif
