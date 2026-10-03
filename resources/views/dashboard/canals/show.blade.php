@extends('layouts.dashboard')

@section('title')
    <title>{{ 'Kanál ' . $canal->title }} | Hlas Cirkvi</title>
@endsection

@section('headerCSS')
    @parent
    @include('partials.canal-overview-style')
@endsection

@section('content')
    <x-pages.dashboard>
        <x-slot name="title">Prehľad kanála</x-slot>
        <x-slot name="page"><x-canal.overview :canal="$canal" /></x-slot>
    </x-pages.dashboard>
@endsection
