<?php

namespace App\Http\Controllers\Api;

use App\Support\HumanCheck;
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
        return Prayer::published()->when(
            auth()->check() && auth()->user()->hasRole('superadmin'),
            fn ($query) => $query->with('canal')
        );
    }

    /**
     * Prihlásený s overeným účtom zverejňuje hneď. Neprihlásený vždy cez
     * potvrdenie e-mailu — aj keď adresa patrí overenému účtu, inak by
     * ktokoľvek so znalosťou cudzieho e-mailu písal za iného. Hosť dostane
     * vždy rovnakú odpoveď, aby sa nedalo zisťovať, ktoré adresy sú registrované.
     */
    public function store(SavePrayerRequest $request)
    {
        $data = collect($request->validated())->except(['email', HumanCheck::STAMP])->all();
        $user = auth()->user();

        if ($user?->hasVerifiedEmail()) {
            abort_if($user->banned(), 403, $user->accountAccessMessage());

            $prayer = app(UserActivation::class)->publishPrayer($user, $data);

            // Klient vloží novú prosbu na začiatok zoznamu bez opätovného načítania.
            return response()->json([
                'pending' => false,
                'prayer' => (new PrayerResource($prayer->refresh()))->resolve(),
            ], 201);
        }

        // Nič sa nezakladá — modlitba čaká v čakárni, kým autor nepotvrdí
        // adresu (Public\PrayerController::confirm). Platí aj pre prihláseného
        // so starším neovereným účtom.
        $email = $user?->email ?? $request->email;

        if (PendingPrayer::limitReached($email)) {
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
     * Parameter sa volal $modlitby, ale routa má {prayer} — implicitná väzba
     * sa preto nenaviazala a kontajner dodal prázdny model, takže úprava
     * modlitby ticho nerobila nič.
     */
    public function update(Prayer $prayer, SavePrayerRequest $request)
    {
        $this->authorize('update', $prayer);

        $prayer->update($request->validated());

        return new PrayerResource($prayer);
    }

    public function destroy(Prayer $prayer)
    {
        $this->authorize('delete', $prayer);

        $prayer->delete();

        return response()->noContent();
    }
}
