{{-- Dávka kariet pásu. Rovnaký súbor vkladá šablóna detailu aj JSON
     odpoveď pre doťahovanie, takže obe cesty vrátia zhodné značky. --}}
@foreach ($items as $item)
    @include('posts._rail-item', ['item' => $item])
@endforeach
