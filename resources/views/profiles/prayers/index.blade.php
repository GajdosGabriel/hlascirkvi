@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Modlitby
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('user.prayer.create', auth()->user()->id) }}">
                Nová modlitba
            </a>
        </x-slot>

        <x-slot name="page">
            <ul>
                @foreach ($prayers as $prayer)
                    <li class="mb-4 shadow-md border-gray-200 border-2 p-2 rounded">
                        <div class="flex justify-between">
                            <div>{{ $prayer->title }}</div>
                            <a href="{{ route('user.prayer.edit', [$user->id, $prayer->id]) }}"
                                class="text-sm hover:text-gray-400">Upraviť</a>
                        </div>
                        <div>{{ $prayer->body }}</div>

                        <div class="flex">
                            <div class="text-gray-400 text-sm font-semibold mr-4">Meno: {{ $prayer->user_name }}</div>
                            <div class="text-gray-400 text-sm">Vytvorené: {{ $prayer->created_at->format('m. d. Y') }}
                            </div>
                        </div>

                    </li>
                @endforeach
            </ul>
        </x-slot>
    </x-pages.admin>

@endsection
