<div class="container">

    <div class="page">

        <div class="page-content">
            <h3>{{ $post->organization->title }} všetky videa</h3>
            <div class="VideoGroup__wrapper">
                @forelse($posts as $post)
                    <div class="card card-flex">
                        <div>
                            <div style="max-height: 11rem; overflow: hidden">
                                <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                    @include('posts.image')
                                </a>
                            </div>

                            <div style="margin-top: -.8rem" class="card-body">
                                <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                    <h6 class="card-title" title="{{ $post->title }}">{{ \Illuminate\Support\Str::limit($post->title, 48) }}</h6>
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

            {{--<news-rss></news-rss>--}}


        </div>


    </div>

</div>