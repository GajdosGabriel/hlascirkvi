<div style="margin-bottom: 3rem">
    <h5>
        @if (Request::is('akcie'))
            <a href="{{ route('event.finished') }}">
                Ukončené akcie
            </a>
        @else
            <a href="{{ route('akcie.index') }}">
                <i style="color: #3b32b3" class="fas fa-check"></i>
                Aktívne akcie
            </a>
        @endif

    </h5>
    {{-- {{ $events.count() }} --}}
</div>
