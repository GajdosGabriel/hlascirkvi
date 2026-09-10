@extends('layouts.dashboard')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Kanál {{ $canal->title }}
        </x-slot>

        <x-slot name="title_right">

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
