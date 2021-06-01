@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('profiles._profil-menu')

            </div>
        </div>


        <div class="col-span-7">

            <div class="flex justify-between mb-6 mt-6 items-center">

                    <h2 class="text-2xl"> {{ $seminar->title }}
                        <span class="text-sm ml-2 text-gray-500">({{ $seminar->posts->count() }})</span>
                    </h2>


                <div class="flex items-center">

                    @can('update', $seminar)
                    <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'"></c-article-dropdown>
                @endcan


                    @if (! $seminar->youtube_playlist == '')
                        <a href="{{ route( 'seminars.uploadVideos', $seminar->id) }}" class="btn btn-default">Načítať z youTube zoznamu</a>
                    @else
                        <a href="{{ route( 'seminars.edit', $seminar->id) }}" class="btn btn-default">Nevyplnený playlist</a>
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                    @forelse($seminar->posts as $post)
                    <post-card :post="{{ $post }}"></post-card>
                    {{-- @include('posts.post-card') --}}
                @empty
                    bez záznamu
                @endforelse
            </div>
        </div>




    @endsection
