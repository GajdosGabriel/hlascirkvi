<?php

namespace App\Http\Controllers\Public;

use App\Enums\CanalKind;
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
            'groups' => collect(CanalKind::cases())
                ->map(fn (CanalKind $kind) => ['kind' => $kind, 'canals' => $frontList->all($kind)])
                ->filter(fn (array $group) => $group['canals']->isNotEmpty()),
        ]);
    }
}
