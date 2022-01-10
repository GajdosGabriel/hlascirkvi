<a title="{{ $title }}" href="?posts={{ $name }}">
    <div
        {{ $attributes->merge(['class' => 'flex  hover:bg-blue-300 p-2 rounded-full h-9 w-9 items-center justify-center mr-1 ' . $bgClass]) }}>
        {{ $slot }}
    </div>
</a>
