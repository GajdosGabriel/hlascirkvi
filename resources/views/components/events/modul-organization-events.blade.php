<x-cards.card :title="$organization->title" :icon="'components.icons.event'">
    <ul>
        @forelse( $post->eventsBelongsToOrganization as $event)
            <x-cards.card_item>
                <a href="{{ $event->url }}">
                    <span style="font-weight: bold">{{ $event->start_at->format('d. m. Y') }}</span>
                    {{ $event->title }}
                </a>
            </x-cards.card_item>
        @empty
            <span class="text-muted" style="font-size: 85%">
                Spoločenstvo neplánuje žiadne
                <a class="underline" href="{{ route('akcie.index') }}">
                    podujatia.
                </a>
            </span>
        @endforelse
    </ul>
</x-cards.card>
