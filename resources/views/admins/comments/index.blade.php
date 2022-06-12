@extends('layouts.app')
@section('title') <title>{{ 'Admin komentáre.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            {{ $title ?? 'Komentáre' }}
        </x-slot>


        <x-slot name="title_right">
           {{--  --}}
        </x-slot>

        <x-slot name="page">
            {{-- index of comments --}}
            @forelse($posts as $post)

            <comments-items :comment="{{ $post }}"></comments-items>

            @empty
                bez komentárov
            @endforelse

            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>

        </x-slot>
    </x-pages.admin>

@endsection
