@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')
            <div class="col-span-7">
                @component('layouts.components.pages.page_title')
                    @slot('title')
                        Modlitby
                    @endslot

                    @slot('title_right')
                        <a class="btn btn-default" href="{{ route('user.prayer.create', auth()->user()->id) }}">
                            Nová modlitba
                        </a>
                    @endslot
                @endcomponent




                <ul>
                    @foreach ($prayers as $prayer)
                        <li class="mb-4 shadow-md border-gray-200 border-2 p-2 rounded">
                            <div>{{ $prayer->title }}</div>
                            <div>{{ $prayer->body }}</div>
                            <div class="text-gray-400 text-sm">Vytvorené: {{ $prayer->created_at->format('m. d. Y') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>


        @endslot
    @endcomponent

@endsection
