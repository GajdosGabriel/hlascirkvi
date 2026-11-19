@extends('layouts.admin')

@php
    $seo = ['title' => 'Osoby vo videu · Administrácia', 'noindex' => true];
    $sources = ['title' => 'názov', 'description' => 'popis', 'review' => 'ručná kontrola', 'manual' => 'ručne', 'canal' => 'osobný kanál', 'ai' => 'AI'];
@endphp

@section('content')
    <x-pages.admin>
        <x-slot name="title">Osoby vo videu</x-slot>
        <x-slot name="lead">
            <a class="ar-link" href="{{ route('post.show', [$post->id, $post->slug]) }}">{{ $post->title }}</a>
            · {{ $post->canal?->title }}
        </x-slot>

        <x-slot name="page">
            <x-dashboard.panel>
                <form method="POST" action="{{ route('admin.persons.post.attach', $post) }}" class="mb-4 flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-64 flex-1">
                        <label class="ar-label" for="person">Pridať osobu</label>
                        <input class="ar-field {{ $errors->has('person') ? 'ar-field--error' : '' }}" type="text" id="person" name="person"
                               list="person-options" required value="{{ old('person') }}" placeholder="Začnite písať meno a vyberte zo zoznamu">
                        @include('admins.persons._options')
                        @error('person') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>
                    <label class="ar-check mb-2">
                        <input type="checkbox" name="create" value="1" @checked(old('create'))> Založiť novú osobu, ak v zozname nie je
                    </label>
                    <button type="submit" class="ar-btn ar-btn--accent"><i class="ph ph-plus" aria-hidden="true"></i> Pridať</button>
                </form>

                <x-dashboard.table label="Osoby vo videu">
                    <thead><tr><th>Osoba</th><th>Zdroj</th><th>Akcia</th></tr></thead>
                    <tbody>
                        @forelse ($persons as $person)
                            <tr>
                                <td><a class="ar-link font-semibold" href="{{ route('admin.persons.edit', $person) }}">{{ $person->name }}</a></td>
                                <td class="text-xs">{{ $sources[$person->pivot->source] ?? $person->pivot->source }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.persons.post.detach', [$post, $person]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ar-act ar-act--danger"><i class="ph ph-x" aria-hidden="true"></i> Odobrať</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-dashboard.empty>Vo videu zatiaľ nie je priradená žiadna osoba.</x-dashboard.empty></td></tr>
                        @endforelse
                    </tbody>
                </x-dashboard.table>
            </x-dashboard.panel>
        </x-slot>
    </x-pages.admin>
@endsection
