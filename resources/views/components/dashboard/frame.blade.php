@props(['label' => 'Váš profil', 'navigationLabel' => 'Profil', 'fold' => false])

<div {{ $attributes->class(['ar-dash mx-auto max-w-6xl px-4 py-8']) }}>
    <div class="grid gap-8 lg:grid-cols-12">
        <aside class="ar-dash__aside min-w-0 lg:col-span-3">
            @if ($fold)
                {{-- Na formulárových stránkach je menu na mobile zbalené — inak
                     formulár začínal až pod tromi riadkami odkazov. Od lg je
                     stále otvorené (skript nižšie), bez JS ostáva otvorené. --}}
                <details class="ar-dash__fold" open data-dash-fold>
                    <summary class="ar-kicker">
                        {{ $label }}
                        <i class="ph ph-caret-down ar-dash__fold-caret" aria-hidden="true"></i>
                    </summary>
                    <nav class="ar-dash__menu mt-3" aria-label="{{ $navigationLabel }}">
                        <x-navigation.aside-menu />
                    </nav>
                </details>
            @else
                <p class="ar-kicker mb-3">{{ $label }}</p>
                <nav class="ar-dash__menu" aria-label="{{ $navigationLabel }}">
                    <x-navigation.aside-menu />
                </nav>
            @endif
        </aside>
        <div class="min-w-0 lg:col-span-9">
            {{-- Oznamy pre správcov kanálov. Rám nesie nástenka aj celá
                 administrácia, takže jedno miesto pokryje obe. --}}
            <x-announcements placement="dashboard" />

            {{ $slot }}
        </div>
    </div>
</div>

@if ($fold)
    @once
        @push('scripts')
            <script nonce="{{ csp_nonce() }}">
                window.arReady(() => {
                    const fold = document.querySelector('[data-dash-fold]');
                    if (!fold) return;
                    const wide = window.matchMedia('(min-width: 1024px)');
                    const apply = () => { fold.open = wide.matches; };
                    wide.addEventListener('change', apply);
                    apply();
                });
            </script>
        @endpush
    @endonce
@endif
