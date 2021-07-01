@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
    <div class="grid grid-cols-12 gap-6  ">


        @include('profiles._profil-menu')



        <div class="col-span-5">


            @component('layouts.pages.page_title')
                @slot('title')

                    Nový seminár

                @endslot

            @endcomponent


            <div class="grid grid-cols-12 gap-5">

            </div>

            <div class="col-span-3">

                <form class="" method="post" action="{{ route('seminars.store') }}">
                    @csrf @method('POST')

                    @include('seminars.form')

                </form>
            </div>
        </div>
    </div>




@endsection
