@extends('layouts.admin')

@php
    $seo = ['title' => 'Nová osoba · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>
        <x-slot name="title">Nová osoba</x-slot>
        <x-slot name="lead">Polia označené <span class="ar-req">*</span> sú povinné. Videá a kanály priradíte po uložení.</x-slot>
        <x-slot name="page">
            @include('admins.persons._form', [
                'action' => route('admin.persons.store'),
                'submit' => 'Založiť osobu',
                'canalOptions' => \App\Models\Canal::query()->orderBy('title')->get(['id', 'title']),
            ])
        </x-slot>
    </x-pages.admin>
@endsection
