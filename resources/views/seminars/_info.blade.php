<div class="mb-6">
    <p class="text-sm font-semibold mb-2">{{ $seminar->kind_label }}</p>
    <p class="text-sm text-gray-500">
        Pridal: <a class="ar-link" href="{{ route('organizations.show', $seminar->canal_id) }}">{{ $seminar->canal->title }}</a>
    </p>
    @if ($seminar->description)
        <p class="mt-3 whitespace-pre-line">{{ $seminar->description }}</p>
    @endif
    @can('update', $seminar)
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="text-sm text-gray-500">{{ $seminar->published ? 'Zverejnené' : 'Koncept · viditeľné iba správcom' }}</span>
            <form action="{{ route('profile.canals.seminars.update', [$seminar->canal_id, $seminar->id]) }}" method="post">
                @csrf @method('PUT')
                <input type="hidden" name="published" value="{{ $seminar->published ? '' : now()->toDateTimeString() }}">
                <button type="submit" class="btn">{{ $seminar->published ? 'Zrušiť zverejnenie' : 'Zverejniť' }}</button>
            </form>
            @if ($seminar->youtube_playlist)
                <form action="{{ route('seminars.uploadVideos', $seminar->id) }}" method="post"
                      data-confirm="Načítať videá z YouTube zoznamu? Import môže chvíľu trvať.">
                    @csrf
                    <button type="submit" class="btn">Načítať videá z YouTube</button>
                </form>
            @else
                <a class="btn" href="{{ route('profile.canals.seminars.edit', [$seminar->canal_id, $seminar->id]) }}">Doplniť playlist</a>
            @endif
        </div>
    @endcan
</div>
