<a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full text-gray-900 text-center p-2
{{ Request::fullUrl() == $url ? 'bg-indigo-500' : 'bg-indigo-300' }}"
href="{{ $url }}">
    {{ $title }}
</a>
