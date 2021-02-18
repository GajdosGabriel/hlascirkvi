@extends('layouts.app')

@section('content')
    <div class="container mx-auto">

        <div class="grid grid-cols-12 gap-7 p-5">

            <div class="grid col-span-8">

                <div class="grid col-span-8 mb-4 py-4">
                    <h2 class="font-semibold text-2xl">
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

                </h2>
            </div>


        </div>
@endsection
