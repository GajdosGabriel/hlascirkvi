<?php

namespace App\View\Components;

use App\Services\FrontList\FrontList;
use App\Services\FrontList\FrontListItem;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Karta predného zoznamu do bočného panela: <x-front-list-card />.
 *
 * Zoznam plnil view composer na `canals.list-users`, ktorý si zavolal
 * repozitár a k nemu ešte prilepil orderBy — kto kartu vykresľoval, netušil,
 * odkiaľ sa dáta berú. Teraz si ich pýta sama, z jednej služby.
 */
class FrontListCard extends Component
{
    /** @var Collection<int, FrontListItem> */
    public Collection $canals;

    public int $total;

    public function __construct(FrontList $frontList, public string $title = 'Kresťanské osobnosti')
    {
        $this->canals = $frontList->forCard();
        $this->total  = $frontList->total();
    }

    /** Prázdnu kartu netreba vykresľovať — bočný panel bez nej vyzerá rovnako. */
    public function shouldRender(): bool
    {
        return $this->canals->isNotEmpty();
    }

    public function render()
    {
        return view('components.front-list-card');
    }
}
