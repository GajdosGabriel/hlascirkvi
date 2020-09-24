@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page">
        <div class="page-content">

            <h2 class="page-header" style="text-align: center; margin-bottom: 3rem">Vzdelávanie a kurzy</h2>

            <div class="VideoGroup__wrapper">
                @foreach($posts as $k => $v)

                    @foreach($v as $post)
                        <div class="card card-flex" style="background: rgba(44, 36, 130, 0.88)">
                            <div class="card-body" style="margin-top: -.8rem; color: whitesmoke; display: flex;align-items: center;height: 100%;">
                            <h4>{{ $post->organization->title }}</h4>
                            </div>
                        </div>
                        @break
                    @endforeach

                    @foreach($v as $post)
                        <div class="card card-flex">
                            <div>
                                <div style="max-height: 11rem; overflow: hidden">
                                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                        @include('posts.image')
                                    </a>
                                </div>

                                <div style="margin-top: -.8rem" class="card-body">
                                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                        <h6 class="card-title" title="{{ $post->title }}">{{ Str::limit($post->title, 46, ' ...') }}</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer">
                                <a href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">{{ $post->organization->title }}</a>
                                <time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>
                            </div>

                        </div>

                    @endforeach
                @endforeach

            </div>

        </div>
    </div>




</div>
@endsection
