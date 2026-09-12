@extends('layouts.dashboard')

@section('title')
    <title>Nový kanál</title>
@endsection

@section('content')
    <x-dashboard.frame>
        <x-dashboard.header heading="Nový kanál">
            <x-slot name="lead">Vyplňte údaje o kanáli. Polia označené * sú povinné.</x-slot>
            <x-slot name="actions">
                <a class="btn btn-default" href="{{ route('profile.canals.index') }}">Späť na kanály</a>
            </x-slot>
        </x-dashboard.header>

        @include('layouts.errors')

        <form method="POST" action="{{ route('profile.canals.store') }}" class="space-y-6">
            @csrf
            <fieldset class="rounded-lg border bg-white p-6">
                <legend class="px-2 text-lg font-semibold">Základné údaje</legend>
                <div class="form-group">
                    <label for="title">Názov kanála *</label>
                    <input class="form-control" type="text" id="title" name="title" value="{{ old('title') }}" minlength="3" maxlength="191" required autofocus placeholder="Napr. Farnosť sv. Martina">
                </div>
                <div class="form-group">
                    <label for="description">Popis kanála</label>
                    <textarea class="form-control" id="description" name="description" rows="5" placeholder="Predstavte svoj kanál a jeho obsah.">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="denomination">Cirkev / zaradenie kanála *</label>
                    <select class="form-control" id="denomination" name="updaters[]" required>
                        <option value="">Vyberte zaradenie</option>
                        @foreach ($denominations as $denomination)
                            <option value="{{ $denomination->id }}" @selected(in_array($denomination->id, old('updaters', [])))>{{ $denomination->title }}</option>
                        @endforeach
                    </select>
                </div>
            </fieldset>

            <fieldset class="rounded-lg border bg-white p-6">
                <legend class="px-2 text-lg font-semibold">Adresa a kontakt</legend>
                <div class="grid gap-x-6 sm:grid-cols-2">
                    <div class="form-group">
                        <label for="village_id">Mesto / obec *</label>
                        <select class="form-control" id="village_id" name="village_id" required autocomplete="address-level2">
                            <option value="">Vyberte mesto alebo obec</option>
                            @foreach ($villages as $village)
                                <option value="{{ $village->id }}" @selected(old('village_id') == $village->id)>{{ $village->fullname }} {{ $village->zip }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="street">Ulica a číslo</label>
                        <input class="form-control" type="text" id="street" name="street" value="{{ old('street') }}" maxlength="191" autocomplete="street-address">
                    </div>
                    <div class="form-group">
                        <label for="email">Kontaktný e-mail</label>
                        <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="100" autocomplete="email" placeholder="kontakt@example.sk">
                    </div>
                    <div class="form-group">
                        <label for="phone">Telefón</label>
                        <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20" autocomplete="tel" placeholder="+421 900 123 456">
                    </div>
                </div>
                <div class="form-group">
                    <label for="url_www">Webová stránka</label>
                    <input class="form-control" type="url" id="url_www" name="url_www" value="{{ old('url_www') }}" maxlength="191" autocomplete="url" placeholder="https://www.example.sk">
                </div>
            </fieldset>

            @if (auth()->user()->hasRole('admin'))
                <fieldset class="rounded-lg border bg-white p-6">
                    <legend class="px-2 text-lg font-semibold">YouTube a príspevky</legend>
                    <p class="mb-4 text-sm text-gray-600">Zadajte ID kanála a playlistu, ktoré sa používajú pri načítaní videí z YouTube. Vložiť sa dá aj adresa kanála — ID si formulár doplní sám.</p>
                    <div class="form-group">
                        <label for="youtube_channel">ID kanála YouTube</label>
                        <input class="form-control" type="text" id="youtube_channel" name="youtube_channel" value="{{ old('youtube_channel') }}" maxlength="191" placeholder="UC… alebo adresa kanála">
                    </div>
                    <div class="form-group">
                        <label for="youtube_playlist">ID playlistu YouTube</label>
                        <input class="form-control" type="text" id="youtube_playlist" name="youtube_playlist" value="{{ old('youtube_playlist') }}" maxlength="191" placeholder="PL… alebo adresa playlistu">
                    </div>
                    <div class="form-group">
                        <label for="mod_title">Text pred názvom príspevku</label>
                        <input class="form-control" type="text" id="mod_title" name="mod_title" value="{{ old('mod_title') }}" maxlength="20">
                    </div>
                </fieldset>
            @endif

            <div class="flex flex-wrap justify-end gap-3">
                <a class="btn btn-default" href="{{ route('profile.canals.index') }}">Zrušiť</a>
                <button class="btn btn-primary" type="submit">Vytvoriť kanál</button>
            </div>
        </form>
    </x-dashboard.frame>
@endsection
