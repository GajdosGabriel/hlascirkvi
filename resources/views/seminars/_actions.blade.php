@can('update', $seminar)
    <dropdown-slot :label="@js($seminar->kind === 'collection' ? 'Spravovať kolekciu' : 'Spravovať seminár')">
        <a href="{{ route('profile.canals.seminars.show', [$seminar->canal_id, $seminar->id]) }}">
            <i class="ph ph-list-checks" aria-hidden="true"></i> Spravovať príspevky
        </a>
        <a href="{{ route('profile.canals.seminars.edit', [$seminar->canal_id, $seminar->id]) }}">
            <i class="ph ph-pencil-simple" aria-hidden="true"></i> Upraviť
        </a>
        @can('delete', $seminar)
            <form action="{{ route('profile.canals.seminars.destroy', [$seminar->canal_id, $seminar->id]) }}"
                  method="post" data-confirm="Naozaj zmazať túto kolekciu? Príspevky zostanú zachované.">
                @csrf @method('DELETE')
                <button type="submit" class="ui-dropdown__item--danger">
                    <i class="ph ph-trash" aria-hidden="true"></i> Zmazať
                </button>
            </form>
        @endcan
    </dropdown-slot>
@endcan
