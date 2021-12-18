@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')

        @slot('title')
            {{ $title ?? 'Pozvánky na podujatia' }}
        @endslot


        @slot('title_right')
            <a href="{{ route('organization.event.create', $organization->id) }}" class="btn btn-primary">Nová akcia</a>
        @endslot

        @slot('page')
            {{-- Upcoming events --}}
            @forelse($events as $event)
                @include('events._list_items')
            @empty
                bez podujatí
            @endforelse

            <div class="md:block flex justify-center my-8">
                {{ $events->links() }}
            </div>

        @endslot
    @endcomponent

@endsection
