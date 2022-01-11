@extends('layouts.app')

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Kanály pre {{ $updater->title }}
        </x-slot>

        <x-slot name="title_right">

            <h2 class="page_title">Pridať kanál</h2>

            @include('tags.form')

        </x-slot>


        <x-slot name="page">

            @forelse ( $updater->organizations as $tag )

                <form action="{{ route('updater.organization.destroy', [$updater->id, $tag->id]) }}" method="POST"
                    class="flex justify-between mb-4 border-b-2 hover:bg-gray-50 border-dashed">

                    @csrf @method('DELETE')
                    <div class="font-semibold text-lg">{{ $tag->title }}</div>


                    <button class="cursor-pointer ">X</button>


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
