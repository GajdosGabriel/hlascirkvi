@extends('layouts.app')
@section('title') <title>{{ $post->title }}</title> @endsection

@section('script-header')
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
    {{-- <script src='https://www.google.com/recaptcha/api.js?render=6LeQ04YUAAAAAN8P0b0mdmZroMdd4pkL4HbhNfHo'></script> --}}
@endsection


@section('othermeta')
    <title>{{ $post->title }}</title>
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="{{ $post->url }}" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $post->title }}" />
    <meta property="og:description" content="{!! \Illuminate\Support\Str::limit($post->body, 130) !!}" />
    <meta property="og:image" content="@forelse($post->images as $image) {{ url($image->originalImageUrl) }}@empty @endforelse" />
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="{{ $post->title }}" />
@endsection

@section('content')


    <div class="page">

        <div class="lg:grid grid-cols-12 gap-10">
            {{-- Header and video --}}
            <div class="col-span-8">


                <div>
                    <div class="">
                        <div>
                            @if (!$post->images)
                                @forelse($post->images as $image)
                                    <img class="rounded" style="width: 100%; margin-bottom: 2rem"
                                        src="{{ url($image->originalImageUrl) }}">
                                @empty
                                @endforelse
                            @endif
                        </div>
                        <div>

                        </div>

                    </div>
                </div>

                {{-- Video --}}
                @if ($post->video_id)
                    <div id="player">
                        <iframe
                            src="https://www.youtube.com/embed/{{ $post->video_id }}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                            allowfullscreen allowtransparency allow="autoplay"></iframe>
                    </div>
                @else
                    @forelse($post->images as $image)
                        <img class="rounded" style="width: 100%; margin-bottom: 2rem"
                            src="{{ url($image->OriginalImageUrl) }}">
                    @empty
                    @endforelse
                @endif


                <div class="mt-2 lg:flex justify-between border-b-2 border-gray-300">
                    {{-- Title video --}}
                    <div class="">
                        <div class="flex flex-col items-start flex-shrink-0">
                            <h1 class="text-lg font-semibold">{{ $post->title }}</h1>
                            <div class="text-sm text-gray-400">
                                <span> pridal: </span>
                                <a
                                    href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">
                                    {{ $post->organization->title }}</a>
                                |
                                <time datetime="{{ $post->created_at }}">dňa: {{ $post->datetime }}</time>
                                | zobrazení: {{ $post->count_view }}
                            </div>
                        </div>

                    </div>

                    {{-- Social button --}}
                    <div class="flex space-x-2 mt-3">

                        @if ($post->video_id)
                            {{-- // Facebook --}}
                            <div id="fb-root"></div>
                            <div class="fb-share-button" data-href="{{ route('post.show', [$post->id, $post->slug]) }}"
                                data-layout="button" data-size="small">
                                <a target="_blank"
                                    href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse"
                                    class="fb-xfbml-parse-ignore">
                                    Zdieľať
                                </a>
                            </div>
                        @endif

                        @if (Session::get($post->slug) == $post->id)
                            <a style="float: right" class="disabled" title="Video ste už doporúčali">Odporúčili
                                ste</a>
                        @else
                            @if ($post->video_id)
                                <favorite-post :post="{{ $post }}"></favorite-post>
                                {{-- <recomend-video :data="{{ $post }}"></recomend-video> --}}
                            @endif
                        @endif


                        @can('update', $post)
                            <article-dropdown :post="{{ $post }}" />
                        @endcan

                    </div>

                </div>

                {{-- Body section --}}
                <div class="md:grid grid-cols-12 gap-4 mt-4">

                    <organization-page-header :organization="{{ $post->organization }}">
                    </organization-page-header>

                    <div class="col-span-8">


                        <div>{!! $post->body !!}</div>

                        @if (!$post->video_id)
                            {{-- // Facebook --}}
                            <div>
                                <div class="fb-like" data-share="true" data-width="350" data-show-faces="true">
                                </div>
                            </div>
                        @endif

                        @auth
                            @include('bigthink._form')
                        @endauth

                        <comments :post="{{ $post }}"></comments>
                    </div>

                    {{-- Body plánované akcie --}}
                    <div class="col-span-4 mb-4">
                        @if ($post->organization->person == 0)
                            <section class="card">

                                <header class="card_header">
                                    <span>{{ $post->organization->title }}</span>
                                    <i class="fa fa-share-alt" aria-hidden="true"></i>
                                </header>

                                <ul class="p-2">
                                    @forelse( $post->eventsBelongsToOrganization as $event)
                                        <li>
                                            <a href="{{ $event->url }}">
                                                <span
                                                    style="font-weight: bold">{{ $event->start_at->format('d. m. Y') }}</span>
                                                {{ $event->title }}
                                            </a>
                                        </li>
                                    @empty
                                        <span class="text-muted" style="font-size: 85%">Spoločenstvo neplánuje žiadne
                                            <a class="underline" href="{{ route('akcie.index') }}">
                                                podujatia.
                                            </a>
                                        </span>
                                    @endforelse
                                </ul>
                            </section>
                        @endif
                    </div>

                </div>
            </div>


            {{-- Aside section --}}
            <div class="col-span-3">
                <news-rss></news-rss>
                @include('events.aside_modul')
            </div>
        </div>
    </div>


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
                @include('posts.post-card')
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
