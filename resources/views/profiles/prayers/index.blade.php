@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')
            <div class="col-span-8">

                {{-- Stlpec I. --}}
                <div class="col-span-4">
                    <prayers-index-page></prayers-index-page>
                </div>


                <prayers-index-page2></prayers-index-page2>


            </div>

            <div class="md:block flex justify-center my-8">
                {{ $prayers->links() }}
            </div>
            </div>
        @endslot
    @endcomponent

@endsection
