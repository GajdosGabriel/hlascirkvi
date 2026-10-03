@extends('layouts.admin')

@section('title')
    <title>Nový kanál | Hlas Cirkvi</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Nový kanál</x-slot>
        <x-slot name="title_right">
            <a href="{{ route('admin.canal.index') }}" class="ar-btn ar-btn--quiet">
                <i class="ph ph-arrow-left"></i> Späť na kanály
            </a>
        </x-slot>
        <x-slot name="page">
            <p class="ar-hint mb-5">Vyplňte údaje o kanáli. Polia označené <span class="ar-req">*</span> sú povinné.</p>
            @include('dashboard.canals._form')
        </x-slot>
    </x-pages.admin>
@endsection
