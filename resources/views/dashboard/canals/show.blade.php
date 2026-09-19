@extends('layouts.dashboard')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Kanál {{ $canal->title }}
        </x-slot>

        <x-slot name="title_right">
            <dropdown-slot label="Možnosti kanála">
                @if ($canal->id === auth()->user()->canal_id)
                    <span class="ui-dropdown__item ui-dropdown__item--current">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        Prihlásený kanál
                    </span>
                @else
                    <form method="POST" action="{{ route('profile.canals.switch', $canal) }}">
                        @method('PUT') @csrf
                        <button type="submit" class="ui-dropdown__item--accent">
                            <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                            Prepnúť na kanál
                        </button>
                    </form>
                @endif

                <hr class="ui-dropdown__divider">

                <a href="{{ route('profile.canals.edit', $canal) }}">
                    <i class="fas fa-pen" aria-hidden="true"></i>
                    Upraviť
                </a>
                <a href="{{ route('profile.canals.prayers.index', $canal) }}">
                    <i class="fas fa-praying-hands" aria-hidden="true"></i>
                    Modlitby
                </a>
                <a href="{{ route('profile.canals.seminars.index', $canal) }}">
                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    Podujatia
                </a>
            </dropdown-slot>
        </x-slot>


        <x-slot name="page">

            <div class="grid gap-5 md:grid-cols-2">

                <div class="ar-panel p-5">
                    <x-canal.statistic :canal="$canal" />
                </div>

                <div class="ar-panel p-5">
                    <h3 class="font-semibold">Prihlásiť sa do kanálu</h3>
                    {{-- Predtým admin.user.update, ktoré je len pre superadmina —
                         bežného správcu to ticho vrátilo na titulku. --}}
                    <form action="{{ route('profile.canals.switch', $canal) }}" method="post" class="mr-4 mb-4">
                        @method('PUT') @csrf
                        <button
                            class="ar-btn ar-btn--accent">Nastaviť
                            {{ $canal->title }}
                        </button>
                    </form>
                </div>


            </div>
        </x-slot>
        </x-pages.dashboard>
    @endsection
