<a href="{{ URL::current() . '?unpublished=true' }}">
    <div @if (Request::filled('unpublished')) class="bg-green-300 w-min px-2 rounded-md flex" @endif>
        Nepublikované
        @if (Request::filled('unpublished'))
            <a href="{{ URL::current() }}">
                <div class="px-2 hover:text-gray-400">
                    X
                </div>
            </a>
        @endif
    </div>
</a>
