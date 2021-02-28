@extends('layouts.app')
@section('title') <title>{{ 'Priame prenosy nedeľných služieb božích a omší.' }}</title> @endsection
@section('content')
    <div class="page">

        <h2 class="page_title text-2xl p-2">Nedeľné bohoslužby</h2>

        @foreach($posts as $k => $v)
            <div class="mb-12 grid grid-cols-12 gap-7 p-2">

                @foreach($v as $post)
                    <div class="col-span-12 md:col-span-8">

                        <div>

                            {{-- Video + last items --}}
                            <div class="">

                                <div class="">

                                    {{--    Title + admin--}}
                                    <div class="page_title">
                                        <div>
                                            <h4 class="font-semibold md:text-lg">{{ $post->title }}</h4>
                                            <div class="text-gray-400">
                                                Pridal:
                                                <a href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">
                                                    {{ $post->organization_name }}
                                                </a> |
                                                dňa: {{ date("d. M. Y", strtotime($post->created_at))  }}
                                            </div>
                                        </div>

                                        @can('update', $post)
                                            <article-admin inline-template>
                                                <div v-cloak class="relative z-10">
                                                    <i style="float: right" @click='toggle' title="Spravovať článok"
                                                       class="fas fa-ellipsis-v cursor-pointer"></i>
                                                    <ul class="dropdown-menu" v-if="open">
                                                        <li class="dropdown-item"><a
                                                                href="{{ route('post.edit', [$post->id, $post->slug]) }}"
                                                            >upraviť
                                                            </a>
                                                        </li>
                                                        <li class="dropdown-item"><a
                                                                href="{{ route('post.delete', [$post->id]) }}"
                                                            >zmazať
                                                            </a>
                                                        </li>
                                                        @can('admin')
                                                            <li class="dropdown-item"><a
                                                                    href="{{ route('admin.youtubeBlocked', [$post->id]) }}">
                                                                    blokovať youtube
                                                                </a>
                                                            </li>
                                                            <li class="dropdown-item"><a
                                                                    href="{{ route('post.toBuffer', [$post->id]) }}">
                                                                    Do buffer
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </article-admin>
                                        @endcan
                                    </div>

                                    @include('posts.post-online')
                                </div>

                                <div class="md:flex justify-between col-span-4 mt-5">
                                    <div class="">
                                        <h5 class="font-semibold text-lg">Predchádzajúce prenosy</h5>

                                        @foreach($v as $post)
                                            <div>
                                                <i class="far fa-dot-circle"></i>

                                                <a href="{{ $post->url }}">
                                                    {{ $post->title }}
                                                </a>

                                            </div>
                                            @break($loop->iteration == 5)
                                        @endforeach
                                    </div>

                                    @if ($post->organization->person == 0)
                                        <div class="flex flex-col justify-between">
                                            <div>
                                                <span class="font-semibold">Plánované akcie</span>
                                                <ul>
                                                    @forelse($post->organization->events as $event)
                                                        <li>{{ $event->title }}</li>
                                                    @empty
                                                        <span class="" style="font-size: 85%">Spoločenstvo neplánuje žiadne akcie.</span>
                                                    @endforelse
                                                </ul>
                                            </div>

                                            <a href="#">Chcem spoznať spoločenstvo</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
