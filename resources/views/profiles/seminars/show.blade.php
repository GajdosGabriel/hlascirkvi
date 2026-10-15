@php
    $seo = ['title' => $seminar->title . ' — správa kolekcie', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame>
        <x-dashboard.header :heading="$seminar->title">
            <x-slot name="lead">{{ $seminar->kind_label }} · {{ $seminar->published ? 'zverejnená' : 'koncept, vidia ju len správcovia' }}</x-slot>
            <x-slot name="actions">
                <a href="{{ route('seminars.show', $seminar) }}" class="ar-btn ar-btn--quiet" target="_blank" rel="noopener">
                    <i class="ph ph-arrow-square-out"></i> Zobraziť verejne
                </a>
                @include('seminars._actions')
            </x-slot>
        </x-dashboard.header>

        <x-dashboard.panel class="mb-6">
            @include('seminars._info')
        </x-dashboard.panel>

        <x-dashboard.panel title="Príspevky v kolekcii" flush>
            <x-slot name="note">
                @if ($posts->hasPages())
                    strana {{ $posts->currentPage() }} z {{ $posts->lastPage() }}
                @endif
            </x-slot>

            <div class="ar-panel__body border-b border-[color:var(--ar-line)]">
                <p class="mb-4 text-sm text-[color:var(--ar-ink-soft)]">
                    Označte príspevky a pridajte ich do kolekcie alebo ich z nej odoberte. Odobratie príspevok nezmaže
                    a jeden príspevok môže patriť do viacerých kolekcií.
                </p>

                {{-- Výber zaradenia odošle filter hneď (data-auto-submit); tlačidlo
                     ostáva pre hľadanie Enterom a bez JavaScriptu. --}}
                <form method="get" action="{{ route('profile.canals.seminars.show', [$canal, $seminar]) }}"
                      class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]" data-collection-filter>
                    <div>
                        <label class="ar-label" for="collection-q">Hľadať príspevky kanála</label>
                        <span class="ar-searchbox">
                            <input id="collection-q" name="q" type="search" class="ar-field" value="{{ $search }}" maxlength="200">
                            <x-search-button label="Hľadať príspevky" />
                        </span>
                    </div>
                    <div>
                        <label class="ar-label" for="membership">Zobraziť</label>
                        <select id="membership" name="membership" class="ar-field" data-auto-submit>
                            <option value="all" @selected($membership === 'all')>Všetky príspevky kanála</option>
                            <option value="in" @selected($membership === 'in')>V tejto kolekcii</option>
                            <option value="out" @selected($membership === 'out')>Mimo tejto kolekcie</option>
                        </select>
                    </div>
                </form>
            </div>

            @if ($errors->any())
                <p role="alert" class="ar-error px-4 pt-3">{{ $errors->first() }}</p>
            @endif

            <form method="post" action="{{ route('profile.canals.seminars.posts', [$canal, $seminar]) }}" data-collection-bulk>
                @csrf

                @if ($posts->isNotEmpty())
                    <label class="ar-row cursor-pointer bg-[color:var(--ar-paper)] text-sm font-semibold">
                        <input type="checkbox" class="mt-0.5 accent-[color:var(--ar-accent)]" data-select-all>
                        <span>Označiť všetko na tejto strane</span>
                    </label>
                @endif

                @forelse ($posts as $post)
                    <div class="ar-row">
                        <input id="member-{{ $post->id }}" type="checkbox" name="posts[]" value="{{ $post->id }}"
                               class="mt-1 accent-[color:var(--ar-accent)]" data-select-item
                               @checked(in_array($post->id, old('posts', [])))>
                        <span class="min-w-0">
                            <label for="member-{{ $post->id }}" class="ar-row__title ar-clamp-2 cursor-pointer">{{ $post->title }}</label>
                            <span class="ar-row__meta">
                                @if ($post->in_collection)
                                    <span class="ar-badge ar-badge--ok">V kolekcii</span>
                                @endif
                                @unless ($post->published_at)
                                    <span class="ar-badge ar-badge--warn">Čaká v bufferi</span>
                                @endunless
                            </span>
                        </span>
                        @can('update', $post)
                            <a class="ar-row__value ar-link text-xs font-medium" href="{{ route('profile.posts.edit', $post) }}">Upraviť</a>
                        @endcan
                    </div>
                @empty
                    <x-dashboard.empty>Žiadne príspevky nezodpovedajú výberu.</x-dashboard.empty>
                @endforelse

                @if ($posts->isNotEmpty())
                    {{-- Výber platí len pre aktuálnu stranu — stránkovanie je
                         obyčajný odkaz a označené políčka si neodnáša. --}}
                    <div class="ar-form__bar m-3" data-bulk-bar>
                        <span class="ar-form__bar-note" data-bulk-count>Označte príspevky na tejto strane.</span>
                        <button type="submit" name="action" value="remove" class="ar-btn ar-btn--quiet" data-bulk-action disabled>
                            <i class="ph ph-minus-circle"></i> Odobrať z kolekcie
                        </button>
                        <button type="submit" name="action" value="add" class="ar-btn ar-btn--accent" data-bulk-action disabled>
                            <i class="ph ph-plus-circle"></i> Pridať do kolekcie
                        </button>
                    </div>
                @endif
            </form>
        </x-dashboard.panel>

        @if ($posts->hasPages())
            <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </x-dashboard.frame>

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        // Až nad strom, ktorý Vue prekreslilo (partials/ar-ready).
        window.arReady(() => {
            const bulk = document.querySelector('[data-collection-bulk]');
            if (!bulk) return;
            const all = bulk.querySelector('[data-select-all]');
            const items = [...bulk.querySelectorAll('[data-select-item]')];
            const count = bulk.querySelector('[data-bulk-count]');
            const actions = bulk.querySelectorAll('[data-bulk-action]');

            const forms = (n) => n === 1 ? 'príspevok' : (n >= 2 && n <= 4 ? 'príspevky' : 'príspevkov');
            const refresh = () => {
                const n = items.filter((i) => i.checked).length;
                actions.forEach((b) => { b.disabled = n === 0; });
                if (count) count.textContent = n ? 'Označené: ' + n + ' ' + forms(n) : 'Označte príspevky na tejto strane.';
                if (all) {
                    all.checked = n > 0 && n === items.length;
                    all.indeterminate = n > 0 && n < items.length;
                }
            };

            all?.addEventListener('change', () => { items.forEach((i) => { i.checked = all.checked; }); refresh(); });
            items.forEach((i) => i.addEventListener('change', refresh));
            refresh();
        });
    </script>
@endpush
@endsection
