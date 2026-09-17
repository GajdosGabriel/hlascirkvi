/**
 * Tlačidlo „Zdieľať" cez systémové menu (Web Share API).
 *
 * Na mobile ponúkne všetky aplikácie, ktoré má čitateľ nainštalované —
 * Messenger, Signal, Telegram… — nielen tých pár ikon na stránke. Kde API
 * nie je (väčšina desktopových prehliadačov), tlačidlá ostanú skryté triedou
 * `hidden` (atribút hidden by prebila trieda `flex`) a zdieľa sa ikonami.
 */
export function initNativeShare() {
    if (typeof navigator.share !== 'function') {
        return;
    }

    document.querySelectorAll('.js-native-share').forEach(function (button) {
        // `!hidden` nesú tlačidlá s triedou ar-btn, ktorej display by obyčajné
        // `hidden` prebilo (design-system sa načítava za Tailwindom).
        button.classList.remove('hidden', '!hidden');

        button.addEventListener('click', function () {
            navigator
                .share({ title: button.dataset.title, url: button.dataset.url })
                // Zavretie ponuky bez výberu je AbortError, nie chyba.
                .catch(function () {});
        });
    });
}
