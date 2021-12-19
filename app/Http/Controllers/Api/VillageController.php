<?php

namespace App\Http\Controllers\Api;

use App\Models\Village;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VillageController extends Controller
{
     public function index()
     {
         $villages = Village::take(10)->get();
        //  $villages = Village::where('fullname', 'like', $fullname . '%')->get();
 
         return response()
             ->json($villages);
     }

     // Hľadanie podla názvu obce
     public function store(Request $request)
     {
         $villages = Village::where('fullname', 'like', $request->input('name') . '%')->take(12)->get();
 
         return response()
             ->json($villages);
     }
}
