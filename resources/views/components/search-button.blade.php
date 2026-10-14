{{-- Lupa vo vyhľadávacom poli — odošle formulár. Patrí do obalu .ar-searchbox
     hneď za <input type="search">. --}}
@props(['label' => 'Hľadať'])
<button type="submit" class="ar-searchbox__btn" title="{{ $label }}" aria-label="{{ $label }}">
    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
         stroke-linecap="round" class="h-4 w-4" aria-hidden="true">
        <circle cx="9" cy="9" r="6" />
        <path d="M13.5 13.5L17 17" />
    </svg>
</button>
