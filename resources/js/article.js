/**
 * Skripty detailu príspevku: ukazovateľ prečítanej časti a vodorovný pás
 * archívu kanála.
 *
 * Bolo to 133 riadkov inline v resources/views/posts/show.blade.php, teda
 * mimo buildu — neminifikované, bez lintu a bez cache. Obe funkcie sa samy
 * ukončia, keď ich uzly na stránke nie sú, takže modul môže ísť do hlavného
 * bundlu a na ostatných stránkach nič nerobí.
 *
 * window.arReady() definuje partials/ar-ready.blade.php — Vue pri mountnutí
 * prekreslí celý #app, takže poslucháče sa vešajú až nad hotovým stromom.
 */
export function initArticle() {
    // Ukazovateľ prečítanej časti — počíta sa z výšky článku, nie stránky,
    // aby komentáre a archív pod ním neposúvali pruh predčasne na koniec.
    window.arReady(function () {
        var bar = document.querySelector('.js-reading-progress');
        var article = document.querySelector('article');
        if (!bar || !article) return;

        var update = function () {
            var span = article.offsetHeight - window.innerHeight;
            if (span <= 0) return;

            var ratio = (window.pageYOffset - article.offsetTop) / span;
            bar.style.width = Math.min(100, Math.max(0, ratio * 100)) + '%';
        };

        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });

    // Pás archívu kanála. Šípky posúvajú o šírku výrezu, doťahovanie beží
    // samo pred koncom pásu — kým čitateľ dojde k poslednej karte, ďalšia
    // dávka už v ňom je. Dlaždica na konci ostáva ako ručná poistka
    // (a bez skriptu ako obyčajný odkaz na kanál).
    window.arReady(function () {
        var rail = document.querySelector('[data-rail]');
        if (!rail) return;

        var shell = rail.querySelector('[data-rail-shell]');
        var track = rail.querySelector('[data-rail-track]');
        var more  = rail.querySelector('[data-rail-more]');
        if (!shell || !track || !more) return;

        var prev  = rail.querySelector('[data-rail-prev]');
        var next  = rail.querySelector('[data-rail-next]');
        var bar   = rail.querySelector('[data-rail-bar]');
        var label = rail.querySelector('[data-rail-more-label]');
        var icon  = more.querySelector('i');

        var url    = rail.dataset.railUrl;
        var cursor = rail.dataset.railNext || '';
        var busy   = false;
        var ticking = false;

        // Archív sa minul — dlaždica sa vráti k tomu, čím je bez skriptu:
        // odkazu na celý kanál.
        var finish = function () {
            cursor = '';
            more.classList.add('is-final');
            if (label) label.textContent = 'Zobraziť celý kanál';
        };

        // Necelý výrez, nech na okraji ostane rozčítaná karta ako stopa,
        // kde posun pokračuje.
        var step = function () { return Math.max(240, track.clientWidth * 0.85); };

        var paint = function () {
            var max  = track.scrollWidth - track.clientWidth;
            var left = track.scrollLeft;
            var atStart = left <= 4;
            var atEnd   = left >= max - 4;

            shell.classList.toggle('is-start', atStart);
            shell.classList.toggle('is-end', atEnd);
            if (prev) prev.hidden = atStart;
            if (next) next.hidden = atEnd && !cursor;
            if (bar)  bar.style.width = (max > 0 ? Math.min(100, (left / max) * 100) : 100) + '%';

            // Karta a kus navyše pred koncom — dávka stihne doraziť skôr,
            // než sa čitateľ doposúva na jej miesto.
            if (max - left < 400) load();
        };

        var load = function () {
            if (!cursor || busy) return;
            busy = true;
            more.classList.add('is-loading');
            if (icon) icon.className = 'fas fa-circle-notch fa-spin';

            fetch(url + '?cursor=' + encodeURIComponent(cursor), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    more.insertAdjacentHTML('beforebegin', data.html);
                    cursor = data.next || '';

                    if (!cursor) finish();
                })
                .catch(function () {
                    // Ticho: v páse ostane, čo už je, a dlaždica vedie na
                    // kanál, takže sa čitateľ k zvyšku aj tak dostane.
                    finish();
                })
                .then(function () {
                    busy = false;
                    more.classList.remove('is-loading');
                    if (icon) icon.className = 'fas fa-arrow-right';
                    paint();
                });
        };

        more.addEventListener('click', function (event) {
            if (!cursor) return;      // bez ďalšej dávky nech odkaz funguje
            event.preventDefault();
            load();
        });

        if (prev) prev.addEventListener('click', function () {
            track.scrollBy({ left: -step(), behavior: 'smooth' });
        });

        if (next) next.addEventListener('click', function () {
            track.scrollBy({ left: step(), behavior: 'smooth' });
        });

        track.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () { ticking = false; paint(); });
        }, { passive: true });

        window.addEventListener('resize', paint);

        if (!cursor) finish();
        paint();
    });

}
