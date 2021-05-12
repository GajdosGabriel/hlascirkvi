<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
      return Auth::user()->notifications()->take(10)->get();
    }

    public function update($notification)
    {
        Auth::user()->notifications()->findOrFail($notification)->markAsRead();
    }
}
