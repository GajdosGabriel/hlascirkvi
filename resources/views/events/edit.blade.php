@extends('layouts.app')


{{-- @section('title', $title) --}}


@section('content')
    @component('layouts.components.pages.profil')


        @slot('title')
            Upraviť podujatie
        @endslot

        @slot('title_right')
            <a class="btn" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Späť</a>
        @endslot


        @slot('page')
            <form method="post" action="{{ route('organization.event.update', [$organization->id, $event->id]) }}"
                class="md:flex" enctype="multipart/form-data">
                {{ method_field('PATCH') }} @csrf

                <div class="page-content md:m-5">

                    @include('events._form_a')
                </div>

                <div class="page-aside md:m-5">
                    @include('events._form_b')
                </div>

            </form>
        @endslot
    @endcomponent
@endsection

@include('posts.editor')
