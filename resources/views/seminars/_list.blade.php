@forelse ( $seminars as $seminar )

    <div class="flex justify-between mb-4">
        <a href="{{ route('seminar.posts.index', [$seminar->id]) }}">
            <h4 class="font-semibold text-lg">{{ $seminar->title }}</h4>
        </a>
        @can('update', $seminar)
            <c-article-dropdown :post="{{ $seminar }}" :model="/seminars/" />
        @endcan
    </div>

@empty
    žiadne semináre
@endforelse
