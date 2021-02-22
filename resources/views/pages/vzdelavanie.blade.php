@extends('layouts.app')

@section('content')
    <div class="page">

            <div class="md:w-8/12 w-full">

                <div class="page_title">
                    <h2 class="text-2xl">
                        Vzdelávanie a kurzy
                    </h2>
                </div>

                <div class="grid grid-cols-4 gap-7">
                    @foreach($posts as $k => $v)

                        @foreach($v as $post)
                            <div class="bg-blue-800 text-gray-300 p-4 rounded-md">

                                <h4>{{ $post->organization->title }}</h4>

                            </div>
                            @break
                        @endforeach

                        @foreach($v as $post)
                            @include('posts.post-card')
                        @endforeach
                    @endforeach
                </div>
            </div>



@endsection
