<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }

    public function index()
    {
        return view('profile.index');
    }



  
}
