<a class="border-2  rounded-lg border-gray-100 hover:bg-indigo-400 hover:text-gray-200 w-full p-2
{{ Request::fullUrl() == $url ? 'bg-indigo-500 text-white' : 'bg-indigo-300 text-gray-900 flex' }}"
    href="{{ $url }}">
    {{ $title }}
</a>
