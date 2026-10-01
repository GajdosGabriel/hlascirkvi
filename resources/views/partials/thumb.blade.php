{{--
    Náhľad článku v karte. Stojí zvlášť, lebo ho vykresľujú tri šablóny kariet
    a rozdiel medzi nimi je len trieda a alt text.

    Nové obrázky majú viac šírok, WebP variant aj rozmery; staršie záznamy nie
    a vtedy ostane samotné data-src, teda správanie ako predtým.

    Očakáva: $model (Post), $alt, $class.
--}}
@php
    // Vzťah images je v $with, takže tu nevzniká dopyt navyše.
    $image = $model->images->first();
    // Karta má najviac ~400 px, 800w stačí pre retina; 1200w by len nafúklo HTML.
    // JPG srcset nesie len prehliadač bez WebP (jediný zdroj stačí) — inak by
    // každá karta niesla tie isté adresy dvakrát.
    $webp = $image?->srcset('webp', 800);
    $srcset = $webp ? null : $image?->srcset('jpg', 800);
@endphp

<picture class="block">
    @if ($webp)
        <source type="image/webp" data-srcset="{{ $webp }}" data-sizes="auto">
    @endif

    <img data-src="{{ $model->thumbImage }}"
         @if ($srcset) data-srcset="{{ $srcset }}" @endif
         @if ($image?->width) width="{{ $image->width }}" height="{{ $image->height }}" @endif
         data-sizes="auto"
         alt="{{ $alt }}"
         class="lazyload {{ $class }}">
</picture>
