@extends('layouts.app')
@section('title') <title>{{ 'Priame prenosy nedeľných služieb božích a omší.' }}</title> @endsection
@section('content')
    <div class="container mx-auto p-4">

        <h2 class="font-semibold text-2xl my-6">Nedeľné bohoslužby</h2>

        @foreach($posts as $k => $v)
            <div class="mb-12">

                @foreach($v as $post)
                    <div class="flex">

                        <div>
                            {{-- Title + admin --}}
                            <div class="flex mb-4">
                                <div>
                                    <h4>{{ $post->title }}</h4>
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
                                            <i style="float: right" @click='toggle' title="Spravovať článok"
                                               class="fas fa-ellipsis-v"></i>
                                            <ul class="dropdown-menu" v-if="all">
                                                <li><a href="{{ route('post.edit', [$post->id, $post->slug]) }}"
                                                       class="dropdown-item">upraviť</a></li>
                                                <li><a href="{{ route('post.delete', [$post->id]) }}"
                                                       class="dropdown-item">zmazať</a>
                                                </li>
                                                @can('admin')
                                                    <li><a href="{{ route('admin.youtubeBlocked', [$post->id]) }}">blokovať
                                                            youtube</a></li>
                                                    <li><a href="{{ route('post.toBuffer', [$post->id]) }}">Do
                                                            buffer</a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </article-admin>
                                @endcan
                            </div>

                            {{-- Video + last items --}}
                            <div class="grid grid-cols-12 gap-7">

                                <div class="col-span-8">
                                    @include('posts.post-online')
                                </div>

                                <div class="flex flex-col justify-between col-span-4">
                                    <div class="">
                                        <h5>Predchádzajúce prenosy</h5>

                                        @foreach($v as $post)
                                            <div>
                                                <i class="far fa-dot-circle"></i>

                                                <a href="{{ route('post.show', [$post->id, $post->slug ] ) }}">
                                                    {{ $post->title }}
                                                </a>

                                            </div>
                                            @break($loop->iteration == 5)
                                        @endforeach
                                    </div>

                                    @if ($post->organization->person == 0)
                                        <div><span>Plánované akcie</span>
                                            <ul>
                                                @forelse($post->organization->events as $event)
                                                    <li>{{ $event->title }}</li>
                                                @empty
                                                    <span class="" style="font-size: 85%">Spoločenstvo neplánuje žiadne akcie.</span>
                                                @endforelse
                                            </ul>
                                        </div>
                                        <a href="#">Chcem spoznať spoločenstvo</a>
                                    @endif

                                </div>
                            </div>
                        </div>

{{--                        <messenger></messenger>--}}
                    </div>
                    @break
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
