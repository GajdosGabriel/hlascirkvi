<a title="{{ $title }}" href="?{{ $requestValue }}=true">
    <div class="flex hover:bg-gray-100 p-2 rounded-full h-9 w-9 items-center justify-center {{ $bgClass() }}">
        {{ $slot }}
    </div>
</a>
