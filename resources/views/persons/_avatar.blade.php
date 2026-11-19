{{-- Obrázok osoby: avatar jej vlastného kanála, inak iniciály. --}}
@php
    $avatarCanal = $person->relationLoaded('canal') ? $person->canal : null;
    $initials = collect(preg_split('/\s+/u', trim($person->name)))
        ->filter(fn ($part) => preg_match('/^\p{Lu}/u', $part))
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');
@endphp

@if ($avatarCanal && $avatarCanal->avatar)
    <img src="{{ \App\Support\MediaUrl::canalAvatar($avatarCanal->id, $avatarCanal->avatar) }}" alt=""
         class="{{ $size }} shrink-0 rounded-full object-cover" loading="lazy">
@else
    <span class="{{ $size }} inline-flex shrink-0 items-center justify-center rounded-full font-bold text-(--ar-accent)"
          style="background: rgba(var(--ar-accent-rgb), .1)" aria-hidden="true">{{ $initials ?: '?' }}</span>
@endif
