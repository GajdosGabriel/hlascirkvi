@props(['name', 'value', 'request' => ''])

@php
$classes = $published ?? false ? 'font-semibold border-b-2 border-gray-400' : '';
@endphp

<a href="{{ URL::current() . $request }}" class="whitespace-nowrap">
    <div @class([
        'bg-green-300' => $value == Request::query('lastDays'),
        'flex w-min px-2 rounded-md border-gray-300 border-2 hover:bg-green-200',
    ])>
        {{ $name }}
        @if (Request::has($value))
            <a href="{{ URL::current() }}">
                <div class="px-2 hover:text-gray-400">
                    X
                </div>
            </a>
        @endif
    </div>
</a>
