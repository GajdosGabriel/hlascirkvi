<?php

namespace App\Http\Controllers\Api;

use App\Village;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VillageController extends Controller
{
     // For event form by Vue.js
     public function index()
     {
         $villages = Village::take(10)->get();
        //  $villages = Village::where('fullname', 'like', $fullname . '%')->get();
 
         return response()
             ->json($villages);
     }
}
