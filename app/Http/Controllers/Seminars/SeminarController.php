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
        $organization = Organization::whereId($seminar->organization_id)->first();

        $videoUploader = new VideoUploadSeminars($seminar, $organization);
        $videoUploader->handle();

        return redirect()->route('profile.organization.seminar.show', [$organization->id, $seminar->id]);
    }
}
