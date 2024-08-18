@extends('layouts.app')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Kanály pre {{ $updater->title }}
        </x-slot>

        <x-slot name="title_right">

            {{-- <h2 class="page_title">Pridať kanál</h2> --}}

            {{-- <form action="{{ route('admin.updater.organization.store', [$updater->id, $tag->id]) }}" method="POST" --}}
            {{-- class="flex justify-between border-b-2 hover:bg-gray-50 border-dashed p-2"> --}}
            @csrf @method('POST')
            <x-organization.select-input />
            <div>
                <button class="btn btn-primary">Uložiť</button>
            </div>
            {{-- </form> --}}

        </x-slot>


        <x-slot name="page">

            @forelse ($updater->organizations as $tag)
                <form action="{{ route('admin.updater.organization.destroy', [$updater->id, $tag->id]) }}" method="POST"
                    class="flex justify-between border-b-2 hover:bg-gray-50 border-dashed p-2">

                    @csrf @method('DELETE')
                    <div class="font-semibold text-lg">{{ $tag->title }}</div>


                    <button class="cursor-pointer px-2 hover:bg-gray-500 rounded-full hover:text-gray-200 ">X</button>


                    {{-- @can('update', $tag)
                        <tag-dropdown :post="{{ $tag }}" />
                    @endcan --}}
                </form>

            @empty
                žiadne kanály
            @endforelse

        </x-slot>

        </x-pages.admin>
    @endsection
