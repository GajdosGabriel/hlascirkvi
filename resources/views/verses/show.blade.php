@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2">

    <div class="page-title mb-4">
        <h1 class="text-2xl font-semibold">{{ $post->title }}</h1>
        <span class="font-semibold"> Zamyslenie na {{ Carbon\Carbon::now()->format('d M Y') }}</span>
    </div>

    <div class="flex">
        <div class="mx-2 w-5/12 flex flex-col justify-between">

            <div class="two-collums"> {!! $post->zamyslenie !!}<p>{{ $post->autor }}</p></div>

            <div class="flex justify-between m-4">
                @if($previous)
                    <a class="hover:bg-gray-100 px-2 rounded" href="{{ URL::to( 'zamyslenia/' . $previous ) }}"><< Včera</a>
                @endif

                @if($next)
                    <a class="hover:bg-gray-100 px-2 rounded" href="{{ URL::to( 'zamyslenia/' . $next ) }}">Zajtra >></a>
                @endif
            </div>
        </div>


        <div class="mx-4 w-3/12 flex flex-col justify-between">
            <div class="border-2 p-3 rounded-md shadow-md">
                <div class="font-semibold">Verš starej zmluvy</div>

                <div class="card-body">
                    <blockquote>
                        {{ $post->biblicky_vers }}
                        <div class="footer">{{ $post->biblicky_vers_ref }}</div>
                    </blockquote>
                </div>
            </div>

            <div class="border-2 p-3 rounded-md shadow-md">
                <div class="font-semibold">Verš novej zmluvy</div>

                <div class="card-body">
                    <blockquote>
                        {{ $post->szvers_text }}
                        <div class="footer">{{ $post->szvers_ref }}</div>
                    </blockquote>
                </div>
            </div>
        </div>

        <img class="rounded-md" src="{{ asset('images/biblia1.jpg' ) }}">

    </div>

</div>
@endsection
