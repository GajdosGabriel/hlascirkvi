<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CanalRequest;
use App\Http\Resources\CanalResource;
use App\Http\Controllers\Canal\CanalController;
use Illuminate\Validation\ValidationException;

class UserCanalController extends Controller
{
    public function store(User $user, CanalRequest $request)
    {
        // Kanál sa vždy zakladá prihlásenému; cudzie {user} v adrese by ho
        // ticho vytvorilo pod iným účtom, než aký je v adrese.
        abort_unless($user->is($request->user()), 403);

        // Rovnaký strop ako v dashboarde (Canal\CanalController::store).
        if (! $request->user()->can('admin')
            && $request->user()->canals()->count() >= CanalController::MAX_CANALS) {
            throw ValidationException::withMessages([
                'title' => 'Môžete mať najviac ' . CanalController::MAX_CANALS . ' kanály. Ďalší kanál vám môže založiť administrátor.',
            ]);
        }

        $canal = $request->save();
        return new CanalResource($canal);
    }
}
