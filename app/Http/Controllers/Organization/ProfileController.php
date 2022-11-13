<?php

namespace App\Http\Controllers\Organization;

use App\Models\Post;
use App\Models\Event;
use App\Models\Seminar;
use App\Models\Messenger;
use App\Models\Organization;
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
        return view('profiles.index');
    }



  
}
