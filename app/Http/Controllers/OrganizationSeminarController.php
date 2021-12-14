<?php

namespace App\Http\Controllers;


use App\Organization;
use Illuminate\Http\Request;

class OrganizationSeminarController extends Controller
{
    public function index(Organization $organization)
    {
        $seminars = $organization->seminars()->withCount('posts')
                    ->orderBy('created_at', 'desc')->get();

        return view('profiles.seminars.index', ['seminars' => $seminars]);
    }
}
