/*
 * Náhrada inline atribútov (onerror, onchange, onsubmit, onclick), aby sa
 * dala zaviesť CSP bez 'unsafe-inline' pre skripty. Šablóny používajú
 * data-atribúty, správanie je delegované na document — Vue pri pripojení
 * #app zahodí pôvodné uzly, poslucháče na uzloch by sa stratili.
 *
 *   data-auto-submit              <select>: po zmene odošle formulár
 *   data-confirm="text"           <form>: pred odoslaním sa opýta
 *   data-img-hide                 <img>: pri chybe načítania sa odstráni
 *   data-img-fallback="url"       <img>: pri chybe načítania zmení zdroj
 *   data-toggle-target="id"       odkaz: prepne hidden na prvku a zaostrí textarea
 *   data-dismiss-remember="kľúč"  tlačidlo: odstráni rodiča a zapamätá si to
 */
function onBrokenImage(img) {
    if (img.hasAttribute('data-img-hide')) {
        img.remove();
    } else if (img.dataset.imgFallback && img.getAttribute('src') !== img.dataset.imgFallback) {
        img.src = img.dataset.imgFallback;
    }
}

export default function initInlineBehaviors() {
    // „error" nebubbluje, v capture fáze ho ale na document zachytíme.
    document.addEventListener('error', (e) => {
        if (e.target instanceof HTMLImageElement) onBrokenImage(e.target);
    }, true);

    // Obrázky, ktoré zlyhali ešte pred načítaním tohto modulu.
    document.querySelectorAll('img[data-img-hide], img[data-img-fallback]').forEach((img) => {
        if (img.complete && img.naturalWidth === 0 && img.getAttribute('src')) onBrokenImage(img);
    });

    document.addEventListener('change', (e) => {
        const el = e.target;
        if (el instanceof HTMLElement && el.hasAttribute('data-auto-submit') && el.form) el.form.submit();
    });

    document.addEventListener('submit', (e) => {
        const message = e.target instanceof HTMLElement && e.target.dataset.confirm;
        if (message && !window.confirm(message)) e.preventDefault();
    });

    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;

        const toggle = e.target.closest('[data-toggle-target]');
        if (toggle) {
            e.preventDefault();
            const box = document.getElementById(toggle.dataset.toggleTarget);
            if (box) {
                box.hidden = !box.hidden;
                if (!box.hidden) box.querySelector('textarea')?.focus();
            }
            return;
        }

        const dismiss = e.target.closest('[data-dismiss-remember]');
        if (dismiss) {
            try { sessionStorage.setItem(dismiss.dataset.dismissRemember, '1'); } catch (err) { /* súkromný režim */ }
            dismiss.parentNode.remove();
        }
    });
}
