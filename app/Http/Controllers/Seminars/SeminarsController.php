<?php

namespace App\Http\Controllers\Seminars;

use App\Seminar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Organization;
use App\Services\VideoUpload;
use App\Services\VideoUploadSeminars;

class SeminarsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('index', 'show');
    }

    public function uploadVideosfromPlaylist(Seminar $seminar)
    {
        $organization = Organization::whereId($seminar->organization_id)->first();

        $videoUploader = new VideoUploadSeminars($seminar, $organization);
        $videoUploader->handle();

        return redirect()->route('organization.seminar.show', [$organization->id, $seminar->id]);
    }
}
