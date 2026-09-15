@props(['unsubscribeUrl' => null])
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            Hlas Cirkvi — kresťanský portál
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            HlasCirkvi.sk — kresťanský portál: {{ config('app.url') }}
            Ochrana osobných údajov: {{ route('gdpr') }}
            @if ($unsubscribeUrl)
            Odhlásiť odber noviniek: {{ $unsubscribeUrl }}
            @endif
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
