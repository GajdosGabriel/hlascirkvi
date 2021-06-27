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

    public function show(Seminar $seminar)
    {
        return view('seminars.show', compact('seminar'));
    }

    public function edit(Seminar $seminar)
    {
        $this->authorize('update', $seminar);
        return view('seminars.edit', compact('seminar'));
    }

    public function create()
    {
        return view('seminars.create', [ 'seminar' => new Seminar()]);
    }

    public function update(Seminar $seminar, Request $request)
    {
        $this->authorize('update', $seminar);
        $seminar->update($request->all());

        if(request()->expectsJson()) {
            return $seminar;
        };

        return redirect()->route('seminars.show', $seminar->id);
    }

    public function store(Request $request)
    {
        Seminar::create(array_merge($request->all(), ['organization_id' => auth()->user()->org_id]));

        return redirect()->route('profile.seminars');
    }

    public function destroy(Seminar $seminar)
    {
        $this->authorize('update', $seminar);
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
