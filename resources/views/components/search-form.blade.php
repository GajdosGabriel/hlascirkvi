<form action="{{ URL::current() }}" class="flex items-center">

    @if (session()->has('search'))
        <a href="{{ URL::current() }}">
            <div class="border-2 bg-gray-200 px-2 rounded-md  mr-4 cursor-pointer">
                {{ session()->get('search') }} X
            </div>
        </a>
    @endif

    <span class="ar-searchbox">
        <input type="search" name="search" class="border-2 border-gray-500 px-2 rounded-md py-1" required
            aria-label="Hľadať" value="{{ session()->get('search') }}" />
        <x-search-button />
    </span>
</form>
