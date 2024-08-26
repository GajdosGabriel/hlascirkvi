@extends('layouts.app')
@section('title')
    <title>{{ 'Všetky články' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Články
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ route('profile.post.create') }}" class="btn btn-primary">Nový článok</a>
        </x-slot>


        <x-slot name="page">

            <div class="">
                @forelse($posts as $post)
                    @include('posts.card-admin')
                @empty
                    bez záznamu
                @endforelse
            </div>


            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>

        </x-slot>

        <x-slot name="pageRight">

            <section class="card">
                <header class="card_header">
                    <h4>Okno 2</h4>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                            clip-rule="evenodd" />
                    </svg>
                </header>
            </section>


        </x-slot>

    </x-pages.dashboard>
@endsection
