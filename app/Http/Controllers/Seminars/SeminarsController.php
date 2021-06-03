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
    public function index()
    {
        $seminars = Seminar::whereOrganizationId(auth()->user()->org_id)->get();

        return view('profiles.seminars', ['seminars' => $seminars]);
    }

    public function show(Seminar $seminar)
    {
        return view('seminars.show', compact('seminar'));
    }

    public function edit(Seminar $seminar)
    {
        return view('seminars.edit', compact('seminar'));
    }

    public function create()
    {
        return view('seminars.create', [ 'seminar' => new Seminar()]);
    }

    public function update(Seminar $seminar, Request $request)
    {
        $seminar->update($request->all());

        if(request()->expectsJson()) {
            return $seminar;
        };

        return redirect()->route('seminars.show', $seminar->id);
    }

    public function store(Request $request)
    {
        Seminar::create(array_merge($request->all(), ['organization_id' => auth()->user()->org_id]));

        return redirect()->route('seminars.index');
    }

    public function destroy(Seminar $seminar)
    {
        $seminar->posts()->detach();
        $seminar->delete();
        return redirect()->route('seminars.index');
    }

    public function uploadVideos(Seminar $seminar)
    {
        $organization = Organization::whereId($seminar->organization_id)->first();

        $videoUploader = new VideoUploadSeminars($seminar, $organization);
        $videoUploader->handle();

        return redirect()->route('seminars.show', $seminar->id);
    }
}
