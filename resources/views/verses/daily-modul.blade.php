<div class="border-2 rounded-sm my-4">
    <header class="flex justify-between bg-blue-300 p-2 items-center">
        <span>Denné zamyslenie</span>
        <i class="fa fa-book" aria-hidden="true"></i>
    </header>

    <div class="p-2 font-semibold text-gray-600">
        @if($verse)
            <p>{{  $verse->biblicky_vers }}</p>
            <p class="">
                <a href="{{url('zamyslenia')}}">Viac <i class="fa fa-angle-double-right"></i></a>
                {{  $verse->biblicky_vers_ref }}
            </p>
        @endif
    </div>
</div>
