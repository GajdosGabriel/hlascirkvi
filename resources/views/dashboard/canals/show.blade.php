@extends('layouts.dashboard')

@section('title')
    <title>{{ 'Kanál ' . $canal->title }}</title>
@endsection

@section('content')
    <x-pages.dashboard>
        <x-slot name="title">Prehľad kanála</x-slot>
        <x-slot name="page"><x-canal.overview :canal="$canal" /></x-slot>
    </x-pages.dashboard>
@endsection
