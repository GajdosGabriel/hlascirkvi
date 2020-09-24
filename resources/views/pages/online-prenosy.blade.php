@extends('layouts.app')
@section('title') <title>{{ 'Priame prenosy nedeľných služieb božích a mší.' }}</title> @endsection
@section('content')
<div class="container">

    {{--<video-item></video-item>--}}

    {{--<youtube-dash></youtube-dash>--}}

    <h2 class="page-header" style="text-align: center; margin-bottom: 3rem">Nedeľné bohoslužby</h2>

    @foreach($posts as $k => $v)

        @foreach($v as $post)
            <div class="video-item">

                <div class="video-header level">
                    <div>
                        <h4 style="font-weight: 600">{{ $post->title }}</h4>
                        <span class="lead">
                            Pridal:
                            <a href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">
                                {{ $post->organization->title }}</a> |
                            dňa: {{ date("d. M. Y", strtotime($post->created_at))  }}
                        </span>
                    </div>

                    @can('update', $post)
                    <article-admin inline-template>
                        <div v-cloak style="padding: 1rem; cursor: pointer;">
                            <i style="float: right" @click='toggle' title="Spravovať článok" class="fas fa-ellipsis-v"></i>
                            <ul class="dropdown-menu" v-if="all">
                                <li><a href="{{ route('post.edit', [$post->id, $post->slug]) }}" class="dropdown-item">upraviť</a></li>
                                <li><a href="{{ route('post.delete', [$post->id]) }}" class="dropdown-item">zmazať</a></li>
                                @can('admin')
                                <li><a href="{{ route('admin.youtubeBlocked', [$post->id]) }}">blokovať youtube</a></li>
                                <li><a href="{{ route('post.toBuffer', [$post->id]) }}">Do buffer</a></li>
                                @endcan
                            </ul>
                        </div>
                    </article-admin>
                    @endcan
                </div>

              @include('posts.post-online')

                <div class="video-item__side" style="display: flex; flex-direction: column; justify-content: space-between">
                    <div class="card-body">
                        <h5>Predchádzajúce prenosy</h5>

                            @foreach($v as $post)
                                <div style="display: flex; flex-direction: row; align-items: stretch; ">
                                    <i class="far fa-dot-circle" style="font-size: 80% ;color: silver; margin-right: .7rem;margin-top: .4rem"></i>

                                    <a  style="font-size: 90%" href="{{ route('post.show', [$post->id, $post->slug ] ) }}">
                                    {{ $post->title }}
                                    </a>

                                </div>
                            @break($loop->iteration == 5)
                            @endforeach

                    </div>

                    @if ($post->organization->person == 0)
                    <div><span  style="font-weight: 700">Plánované akcie</span>
                        <ul>
                            @forelse($post->organization->events as $event)
                                <li>{{ $event->title }}</li>
                            @empty
                            <span class="text-muted" style="font-size: 85%">Spoločenstvo neplánuje žiadne akcie.</span>
                            @endforelse
                        </ul>
                    </div>
                    <a href="#">Chcem spoznať spoločenstvo</a>
                    @endif

                </div>

                <div>
                <messenger></messenger>
                </div>
            </div>
            @break
        @endforeach
    @endforeach



    {{--{{ $posts->links() }}--}}






</div>
@endsection
