@extends('layouts.app')
@section('title')
    <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title>
@endsection



@section('content')
    <x-pages.dashboard-and-aside>
        <x-slot name="title">

            <div>
                <h2 class="text-2xl font-semibold">
                    <seminar-title :seminar="{{ $seminar }}"></seminar-title>
                </h2>
                <seminar-info :seminar="{{ $seminar }}"></seminar-info>
                <seminar-description :seminar="{{ $seminar }}"></seminar-description>
            </div>

            <div class="flex items-center">
                <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'">
                </c-article-dropdown>
            </div>

        </x-slot>


        <x-slot name="page">

            <div class="col-span-3">

                <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                    @forelse($seminar->posts as $post)
                        <card-front :post="{{ $post }}"></card-front>
                        {{-- @include('posts.card-front') --}}
                    @empty
                        bez záznamu
                    @endforelse
                </div>
            </div>

        </x-slot>
        </x-pages.dashboard-and-aside>
    @endsection
