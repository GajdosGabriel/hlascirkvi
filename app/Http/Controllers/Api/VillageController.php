<?php

namespace App\Http\Controllers\Api;

use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Http\Resources\VillageResource;

class VillageController extends Controller
{
    /** Najviac návrhov v jednej odpovedi. */
    private const LIMIT = 12;

    /** Najdlhší hľadaný text; dlhší názov obce neexistuje. */
    private const MAX_LENGTH = 50;

    public function index()
    {
        $villages = Village::take(10)->get();


        return VillageResource::collection($villages)
            ->response()
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function show($villages)
    {
        return VillageResource::collection($this->search($villages));
    }

    // Hľadanie podla názvu obce
    public function store(Request $request)
    {
        // Vracia sa kolekcia, takže VillageResource::collection — `new VillageResource`
        // obalil celú kolekciu do jedného resource a klient dostal iný tvar.
        return VillageResource::collection($this->search($request->input('name')));
    }

    /**
     * Návrhy obcí podľa začiatku názvu. Hľadá sa pri každom stlačení klávesu,
     * preto sa výsledok cachuje podľa hľadaného textu. `%` a `_` sa escapujú,
     * inak by `%` vrátil všetky obce naraz.
     */
    private function search(mixed $text)
    {
        $text = is_scalar($text) ? mb_substr(trim((string) $text), 0, self::MAX_LENGTH) : '';

        return Cache::remember(
            'villages.search.' . md5(mb_strtolower($text)),
            now()->addHours(24),
            fn () => Village::where('fullname', 'like', addcslashes($text, '%_\\') . '%')
                ->take(self::LIMIT)
                ->get()
        );
    }
}
