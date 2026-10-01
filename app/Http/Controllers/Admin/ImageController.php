<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\SystemLog\Recorder;

class ImageController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'checkSuperAdmin']);
    }

    public function index() {
        $images = Image::onlyTrashed()->get();
        return view('admins.images.index', compact('images'));
    }

    // Definitivne vymazanie obrázkov a záznamov v DB
    public function destroy()
    {
        $deleted = 0;

        // Po dávkach, aby sa pri tisíckach obrázkov nenačítalo všetko naraz.
        Image::onlyTrashed()->chunkById(200, function ($images) use (&$deleted) {
            foreach ($images as $image) {
                // delete big img + small img
                \App\Support\MediaUrl::disk()->delete($image->url);
                \App\Support\MediaUrl::disk()->delete($image->thumb);

                $image->forceDelete();
                $deleted++;
            }
        });

        Recorder::info('admin', 'images_purged', 'Definitívne vymazané obrázky z koša',
            userId: auth()->id(), context: ['count' => $deleted]);

        session()->flash('flash', 'Obrázky boli definitívne vymazané!');

        return redirect()->route('admin.image.index');
    }
}
