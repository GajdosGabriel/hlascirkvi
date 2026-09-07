<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Prayer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PrayerResource;
use App\Notifications\Prayer\NewPrayer;
use App\Http\Requests\SavePrayerRequest;
use App\Http\Resources\PrayerCollection;
use Illuminate\Support\Facades\Notification;
use App\Repositories\Eloquent\EloquentUserRepository;

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
            fn ($query) => $query->with('organization')
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
        if ($request->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($request);
        }

        // `email` je pri neprihlásenom autorovi len vstup pre založenie účtu
        // vyššie — v tabuľke `prayers` taký stĺpec nie je.
        $prayer = auth()->user()->organization->prayers()->create(
            collect($request->validated())->except('email')->all()
        );

        Notification::send(User::role('admin')->get(), new NewPrayer($prayer));
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
