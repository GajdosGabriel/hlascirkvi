<x-cards.card :title="$organization->title" :icon="'components.icons.event'">
    <ul class="p-2">
        @forelse( $post->eventsBelongsToOrganization as $event)
            <li>
                <a href="{{ $event->url }}">
                    <span style="font-weight: bold">{{ $event->start_at->format('d. m. Y') }}</span>
                    {{ $event->title }}
                </a>
            </li>
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
