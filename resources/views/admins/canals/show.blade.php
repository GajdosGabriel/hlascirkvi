@extends('layouts.admin')

@section('title')
    <title>{{ 'Kanál ' . $canal->title }}</title>
@endsection

@php
    $plural = fn (int $n, string $one, string $few, string $many)
        => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Kanál {{ $canal->title }}
        </x-slot>

        <x-slot name="title_right">
            <dropdown-slot label="Možnosti kanála">
                @unless ($canal->trashed())
                    <a href="{{ route('organizations.show', $canal) }}">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                        Verejný profil
                    </a>
                @endunless
                <a href="{{ route('admin.buffer.index', ['posts' => $canal->id]) }}">
                    <i class="fas fa-inbox" aria-hidden="true"></i>
                    Čakajúce v buffri
                </a>

                <hr class="ui-dropdown__divider">

                {{-- Superadmin prejde policy `manage` cez Gate::before. --}}
                <a href="{{ route('profile.canals.edit', $canal) }}">
                    <i class="fas fa-pen" aria-hidden="true"></i>
                    Upraviť
                </a>
                <a href="{{ route('profile.canals.show', $canal) }}">
                    <i class="fas fa-columns" aria-hidden="true"></i>
                    Nástenka kanála
                </a>
            </dropdown-slot>
        </x-slot>

        <x-slot name="page">
            <x-canal.overview :canal="$canal">
                <div class="ar-panel p-5">
                    <h3 class="font-semibold">Podrobnosti</h3>
                    @include('components.canal.admin-details', ['canal' => $canal, 'plural' => $plural])
                </div>
            </x-canal.overview>
        </x-slot>

    </x-pages.admin>
@endsection
