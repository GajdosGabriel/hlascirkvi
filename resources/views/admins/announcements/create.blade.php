@extends('layouts.admin')

@php
    $seo = ['title' => 'Nový oznam · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Nový oznam
        </x-slot>

        <x-slot name="lead">Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>

        <x-slot name="page">
            @include('admins.announcements._form', [
                'action' => route('admin.announcement.store'),
                'submit' => 'Vytvoriť oznam',
            ])
        </x-slot>
    </x-pages.admin>
@endsection
