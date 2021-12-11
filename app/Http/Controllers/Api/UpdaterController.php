<?php

namespace App\Http\Controllers\Api;

use App\Updater;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdaterController extends Controller
{
     public function index()
     {
         $updaters = Updater::all();
        //  $villages = Village::where('fullname', 'like', $fullname . '%')->get();
 
         return response()
             ->json($updaters);
     }
}
