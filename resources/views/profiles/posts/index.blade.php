@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Články
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ route('organization.post.create', $organization->id) }}" class="btn btn-primary">Nový článok</a>
        </x-slot>


        <x-slot name="page">

            @forelse($posts as $post)

                @include('posts._post-list')
            @empty
                bez záznamu
            @endforelse



            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>

        </x-slot>

    </x-pages.admin>

@endsection
