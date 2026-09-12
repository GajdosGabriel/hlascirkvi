@extends('layouts.admin')

@section('title')
    <title>{{ 'Úprava oznamu' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Úprava oznamu
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('admin.announcement.index') }}">Späť na oznamy</a>
        </x-slot>

        <x-slot name="page">
            @include('admins.announcements._form', [
                'action' => route('admin.announcement.update', $announcement),
                'submit' => 'Uložiť zmeny',
            ])
        </x-slot>
    </x-pages.admin>
@endsection
