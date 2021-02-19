{{--  Currently events --}}
<div class="text-sm">
    <span class="font-semibold">Práve prebiehajúce akcie</span>
    @forelse($currentlyEvents as $event)
        <div>
            <i class="far fa-dot-circle"
               style="font-size: 80% ;color: silver; margin-right: .7rem;margin-top: .4rem"></i>

            <a style="margin-left: 5px"
               href="{{ route('event.show', [ $event->id, $event->slug]) }}">{{ $event->title }}</a>
        </div>
    @empty
        Aktuálne neprebieha žiadna akcia
    @endforelse
</div>
