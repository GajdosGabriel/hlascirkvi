<x-cards.card :title="$organization->title" :icon="'components.icons.event'">
    <ul>
        @forelse( $post->eventsBelongsToOrganization as $event)
            <x-cards.carditem>
                <a href="{{ $event->routeShow() }}">
                    <span style="font-weight: bold">{{ $event->start_at->format('d. m. Y') }}</span>
                    {{ $event->title }}
                </a>
            </x-cards.carditem>
        @empty
            <span class="text-muted px-3" style="font-size: 85%">
                Spoločenstvo neplánuje žiadne
                <a class="underline" href="{{ route('public.event.index') }}">
                    podujatia.
                </a>
            </span>
        @endforelse
    </ul>
</x-cards.card>
