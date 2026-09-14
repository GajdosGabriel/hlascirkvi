<x-cards.card :title="$title" :icon="$type->icon()">
    {{-- Poradie je automatické: navrchu tí, o ktorých je teraz záujem, pod nimi
         kanály, ktoré práve niečo vydali (App\Services\FrontList\FrontList). --}}
    <ul class="card_body divide-y divide-gray-100">
        @foreach ($canals as $canal)
            <li>
                <a href="{{ route('organizations.show', [$canal->id]) }}"
                   class="flex items-center justify-between gap-2 px-1 py-1.5 hover:bg-gray-100">
                    <span class="min-w-0 truncate">{{ $canal->title }}</span>

                    @if ($canal->hasFreshPost())
                        <span class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">nové</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>

    @if ($total > $canals->count())
        <p class="border-t border-gray-100 px-1 py-2 text-sm">
            <a href="{{ route('frontlist.index') }}#{{ $type->anchor() }}" class="font-semibold">
                {{ $type->showAllLabel($total) }} <i class="fa fa-angle-double-right" aria-hidden="true"></i>
            </a>
        </p>
    @endif
</x-cards.card>
