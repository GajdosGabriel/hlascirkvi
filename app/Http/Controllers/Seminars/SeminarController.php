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
        $isPublic = Seminar::published()->whereKey($seminar->id)->exists();
        abort_if(! $isPublic && \Illuminate\Support\Facades\Gate::denies('view', $seminar), 404);
        $posts = $seminar->posts()->published()->available()->orderBy('posts.id')->paginate(24);
        $seminar->setRelation('posts', $posts->getCollection());

        return view('seminars.show', compact('seminar', 'posts', 'isPublic'));
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
