<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Services\Extractor\ExtractTkkbs;

class EventServiceController extends Controller
{
    public function newReolad(Event $event) {

        $event = (new ExtractTkkbs())->parseEvent($event->orginal_source, $event);

        return back();

       dd($event);

    }
}
