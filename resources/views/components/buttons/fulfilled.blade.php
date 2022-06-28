@if ($showComponent())
    <a href="{{ URL::current() . $url() }}" class="whitespace-nowrap">
        <div @class([
            'bg-green-300' => Request::has($type()),
            'flex w-min px-2 rounded-md border-gray-300 border-2 hover:bg-gray-300',
        ])>
            {{ $name() }}
            @if (Request::has($type()))
                <a href="{{ URL::current() }}">
                    <div class="px-2 hover:text-gray-400">
                        X
                    </div>
                </a>
            @endif
        </div>
    </a>
@endif