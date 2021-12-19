<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImagesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        $images = Image::onlyTrashed()->get();
        return view('admins.images.index', compact('images'));
    }

    // Definitivne vymazanie obrázkov a záznamov v DB
    public function destroy()
    {
        $images = Image::onlyTrashed()->get();

        foreach( $images as $image)
        {
            // delete big img
            Storage::disk('public')->delete($image->url);

            // delete small img
            Storage::disk('public')->delete($image->thumb);

            $image->forceDelete();
        }

        session()->flash('flash', 'Obrázky boli definitívne vymazané!');

        return redirect()->route('admin.images.index');
    }
}
