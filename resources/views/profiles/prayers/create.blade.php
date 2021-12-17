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


               <form action="{{ route('user.prayer.store', auth()->user()->id ) }}" method="post" class="md:w-1/2">
                   @csrf @method('POST')
                   <input name="title" placeholder="Nadpis modlitby" class="w-full mb-2 border-2 rounded p-2 border-gray-300" required />
                   <textarea name="body" placeholder="Obsah modlitby" class="w-full mb-2 border-2 rounded p-2 border-gray-300" required ></textarea>
                   <div class="text-right ">
                       <button class="btn btn-primary">Uložiť</button>
                   </div>
               </form>
            </div>


        @endslot
    @endcomponent

@endsection
