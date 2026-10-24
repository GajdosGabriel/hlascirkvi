<?php

namespace App\View\Components\Navigation;

use App\Enums\SeasonalLogo;
use App\Services\Liturgy\SeasonalLogoPicker;
use Illuminate\View\Component;

/**
 * Logo v hlavnej navigácii: <x-navigation.logo />.
 *
 * Variant sa mení podľa ročného obdobia a liturgického roka. Lokálne sa dá
 * ľubovoľný pozrieť cez ?logo=snowman (hodnoty App\Enums\SeasonalLogo).
 */
class Logo extends Component
{
    public SeasonalLogo $logo;

    /** Koľko sviečok horí na adventnom venci. */
    public int $candles;

    public function __construct(SeasonalLogoPicker $picker)
    {
        ['logo' => $this->logo, 'candles' => $this->candles] = $picker->for(now());

        $preview = app()->isLocal() ? SeasonalLogo::tryFrom((string) request()->query('logo')) : null;

        if ($preview) {
            $this->logo = $preview;
            $this->candles = $preview === SeasonalLogo::Advent ? 4 : 0;
        }
    }

    public function render()
    {
        return view('components.navigation.logo');
    }
}
