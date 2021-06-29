@extends('layouts.app')

@section('content')
    <div class="page">

                @component('layouts.pages.page_title')
                    @slot('title')

                        Vzdelávanie a kurzy

                    @endslot

                    @slot('title_right')

                        <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300"
                            href="{{ route('seminars.create') }}"><i class="fas fa-plus"></i>
                            Nový seminár
                        </a>

                    @endslot
                @endcomponent


                @foreach ($seminars as $seminar)
                    <div class="grid col-span-12  mb-4">
                        <a href="{{ route('seminars.show', $seminar->id) }}">
                            <h4 class="text-2xl font-semibold">{{ $seminar->title }}</h4>
                        </a>

                        <div class="cursor-pointer hover:text-gray-600 text-gray-500">
                            Pridal: {{ $seminar->organization->title }}
                        </div>

                        <div class="block">{{ $seminar->description }}</div>
                    </div>

                    <div class="md:grid md:grid-cols-4 lg:grid-cols-7 gap-6 mb-10">
                        @foreach ($seminar->posts as $post)

                            @include('posts.post-card')

                        @endforeach

                    </div>
                @endforeach
            </div>
        </div>



    @endsection



    {{-- @extends('layouts.app')

@section('content')
    <div class="page">

            <div class="md:w-8/12 w-full">

                <div class="page_title">
                    <h2 class="text-2xl">
                        Vzdelávanie a kurzy
                    </h2>
                </div>

                <div class="grid grid-cols-4 gap-7">
                    @foreach ($posts as $k => $v)

                        @foreach ($v as $post)
                            <div class="bg-blue-800 text-gray-300 p-4 rounded-md">

                                <h4>{{ $post->organization->title }}</h4>

                            </div>
                            @break
                        @endforeach

                        @foreach ($v as $post)
                            @include('posts.post-card')
                        @endforeach
                    @endforeach
                </div>
            </div>



@endsection --}}
