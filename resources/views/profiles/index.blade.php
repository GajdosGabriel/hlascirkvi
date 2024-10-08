@extends('layouts.app')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Profil home
        </x-slot>

        <x-slot name="page">
            <div class="md:grid grid-cols-3 gap-10">
                <x-organization.statistic />

                <section class="card">
                    <header class="card_header">
                        <h4>Okno 2</h4>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                                clip-rule="evenodd" />
                        </svg>
                    </header>
                </section>

                <user-comments-card></user-comments-card>

                {{-- <section class="card">
                    <header class="card_header">
                        <h4>Okno 3</h4>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                                clip-rule="evenodd" />
                        </svg>
                    </header>
                </section> --}}

            </div>
        </x-slot>

        </x-pages.admin>
    @endsection
