<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Canal;
use App\Services\FrontList\FrontList;
use Illuminate\Http\Request;

/**
 * Správa predného zoznamu („Kresťanské osobnosti" na úvodnej stránke).
 *
 * Do 9/2026 sa zoznam spravoval cez /admin/updater/14/canal, kde bol formulár
 * na pridanie kanála zakomentovaný — pridať sa dalo len zaškrtávadlom
 * v editácii konkrétneho kanála a poradie sa nedalo nastaviť vôbec.
 *
 * Celá skupina `admin.` beží za middleware `checkSuperAdmin` (routes/web.php).
 */
class FrontListController extends Controller
{
    public function __construct(protected FrontList $frontList)
    {
    }

    public function index(Request $request)
    {
        $hladane = trim((string) $request->input('hladat'));

        return view('admins.frontlist.index', [
            'canals'    => $this->frontList->forAdmin(),
            'hladane'   => $hladane,
            'najdene'   => $hladane === '' ? collect() : $this->search($hladane),
        ]);
    }

    public function store(Request $request)
    {
        $canal = Canal::findOrFail($request->input('canal'));

        $this->frontList->add($canal);

        return back()->with('flash', 'Kanál „' . $canal->title . '“ je v prednom zozname.');
    }

    public function destroy(Canal $canal)
    {
        $this->frontList->remove($canal);

        return back()->with('flash', 'Kanál „' . $canal->title . '“ už v prednom zozname nie je.');
    }

    /**
     * Posun o jedno miesto hore alebo dole. Poradie sa inak nastaviť nedá —
     * zoznam má rádovo desiatky riadkov a ťahanie myšou by sem prinieslo
     * skript, ktorý by tu bol jediný svojho druhu.
     */
    public function move(Request $request, Canal $canal)
    {
        $this->frontList->move($canal, $request->input('smer') === 'hore' ? -1 : 1);

        return back();
    }

    /**
     * Kanály, ktoré sa dajú do zoznamu pridať — teda tie, čo v ňom ešte nie sú.
     * Výber cez hľadanie, nie rozbaľovací zoznam: kanálov je vyše päťsto.
     */
    protected function search(string $hladane)
    {
        return Canal::whereNull('front_listed_at')
            ->where('title', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $hladane) . '%')
            ->without('favorites')
            ->orderBy('title')
            ->limit(20)
            ->get(['id', 'title', 'published']);
    }
}
