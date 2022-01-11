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



                <ul>
                    <li class="hover:bg-gray-200 p-2 rounded">
                        <a href="{{ route('videa.videa') }}">
                            Videa a kanaly
                        </a>
                    </li>

                    <li class="hover:bg-gray-200 p-2 rounded">
                        <a href="{{ route('akcie.akcie') }}">
                            Akcie
                        </a>
                    </li>

                    <li class="hover:bg-gray-200 p-2 rounded">
                        <a href="{{ route('modlitby.modlitby') }}">
                            Modlitby
                        </a>
                    </li>

                    <li class="hover:bg-gray-200 p-2 rounded">
                        <a href="{{ route('comments.comments') }}">
                            Youtube komentáre </a>
                    </li>

                </ul>
            </div>
        </x-slot>
    </x-pages.admin>
@endsection
