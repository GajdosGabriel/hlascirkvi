@extends('layouts.app')

@section('title')
    <title>{{ "Admin nezverejnené videa" }}</title>
@endsection

@section('content')


    <x-pages.admin>

        <x-slot name="title">
            Updater
        </x-slot>

        <x-slot name="title_right">
            @include('admins.updater.form')
        </x-slot>


        <x-slot name="page">

            @forelse ( $updaters as $tag )

                <div class="flex justify-between p-2 border-b-2 hover:bg-gray-50 border-dashed">
                    <a href="{{ route('admin.updater.organization.index', [$tag->id]) }}">
                        <h4 class="font-semibold text-lg">{{ $tag->id }}. {{ $tag->title }}</h4>
                    </a>
                    <div class="flex">
                        <div class="mr-2">{{ $tag->type }}</div>
                        <div class="font-semibold text-lg">{{ $tag->organizations->count() }}</div>
                    </div>

                    {{-- @can('update', $tag)
                        <tag-dropdown :post="{{ $tag }}" />
                    @endcan --}}
                </div>

            @empty
                žiadne updaters
            @endforelse

    

        </x-slot>
    </x-pages.admin>



@endsection
