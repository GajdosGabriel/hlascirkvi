
<a class="border-2  rounded-lg border-gray-100 hover:bg-indigo-400 hover:text-gray-200 w-full text-gray-900 text-center p-2
{{ Request::fullUrl() == $url ? 'bg-indigo-100 text-white' : 'bg-indigo-300' }}" href="{{ $url }}">
    {{ $title }}
</a>
