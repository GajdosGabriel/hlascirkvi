@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')
        
                {{-- Stlpec I. --}}
                <div class="col-span-4 mt-6">
                    <prayers-index-page></prayers-index-page>
                </div>

                <div class="col-span-4 mt-6">
                <prayers-index-page2></prayers-index-page2>
                </div>

        @endslot
    @endcomponent

@endsection
