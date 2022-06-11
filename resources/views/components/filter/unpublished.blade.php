<a href="{{ URL::current() . '?unpublished=true' }}">
    <div @class([
        'bg-green-300' => Request::has('unpublished'),
        'flex w-min px-2 rounded-md border-gray-300 border-2 hover:bg-gray-300',
    ])>
        Nepublikované
        @if (Request::has('unpublished'))
            <a href="{{ URL::current() }}">
                <div class="px-2 hover:text-gray-400">
                    X
                </div>
            </a>
        @endif
    </div>
</a>
