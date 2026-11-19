@extends('layouts.admin')

@php
    $seo = ['title' => $person->name . ' · Osoby · Administrácia', 'noindex' => true];
    $sources = ['title' => 'názov', 'description' => 'popis', 'review' => 'ručná kontrola', 'manual' => 'ručne', 'canal' => 'osobný kanál', 'ai' => 'AI'];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">{{ $person->name }}</x-slot>

        <x-slot name="title_right">
            <a class="ar-btn" href="{{ route('person.show', $person) }}" target="_blank" rel="noopener">
                <i class="ph ph-arrow-square-out" aria-hidden="true"></i> Na webe
            </a>
        </x-slot>

        <x-slot name="page">
            @include('admins.persons._form', [
                'action' => route('admin.persons.update', $person),
                'submit' => 'Uložiť zmeny',
            ])

            {{-- Videá osoby --}}
            <x-dashboard.panel class="mt-6" :title="'Videá (' . $posts->total() . ')'">
                <form method="POST" action="{{ route('admin.persons.posts.attach', $person) }}" class="mb-4 flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="min-w-64 flex-1">
                        <label class="ar-label" for="post">Priradiť video</label>
                        <input class="ar-field {{ $errors->has('post') ? 'ar-field--error' : '' }}" type="text" id="post" name="post" required
                               placeholder="Číslo príspevku, adresa z hlascirkvi.sk alebo odkaz na YouTube">
                        @error('post') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="ar-btn ar-btn--accent"><i class="ph ph-plus" aria-hidden="true"></i> Priradiť</button>
                </form>

                <x-dashboard.table label="Videá osoby">
                    <thead>
                        <tr><th>Video</th><th>Kanál</th><th>Zdroj</th><th>Akcia</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td>
                                    <a class="ar-link" href="{{ route('post.show', [$post->id, $post->slug]) }}" target="_blank" rel="noopener">{{ $post->title }}</a>
                                    <div class="text-xs text-(--ar-muted)">
                                        {{ $post->created_at?->format('d.m.Y') }}{{ $post->published_at ? '' : ' · v bufferi' }}
                                    </div>
                                </td>
                                <td>{{ $post->canal?->title }}</td>
                                <td class="whitespace-nowrap text-xs">{{ $sources[$post->pivot->source] ?? $post->pivot->source }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.persons.posts.detach', [$person, $post->id]) }}"
                                          data-confirm="Odobrať osobu z videa „{{ $post->title }}“?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ar-act ar-act--danger"><i class="ph ph-x" aria-hidden="true"></i> Odobrať</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-dashboard.empty>Osoba zatiaľ nemá priradené žiadne video.</x-dashboard.empty></td></tr>
                        @endforelse
                    </tbody>
                </x-dashboard.table>
                <div class="mt-4">{{ $posts->fragment('videa')->onEachSide(1)->links() }}</div>
            </x-dashboard.panel>

            {{-- Kanály, v ktorých osoba pôsobí --}}
            <x-dashboard.panel class="mt-6" title="Pôsobí v kanáloch">
                <form method="POST" action="{{ route('admin.persons.canals.attach', $person) }}" class="mb-4 flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="min-w-64 flex-1">
                        <label class="ar-label" for="canal_attach">Kanál</label>
                        <select class="ar-field" id="canal_attach" name="canal_id" required>
                            <option value="">— vyberte kanál —</option>
                            @foreach ($canalOptions as $canal)
                                <option value="{{ $canal->id }}">{{ $canal->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ar-label" for="role">Rola</label>
                        <input class="ar-field" type="text" id="role" name="role" maxlength="50" list="roles" placeholder="kazateľ, farár, hosť…">
                        <datalist id="roles">
                            @foreach (['kazateľ', 'pastor', 'farár', 'kaplán', 'biskup', 'hosť', 'vedúci spoločenstva', 'moderátor'] as $role)
                                <option value="{{ $role }}">
                            @endforeach
                        </datalist>
                    </div>
                    <button type="submit" class="ar-btn ar-btn--accent"><i class="ph ph-plus" aria-hidden="true"></i> Pridať</button>
                </form>

                <x-dashboard.table label="Kanály osoby">
                    <thead>
                        <tr><th>Kanál</th><th>Rola</th><th>Videí v kanáli</th><th>Zdroj</th><th>Akcia</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($canals as $canal)
                            <tr>
                                <td><a class="ar-link" href="{{ route('organizations.show', [$canal->id]) }}" target="_blank" rel="noopener">{{ $canal->title }}</a></td>
                                <td>{{ $canal->pivot->role ?? '—' }}</td>
                                <td>{{ $canal->pivot->posts_count }}</td>
                                <td class="text-xs">{{ $canal->pivot->source === 'derived' ? 'z videí' : 'ručne' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.persons.canals.detach', [$person, $canal->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ar-act ar-act--danger"><i class="ph ph-x" aria-hidden="true"></i> Odobrať</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-dashboard.empty>Osoba nemá priradený žiadny kanál.</x-dashboard.empty></td></tr>
                        @endforelse
                    </tbody>
                </x-dashboard.table>
                <p class="ar-hint mt-3">Kanály „z videí“ sa každú hodinu prepočítajú (aspoň {{ config('persons.canal_min_posts') }} videí v kanáli).
                    Ručne pridané ostávajú, kým ich neodoberiete.</p>
            </x-dashboard.panel>

            {{-- Zlúčenie a zmazanie --}}
            <x-dashboard.panel class="mt-6" title="Duplicita a zmazanie">
                <form method="POST" action="{{ route('admin.persons.merge', $person) }}" class="flex flex-wrap items-end gap-2"
                      data-confirm="Zlúčiť {{ $person->name }} do vybranej osoby? Táto osoba zanikne.">
                    @csrf
                    <div class="min-w-64 flex-1">
                        <label class="ar-label" for="target">Zlúčiť do osoby</label>
                        <input class="ar-field {{ $errors->has('target') ? 'ar-field--error' : '' }}" type="text" id="target" name="target"
                               list="person-options" required placeholder="Začnite písať meno a vyberte zo zoznamu">
                        @include('admins.persons._options')
                        @error('target') <p class="ar-error">{{ $message }}</p> @enderror
                        <p class="ar-hint">Videá a kanály prejdú na vybranú osobu, meno „{{ $person->name }}“ ostane ako jej iná podoba mena.</p>
                    </div>
                    <button type="submit" class="ar-btn"><i class="ph ph-git-merge" aria-hidden="true"></i> Zlúčiť</button>
                </form>

                <form method="POST" action="{{ route('admin.persons.destroy', $person) }}" class="mt-5"
                      data-confirm="Naozaj zmazať osobu {{ $person->name }}? Zmažú sa aj všetky jej priradenia k videám a kanálom.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ar-act ar-act--danger"><i class="ph ph-trash" aria-hidden="true"></i> Zmazať osobu</button>
                </form>
            </x-dashboard.panel>
        </x-slot>
    </x-pages.admin>
@endsection
