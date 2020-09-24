
<div class="card">
    <div class="card-header">
        <span>Denné zamyslenie</span>
        <i class="fa fa-book" aria-hidden="true"></i>
    </div>
    <div class="card-body">
        <blockquote class="blockquote clearfix">
            @if($verse)
            <div class="left">{{  $verse->biblicky_vers }}</div>
            <div style="margin-top:1rem " class="footer">
                <a style="float: left;display:block;text-align:left" href="{{url('zamyslenia')}}">Viac <i class="fa fa-angle-double-right"></i></a>
                {{  $verse->biblicky_vers_ref }}
            </div>
                @endif
        </blockquote>
    </div>
</div>