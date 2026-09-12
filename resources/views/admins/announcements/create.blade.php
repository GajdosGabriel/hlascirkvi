@extends('layouts.admin')

@section('title')
    <title>{{ 'Nový oznam' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Nový oznam
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('admin.announcement.index') }}">Späť na oznamy</a>
        </x-slot>

        <x-slot name="page">
            @include('admins.announcements._form', [
                'action' => route('admin.announcement.store'),
                'submit' => 'Vytvoriť oznam',
            ])
        </x-slot>
    </x-pages.admin>
@endsection
