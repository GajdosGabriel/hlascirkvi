<?php

namespace App\Http\Controllers\Api;

use App\Models\Canal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CanalResource;

class CanalController extends Controller
{
    public function show(Canal $organization)
    {
        return new CanalResource($organization);
    }
}
