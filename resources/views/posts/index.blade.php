@extends('layouts.app')

@section('title')
    <title>{{ 'Kázne kresťanskej komunity' }}</title>
@endsection

@section('othermeta')
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="https://hlascirkvi.sk" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Kázne kresťanskej komunity" />
    <meta property="og:description" content="Kázne kresťanskej komunity" />
    <meta property="og:image" content="https://hlascirkvi.sk/images/foto.jpg" />
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="Kázne kresťanskej komunity" />
@endsection

@section('content')



    <x-pages.page_3_2>

        {{-- Stlpec I. --}}
        <x-slot name="page">

            {{-- @include('posts.sviatok') --}}
            {{-- @include('cases.amt') --}}



            @if (request()->is('/'))
                <div class="page_title px-3 py-1 rounded-t-md bg-blue-200 border-gray-300 border-2  shadow-md">

                    @if (request()->has('recomended'))
                        <h3 class="text-2xl">Obľúbené príspevky</h3>
                    @elseif (request()->has('mostVisited'))
                        <h3 class="text-2xl">Podľa počtu zobrazení (všetky)</h3>
                    @elseif (request()->has('trends'))
                        <h3 class="text-2xl">Trend sledovanosti za posledné 2 týždne</h3>
                    @else
                        <h2 class="font-semibold md:text-2xl">Príspevky kresťanskej komunity</h2>
                    @endif



                    <div class="hidden sm:block">
                        <div class="flex">
                            <x-icons.background request-value="recomended" title="Doporučené našími čitateľmi">
                                <i class="fas fa-thumbs-up"></i>
                            </x-icons.background>

                            <x-icons.background request-value='trends' title="Najsledovanejšie videa za dva týždne">
                                <i class="fas fa-sort-amount-up"></i>
                            </x-icons.background>

                            <x-icons.background request-value='mostVisited' title="Videa podľa počtu zobrazení">
                                <i class="far fa-eye"></i>
                            </x-icons.background>
                        </div>
                    </div>
                </div>
            @endif


            <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                @forelse($posts as $post)
                    {{-- <card-front :post="{{ $post }}"></card-front> --}}
                    @include('posts.card-front')
                @empty
                    bez záznamu
                @endforelse
            </div>

            <div class="md:block flex justify-center my-8">
                {{ $posts->onEachSide(1)->links() }}
            </div>

        </x-slot>

        {{-- Stlpec II. --}}
        <x-slot name="aside">
            <div class="col-span-3">

                <comment-card></comment-card>
                <prayers-card></prayers-card>


                @include('verses.daily-modul')


                @include('organizations.list-users')

                {{-- @include('events.aside_modul') --}}
                {{-- @include('bigthink.aside_last_big_think') --}}
                {{-- @include('posts.posts-history') --}}
                {{-- @include('verses.credit-modul') --}}
                {{-- @include('posts.rss-zaloha-php') --}}
            </div>

        </x-slot>
    </x-pages.page_3_2>
@endsection
