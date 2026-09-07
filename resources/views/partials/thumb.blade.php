{{--
    Náhľad článku v karte. Stojí zvlášť, lebo ho vykresľujú tri šablóny kariet
    a rozdiel medzi nimi je len trieda a alt text.

    Nové obrázky majú viac šírok aj WebP variant, staršie záznamy nie —
    v tom prípade ostane samotné data-src a správanie je ako predtým.

    Očakáva: $model (Post), $alt, $class.
--}}
@php
    $srcset = $model->thumbImageSrcset;
    $webp = $model->thumbImageWebpSrcset;
@endphp

<picture class="block">
    @if ($webp)
        <source type="image/webp" data-srcset="{{ $webp }}" data-sizes="auto">
    @endif

    <img data-src="{{ $model->thumbImage }}"
         @if ($srcset) data-srcset="{{ $srcset }}" @endif
         data-sizes="auto"
         alt="{{ $alt }}"
         class="lazyload {{ $class }}">
</picture>
