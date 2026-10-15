@php
    $seo = ['title' => 'Upraviť kolekciu', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header :heading="$seminar->title">
            <x-slot name="lead">Úprava kolekcie. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
            <x-slot name="actions">
                <a href="{{ route('seminars.show', $seminar) }}" class="ar-btn ar-btn--quiet" target="_blank" rel="noopener">
                    <i class="ph ph-arrow-square-out"></i> Zobraziť verejne
                </a>
                <a href="{{ route('profile.canals.seminars.show', [$canal->id, $seminar->id]) }}" class="ar-btn ar-btn--quiet">
                    <i class="ph ph-list-checks"></i> Spravovať príspevky
                </a>
            </x-slot>
        </x-dashboard.header>

        <form method="post" action="{{ route('profile.canals.seminars.update', [$canal->id, $seminar->id]) }}" class="max-w-3xl">
            @csrf @method('PUT')
            @include('seminars.form')
        </form>
    </x-dashboard.frame>
@endsection
