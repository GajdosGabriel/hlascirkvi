/**
 * Prehliadač obrázkov (lightbox) pre verejnú časť — prevzatý z projektu event.
 *
 * Klik na odkaz s `data-lightbox="<skupina>"` otvorí obrázok na čiernej ploche
 * namiesto skoku do novej karty. Druhý klik priblíži na rozlíšenie originálu,
 * aby sa dal prečítať text na plagátoch; bod, na ktorý používateľ klikol,
 * ostáva v strede pohľadu.
 *
 * Vrstva sa vkladá priamo do `body`, teda mimo `#app`, a poslucháč visí na
 * `document` — Vue pri mountovaní prekresľuje len `#app`, takže sa ho to
 * nedotkne a nezáleží na poradí načítania.
 */

/** Koľkonásobne sa priblíži obrázok, ktorý sa v okne zobrazuje takmer 1:1. */
const MIN_ZOOM = 1.8;
/** Strop priblíženia, aby sa z malého obrázka nestala rozmazaná plocha. */
const MAX_ZOOM = 4;

const STYLE = `
.ar-lb { position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.88);
         opacity: 0; transition: opacity .15s ease; }
.ar-lb.is-open { opacity: 1; }
.ar-lb[hidden] { display: none; }
.ar-lb__viewport { display: flex; width: 100%; height: 100%; overflow: hidden; padding: 1rem; }
.ar-lb.is-zoomed .ar-lb__viewport { overflow: auto; padding: 0; }
.ar-lb__img { margin: auto; display: block; height: auto; max-height: 90vh; max-width: 90vw;
              object-fit: contain; border-radius: .75rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,.6);
              cursor: zoom-in; user-select: none; -webkit-user-select: none; }
/* Bez flex: none by pružný kontajner priblížený obrázok stiahol späť na svoju
   šírku a zväčšenie by sa zastavilo na rozlíšení originálu. */
.ar-lb.is-zoomed .ar-lb__img { flex: none; max-height: none; max-width: none; border-radius: 0;
                               box-shadow: none; cursor: zoom-out; }
.ar-lb__btn { position: absolute; display: flex; align-items: center; justify-content: center;
              border: 0; border-radius: 9999px; background: rgba(255,255,255,.1); color: #fff;
              cursor: pointer; line-height: 0; transition: background .15s ease; }
.ar-lb__btn:hover { background: rgba(255,255,255,.25); }
.ar-lb__btn[hidden] { display: none; }
.ar-lb__btn svg { width: 1.25rem; height: 1.25rem; }
.ar-lb__close { top: 1rem; right: 1rem; padding: .5rem; }
.ar-lb__nav { top: 50%; transform: translateY(-50%); padding: .75rem; }
.ar-lb__nav--prev { left: 1rem; }
.ar-lb__nav--next { right: 1rem; }
.ar-lb.is-zoomed .ar-lb__nav { display: none; }
.ar-lb__count { position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%);
                pointer-events: none; border-radius: 9999px; background: rgba(0,0,0,.5);
                padding: .25rem .75rem; font-size: .75rem; color: #fff; }
.ar-lb__count[hidden] { display: none; }
.ar-lb.is-zoomed .ar-lb__count { display: none; }
`;

const ICONS = {
    close: 'M6 18L18 6M6 6l12 12',
    prev: 'M15 19l-7-7 7-7',
    next: 'M9 5l7 7-7 7',
};

function icon(path) {
    return '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">'
        + '<path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';
}

/**
 * Zobrazuje sa to, kam odkaz vedie — teda ten istý obrázok, ktorý sa doteraz
 * otváral v novej karte. `data-lightbox-src` to prebije ľahším náhľadom.
 */
function displaySrc(link) {
    return link.dataset.lightboxSrc || link.href;
}

/** Väčší obrázok pre priblíženie; sťahuje sa až pri ňom, v náhľade by bol zbytočný. */
function zoomSrc(link) {
    return link.dataset.lightboxZoom || link.href;
}

function altText(link) {
    const img = link.querySelector('img');
    return (img && img.getAttribute('alt')) || link.getAttribute('title') || '';
}

/** Načíta obrázok do cache prehliadača; `null` keď sa nepodarí. */
function preload(src) {
    return new Promise((resolve) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => resolve(null);
        image.src = src;
    });
}

class Lightbox {
    constructor() {
        this.items = [];
        this.index = 0;
        this.zoomed = false;
        this.hiRes = null;
        this.panStart = null;
        this.panMoved = false;
        this.build();
    }

    build() {
        const style = document.createElement('style');
        style.textContent = STYLE;
        document.head.appendChild(style);

        const root = document.createElement('div');
        root.className = 'ar-lb';
        root.hidden = true;
        root.innerHTML = '<div class="ar-lb__viewport"><img class="ar-lb__img" alt="" draggable="false"></div>'
            + '<button type="button" class="ar-lb__btn ar-lb__close" title="Zavrieť (Esc)">' + icon(ICONS.close) + '</button>'
            + '<button type="button" class="ar-lb__btn ar-lb__nav ar-lb__nav--prev" title="Predchádzajúci">' + icon(ICONS.prev) + '</button>'
            + '<button type="button" class="ar-lb__btn ar-lb__nav ar-lb__nav--next" title="Ďalší">' + icon(ICONS.next) + '</button>'
            + '<div class="ar-lb__count"></div>';
        document.body.appendChild(root);

        this.root = root;
        this.viewport = root.querySelector('.ar-lb__viewport');
        this.img = root.querySelector('.ar-lb__img');
        this.prevBtn = root.querySelector('.ar-lb__nav--prev');
        this.nextBtn = root.querySelector('.ar-lb__nav--next');
        this.count = root.querySelector('.ar-lb__count');

        root.querySelector('.ar-lb__close').addEventListener('click', () => this.close());
        this.prevBtn.addEventListener('click', () => this.go(this.index - 1));
        this.nextBtn.addEventListener('click', () => this.go(this.index + 1));

        // Klik mimo obrázka zatvára — ale nie vtedy, keď to bol koniec ťahania.
        this.viewport.addEventListener('click', (e) => {
            if (this.panMoved) { this.panMoved = false; return; }
            if (e.target === this.viewport) this.close();
        });
        this.img.addEventListener('click', (e) => { e.stopPropagation(); this.onImageClick(e); });
        this.viewport.addEventListener('pointerdown', (e) => this.onPointerDown(e));

        // Originál občas v úložisku chýba, náhľad býva vždy — bez zálohy by na
        // čiernej ploche ostalo prázdne miesto.
        this.img.addEventListener('error', () => {
            const fallback = this.items[this.index] && this.items[this.index].fallback;
            if (fallback && this.img.getAttribute('src') !== fallback) this.img.src = fallback;
        });

        document.addEventListener('keydown', (e) => this.onKeydown(e));
    }

    get isOpen() { return !this.root.hidden; }

    open(items, index) {
        this.items = items;
        this.root.hidden = false;
        // Vynútený prepočet rozloženia dá prechodu východiskový stav.
        // requestAnimationFrame by tu neposlúžil — v skrytej karte prehliadača
        // nebeží a vrstva by ostala priehľadná.
        void this.root.offsetWidth;
        this.root.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        this.go(index);
    }

    close() {
        this.setZoom(false);
        this.root.classList.remove('is-open');
        document.body.style.overflow = '';
        const hide = () => {
            if (this.root.classList.contains('is-open')) return;
            this.root.hidden = true;
            this.img.removeAttribute('src');
        };
        this.root.addEventListener('transitionend', hide, { once: true });
        // Poistka, keby prechod nenabehol (napríklad v skrytej karte prehliadača).
        setTimeout(hide, 300);
    }

    go(index) {
        if (index < 0 || index >= this.items.length) return;
        this.index = index;
        this.setZoom(false);
        this.hiRes = null;
        const item = this.items[index];
        this.img.src = item.src;
        this.img.alt = item.alt;
        this.prevBtn.hidden = index === 0;
        this.nextBtn.hidden = index === this.items.length - 1;
        this.showCount();
    }

    showCount() {
        this.count.hidden = this.items.length < 2;
        this.count.textContent = (this.index + 1) + ' / ' + this.items.length;
    }

    setZoom(on) {
        this.zoomed = on;
        this.root.classList.toggle('is-zoomed', on);
        if (!on) this.img.style.width = '';
    }

    onKeydown(e) {
        if (!this.isOpen) return;
        // Esc najprv zruší priblíženie, až potom zatvára — inak by sa nedalo
        // vrátiť späť na celý obrázok.
        if (e.key === 'Escape') { this.zoomed ? this.setZoom(false) : this.close(); }
        else if (e.key === 'ArrowLeft') this.go(this.index - 1);
        else if (e.key === 'ArrowRight') this.go(this.index + 1);
        else return;
        e.preventDefault();
    }

    /**
     * Druhý klik na obrázok priblíži na rozlíšenie originálu (najviac MAX_ZOOM),
     * aby sa dal prečítať text na plagátoch. Ďalší klik vráti obrázok späť.
     */
    async onImageClick(event) {
        if (this.panMoved) { this.panMoved = false; return; }
        if (this.zoomed) { this.setZoom(false); return; }

        const rect = this.img.getBoundingClientRect();
        if (!rect.width) return;

        const relX = (event.clientX - rect.left) / rect.width;
        const relY = (event.clientY - rect.top) / rect.height;

        let natural = this.img.naturalWidth || rect.width;
        const item = this.items[this.index];

        if (item.zoom && item.zoom !== item.src && !this.hiRes) {
            const wanted = this.index;
            this.count.hidden = false;
            this.count.textContent = 'Načítavam…';
            const loaded = await preload(item.zoom);
            if (this.index !== wanted) return;
            this.showCount();
            if (loaded) {
                this.hiRes = item.zoom;
                this.img.src = item.zoom;
                natural = Math.max(natural, loaded.naturalWidth);
            }
        } else if (this.hiRes) {
            natural = Math.max(natural, this.img.naturalWidth);
        }

        const factor = Math.min(Math.max(natural / rect.width, MIN_ZOOM), MAX_ZOOM);
        this.img.style.width = Math.round(rect.width * factor) + 'px';
        this.setZoom(true);

        // Bod, na ktorý používateľ klikol, ostáva v strede pohľadu — rovnako
        // ako to robia bežné prehliadače fotiek.
        this.viewport.scrollLeft = relX * this.viewport.scrollWidth - this.viewport.clientWidth / 2;
        this.viewport.scrollTop = relY * this.viewport.scrollHeight - this.viewport.clientHeight / 2;
    }

    // Posúvanie myšou v priblíženom stave. Dotykové zariadenia scrollujú natívne.
    onPointerDown(event) {
        if (!this.zoomed || event.pointerType !== 'mouse' || event.button !== 0) return;

        this.panStart = {
            x: event.clientX,
            y: event.clientY,
            left: this.viewport.scrollLeft,
            top: this.viewport.scrollTop,
        };
        this.panMoved = false;
        event.preventDefault();

        const move = (e) => {
            if (!this.panStart) return;
            const dx = e.clientX - this.panStart.x;
            const dy = e.clientY - this.panStart.y;
            if (Math.abs(dx) > 4 || Math.abs(dy) > 4) this.panMoved = true;
            this.viewport.scrollLeft = this.panStart.left - dx;
            this.viewport.scrollTop = this.panStart.top - dy;
        };
        const up = () => {
            this.panStart = null;
            window.removeEventListener('pointermove', move);
            window.removeEventListener('pointerup', up);
        };

        window.addEventListener('pointermove', move);
        window.addEventListener('pointerup', up);
    }
}

let instance = null;

document.addEventListener('click', (event) => {
    // Ctrl/⌘ a stredný klik nechávame prehliadaču — používateľ chce novú kartu.
    if (event.defaultPrevented || event.button !== 0) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const target = event.target instanceof Element ? event.target : null;
    const link = target && target.closest('a[data-lightbox]');
    if (!link) return;

    const group = link.dataset.lightbox;
    const links = Array.prototype.slice.call(
        document.querySelectorAll('a[data-lightbox="' + group.replace(/"/g, '\\"') + '"]')
    );
    const index = links.indexOf(link);
    if (index === -1) return;

    event.preventDefault();

    if (!instance) instance = new Lightbox();
    instance.open(links.map((el) => ({
        src: displaySrc(el),
        zoom: zoomSrc(el),
        fallback: el.dataset.lightboxFallback || null,
        alt: altText(el),
    })), index);
});
