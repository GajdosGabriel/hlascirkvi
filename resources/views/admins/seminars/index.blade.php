@extends('layouts.admin')

@section('title')
    <title>Kolekcie a semináre — administrácia</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Kolekcie a semináre</x-slot>
        <x-slot name="title_right">
            <a class="ar-btn ar-btn--accent" href="{{ route('admin.seminar.create') }}">
                <i class="ph ph-plus" aria-hidden="true"></i> Nová kolekcia
            </a>
        </x-slot>
        <x-slot name="page">
            <x-dashboard.panel title="Kolekcie a semináre všetkých kanálov" flush>
                @include('profiles.seminars._list', ['showChannel' => true])
            </x-dashboard.panel>
            <div class="mt-6">{{ $seminars->links() }}</div>
        </x-slot>
    </x-pages.admin>
@endsection
