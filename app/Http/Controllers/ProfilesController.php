<?php

namespace App\Http\Controllers;

use App\Post;
use App\Event;
use App\Seminar;
use App\Messenger;
use App\Organization;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentOrganizationRepository;

class ProfilesController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }

    public function home()
    {
        return view('profiles.home');
    }



  
}
