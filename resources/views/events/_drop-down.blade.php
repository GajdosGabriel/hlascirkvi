<a target="_blank" href=" {{ $item->url['edit'] }}">
    <li class="dropdown-item">upraviť</li>
</a>

<a href="{{ route('profile.event.subscribe.index', $item->id) }}">
    <li class="dropdown-item whitespace-nowrap">Administrácia</li>
</a>

<li class="dropdown-item">
    <form action="{{ $item->url['destroy'] }}" method="POST">
        @csrf @method('DELETE')
        @if ($item->deleted_at)
            <button>odnoviť</button>
        @else
            <button>zmazať</button>
        @endif
    </form>

</li>

@can('admin')
    <a href="{{ route('eventServices.newReolad', $item->id) }}">
        <li class="dropdown-item whitespace-nowrap">New reload</li>
    </a>
@endcan
