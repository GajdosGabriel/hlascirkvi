@php
    $seo = ['title' => 'Nová kolekcia · Administrácia', 'noindex' => true];
@endphp
@extends('layouts.admin')

@section('content')
    <x-pages.admin>
        <x-slot name="title">Nová kolekcia</x-slot>
        <x-slot name="lead">Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        <x-slot name="page">
            {{-- Kanál aj údaje kolekcie v jednom formulári; predtým sa tu len
                 vyberal kanál a formulár odskočil do správy kanála. --}}
            <form method="POST" action="{{ route('admin.seminar.store') }}" class="max-w-3xl space-y-5">
                @csrf

                <section class="ar-panel">
                    <div class="ar-panel__head"><h2 class="ar-panel__title">Kanál</h2></div>
                    <div class="ar-panel__body">
                        <label class="ar-label" for="canal_id">Kanál kolekcie <span class="ar-req">*</span></label>
                        <select class="ar-field{{ $errors->has('canal_id') ? ' ar-field--error' : '' }}" id="canal_id" name="canal_id" required>
                            <option value="">Vyberte kanál</option>
                            @foreach ($canals as $canal)
                                <option value="{{ $canal->id }}" @selected((string) old('canal_id') === (string) $canal->id)>{{ $canal->title }}</option>
                            @endforeach
                        </select>
                        @error('canal_id') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>
                </section>

                @include('seminars.form', [
                    'submitLabel' => 'Vytvoriť kolekciu',
                    'cancelUrl' => route('admin.seminar.index'),
                ])
            </form>
        </x-slot>
    </x-pages.admin>
@endsection
