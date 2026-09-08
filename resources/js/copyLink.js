/**
 * Tlačidlo „Kopírovať odkaz".
 *
 * Rovnaký kód bol nakopírovaný v resources/views/posts/show.blade.php aj
 * v resources/views/events/portal/show.blade.php. Odtiaľ ho obe stránky
 * dostanú z bundlu — a prejde cez build, nie ako neminifikovaný inline blok.
 *
 * clipboard API funguje len cez https; na http sa tlačidlo bez fallbacku
 * správalo, akoby sa nič nestalo.
 */
export function initCopyLink() {
    document.querySelectorAll('.js-copy-link').forEach(function (button) {
        button.addEventListener('click', function () {
            var done = function () {
                var original = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Skopírované';
                setTimeout(function () { button.innerHTML = original; }, 2000);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(button.dataset.url).then(done);
                return;
            }

            var field = document.createElement('input');
            field.value = button.dataset.url;
            document.body.appendChild(field);
            field.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(field);
        });
    });
}
