@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection



@section('content')
    <div class="grid grid-cols-12 gap-6  ">


        @include('profiles._profil-menu')



        <div class="col-span-8">


            @component('layouts.pages.page_title')
                @slot('title')

                    <div>
                        <h2 class="text-2xl font-semibold">
                            <seminar-title :seminar="{{ $seminar }}"></seminar-title>
                        </h2>
                        <seminar-info :seminar="{{ $seminar }}"></seminar-info>
                        <seminar-description :seminar="{{ $seminar }}"></seminar-description>
                    </div>

                    <div class="flex items-center">
                        <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'">
                        </c-article-dropdown>
                    </div>

                @endslot

            @endcomponent


            <div class="col-span-3">

                <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                    @forelse($seminar->posts as $post)
                        <post-card :post="{{ $post }}"></post-card>
                        {{-- @include('posts.post-card') --}}
                    @empty
                        bez záznamu
                    @endforelse
                </div>
            </div>
        </div>
    </div>




@endsection
