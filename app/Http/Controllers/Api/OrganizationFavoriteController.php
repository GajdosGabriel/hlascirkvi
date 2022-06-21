<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Repositories\Eloquent\EloquentUserRepository;

class OrganizationFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('update');
    }

    public function store(Organization $organization, Request $request)
    {
        if ($request->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($request);
        }

        return new FavoriteResource($organization->favorite());
    }

}
