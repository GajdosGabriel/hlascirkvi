@props(['label' => 'Váš profil', 'navigationLabel' => 'Profil'])

<div {{ $attributes->class(['ar-dash mx-auto max-w-6xl px-4 py-8']) }}>
    <div class="grid gap-8 lg:grid-cols-12">
        <aside class="min-w-0 lg:col-span-3">
            <p class="ar-kicker mb-3">{{ $label }}</p>
            <nav class="ar-dash__menu" aria-label="{{ $navigationLabel }}">
                <x-navigation.aside-menu />
            </nav>
        </aside>
        <div class="min-w-0 lg:col-span-9">
            {{-- Oznamy pre správcov kanálov. Rám nesie nástenka aj celá
                 administrácia, takže jedno miesto pokryje obe. --}}
            <x-announcements placement="dashboard" />

            {{ $slot }}
        </div>
    </div>
</div>
