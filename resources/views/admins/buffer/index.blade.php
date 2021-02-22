@extends('layouts.app')

@section('content')

    <div class="page">

        <div class="flex">

            <div class="md:w-8/12 md:mr-5">
                <h3 class="page_title text-2xl">Buffer príspevky (Nezverenené)</h3>
                <div class="grid grid-cols-4 gap-5">
                    @forelse($posts as $post)
                        <div class="card card-flex">
                            <div>
                                <div style="max-height: 13rem; overflow: hidden">
                                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                        @include('posts.image')
                                    </a>
                                </div>

                                <div style="margin-top: -1.2rem" class="card-body">
                                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                        <strong class="card-title">{{ $post->title }}</strong>
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer">
                                {{ $post->organization->title }}
                                <time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>

                                <div class="level">
                                    <a href="{{ route('admin.bufferedVideosPublish', [$post->id]) }}">Zverejniť</a>
                                    @can('admin')
                                        <a href="{{ route('admin.youtubeBlocked', [$post->id]) }}">Blokovať</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        bez záznamu
                    @endforelse

                </div>

                {{ $posts->links() }}
            </div>

            <div class="md:w-3/12">
                @include('admins.buffer.list-organizations')
            </div>


        </div>

    </div>


@endsection
