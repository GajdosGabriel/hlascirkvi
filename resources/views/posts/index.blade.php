@extends('layouts.app')

@section('title') <title>{{ 'Kázne kresťanskej komunity' }}</title> @endsection

@section('othermeta')
    <meta property="fb:app_id" content="241173683337522"/>
    <meta property="og:url" content="https://hlascirkvi.sk"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="Kázne kresťanskej komunity"/>
    <meta property="og:description" content="Kázne kresťanskej komunity"/>
    <meta property="og:image" content="https://hlascirkvi.sk/images/foto.jpg"/>
    <meta property="og:image:width" content="360"/>
    <meta property="og:image:height" content="210"/>
    <meta property="og:image:alt" content="Kázne kresťanskej komunity"/>

@endsection

@section('content')
    <div class="page">

        <div class="md:flex">

            {{--  Stlpec I. --}}
            <div class="md:w-8/12 w-full">
                @if(request()->is('/'))

                    <div class="page_title">
                        @switch(request()->input('posts'))
                            @case('recomended')
                            <h3 class="text-2xl">Obľúbené príspevky</h3>
                            @break

                            @case('mostVisited')
                            <h3 class="text-2xl">Podľa počtu zobrazení (všetky)</h3>
                            @break

                            @case('trends')
                            <h3 class="text-2xl">Trend sledovanosti za posledné 2 týždne</h3>
                            @break
                            @default


                            <h2 class="font-semibold md:text-2xl ">Príspevky kresťanskej komunity</h2>
                        @endswitch
                        <div>
                            <a title="Doporučené našími čitateľmi" href="?posts=recomended" style="margin: .5rem"><i
                                    class="fas fa-thumbs-up"></i></a>
                            <a title="Najsledovanejšie videa za dva týždne" href="?posts=trends"
                               style="margin: .5rem"><i class="fas fa-sort-amount-up"></i></a>
                            <a title="Videa podľa počtu zobrazení" href="?posts=mostVisited"><i class="far fa-eye"></i></a>
                            {{--                                <a title="Videa na odporúčania našich čitateľov" href="?posts=latestComments">posledné komentované</a>--}}
                        </div>
                    </div>
                @else
                    @if( !empty($organization) )
                        <h2 class="font-semibold text-2xl mb-5 text-gray-700">{{ $organization->title }}</h2>
                    @endif
                @endif

                <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                    @forelse($posts as $post)
                        @include('posts.post-card')
                    @empty
                        bez záznamu
                    @endforelse
                </div>

                    <div class="hidden md:block flex justify-center my-8">
                        {{ $posts->links() }}
                    </div>
            </div>

            {{--  Stlpec II. --}}
            <div class="md:w-3/12 md:mx-7">

                <prayers-card></prayers-card>


                @if(request()->is('/'))
                    @include('verses.daily-modul')
                @else
                    @if( !empty($post->user) )
                        <user-card :user="{{ $post->user }}"></user-card>
                        @include('users.user-card', ['user' => $post->user])
                    @endif
                @endif

                @include('organizations.list-users')

                {{--                @include('events.aside_modul')--}}
                {{--                @include('bigthink.aside_last_big_think')--}}
                {{--                @include('posts.posts-history')--}}
                {{--                @include('verses.credit-modul')--}}
                {{--                    @include('posts.rss-zaloha-php')--}}
            </div>

        </div>
    </div>



@endsection
