@extends('layouts.app')

@section('content')


    @component('components.pages.admin')

        @slot('title')
            Admin panel
        @endslot

        @slot('title_right')

        @endslot


        @slot('page')

            <ul>
                <li class="mb-5">
                    <a href="{{ route('videa.videa') }}">
                        Videa a kanaly
                    </a>
                </li>

                <li class="mb-5">
                    <a href="{{ route('akcie.akcie') }}">
                        Akcie
                    </a>
                </li>

                <li class="mb-5">
                    <a href="{{ route('modlitby.modlitby') }}">
                        Modlitby
                    </a>
                </li>

            </ul>

        @endslot
    @endcomponent
@endsection
