@extends('layouts.admin')

@section('title')
    <title>Nová kolekcia — administrácia</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Nová kolekcia</x-slot>
        <x-slot name="title_right">
            <a class="ar-btn ar-btn--quiet" href="{{ route('admin.seminar.index') }}">Späť na kolekcie</a>
        </x-slot>
        <x-slot name="page">
            <x-dashboard.panel title="Kanál kolekcie">
                <form method="GET" action="{{ route('admin.seminar.create') }}" class="ar-form">
                    <div>
                        <label class="ar-label" for="canal_id">Vyberte kanál *</label>
                        <select class="ar-field" id="canal_id" name="canal_id" required>
                            <option value="">Vyberte kanál</option>
                            @foreach ($canals as $canal)
                                <option value="{{ $canal->id }}" @selected((string) old('canal_id') === (string) $canal->id)>{{ $canal->title }}</option>
                            @endforeach
                        </select>
                        @error('canal_id') <p class="ar-error">{{ $message }}</p> @enderror
                        <p class="mt-2 text-sm text-gray-500">Údaje kolekcie vyplníte vo formulári vybraného kanála.</p>
                    </div>
                    <x-dashboard.form-bar :cancel="route('admin.seminar.index')" submit="Pokračovať" />
                </form>
            </x-dashboard.panel>
        </x-slot>
    </x-pages.admin>
@endsection
