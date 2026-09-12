<x-cards.card :title="$title" :icon="'components.icons.users'">
    <ul class="card_body divide-y divide-gray-100">
        @foreach ($canals as $canal)
            <li>
                <a href="{{ route('organizations.show', [$canal->id]) }}"
                   class="flex items-center justify-between gap-2 px-1 py-1.5 hover:bg-gray-100">
                    <span class="min-w-0 truncate">{{ $canal->title }}</span>

                    {{-- Počet zverejnených príspevkov, nie všetkých v databáze:
                         to, čo ešte čaká v bufferi, návštevník nikde neuvidí. --}}
                    <span class="shrink-0 text-sm text-gray-500">{{ $canal->postsCount }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    @if ($total > $canals->count())
        <p class="border-t border-gray-100 px-1 py-2 text-sm">
            <a href="{{ route('frontlist.index') }}" class="font-semibold">
                Zobraziť všetkých {{ $total }} <i class="fa fa-angle-double-right" aria-hidden="true"></i>
            </a>
        </p>
    @endif
</x-cards.card>
