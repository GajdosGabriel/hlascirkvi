@extends('layouts.app')


{{-- @section('title', $title) --}}


@section('content')
    <div class="page">

        <form method="post" action="{{ route('akcie.update', [$event->id]) }}" class="md:flex"
            enctype="multipart/form-data">
            {{ method_field('PATCH') }} @csrf

            <div class="page-content md:m-5">


                @component('layouts.pages.page_title')
                    @slot('title')

                        Upraviť podujatie

                    @endslot

                    @slot('title_site')

                    <div>
                        <a class="btn" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Späť</a>
                    </div>

                    @endslot
                @endcomponent

                @include('events._form_a')
            </div>

            <div class="page-aside md:m-5">
                @include('events._form_b')

            </div>

        </form>
    </div>
@endsection

@include('posts.editor')
