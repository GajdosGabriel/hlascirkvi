@can('update', $post)
<dropdown-slot>

    <ul class="dropdown-menu z-50">
        <a href={{ $post->url['edit'] }}>
            <li class="dropdown-item">upraviť</li>
        </a>

        <li class="dropdown-item">
            <form
                action="{{ $post->url['destroy'] }}"
                method="post">
                @csrf @method('DELETE')
                @if ($post->deleted_at)
                    <button>odnoviť</button>
                @else()
                    <button>zmazať</button>
                @endif()
            </form>
        </li>

        <li class="dropdown-item whitespace-nowrap">

            <form action="{{ $post->url['update'] }}" method="post">
                @csrf @method('PUT')
                <button>Do buffer</button>
            </form>
        </li>
    </ul>
</dropdown-slot>
@endcan