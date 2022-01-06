@extends('layouts.app')

@section('content')


    @component('components.pages.admin')

        @slot('title')
            Admin panel
        @endslot

        @slot('title_right')

        @endslot


        @slot('page')

        <div class="grid grid-cols-2 gap-10">
            {{-- <div class="bg-gray-200">
                jeden
            </div>

            <div class="bg-gray-200">
                jeden
            </div> --}}

    

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
                        Youtube komentáre                    </a>
                </li>

            </ul>
        </div>
        @endslot
    @endcomponent
@endsection
