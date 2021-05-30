@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('profiles._profil-menu')

            </div>
        </div>


        <div class="col-span-5">

            <div class="page_title">
                <h2 class="text-2xl">
                    Vzdelávanie a semináre
                </h2>
            </div>

            <div class="grid grid-cols-12 gap-5">

            </div>

            <div class="col-span-3">

                <h2 class="page_title">Upraviť seminár</h2>

                <form class="" method="post" action="{{ route('seminars.edit', [$seminar->id]) }}">
                    @csrf @method('POST')

                    @include('seminars.form')
                </form>

            </div>
        </div>
    </div>




@endsection
