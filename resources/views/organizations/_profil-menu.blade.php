<div class="flex mb-5 cursor-pointer">
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center p-2" href="{{ route('organization.index', [ auth()->id(), auth()->user()->slug]) }}">Kanály</a>
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center p-2" href="{{ route('posts.create') }}">Nový článok</a>
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center p-2" href="{{ route('akcie.create') }}">Nové Podujatie</a>
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center p-2" href="{{ route('addresBook.importContacts', [ auth()->id(), auth()->user()->slug] ) }}">Moje kontakty</a>

</div>


