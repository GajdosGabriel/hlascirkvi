@php
    // Porovnáva sa adresa bez query stringu — s fullUrl() stratila položka
    // zvýraznenie hneď, ako sa na výpise zapol filter alebo stránkovanie.
    $active = \Request::url() === $url;
@endphp

<a class="border-2 rounded-lg border-gray-100 hover:bg-indigo-400 hover:text-gray-200 w-full p-2 flex items-center
{{ $active ? 'bg-indigo-500 text-white' : 'bg-indigo-300 text-gray-900 flex' }}"
    href="{{ $url }}" @if ($active) aria-current="page" @endif>
    {{ $title }}
</a>
