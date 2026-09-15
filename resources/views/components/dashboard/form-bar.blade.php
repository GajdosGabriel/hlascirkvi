@props([
    'cancel' => null,
    'submit' => 'Uložiť zmeny',
    'note' => 'Zmeny sa prejavia hneď po uložení.',
])

{{-- Lišta s tlačidlami, ktorá sa drží spodku okna (.ar-form__bar
     v partials/dashboard-system). Pri dlhom formulári by „Uložiť" bolo
     až za poslednou sekciou. Musí stáť vnútri <form>. --}}
<div {{ $attributes->class('ar-form__bar') }} data-form-bar>
    <span class="ar-form__bar-note" data-form-bar-note data-saved="{{ $note }}">{{ $note }}</span>
    @if ($cancel)
        <a href="{{ $cancel }}" class="ar-btn ar-btn--quiet">Zrušiť</a>
    @endif
    <button type="submit" class="ar-btn ar-btn--accent"><i class="fas fa-check"></i> {{ $submit }}</button>
</div>

@once
    @push('scripts')
        <script>
            // Po prvej úprave poľa lišta upozorní, že zmeny ešte nie sú uložené.
            // Počúva sa na document: Vue obsah #app pri pripojení prekreslí,
            // takže udalosti naviazané priamo na formulár by sa stratili.
            const markFormDirty = (e) => {
                const bar = e.target.closest?.('form')?.querySelector('[data-form-bar]');
                if (!bar || bar.classList.contains('ar-form__bar--dirty')) return;
                bar.classList.add('ar-form__bar--dirty');
                const note = bar.querySelector('[data-form-bar-note]');
                if (note) note.textContent = 'Máte neuložené zmeny.';
            };
            document.addEventListener('input', markFormDirty);
            document.addEventListener('change', markFormDirty);
        </script>
    @endpush
@endonce
