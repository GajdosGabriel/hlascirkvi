@extends('layouts.app')
@section('title')
    <title>{{ "Všetky články" }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Články
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ route('organization.post.create', $organization->id) }}" class="btn btn-primary">Nový článok</a>
        </x-slot>


        <x-slot name="page">

            <div class="">
                @forelse($posts as $post)
                    @include('posts.post-card-admin')
                @empty
                    bez záznamu
                @endforelse
            </div>


            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>

        </x-slot>

    </x-pages.admin>
@endsection
