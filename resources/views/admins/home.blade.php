@extends('layouts.admin')

@section('title')
    <title>Administrácia</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Administrácia</x-slot>
        <x-slot name="page">
            <p class="mb-6 text-sm text-[color:var(--ar-ink-soft)]">Správa obsahu, kanálov a používateľov Hlasu Cirkvi.</p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ((new \App\View\Components\Navigation\AsideMenu)->adminMenu() as $item)
                    @continue($item['url'] === route('admin.home.index'))
                    <a href="{{ $item['url'] }}" class="ar-card ar-link flex items-center gap-3 rounded-xl p-4">
                        <span class="h-5 w-5 shrink-0 text-[color:var(--ar-accent)]">
                            @include('components.icons.' . $item['icon'])
                        </span>
                        <span class="ar-display font-semibold">{{ trim($item['name']) }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">
                <comments-card></comments-card>
            </div>
        </x-slot>
    </x-pages.admin>
@endsection
