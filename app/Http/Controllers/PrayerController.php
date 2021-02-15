<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePrayerRequest;
use App\Prayer;
use App\Repository\User\UserRegistration;
use Illuminate\Http\Request;

class PrayerController extends Controller
{
    public function index()
    {

        return view('prayer.index');
    }

    public function create()
    {
        return Prayer::latest()->paginate(7);
    }

    public function store(SavePrayerRequest $request){

        if($request->email) {
          (new UserRegistration)->commentCheckIfUserAccountExist($request);
        }

       auth()->user()->prayers()->create($request->all() );

    }
}
