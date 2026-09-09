@extends('layouts.dashboard')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Kanál {{ $organization->title }}
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">

            <div class="grid gap-5 md:grid-cols-2">

                <div class="ar-panel p-5">
                    <x-organization.statistic />
                </div>

                <div class="ar-panel p-5">
                    <h3 class="font-semibold">Prihlásiť sa do kanálu</h3>
                    <form action="{{ route('admin.user.update', auth()->id()) }}" method="post" class="mr-4 mb-4">
                        @method('PUT') @csrf
                        <input type="hidden" name="org_id" value="{{ $organization->id }}" />
                        <button
                            class="ar-btn ar-btn--accent">Nastaviť
                            {{ $organization->title }}
                        </button>
                    </form>
                </div>


            </div>
        </x-slot>
        </x-pages.dashboard>
    @endsection
