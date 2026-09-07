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

        $prayer = auth()->user()->organization->prayers()->create($request->all());

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
    public function update(Prayer $modlitby, SavePrayerRequest $request)
    {
        $modlitby->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prayer $prayer)
    {
        $prayer->delete();
    }
}
