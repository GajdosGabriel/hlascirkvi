{{--  Currently events --}}
<div class="text-sm text-gray-700 p-2">
    <h4 class="font-semibold pb-2">Práve prebiehajúce akcie</h4>
    @forelse($currentlyEvents as $event)
        <div class="flex mb-2">
            <i class="far fa-dot-circle"
               style="font-size: 80% ;color: silver; margin-right: .7rem;margin-top: .4rem"></i>

            <a class="hover:text-gray-900" href="{{ route('event.show', [ $event->id, $event->slug]) }}">{{ $event->title }}</a>
        </div>
    @empty
        Aktuálne neprebieha žiadna akcia
    @endforelse
</div>
