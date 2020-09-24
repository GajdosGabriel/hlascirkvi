<div class="card">
    <div class="card-header">
        <span>Osobný Credit</span>
        <i class="fab fa-pagelines"></i>
    </div>
    {{--<div class="card-body">--}}
        {{--<div class="level">--}}
            {{--<div>Návšteva web</div>--}}
            {{--<div>10</div>--}}
        {{--</div>--}}

        {{--<div class="level">--}}
            {{--<div>Qiuz creditov</div>--}}
            {{--<div>30</div>--}}
        {{--</div>--}}

    {{--</div>--}}

    <div class="footer level" style="padding: .7rem; border-top: .1rem solid black">
        <div>Celkom kreditov</div>
        @if(Auth::check())
            <div>{{ Auth::user()->credit }}</div>
            @else
        <div>{{ \Cache::get('actualCredit') }}</div>
            @endif
    </div>
</div>