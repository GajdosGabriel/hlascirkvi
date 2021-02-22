<section class="card">
    <header class="card_header">
        <span>Denné zamyslenie</span>
        <i class="fa fa-book" aria-hidden="true"></i>
    </header>

    <div class="card_body">
        <div class="font-semibold text-gray-600">
            @if($verse)
                <p>{{  $verse->biblicky_vers }}</p>
                <p class="">
                    <a href="{{url('zamyslenia')}}">Viac <i class="fa fa-angle-double-right"></i></a>
                    {{  $verse->biblicky_vers_ref }}
                </p>
            @endif
        </div>
    </div>
</section>
