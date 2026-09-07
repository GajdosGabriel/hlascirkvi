<section class="card">
    <header class="card_header">Dnešný plán zverejňovania</header>
    <div class="card_body">

        {{-- Časy počíta App\Services\Buffer\PublishPlan zo semienka podľa
             dátumu, takže sú počas dňa stále rovnaké. --}}
        <p class="mb-3">
            Na zverejnenie čaká {{ $status['waiting'] }} príspevkov s dostupným videom,
            dnes ich má vyjsť {{ count($status['plan']) }}
            (zatiaľ {{ $status['done_today'] }}{{ $status['archive_today'] ? ', z toho ' . $status['archive_today'] . ' zo starého frontu' : '' }}).
            Import prináša {{ number_format($status['inflow'], 1, ',', ' ') }} príspevkov denne.
        </p>

        <ul class="flex flex-wrap gap-2">
            @forelse ($status['plan'] as $index => $slot)
                <li @class([
                        'px-2 py-1 rounded text-sm',
                        'bg-green-100 text-green-800' => $index < $status['done_today'],
                        'bg-blue-100 text-blue-800 font-semibold' => $index === $status['done_today'],
                        'bg-gray-100 text-gray-500' => $index > $status['done_today'],
                    ])>
                    {{ $slot->format('H:i') }}
                </li>
            @empty
                <li>Na dnes nie je naplánované nič.</li>
            @endforelse
        </ul>

    </div>
</section>
