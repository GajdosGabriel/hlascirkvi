<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index()
    {
      return NotificationCollection::collection(Auth::user()->notifications()->take(10)->get() );
    }

    public function update($notification)
    {
       $notify =  Auth::user()->notifications()->findOrFail($notification)->markAsRead();

       return new NotificationResource($notify);
    }
}
