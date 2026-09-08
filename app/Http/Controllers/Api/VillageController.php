<?php

namespace App\Http\Controllers\Api;

use App\Models\Village;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\VillageResource;

class VillageController extends Controller
{
    public function index()
    {
        $villages = Village::take(10)->get();


        return VillageResource::collection($villages);
    }

    public function show($villages)
    {
        $villages = Village::where('fullname', 'like', $villages . '%')->get();

        return VillageResource::collection($villages);
    }

    // Hľadanie podla názvu obce
    public function store(Request $request)
    {
        // Vracia sa kolekcia, takže VillageResource::collection — `new VillageResource`
        // obalil celú kolekciu do jedného resource a klient dostal iný tvar.
        $villages = Village::where('fullname', 'like', $request->input('name') . '%')->take(12)->get();

        return VillageResource::collection($villages);
    }
}
