<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CanalType;
use App\Http\Controllers\Controller;
use App\Models\Canal;
use App\Services\FrontList\FrontList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Správa predného zoznamu (karty „Kresťanské osobnosti" a „Cirkvi
 * a spoločenstvá" v bočnom paneli).
 *
 * Správca určuje, kto v zozname je a akého je typu. Poradie na karte je
 * automatické podľa záujmu návštevníkov — ručné posúvanie zaniklo 9/2026.
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
        $canals  = $this->frontList->forAdmin();

        return view('admins.frontlist.index', [
            'canals'  => $canals,
            'cardIds' => $this->frontList->cardIds($canals),
            'types'   => CanalType::options(),
            'hladane' => $hladane,
            'najdene' => $hladane === '' ? collect() : $this->search($hladane),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'canal' => ['required', 'integer'],
            'type'  => ['required', Rule::enum(CanalType::class)],
        ]);

        $canal = Canal::findOrFail($data['canal']);

        $this->frontList->add($canal, CanalType::from($data['type']));

        return back()->with('flash', 'Kanál „' . $canal->title . '“ je v prednom zozname.');
    }

    public function updateType(Request $request, Canal $canal)
    {
        $data = $request->validate([
            'type' => ['required', Rule::enum(CanalType::class)],
        ]);

        $this->frontList->setType($canal, CanalType::from($data['type']));

        return back()->with('flash', 'Kanál „' . $canal->title . '“ je teraz: ' . $canal->type->label() . '.');
    }

    public function destroy(Canal $canal)
    {
        $this->frontList->remove($canal);

        return back()->with('flash', 'Kanál „' . $canal->title . '“ už v prednom zozname nie je.');
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
            ->get(['id', 'title', 'published', 'type']);
    }
}
