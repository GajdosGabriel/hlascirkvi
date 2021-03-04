@if(auth()->check())
<section class="module">
    <div class="top-bar">

        <div class="flex">
            <h1>Správa pre  ...</h1>
            <span>X</span>
        </div>

    </div>

    <ol class="discussion">

        @foreach($messages as $message)
            @if(auth()->user()->id == $message->requested_organization)
            <li class="other">
                <div class="avatar">
                    <img src="{{  url('storage/'. $user->userPictureUrl() ) }}" />
                </div>
                <div class="messages">
                    <p> {{ $message->body }}</p>
                    <time datetime="{{ $message->created_at }}">{{ $message->created_at }}</time>
                </div>
            </li>

            @else
            <li class="self">
                <div class="avatar">
                    <img src="{{  url('storage/'. auth()->user()->userPictureUrl() ) }}" />
                </div>
                <div class="messages">
                    <p>{{ $message->body }}</p>
                    <time datetime="{{ $message->created_at }}">{{ $message->created_at }}</time>
                </div>
            </li>
            @endif
        @endforeach


        {{--<li class="self">--}}
            {{--<div class="avatar">--}}
                {{--<img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/3/profile/profile-80_20.jpg" />--}}
            {{--</div>--}}
            {{--<div class="messages">--}}
                {{--<p>That makes sense.</p>--}}
                {{--<p>It's a pretty small airport.</p>--}}
                {{--<time datetime="2009-11-13T20:14">37 mins</time>--}}
            {{--</div>--}}
        {{--</li>--}}
        {{--<li class="other">--}}
            {{--<div class="avatar">--}}
                {{--<img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/5/profile/profile-80_9.jpg" />--}}
            {{--</div>--}}
            {{--<div class="messages">--}}
                {{--<p>that mongodb thing looks good, huh?</p>--}}
                {{--<p>--}}
                    {{--tiny master db, and huge document store</p>--}}
            {{--</div>--}}
        {{--</li>--}}
    </ol>
    <form action="{{ route('messengers.store.users', [$post->user->id] ) }}" method="post"> @csrf
        <div class="level">
            <input type="text" name="body" class="form-control">
            <button class="btn btn-small">Odoslať</button>
        </div>
    </form>

</section>
    @endif
