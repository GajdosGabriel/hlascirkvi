<?php

namespace App\Http\Controllers\Seminars;

use App\Models\Seminar;
use App\Models\Canal;
use Illuminate\Http\Request;
use App\Services\VideoUpload;
use App\Http\Controllers\Controller;
use App\Services\VideoUploadSeminars;

class SeminarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('index', 'show');
    }

    public function show(Seminar $seminar)
    {
        $seminar->load(['posts' => fn ($query) => $query->published()->available()]);

        return view('seminars.show', compact('seminar'));
    }

    public function uploadVideosfromPlaylist(Seminar $seminar)
    {
        // Import z YouTube zapisuje do kanála seminára — smie ho spustiť len
        // jeho správca. Doteraz stačilo byť prihlásený a poznať ID seminára.
        $this->authorize('update', $seminar);

        $canal = Canal::whereId($seminar->canal_id)->first();

        abort_if($canal === null, 404);

        $videoUploader = new VideoUploadSeminars($seminar, $canal);
        $videoUploader->handle();

        return redirect()->route('profile.canals.seminars.show', [$canal->id, $seminar->id]);
    }
}
