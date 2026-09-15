@props(['unsubscribeUrl' => null])
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')" />
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
**HlasCirkvi.sk** — kresťanský portál

[Navštíviť portál]({{ config('app.url') }}) · [Ochrana osobných údajov]({{ route('gdpr') }})

@if ($unsubscribeUrl)
Tento e-mail dostávate, lebo máte zapnutý odber noviniek. [Odhlásiť odber]({{ $unsubscribeUrl }})
@else
Tento e-mail sa týka vášho účtu na portáli HlasCirkvi.sk.
@endif

© {{ date('Y') }} HlasCirkvi.sk
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
