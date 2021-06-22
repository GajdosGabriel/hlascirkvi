@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.pages.profil')
        @slot('page')

            <div class="col-span-8 pt-6">


                <div class="flex justify-between mb-6">
                    <h2 class="text-2xl">Posts</h2>
                    <div>
                        <a href="{{ route('posts.create') }}" class="btn btn-default">Nový post</a>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                    @forelse($posts as $post)

                        @include('posts.post-card')
                    @empty
                        bez záznamu
                    @endforelse

                </div>
            </div>

        @endslot
    @endcomponent

@endsection
