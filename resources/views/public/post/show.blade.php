@extends('layouts.app')
@section('title')
    <title>{{ $post->title }}</title>
@endsection

@section('script-header')
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
    {{-- <script src='https://www.google.com/recaptcha/api.js?render=6LeQ04YUAAAAAN8P0b0mdmZroMdd4pkL4HbhNfHo'></script> --}}
@endsection


@section('othermeta')
    <title>{{ $post->title }}</title>
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="{{ $post->url['show'] }}" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $post->title }}" />
    <meta property="og:description" content="{!! \Illuminate\Support\Str::limit($post->body, 130) !!}" />
    <meta property="og:image"
        content="@forelse($post->images as $image) {{ url($image->originalImageUrl) }}@empty @endforelse" />
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="{{ $post->title }}" />
@endsection

@section('content')

    <x-pages.page_standart>

        <x-slot name="page">
            @include('posts.show')
        </x-slot>

        <x-slot name="aside">
            @include('events.aside_modul')
        </x-slot>
    </x-pages.page_standart>


    {{-- Image section --}}
    <div class="container mx-auto text-gray-600 grid grid-cols-12 gap-7">
        {{-- Header and video --}}
        <div class="grid col-span-8">


        </div>

        {{-- Face Book and recomended --}}
        <div class="">
            <div class="">
                {{-- Nevedel som vyriešiť session vo vue tak zatiaľ tak kostrbato --}}


                <div class="page-pictures">
                    <div>
                        @if (!$post->images)
                            @forelse($post->images as $image)
                                <img class="rounded" style="width: 100%; margin-bottom: 2rem"
                                    src="{{ url($image->originalImageUrl) }}">
                            @empty
                            @endforelse
                        @endif
                    </div>


                </div>
            </div>

        </div>

        {{-- Body text --}}
        <div class="">
            <div class="page-content">
                <div class="page-pictures">
                    <div>
                        @if (!$post->images)
                            @forelse($post->images as $image)
                                <img class="rounded" style="width: 100%; margin-bottom: 2rem"
                                    src="{{ url($image->originalImageUrl) }}">
                            @empty
                            @endforelse
                        @endif

                        {{-- <user-card :user="{{ $post->organization }}"></user-card> --}}

                        {{-- @if (auth()->check()) --}}
                        {{-- <messenger-modul :user="{{ $post->organization }}" :messages="{{ $messages }}"></messenger-modul> --}}
                        {{-- @endif --}}


                        @if (auth()->check())
                            {{-- <messenger-modul :user="{{ $post->organization }}" :messages="{{ $messages }}">
                            </messenger-modul> --}}
                        @endif


                    </div>
                    <div>
                    </div>

                </div>
            </div>

        </div>


    </div>



    {{-- All video belong to user --}}
    <div class="page">

        <h3 class="text-2xl font-semibold mb-6">{{ $post->organization->title }} všetky videa</h3>

        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-8  gap-4  w-11/12">
            @forelse($posts as $post)
                @include('posts.card-front')
            @empty
                bez záznamu
            @endforelse
        </div>

        <div class="md:block flex justify-center my-8">
            {{ $posts->links() }}
        </div>

    </div>


@endsection


@section('script')
    <script src="https://cdn.plyr.io/3.5.3/plyr.js"></script>
    <script defer>
        const player = new Plyr('#player');
    </script>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/sk_SK/sdk.js#xfbml=1&version=v5.0&appId=500741757380226"></script>
@endsection
