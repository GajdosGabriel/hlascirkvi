@extends('layouts.app')


{{-- @section('title', $title) --}}


@section('content')
    @component('layouts.components.pages.profil')

        @slot('title')
            Nové podujatie
        @endslot

        @slot('title_right')
            <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
        @endslot

        @slot('page')
            <div class="grid col-span-10 p-5">
                <form method="post" action="{{ route('organization.event.store', $organization->id) }}" class="flex"
                    enctype="multipart/form-data"> @csrf

                    <div class="page-content">
                        @include('events._form_a')
                    </div>

                    <div class="page-aside mx-5">
                        @include('events._form_b')
                    </div>

                </form>
            </div>
        @endslot
    @endcomponent
@endsection

@include('posts.editor')
