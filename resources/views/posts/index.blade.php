@extends('layouts.app')

@section('title') <title>{{ 'Kázne kresťanskej komunity' }}</title> @endsection

@section('othermeta')
    <meta property="fb:app_id"        content="241173683337522"/>
    <meta property="og:url"           content="https://hlascirkvi.sk"/>
    <meta property="og:type"          content="article"/>
    <meta property="og:title"         content="Kázne kresťanskej komunity"/>
    <meta property="og:description"   content="Kázne kresťanskej komunity"/>
    <meta property="og:image"         content="https://hlascirkvi.sk/images/foto.jpg"/>
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="Kázne kresťanskej komunity" />

@endsection

@section('content')
    <div class="container">

        <div class="page">

            <div class="page-content">
                @if(request()->is('/'))

                    {{--  News section  --}}
{{--                    @include('posts.news')--}}

                    <div class="flex justify-between">
                        @switch(request()->input('posts'))
                            @case('recomended')
                            <h3>Obľúbené príspevky</h3>
                            @break

                            @case('mostVisited')
                            <h3>Podľa počtu zobrazení (všetky)</h3>
                            @break

                            @case('trends')
                            <h3>Trend sledovanosti za posledné 2 týždne</h3>
                            @break

                        @default


                            <h3>Príspevky kresťanskej komunity</h3>
                        @endswitch
                            <div>
                                <a title="Doporučené našími čitateľmi" href="?posts=recomended" style="margin: .5rem"><i class="fas fa-thumbs-up"></i></a>
                                <a title="Najsledovanejšie videa za dva týždne" href="?posts=trends" style="margin: .5rem"><i class="fas fa-sort-amount-up"></i></a>
                                <a title="Videa podľa počtu zobrazení" href="?posts=mostVisited"><i class="far fa-eye"></i></a>
                                {{--<a title="Videa na odporúčania našich čitateľov" href="?posts=latestComments">posledné komentované</a>--}}
                            </div>
                    </div>
                @else
                @if( !empty($organization) )
                    <h3>{{ $organization->title }}</h3>
                @endif
                @endif

                <div class="VideoGroup__wrapper">
                    @forelse($posts as $post)
                        <div class="card card-flex">
                            <div>
                                <div style="max-height: 11rem; overflow: hidden; position: relative">
                                    @if($post->favorites()->count() > 0)
                                    <div style=" float: left;position: absolute;right: 0px;bottom: 0px;
                                        z-index: 1000; background-color: #ad5092; padding: 5px;color: #FFFFFF;font-size: 70%">
                                        Doporúčené
                                    </div>
                                    @endif
                                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                        @include('posts.image')
                                    </a>
                                </div>

                                <div style="margin-top: -.8rem" class="card-body">
                                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                        <h6 class="card-title" title="{{ $post->title }}">{{ Str::limit($post->title, 48) }}</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer">
                                <a href="{{ route('organization.show', [$post->organization->id, $post->organization->slug]) }}">{{ $post->organization->title }}</a>
                                <time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>
                            </div>

                        </div>
                    @empty
                    bez záznamu
                    @endforelse
                </div>

                {{ $posts->links() }}
            </div>

            <div class="page-aside">

                <prayer-card></prayer-card>

                <news-rss></news-rss>

            @if(request()->is('/'))
                    @include('verses.daily-modul')
                @else
                    @if( !empty($post->user) )
                        <user-card :user="{{ $post->user }}"></user-card>
                        @include('users.user-card', ['user' => $post->user])
                    @endif
                @endif

                @include('organizations.list-users')
                @include('events.aside_modul')
                {{--@include('bigthink.aside_last_big_think')--}}
{{--                @include('posts.posts-history')--}}
{{--                @include('verses.credit-modul')--}}
                    {{--@include('posts.rss-zaloha-php')--}}
            </div>


        </div>

    </div>


    @endsection
