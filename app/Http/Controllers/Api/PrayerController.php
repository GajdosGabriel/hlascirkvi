<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Prayer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PrayerResource;
use App\Services\UserActivation;
use App\Http\Requests\SavePrayerRequest;
use App\Http\Resources\PrayerCollection;
use App\Models\PendingPrayer;
use Illuminate\Validation\ValidationException;

class PrayerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PrayerResource::collection(
            $this->query()->orderBy('created_at', 'desc')->paginate(15)
        );
    }

    public function fulfilled()
    {
        return PrayerResource::collection(
            $this->query()->whereNotNull('fulfilled_at')->orderBy('fulfilled_at', 'desc')->paginate(15)
        );
    }

    /**
     * Názov kanála vidí vo výpise len superadmin, takže väzbu naťahujeme len
     * preňho — ostatným by to bol dopyt navyše na každej stránke.
     */
    protected function query()
    {
        return Prayer::query()->when(
            auth()->check() && auth()->user()->hasRole('superadmin'),
            fn ($query) => $query->with('canal')
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SavePrayerRequest $request)
    {
        $data = collect($request->validated())->except('email')->all();
        $user = auth()->user() ?? User::whereEmail($request->email)->first();

        // Adresa patrí overenému účtu — modlitba mu pribudne, akoby ju pridal
        // prihlásený. Návštevníka to však do účtu neprihlási.
        if ($user?->hasVerifiedEmail()) {
            abort_if($user->banned(), 403, $user->accountAccessMessage());

            app(UserActivation::class)->publishPrayer($user, $data);

            return response()->json(['pending' => false], 201);
        }

        // Bez overeného účtu sa nič nezakladá — modlitba čaká v čakárni, kým
        // autor nepotvrdí adresu (Public\PrayerController::confirm). Platí aj
        // pre prihláseného so starším neovereným účtom.
        $email = $user?->email ?? $request->email;

        if (PendingPrayer::forEmail($email)->count() >= PendingPrayer::MAX_PER_EMAIL) {
            throw ValidationException::withMessages([
                'email' => 'Na túto adresu už čakajú modlitby na potvrdenie. Skontrolujte, prosím, e-mail.',
            ]);
        }

        PendingPrayer::create($data + [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ])->sendConfirmation();

        return response()->json(['pending' => true], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Parameter sa volal $modlitby, ale routa má {prayer} — implicitná väzba
     * sa preto nenaviazala a kontajner dodal prázdny model, takže úprava
     * modlitby ticho nerobila nič.
     */
    public function update(Prayer $prayer, SavePrayerRequest $request)
    {
        $this->authorize('update', $prayer);

        $prayer->update($request->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prayer $prayer)
    {
        $this->authorize('delete', $prayer);

        $prayer->delete();
    }
}
