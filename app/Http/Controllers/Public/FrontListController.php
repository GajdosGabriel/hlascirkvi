<?php

namespace App\Http\Controllers\Public;

use App\Enums\CanalType;
use App\Http\Controllers\Controller;
use App\Services\FrontList\FrontList;

/**
 * Celý predný zoznam kanálov — osobnosti aj cirkvi a spoločenstvá, abecedne.
 * Karty v bočnom paneli ukazujú len pár kanálov (config frontlist.card_limit)
 * a zvyšok je za odkazom sem.
 */
class FrontListController extends Controller
{
    public function index(FrontList $frontList)
    {
        return view('frontlist.index', [
            'groups' => collect(CanalType::cases())
                ->map(fn (CanalType $type) => ['type' => $type, 'canals' => $frontList->all($type)])
                ->filter(fn (array $group) => $group['canals']->isNotEmpty()),
        ]);
    }
}
