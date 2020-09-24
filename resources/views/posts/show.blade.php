@extends('layouts.app')
@section('title') <title>{{ $post->title }}</title> @endsection

@section('script-header')
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
    {{--<script src='https://www.google.com/recaptcha/api.js?render=6LeQ04YUAAAAAN8P0b0mdmZroMdd4pkL4HbhNfHo'></script>--}}
@endsection


@section('othermeta')
    <title>{{ $post->title }}</title>
    <meta property="fb:app_id" content="241173683337522"/>
    <meta property="og:url"           content="{{ route('post.show', [$post->id, $post->slug]) }}"/>
    <meta property="og:type"          content="article"/>
    <meta property="og:title"         content="{{ $post->title }}"/>
    <meta property="og:description"   content="{!! \Illuminate\Support\Str::limit($post->body, 130) !!}"/>
    <meta property="og:image"         content="@forelse($post->images as $image){{ url($image->originalImageUrl) }}@empty @endforelse"/>
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="{{ $post->title }}" />
@endsection

@section('content')

    <div class="container" style="min-height: 20px">
        <div class="page-content">

            <page-header :organization="{{ $post->organization }}" :post="{{ $post }}"></page-header>

        </div>
    </div>


    <div class="container">
        {{-- Header and video--}}
        <div class="page">

            <div class="page-content">

                <div class="page-header level">

                    <h1>{{ $post->title }}</h1>

                    @can('update', $post)
                    <article-admin inline-template>
                        <div v-cloak style="padding: 1rem; cursor: pointer;">
                            <i style="float: right" @click='toggle' title="Spravovať článok" class="fas fa-ellipsis-v"></i>
                            <ul class="dropdown-menu" v-if="all">
                                <li><a href="{{ route('post.edit', [$post->id, $post->slug]) }}" class="dropdown-item">upraviť</a></li>
                                <li><a href="{{ route('post.delete', [$post->id]) }}" class="dropdown-item">zmazať</a></li>
                                @can('admin')
                                <li><a href="{{ route('admin.youtubeBlocked', [$post->id]) }}" class="dropdown-item">blokovať youtube</a></li>
                                <li><a href="{{ route('post.toBuffer', [$post->id]) }}" class="dropdown-item">Do buffer</a></li>
                                @endcan
                            </ul>
                        </div>
                    </article-admin>
                    @endcan

                </div>


                <div class="level">
                    <span class="lead">
                    pridal:
                        <a href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">
                            {{ $post->organization->title }}</a>
                        |  <time datetime="{{ $post->created_at }}">dňa: {{ $post->datetime }}</time>
                        | zobrazení: {{ $post->count_view }}
                    </span>


                </div>


                @if($post->video_id)

                    <div class="plyr__video-embed" id="player">
                        <iframe
                                src="https://www.youtube.com/embed/{{ $post->video_id }}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                                allowfullscreen
                                allowtransparency
                                allow="autoplay"
                        ></iframe>
                    </div>

                @else

                    @forelse($post->images as $image)
                        <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url( $image->OriginalImageUrl ) }}">
                    @empty
                    @endforelse

                @endif

                    <div class="page-pictures">
                        <div>
                            @if (!$post->images)
                                @forelse($post->images as $image)
                                    <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url($image->originalImageUrl) }}">
                                @empty
                                @endforelse
                            @endif
                        </div>
                    <div>

                    </div>

                </div>
            </div>

            <div class="page-aside-content">
                <news-rss></news-rss>
            </div>

        </div>

        {{--Face Book and recomended--}}
        <div class="page">
            <div class="page-content">
                    {{--Nevedel som vyriešiť session vo vue tak zatiaľ tak kostrbato--}}
                    @if (Session::get($post->slug) == $post->id)
                        <a style="float: right" class="disabled" title="Video ste už doporúčali">Odporúčili ste</a>
                    @else
                        @if ($post->video_id)
                        <favorite-post :post="{{ $post }}"></favorite-post>
                            {{--<recomend-video :data="{{ $post }}"></recomend-video>--}}
                        @endif
                    @endif


                @if ($post->video_id)
                    {{--// Facebook--}}
                    <div id="fb-root" style="padding-top: 0.4rem"></div>
                    <div class="fb-share-button" data-href="{{ route('post.show', [$post->id, $post->slug]) }}" data-layout="button" data-size="small"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Zdieľať</a></div>
                @endif

                <div class="page-pictures">
                    <div>
                    @if (!$post->images)
                            @forelse($post->images as $image)
                                <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url($image->originalImageUrl) }}">
                            @empty
                            @endforelse
                        @endif
                    </div>



                </div>
            </div>

        </div>

        {{--Body text--}}
        <div class="page">

            <div class="page-content">

                    <div class="page-pictures">
                        <div>
                            @if (!$post->images)
                                @forelse($post->images as $image)
                                    <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url($image->originalImageUrl) }}">
                                @empty
                                @endforelse
                            @endif

                                {{--<user-card :user="{{ $post->organization }}"></user-card>--}}

                                {{--@if(auth()->check())--}}
                                    {{--<messenger-modul :user="{{ $post->organization }}" :messages="{{ $messages }}"></messenger-modul>--}}
                                {{--@endif--}}

                            @if ($post->video_id)
{{--                            <help-us></help-us>--}}
                            @endif

                                @if(auth()->check())
                                <messenger-modul :user="{{ $post->organization }}" :messages="{{ $messages }}"></messenger-modul>
                                @endif

                                @if ($post->organization->person == 0)
                                <div><span  style="font-weight: 700">Plánované akcie {{ $post->organization->title }}</span>
                                    <ul>
                                        @forelse( $post->organization->events as $event)
                                        <li><a href="{{ route('event.show', [$event->id, $event->slug]) }}">
                                                <span style="font-weight: bold">{{ $event->start_at->format('d. m. Y') }}</span>
                                                {{ $event->title }}
                                            </a></li>
                                        @empty
                                        <span class="text-muted" style="font-size: 85%">Spoločenstvo neplánuje žiadne akcie.</span>
                                        @endforelse
                                    </ul>
                                </div>
{{--                                <a href="#">Chcem spoznať spoločenstvo</a>--}}
                                @endif
                        </div>

                        <div class="comments">
                            <div>{!! $post->body !!}</div>

                            @if (!$post->video_id)
                                {{--// Facebook--}}
                                <div>
                                    <div class="fb-like"
                                         data-share="true"
                                         data-width="280"
                                         data-show-faces="true">
                                    </div>
                                </div>
                            @endif

                            @include('bigthink._form')

                            <div>
                                <replies :data="{{ $post->comments }}"></replies>
                            </div>


                        </div>
                    <div>

                    </div>

                </div>
            </div>

            <div class="page-aside">
{{--                @include('messenger.index', ['user' => $post->user])--}}
                {{--@include('users.user-card', ['user' => $post->user])--}}
                @include('events.aside_modul')
            </div>


        </div>

        {{--Comments--}}
        <div class="page">

            <div class="page-content">
                {{--<replies :data="{{ $post->comments }}"></replies>--}}
            </div>

            <div class="page-aside">
{{--                @include('messenger.index', ['user' => $post->user])--}}
                {{--@include('users.user-card', ['user' => $post->user])--}}

            </div>


        </div>
    </div>

    @include('posts.index-posts')


    @endsection


    @section('script')

        <script src="https://cdn.plyr.io/3.5.3/plyr.js"></script>
        <script defer>
            const player = new Plyr('#player');
        </script>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/sk_SK/sdk.js#xfbml=1&version=v5.0&appId=500741757380226"></script>


    @endsection







