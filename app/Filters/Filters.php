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
    protected function likePattern(?string $value): string
    {
        return '%' . addcslashes((string) $value, '%_\\') . '%';
    }

    public function getFilters()
    {
        return array_filter($this->request->only($this->filters));
    }


}