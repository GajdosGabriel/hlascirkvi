<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 22.09.2018
 * Time: 7:35
 */

namespace App\Filters;


use Illuminate\Http\Request;

abstract class Filters
{

    protected const MAX_SEARCH_LENGTH = 100;

    protected $request, $builder;
    protected $filters = [];


    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function apply($builder)
    {
        $this->builder = $builder;

        foreach($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        // Filtre ako mostVisited či trends si radenie určujú samy. Bezpodmienečné
        // latest() im pridávalo created_at ako druhý stĺpec do ORDER BY a tým
        // znemožnilo použiť index — výpis potom končil filesortom nad celou
        // tabuľkou.
        if (empty($this->builder->getQuery()->orders)) {
            $this->builder->latest();
        }

        return $this->builder;
    }

    /**
     * Vzor pre LIKE z používateľského vstupu.
     *
     * `%` a `_` sú v LIKE zástupné znaky. Neošetrené to znamenalo, že hľadanie
     * jediného znaku `%` prešlo celú tabuľku a vrátilo všetko — nad 40-tisíc
     * príspevkami je to plný sken na jedno kliknutie.
     */
    protected function likePattern(mixed $value): string
    {
        $value = is_scalar($value) ? (string) $value : '';
        $value = mb_substr(trim($value), 0, self::MAX_SEARCH_LENGTH);

        return '%' . addcslashes($value, '%_\\') . '%';
    }

    public function getFilters()
    {
        // Pole v parametri (`?search[]=a`) nie je platný vstup žiadneho filtra.
        return array_filter(
            $this->request->only($this->filters),
            fn ($value) => ! is_array($value)
        );
    }


}