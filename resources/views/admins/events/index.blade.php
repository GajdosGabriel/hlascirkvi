@extends('layouts.app')
@section('title') <title>{{ 'Akcie a podujatia.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            {{ $title ?? 'Pozvánky na podujatia' }}
        </x-slot>


        <x-slot name="title_right">
            <x-search-form />
        </x-slot>

        <x-slot name="page">
            {{-- Upcoming events --}}
            @forelse($events as $event)
                @include('events._list_items')
            @empty
                bez podujatí
            @endforelse

            <div class="md:block flex justify-center my-8">
                {{ $events->links() }}
            </div>

        </x-slot>
    </x-pages.admin>

@endsection
