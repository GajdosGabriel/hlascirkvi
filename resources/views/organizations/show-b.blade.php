@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                    @forelse($posts as $post)

                        @if ($loop->first)

                        <a href="{{ url()->full() }}"><h1>{{ $post->title }}</h1></a>

                        @can('update', $post)
                        <article-admin inline-template>
                            <div v-cloak>
                                <i style="float: right" @click='toggle' title="Spravovať článok" class="fas fa-ellipsis-v"></i>
                                <ul class="dropdown-menu" v-if="all">
                                    <li><a href="{{ route('post.edit', [$post->id, $post->slug]) }}" class="dropdown-item">upraviť</a></li>
                                    <li><a href="{{ route('post.delete', [$post->id, $post->slug]) }}" class="dropdown-item">zmazať</a></li>
                                    @can('admin')
                                    <li><a href="{{ route('admin.youtubeBlocked', [$post->id]) }}">blokovať youtube</a></li>
                                    <li><a href="{{ route('post.toBuffer', [$post->id]) }}">Do buffer</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </article-admin>
                        @endcan

                        <div class="level">
                    <span class="lead">
                    pridal:
                        <a href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">
                            {{ $post->organization->title }}
                        </a>
                        |  <time datetime="{{ $post->created_at }}">dňa: {{ $post->datetime }}</time>
                        | zobrazení: {{ $post->count_view }}
                    </span>

                            <favorite-button :post="{{ $post }}"></favorite-button>
                        </div>

                    @if ($post->video_id)
                                <div class="video-container">
                                    <iframe width="640" height="360" src="//www.youtube.com/embed/{{ $post->video_id }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>
                                </div>

                            @else

                                @forelse($post->images as $image)
                                    <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url( $image->OriginalImageUrl ) }}">
                                @empty
                                @endforelse

                            @endif
                        @endif

                    @empty
                        bez záznamu
                    @endforelse

                        <div class="page-pictures">
                            <div>
                                @if (! isset($post->images))
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
                                    <help-us></help-us>
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

                            </div>

                            <div>

                            </div>

                        </div>

                        <replies :data="{{ $post->comments }}"></replies>

                        </div>

            <div class="page-aside-content">
                {{--@if($post->organization->person)--}}
                    {{--<user-card :user="{{ $post->organization }}"></user-card>--}}
                {{--@else--}}
                    {{--<organization-card :user="{{ $post->organization }}"></organization-card>--}}
                {{--@endif--}}

                {{--@if(auth()->check())--}}
                    {{--<messenger-modul :user="{{ $post->organization }}" :messages="{{ $messages }}"></messenger-modul>--}}
                {{--@endif--}}

                {{--@if ($post->video_id)--}}
                    {{--<a href="#">Mám záujem spoznať spoločenstvo</a>--}}
                {{--@endif--}}

            </div>

        </div>
    </div>


           @include('posts.index-posts')


    @endsection
