<?php

namespace App\Http\Controllers\Api;

use App\Enums\Denomination;
use App\Http\Controllers\Controller;

/**
 * Číselník cirkevného zaradenia pre formulár kanála vo Vue
 * (resources/js/organizations/NewOrganization.vue).
 *
 * Predtým si formulár sťahoval celú tabuľku updaterov cez /api/updaters
 * a sám si z nej filtroval riadky s typom `denomination`. Hodnoty aj popisky
 * dnes drží App\Enums\Denomination, takže ich netreba mať druhýkrát v JS.
 */
class DenominationController extends Controller
{
    public function index()
    {
        return collect(Denomination::options())
            ->map(fn (Denomination $denomination) => [
                'value' => $denomination->value,
                'label' => $denomination->label(),
            ]);
    }
}
