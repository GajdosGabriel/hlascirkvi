<div class="flex flex-nowrap mb-5">
{{--    <div>--}}
{{--        --}}{{--                    <a class="tag" href="{{ route('organization.profile', [auth()->id(), auth()->user()->slug]) }}">Profil</a>--}}
{{--        <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('organization.index', [ auth()->id(), auth()->user()->slug]) }}">Kanály</a>--}}
{{--        <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('post.create') }}">Nový článok</a>--}}
{{--        <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('event.create') }}">Nové Podujatie</a>--}}
{{--        <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('addresBook.importContacts', [ auth()->id(), auth()->user()->slug] ) }}">Moje kontakty</a>--}}
{{--    </div>--}}


        {{--                    <a class="tag" href="{{ route('organization.profile', [auth()->id(), auth()->user()->slug]) }}">Profil</a>--}}
        <a class="cursor-pointer hover:text-gray-500 h-8  w-full bg-indigo-300 text-gray-900 text-center px-2" href="{{ route('organization.index', [ auth()->id(), auth()->user()->slug]) }}">Kanály</a>
        <a class="cursor-pointer hover:text-gray-500 h-8  w-full bg-indigo-300 text-gray-900 text-center px-2" href="{{ route('post.create') }}">Nový článok</a>
        <a class="cursor-pointer hover:text-gray-500 h-8  w-full bg-indigo-300 text-gray-900 text-center px-2" href="{{ route('event.create') }}">Nové Podujatie</a>
        <a class="cursor-pointer hover:text-gray-500 h-8  w-full bg-indigo-300 text-gray-900 text-center px-2" href="{{ route('addresBook.importContacts', [ auth()->id(), auth()->user()->slug] ) }}">Moje kontakty</a>


</div>
