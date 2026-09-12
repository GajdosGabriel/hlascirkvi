<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FrontList\FrontList;

/**
 * Celý predný zoznam kanálov. Karta v bočnom paneli ukazuje len prvých pár
 * (config frontlist.card_limit) a zvyšok je za odkazom sem — inak by panel
 * s každým pridaným kanálom rástol.
 */
class FrontListController extends Controller
{
    public function index(FrontList $frontList)
    {
        return view('frontlist.index', ['canals' => $frontList->all()]);
    }
}
