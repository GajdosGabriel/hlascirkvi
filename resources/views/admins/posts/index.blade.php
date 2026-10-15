@extends('layouts.admin')
@php
    $seo = ['title' => 'Články · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>
        <x-slot name="title">Články</x-slot>
        <x-slot name="lead">
            {{ number_format($posts->total(), 0, ',', ' ') }} vo výbere
            @if ($posts->hasPages())
                <span class="mx-1.5 text-gray-300" aria-hidden="true">·</span> strana {{ $posts->currentPage() }} z {{ $posts->lastPage() }}
            @endif
        </x-slot>

        <x-slot name="page">
            <x-dashboard.panel flush>
                @forelse ($posts as $post)
                    @include('profiles.posts._row', ['showCanal' => true])
                @empty
                    <x-dashboard.empty>Výberu nezodpovedá žiadny článok.</x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            @if ($posts->hasPages())
                <div class="mt-8">{{ $posts->links() }}</div>
            @endif
        </x-slot>
    </x-pages.admin>
@endsection
