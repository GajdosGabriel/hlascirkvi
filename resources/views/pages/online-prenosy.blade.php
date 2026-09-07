@extends('layouts.app')
@section('title') <title>{{ 'Priame prenosy nedeľných služieb božích a omší.' }}</title> @endsection
@section('content')
    <div class="page">

        <h2 class="page_title text-2xl p-2">Nedeľné bohoslužby</h2>

        @foreach ($posts as $k => $v)
            <div class="mb-12 grid grid-cols-12 gap-7 p-2">

                @foreach ($v as $post)
                    <div class="col-span-12 md:col-span-8">

                        <div>

                            {{-- Video + last items --}}
                            <div class="">

                                <div class="">

                                    {{-- Title + admin --}}
                                    <div class="page_title">
                                        <div class="flex flex-col items-start">
                                            <h4 class="font-semibold md:text-lg">{{ $post->title }}</h4>
                                            <div class="text-gray-400">
                                                Pridal:
                                                <a href="{{ route('organizations.show', [$post->organization->id]) }}">
                                                    {{ $post->organization->title }}
                                                </a> |
                                                dňa: {{ date('d. M. Y', strtotime($post->created_at)) }}
                                            </div>
                                        </div>

                                        @can('update', $post)
                                            <article-dropdown :post="{{ $post }}" />
                                        @endcan


                                    </div>

                                    @include('posts._post-online')
                                </div>

                                <div class="md:flex justify-between col-span-4 mt-5">
                                    <div class="">
                                        <h5 class="font-semibold text-lg">Predchádzajúce prenosy</h5>

                                        @foreach ($v as $post)
                                            <div>
                                                <i class="far fa-dot-circle"></i>

                                                <a href="{{ $post->routeShow() }}">
                                                    {{ $post->title }}
                                                </a>

                                            </div>
                                        @break($loop->iteration == 5)
                @endforeach
            </div>

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
