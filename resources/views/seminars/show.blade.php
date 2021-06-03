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

            <seminar-title :seminar="{{ $seminar }}"></seminar-title>

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
