/**
 * Skripty detailu príspevku: ukazovateľ prečítanej časti a doťahovanie
 * ďalších riadkov do mriežky archívu kanála.
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

    // Archív kanála pod článkom. Dávka zo servera je presne jeden riadok
    // mriežky na širokej obrazovke, takže kliknutie na "Viac príspevkov"
    // pridá riadok a stránka sa nikam nepresunie. Bez skriptu ostáva
    // z tlačidla obyčajný odkaz na kanál.
    window.arReady(function () {
        var archive = document.querySelector('[data-archive]');
        if (!archive) return;

        var grid = archive.querySelector('[data-archive-grid]');
        var more = archive.querySelector('[data-archive-more]');
        if (!grid || !more) return;

        var label = more.querySelector('[data-archive-label]');
        var icon  = more.querySelector('i');

        var url    = archive.dataset.archiveUrl;
        var cursor = archive.dataset.archiveNext || '';
        var busy   = false;

        // Archív sa minul — tlačidlo sa vráti k tomu, čím je bez skriptu:
        // odkazu na celý kanál.
        var finish = function () {
            cursor = '';
            if (label) label.textContent = 'Zobraziť celý kanál';
            if (icon) icon.className = 'fas fa-arrow-right';
        };

        var load = function () {
            if (!cursor || busy) return;
            busy = true;
            if (label) label.textContent = 'Načítavam…';
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
                    grid.insertAdjacentHTML('beforeend', data.html);
                    cursor = data.next || '';

                    if (cursor) {
                        if (label) label.textContent = 'Viac príspevkov';
                        if (icon) icon.className = 'fas fa-arrow-down';
                    } else {
                        finish();
                    }
                })
                .catch(function () {
                    // Ticho: v mriežke ostane, čo už v nej je, a tlačidlo vedie
                    // na kanál, takže sa čitateľ k zvyšku aj tak dostane.
                    finish();
                })
                .then(function () {
                    busy = false;
                });
        };

        more.addEventListener('click', function (event) {
            if (!cursor) return;      // bez ďalšej dávky nech odkaz funguje
            event.preventDefault();
            load();
        });

        if (!cursor) finish();
    });
}
