@component('mail::message')
# Najlepšie príspevky za mesiac ***

Podľa počtu sledovaní.

@forelse($posts as $post)
<div  style="text-align: center; margin-bottom: 20px">

        <a  href="{{ route('post.show', [$post->id, $post->slug]) }}">
            dddddddddd
        {{-- @include('posts.image') --}}
        </a>


    <div style="margin-top: -.8rem">
        <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
        <h5 title="{{ $post->title }}">{{ $loop->index +1 }}. {{ $post->title }}</h5>
        </a>
    </div>

     <div style="margin-top: -20px; font-size: 80%">Autor: {{ $post->organization->title }}</div>

</div>
@empty
bez záznamu
@endforelse

@component('mail::button', ['url' => 'http'])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent


{{--<h2>Najzaujímavejšie príspevky</h2>--}}

{{--<div class="page-content">--}}

    {{--<div class="VideoGroup__wrapper">--}}
        {{--@forelse($posts as $post)--}}
            {{--<div class="card card-flex">--}}
                {{--<div>--}}
                    {{--<div style="max-height: 11rem; overflow: hidden">--}}
                        {{--<a href="{{ route('post.show', [$post->id, $post->slug]) }}">--}}
                            {{--@include('posts.image')--}}
                        {{--</a>--}}
                    {{--</div>--}}

                    {{--<div style="margin-top: -.8rem" class="card-body">--}}
                        {{--<a href="{{ route('post.show', [$post->id, $post->slug]) }}">--}}
                            {{--<h6 class="card-title" title="{{ $post->title }}">{{ str_limit($post->title, 48) }}</h6>--}}
                        {{--</a>--}}
                    {{--</div>--}}
                {{--</div>--}}

                {{--<div class="card-footer">--}}
                    {{--<a href="{{ route('user.show', [$post->organization->id, $post->organization->slug]) }}">{{ $post->organization->title }}</a>--}}
                    {{--<time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>--}}
                {{--</div>--}}

            {{--</div>--}}
        {{--@empty--}}
            {{--bez záznamu--}}
        {{--@endforelse--}}
    {{--</div>--}}


{{--</div>--}}
