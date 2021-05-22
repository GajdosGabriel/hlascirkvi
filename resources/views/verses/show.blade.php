@extends('layouts.app')

@section('content')

    <div class="page">

        <div class="md:grid grid-cols-12 gap-16 px-6 mb-4">
            <div class="col-span-12">
                <h1 class="text-2xl font-semibold">{{ $post->title }}</h1>
                <span class="font-semibold"> Zamyslenie na {{ Carbon\Carbon::now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="md:grid grid-cols-12 gap-16 px-6">

            <div class="col-span-6">
                <div class="">

                    <div class="two-collums"> {!! $post->zamyslenie !!}<p>{{ $post->autor }}</p>
                    </div>

                    <div class="flex justify-between m-4">
                        @if ($previous)
                            <a class="hover:bg-gray-100 px-2 rounded" href="{{ URL::to('zamyslenia/' . $previous) }}">
                                << Včera</a>
                        @endif

                        @if ($next)
                            <a class="hover:bg-gray-100 px-2 rounded" href="{{ URL::to('zamyslenia/' . $next) }}">Zajtra
                                >></a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-span-3 mb-6">
                <div class="border-2 p-3 rounded-md shadow-md mb-6">
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

            <div class="col-span-2">
                <img class="rounded-md" src="{{ asset('images/biblia1.jpg') }}">
            </div>

        </div>
    </div>
@endsection
