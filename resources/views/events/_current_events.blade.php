{{--  Currently events --}}
<div style="margin-bottom: 10px">
    <span style="font-weight: bold">Práve prebiehajúce akcie</span>
    @forelse($currentlyEvents as $event)
        <div>
            <div class="loader loader--style8" style="float: left" title="7">
                <i class="far fa-dot-circle" style="font-size: 80% ;color: silver; margin-right: .7rem;margin-top: .4rem"></i>
            </div>
            <a style="margin-left: 5px" href="{{ route('event.show', [ $event->id, $event->slug]) }}">{{ $event->title }}</a>
        </div>
    @empty
        Aktuálne neprebieha žiadna akcia
    @endforelse
</div>
