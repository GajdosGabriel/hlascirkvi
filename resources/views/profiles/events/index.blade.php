@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
        <div class="col-span-8">

            <div class="page_title">
                <h2 class="text-2xl"> {{ $title ?? 'Pozvánky na podujatia' }}</h2>

                <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300"
                    href="{{ route('akcie.create') }}"><i class="fas fa-plus"></i>
                    Nové podujatie
                </a>
            </div>

            {{-- Upcoming events --}}
            @forelse($events as $event)
                @include('events._list_items')
            @empty
                bez podujatí
            @endforelse

            {{ $events->links() }}
        </div>
        @endslot
    @endcomponent

@endsection
