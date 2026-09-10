<?php

namespace App\View\Components\Canal;

use App\Models\Canal;
use Illuminate\View\Component;

/**
 * Súhrn článkov kanála na jeho detaile v nástenke.
 *
 * Kanál sa predtým bral z auth()->user()->organization, takže detail
 * ľubovoľného kanála ukazoval čísla aktívneho kanála užívateľa.
 */
class Statistic extends Component
{
    public function __construct(public Canal $canal)
    {
    }

    public function render()
    {
        return view('components.canal.statistic');
    }
}
