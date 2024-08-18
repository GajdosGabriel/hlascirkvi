@extends('layouts.app')

@section('title')
    <title>{{ 'Admin tágy (nálepky)' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Tags panel
        </x-slot>

        <x-slot name="title_right">

            <h2 class="page_title">Nový tág</h2>

            @include('tags.form')

        </x-slot>


        <x-slot name="page">
            @forelse ($tags as $tag)
                <div class="flex justify-between mb-4">
                    <h4 class="font-semibold text-lg">{{ $tag->title }}</h4>

                    @can('update', $tag)
                        <tag-dropdown :post="{{ $tag }}" />
                    @endcan
                </div>

            @empty
                žiadne tagy
            @endforelse

        </x-slot>

        </x-pages.admin>
    @endsection
