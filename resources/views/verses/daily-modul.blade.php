
<div class="border-2 rounded-sm">
    <header class="flex justify-between bg-blue-300 p-2 items-center">
        <span>Denné zamyslenie</span>
        <i class="fa fa-book" aria-hidden="true"></i>
    </header>
    <div class="p-2">
        <blockquote class="">
            @if($verse)
            <div class="">{{  $verse->biblicky_vers }}</div>
            <div class="">
                <a href="{{url('zamyslenia')}}">Viac <i class="fa fa-angle-double-right"></i></a>
                {{  $verse->biblicky_vers_ref }}
            </div>
                @endif
        </blockquote>
    </div>
</div>
