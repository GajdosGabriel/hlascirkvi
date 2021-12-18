@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')
            <div class="col-span-8">
                
                @component('layouts.components.pages.page_title')
                    @slot('title')
                        Nová modlitba
                    @endslot

                    @slot('title_right')
                        <a class="btn btn-default" href="{{ route('user.prayer.index', auth()->user()->id) }}">
                            Späť
                        </a>
                    @endslot
                @endcomponent


                <form action="{{ route('user.prayer.store', auth()->user()->id) }}" method="post" class="md:w-1/2">
                    @csrf @method('POST')
                    @include('prayers._form')
                </form>
            </div>


        @endslot
    @endcomponent

@endsection
