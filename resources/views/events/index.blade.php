@extends('layouts.app')
@section('title') <title>{{ 'Kresťanské akcie' }}</title> @endsection

@section('content')

    <x-pages.page_3_2>

        <x-slot name="page_full">
            <x-events.modul-current-events />
        </x-slot>

        <x-slot name="page">

            <x-pages.page_title>
                <x-slot name="title">
                    {{ $title ?? 'Pozvánky na podujatia' }}
                </x-slot>

                <x-slot name="title_right">
                    @auth
                        <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300"
                            href="{{ route('organization.event.create', auth()->user()->org_id) }}"><i
                                class="fas fa-plus"></i>
                            Nové podujatie
                        </a>
                    @else
                        <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300"
                            href="{{ route('login') }}"><i class="fas fa-plus"></i>
                            Nové podujatie
                        </a>
                    @endauth
                </x-slot>
            </x-pages.page_title>

            {{-- Upcoming events --}}
            @forelse($events as $event)
                @include('events.card_items')
            @empty
                bez podujatí
            @endforelse

            {{ $events->links() }}

        </x-slot>

        {{-- Aside section --}}
        <x-slot name="aside">

            @include('events.districts_modul')
            @include('events.finished_event_modul')

        </x-slot>
    </x-pages.page_3_2>

@endsection
