<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Services\Extractor\ExtractTkkbs;
use App\Services\Extractor\ExtractVyveska;
use App\Services\Extractor\ExtractEcav;

class EventServiceController extends Controller
{
    public function newReolad(Event $event) {

        if($event->organization_id == 101 ) {
            $event = (new ExtractTkkbs())->parseEvent($event->orginal_source, $event);
        }

        if($event->organization_id == 271 ) {
            $event = (new ExtractVyveska())->parseEvent($event->orginal_source, $event);
        }

        if($event->organization_id == 102 ) {
            $event = (new ExtractEcav())->parseEvent($event->orginal_source, $event);
        }

        return back();

    }
}
