@extends('layouts.admin')

@php
    $seo = ['title' => 'Úprava oznamu · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Úprava oznamu
        </x-slot>

        <x-slot name="lead">Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>

        <x-slot name="page">
            @include('admins.announcements._form', [
                'action' => route('admin.announcement.update', $announcement),
                'submit' => 'Uložiť zmeny',
            ])
        </x-slot>
    </x-pages.admin>
@endsection
