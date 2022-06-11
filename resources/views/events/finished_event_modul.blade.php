<div style="margin-bottom: 3rem">
    <h5>
        @if (Request::has('finished'))
            <a href="{{ route('akcie.index') }}">
                <i style="color: #3b32b3" class="fas fa-check"></i>
                Ukončené akcie
            </a>
        @else
            <a href="{{ route('akcie.index') . '?finished=true' }}">

                Ukončené akcie
            </a>
        @endif

    </h5>
    {{-- {{ $events.count() }} --}}
</div>
