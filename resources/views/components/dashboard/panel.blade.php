@props(['title' => null, 'flush' => false])
<section {{ $attributes->class(['ar-panel']) }}>
    @if ($title !== null || isset($note))
        <header class="ar-panel__head">
            @if ($title !== null)<h2 class="ar-panel__title">{{ $title }}</h2>@endif
            @isset($note)<div class="ar-panel__note">{{ $note }}</div>@endisset
        </header>
    @endif
    <div @class(['ar-panel__body', 'ar-panel__body--flush' => $flush])>{{ $slot }}</div>
    @isset($footer)<footer class="ar-panel__foot">{{ $footer }}</footer>@endisset
</section>
