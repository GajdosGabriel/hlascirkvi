@extends('layouts.dashboard')
@section('title')
    <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title>
@endsection



@section('content')
    <x-pages.dashboard>
        <x-slot name="title"><seminar-title :seminar="{{ $seminar }}"></seminar-title></x-slot>
        <x-slot name="title_right">
            <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'"></c-article-dropdown>
        </x-slot>


        <x-slot name="page">
            <x-dashboard.panel class="mb-6">
                <seminar-info :seminar="{{ $seminar }}"></seminar-info>
                <seminar-description :seminar="{{ $seminar }}"></seminar-description>
            </x-dashboard.panel>

            <div class="col-span-3">

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse($seminar->posts as $post)
                        <card-front :post="{{ $post }}"></card-front>
                        {{-- @include('posts.card-front') --}}
                    @empty
                        <x-dashboard.empty>Seminár zatiaľ nemá žiadne články.</x-dashboard.empty>
                    @endforelse
                </div>
            </div>

        </x-slot>
        </x-pages.dashboard>
    @endsection
