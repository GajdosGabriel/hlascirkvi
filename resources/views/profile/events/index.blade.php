@extends('layouts.app')
@section('title')
    <title>{{ "Všetky akcie {$organization->title}" }}</title>
@endsection

@section('content')
    <x-pages.dashboard-and-right>

        <x-slot name="title">
            {{ $title ?? 'Pozvánky na podujatia' }}
        </x-slot>


        <x-slot name="title_right">
            <a href="{{ route('profile.event.create', $organization->id) }}" class="btn btn-primary">Nové podujatie</a>
        </x-slot>

        <x-slot name="page">

            {{-- Upcoming events --}}
            @forelse($events as $event)
                @include('events.card_item')
            @empty
                bez podujatí
            @endforelse

            <div class="md:block flex justify-center my-8">
                {{ $events->links() }}
            </div>

        </x-slot>
        </x-pages.dashboard-and-right>
    @endsection
