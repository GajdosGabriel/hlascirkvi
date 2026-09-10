<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Canal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CanalRequest;
use App\Http\Resources\CanalResource;

class UserCanalController extends Controller
{
    public function store(User $user, CanalRequest $request)
    {
        $organization = $request->save();
        return new CanalResource($organization);
    }
}
