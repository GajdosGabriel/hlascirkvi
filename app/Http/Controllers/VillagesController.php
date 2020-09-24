<?php

namespace App\Http\Controllers;

use App\Village;
use Illuminate\Http\Request;

class VillagesController extends Controller
{
    // For event form by Vue.js
    public function index($fullname){
        $villages = Village::where('fullname', 'like' , $fullname.'%' )->get();

        return response()
            ->json($villages);
    }
}
