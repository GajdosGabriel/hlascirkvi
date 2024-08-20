@extends('layouts.app')


{{-- @section('title', $title) --}}


@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Nové podujatie
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
        </x-slot>

        <x-slot name="page">
            <div class="grid col-span-10 p-5">
                <form method="post" action="{{ route('profile.organization.event.store', $organization->id) }}" class="flex"
                    enctype="multipart/form-data"> @csrf

                    <div class="page-content">
                        @include('events._form_a')
                    </div>

                    <div class="page-aside mx-5">
                        @include('events._form_b')
                    </div>

                </form>
            </div>
        </x-slot>
        </x-pages.admin>
    @endsection

    @include('posts.editor')
