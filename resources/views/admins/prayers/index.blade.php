@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Modlitby
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('organization.prayer.create', auth()->user()->org_id) }}">
                Nová modlitba
            </a>
        </x-slot>

        <x-slot name="page">
            <ul>
                @foreach ($prayers as $prayer)
                    <li class="mb-4 shadow-md border-gray-200 border-2 p-2 rounded">
                        <div class="flex justify-between">
                            <div>{{ $prayer->title }}</div>
                            <div class="flex space-x-1 items-center">
                                <a href="{{ route('organization.prayer.edit', [$prayer->organization->id, $prayer->id]) }}"
                                    class="text-sm hover:text-gray-600 hover:bg-gray-100 px-2 rounded-md">Upraviť
                                </a>

                                <form action="{{ route('organization.prayer.destroy', [$prayer->organization->id, $prayer->id]) }}"
                                    method="post">
                                    @method('DELETE') @csrf
                                    <button
                                        class="text-sm hover:text-gray-600 hover:bg-gray-100 px-2 rounded-md">Zmazať</button>
                                </form>
                            </div>

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
            </div>
        </x-slot>
    </x-pages.admin>

@endsection
