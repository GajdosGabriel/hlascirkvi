{{-- Skripty šablón bežia počas parsovania stránky, ale Vue vzápätí prekreslí
     celý #app a pôvodné uzly aj s ich poslucháčmi zahodí. arReady() ich preto
     podrží a spustí až nad hotovým stromom. window.load je poistka pre prípad,
     že by bundle nenabehol.

     Musí stáť v každom layoute s <div id="app">, inak je window.arReady
     v šablónach pod ním undefined a volanie skončí na TypeError. Definícia
     žila len v layouts/article; layouts/app a layouts/events ju nemali, hoci
     pod nimi je 55 pohľadov. Pohľady podujatí si dovtedy pomáhali vlastným
     DOMContentLoaded (funguje, lebo @vite vkladá modul, ktorý beží pred ním). --}}
<script>
    (function () {
        var pending = [];
        var started = false;

        var start = function () {
            if (started) return;
            started = true;
            pending.forEach(function (fn) { fn(); });
            pending = [];
        };

        window.arReady = function (fn) { started ? fn() : pending.push(fn); };

        document.addEventListener('app:ready', start);
        window.addEventListener('load', start);
    })();
</script>
