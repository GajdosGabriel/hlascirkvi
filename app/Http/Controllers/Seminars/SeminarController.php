<?php

namespace App\Http\Controllers\Seminars;

use App\Models\Seminar;
use App\Models\Organization;
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
        return view('seminars.show', compact('seminar'));
    }

    public function uploadVideosfromPlaylist(Seminar $seminar)
    {
        // Import z YouTube zapisuje do kanála seminára — smie ho spustiť len
        // jeho správca. Doteraz stačilo byť prihlásený a poznať ID seminára.
        $this->authorize('update', $seminar);

        $organization = Organization::whereId($seminar->organization_id)->first();

        abort_if($organization === null, 404);

        $videoUploader = new VideoUploadSeminars($seminar, $organization);
        $videoUploader->handle();

        return redirect()->route('profile.organization.seminar.show', [$organization->id, $seminar->id]);
    }
}
