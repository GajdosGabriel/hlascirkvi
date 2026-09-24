<?php

namespace App\View\Components;

use App\Enums\CanalType;
use App\Services\FrontList\FrontList;
use App\Services\FrontList\FrontListItem;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Karta predného zoznamu do bočného panela:
 * <x-front-list-card type="personal" /> alebo <x-front-list-card type="organization" />.
 *
 * Zoznam plnil view composer na `canals.list-users`, ktorý si zavolal
 * repozitár a k nemu ešte prilepil orderBy — kto kartu vykresľoval, netušil,
 * odkiaľ sa dáta berú. Teraz si ich pýta sama, z jednej služby.
 */
class FrontListCard extends Component
{
    public CanalType $type;

    public string $title;

    /** @var Collection<int, FrontListItem> */
    public Collection $canals;

    public int $total;

    public function __construct(FrontList $frontList, string $type = 'personal', ?string $title = null)
    {
        $this->type   = CanalType::from($type);
        $this->title  = $title ?? $this->type->cardTitle();
        $this->canals = $frontList->forCard($this->type);
        $this->total  = $frontList->total($this->type);
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
