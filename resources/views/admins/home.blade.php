@extends('layouts.app')

@section('content')


    <x-pages.admin>

        <x-slot name="title">
            Admin panel
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">

            <div class="grid grid-cols-3 gap-10">
                <div class="bg-gray-200 p-2">
                    Okno 1
                </div>

                <div class="bg-gray-200 p-2">
                    Okno 2
                </div>

                <div class="bg-gray-200 p-2">
                    Okno 3
                </div>

            </div>
        </x-slot>
    </x-pages.admin>
@endsection
