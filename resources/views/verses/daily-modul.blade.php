<x-cards.card :title="'Denné zamyslenie'" :icon="'components.icons.book'">
    <div class="card_body">
        <div class="font-semibold text-gray-600">
            @if ($verse)
                <p>{{ $verse->biblicky_vers }}</p>
                <p class="">
                    <a href="{{ url('zamyslenia') }}">Viac <i class="fa fa-angle-double-right"></i></a>
                    {{ $verse->biblicky_vers_ref }}
                </p>
            @endif
        </div>
    </div>
</x-cards.card>
