<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotificationCollection;
use Illuminate\Notifications\Notification;

class NotificationController extends Controller
{
  public function update($notification)
  {
     $notify =  Auth::user()->notifications()->findOrFail($notification)->markAsRead();

     return new NotificationResource($notify);
  }
}
