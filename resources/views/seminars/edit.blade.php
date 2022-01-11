@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
    <div class="grid grid-cols-12 gap-6  ">



        @include('profiles._profil-menu')


        <div class="col-span-5">

            <x-pages.page_title>
                <x-slot name="title">

                    Upraviť seminár

                </x-slot>

                <x-slot name="title_right">

                </x-slot>
            </x-pages.page_title>


            <div class="col-span-3">

                <h2 class="page_title">Upraviť seminár</h2>

                <form class="" method="post"
                    action="{{ route('organization.seminar.update', [$organization->id, $seminar->id]) }}">
                    @csrf @method('PUT')

                    @include('seminars.form')
                </form>

            </div>
        </div>
    </div>




@endsection
