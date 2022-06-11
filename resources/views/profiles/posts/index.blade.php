@extends('layouts.app')
@section('title')
    <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title>
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

            <x-filter.card>
                <x-slot name="left">
                    <x-filter.unpublished></x-filter.unpublished>
                </x-slot>

                <x-slot name="right">
                    <x-search-form />
                </x-slot>
            </x-filter.card>

            <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                @forelse($posts as $post)
                    <post-card :post="{{ $post }}"></post-card>

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
