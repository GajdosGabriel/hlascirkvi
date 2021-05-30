@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
<div class="page">

    <div class="md:w-8/12 w-full">

        <div class="page_title">
            <h2 class="text-2xl">
                Vzdelávanie a kurzy
            </h2>
        </div>

        <div class="grid grid-cols-4 gap-7">
            @foreach($tags as $k => $v)

                @foreach($v as $tag)
                    <div class="bg-blue-800 text-gray-300 p-4 rounded-md">

                        <h4>{{ $tag->title }}</h4>

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
