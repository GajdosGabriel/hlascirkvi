{{-- Vlákno podpory. $ticket s načítanými messages.author, $staffView = či sa
     pozerá správca (vidí mená správcov; používateľ vidí len „Podpora"). --}}
<ol class="space-y-4">
    @foreach ($ticket->messages as $message)
        @php
            $author = $message->is_staff && ! $staffView
                ? 'Podpora HlasCirkvi.sk'
                : ($message->author?->adminName() ?? '—');
        @endphp
        {{-- Vlastné správy (svojej strany) vpravo, ako v chate. --}}
        <li @class(['flex', 'justify-end' => $message->is_staff === $staffView])>
            <div @class([
                'max-w-[85%] rounded-lg border p-3 text-sm',
                'border-indigo-200 bg-indigo-50' => $message->is_staff,
                'border-gray-200 bg-white' => ! $message->is_staff,
            ])>
                <p class="mb-1 flex flex-wrap items-center gap-x-2 text-xs text-[color:var(--ar-muted)]">
                    <span class="font-semibold text-[color:var(--ar-ink)]">{{ $author }}</span>
                    @if ($message->is_staff)
                        <span>· podpora</span>
                    @endif
                    <time datetime="{{ $message->created_at?->toIso8601String() }}" title="{{ $message->created_at?->format('j. n. Y H:i') }}">
                        · {{ $message->created_at?->format('j. n. Y H:i') }}
                    </time>
                </p>
                <div class="whitespace-pre-line break-words text-[color:var(--ar-ink)]">{{ $message->body }}</div>
            </div>
        </li>
    @endforeach
</ol>
