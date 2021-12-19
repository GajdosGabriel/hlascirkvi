<?php

namespace App\Http\Controllers\Api;

use App\Models\Updater;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UpdaterResource;
use App\Http\Resources\UpdaterCollection;

class UpdaterController extends Controller
{
     public function index()
     {
         $updaters = Updater::all();
        //  $villages = Village::where('fullname', 'like', $fullname . '%')->get();
 
         return UpdaterResource::collection($updaters);
     }
}
