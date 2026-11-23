<?php

namespace App\Http\Controllers\Canal;

use App\Http\Controllers\Controller;
use App\Services\Youtube\YoutubeSource;
use Illuminate\Http\Request;

/**
 * Kontrola zdroja videí pod poľom „YouTube kanál alebo playlist“ vo formulári
 * kanála. Pole vidí len admin, tak aj kontrolu — každá stojí kvótu YouTube API.
 */
class YoutubeSourceController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('admin');

        $value = $request->validate(['value' => 'nullable|string|max:300'])['value'] ?? '';

        return response()->json(YoutubeSource::check($value));
    }
}
