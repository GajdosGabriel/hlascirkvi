@extends('layouts.app')


{{-- @section('title', $title) --}}


@section('content')
    <x-pages.dashboard>


        <x-slot name="title">
            Upraviť podujatie
        </x-slot>

        <x-slot name="title_right">
            <a class="btn" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Späť</a>
        </x-slot>


        <x-slot name="page">
            <form method="post" action="{{ route('profile.event.update', [$organization->id, $event->id]) }}"
                class="md:flex" enctype="multipart/form-data">
                {{ method_field('PATCH') }} @csrf

                <div class="md:w-2/3 md:m-5">

                    @include('events._form_a')
                </div>

                <div class="md:w-1/3 md:m-5">
                    @include('events._form_b')
                </div>

            </form>
        </x-slot>
        </x-pages.admin>
    @endsection

    @include('posts.editor')
