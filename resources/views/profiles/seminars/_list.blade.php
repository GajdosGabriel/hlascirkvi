@forelse ( $seminars as $seminar )

    <div class="flex justify-between mb-4">
        <a href="{{ route('profileSeminars.show', [$seminar->id]) }}" class=" hover:underline">
            <h4 class="font-semibold text-lg ">
                <seminar-title :seminar="{{ $seminar }}"></seminar-title>
            </h4>
        </a>
        @can('update', $seminar)
            <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'" />
        @endcan
    </div>

@empty
    žiadne semináre
@endforelse
