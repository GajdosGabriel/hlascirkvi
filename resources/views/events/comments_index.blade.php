
<span>Hľadám na akciu:</span>
    @forelse($commentsLook as $comment)
        <span class="comment">
            <div class="flex">

                    <small> {{ $comment->user->Fullname }}: </small>

                @if(auth()->user() AND $comment->organization_id == auth()->user()->org_id)
                    <small><a href="{{ route('comment.destroy', [$comment->id]) }}">zmazať</a></small>

                @else

                    @if($comment->published)
                        <small><a>mám&nbsp;záujem</a></small>
                    @else
                        <small><a>obsadené</a></small>
                    @endif


                @endif
            </div>
            {{ $comment->body }}
        </span>
    @empty
        @if(auth()->check())
        {{--<h5 style="margin-top: -3rem">Pridajte prvý komentár</h5>--}}
        @else
        {{--<h5 style="margin-top: -3rem"><a href="{{ url('login') }}">Pridajte prvý komentár</a></h5>--}}
        @endif
    @endforelse


    <span>Ponúkam na akciu:</span>

    @forelse($commentsOffer as $comment)
        <span style="background: #ded1d1" class="comment">
            <div class="level">

                <small> {{ $comment->user->Fullname }}: </small>

                @if(auth()->user() AND $comment->organization_id == auth()->user()->org_id)
                    <small><a href="{{ route('comment.destroy', [$comment->id]) }}">zmazať</a></small>

                @else

                    @if($comment->published)
                        <small><a>mám&nbsp;záujem</a></small>
                    @else
                        <small><a>obsadené</a></small>
                    @endif


                @endif
            </div>
            {{ $comment->body }}
        </span>
    @empty
        @if(auth()->check())
            {{--<h5 style="margin-top: -3rem">Pridajte prvý komentár</h5>--}}
        @else
            {{--<h5 style="margin-top: -3rem"><a href="{{ url('login') }}">Pridajte prvý komentár</a></h5>--}}
        @endif
    @endforelse



