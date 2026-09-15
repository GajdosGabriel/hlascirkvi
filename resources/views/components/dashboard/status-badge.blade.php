{{--
    Farebný štítok stavu. Použitie:
    <x-dashboard.status-badge :badge="$user->accountBadge()" />
    alebo <x-dashboard.status-badge label="Aktívny" tone="green" title="…" />

    Štýly sú inline, aby štítok nezávisel od toho, či je Tailwind prebuildovaný.
--}}
@props(['badge' => null, 'label' => null, 'tone' => 'gray', 'title' => null])

@php
    $label = $badge['label'] ?? $label;
    $tone = $badge['tone'] ?? $tone;
    $title = $badge['title'] ?? $title;

    // [pozadie, text, okraj, bodka]
    $palette = [
        'green' => ['#dcfce7', '#166534', '#86efac', '#22c55e'],
        'amber' => ['#fef3c7', '#92400e', '#fcd34d', '#f59e0b'],
        'red' => ['#fee2e2', '#991b1b', '#fca5a5', '#ef4444'],
        'gray' => ['#f3f4f6', '#374151', '#d1d5db', '#9ca3af'],
    ];
    [$bg, $fg, $border, $dot] = $palette[$tone] ?? $palette['gray'];
@endphp

<span {{ $attributes }}
      style="display:inline-flex; align-items:center; justify-content:center; gap:.375rem; padding:.1875rem .625rem; border:1px solid {{ $border }}; border-radius:9999px; background:{{ $bg }}; color:{{ $fg }}; font-size:.75rem; font-weight:600; line-height:1; white-space:nowrap; vertical-align:middle;"
      @if ($title) title="{{ $title }}" @endif>
    <span style="width:.375rem; height:.375rem; flex:none; border-radius:9999px; background:{{ $dot }};" aria-hidden="true"></span>
    <span>{{ $label }}</span>
</span>
