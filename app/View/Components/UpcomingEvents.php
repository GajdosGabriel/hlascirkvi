<?php

namespace App\View\Components;

use App\Services\EventPortal\EventPortalClient;
use App\Services\EventPortal\RemoteEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Karta „Najbližšie akcie" do bočného panela: <x-upcoming-events />.
 *
 * Ukáže podujatia najbližšieho dňa, na ktorý nejaké pripadá — prvé tri
 * hneď, zvyšok po rozkliknutí. Pýta sa presne ten istý výpis ako prvá strana
 * /akcie, takže obe stránky zdieľajú jednu položku v cache a titulka kvôli
 * karte nevolá portál navyše.
 */
class UpcomingEvents extends Component
{
    /** Koľko podujatí je vidieť bez rozkliknutia. */
    public const VISIBLE = 3;

    /** @var Collection<int, RemoteEvent> */
    public Collection $events;

    public ?Carbon $day = null;

    public function __construct(EventPortalClient $portal)
    {
        $today = now()->toDateString();

        // Výpis `upcoming` vracia aj prebiehajúce viacdňové podujatia, ktoré
        // začali skôr — tie by „najbližší deň" posunuli do minulosti.
        $upcoming = collect($portal->events(['list' => 'upcoming'])->items())
            ->filter(fn (RemoteEvent $event) => $event->startAt() !== null && $event->dayKey() >= $today)
            ->sortBy(fn (RemoteEvent $event) => $event->startAt()->getTimestamp())
            ->values();

        $dayKey = $upcoming->first()?->dayKey();

        $this->events = $upcoming
            ->filter(fn (RemoteEvent $event) => $event->dayKey() === $dayKey)
            ->values();

        $this->day = $this->events->first()?->startAt();
    }

    /** "Dnes", "Zajtra" alebo "Sobota 10. októbra". */
    public function dayLabel(): string
    {
        if (! $this->day) {
            return '';
        }

        return match (true) {
            $this->day->isToday() => 'Dnes',
            $this->day->isTomorrow() => 'Zajtra',
            default => ucfirst($this->day->locale('sk')->isoFormat('dddd D. MMMM')),
        };
    }

    /** Bez podujatí (alebo pri výpadku portálu bez záložnej kópie) karta nie je. */
    public function shouldRender(): bool
    {
        return $this->events->isNotEmpty();
    }

    public function render()
    {
        return view('components.upcoming-events');
    }
}
