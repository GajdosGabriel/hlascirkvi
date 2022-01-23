<form action="{{ URL::current() }}" class="flex items-center">
    
    @if (session()->has('search'))
        <a href="{{ URL::current() }}">
            <div class="border-2 bg-gray-200 px-2 rounded-md  mr-4 cursor-pointer">
                {{ session()->get('search') }} X
            </div>
        </a>
    @endif

    <input name="posts" value="search" type="hidden" class="border-2 border-gray-500 px-2 rounded-md" />
    <input name="title" class="border-2 border-gray-500 px-2 rounded-md py-1" required
        value="{{ session()->get('search') }}" />

    <button class="btn btn-small">Hľadať</button>
</form>
