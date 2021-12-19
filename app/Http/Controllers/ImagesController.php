<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImagesController extends Controller
{
    public function destroy(Image $image)
    {
        $image->delete();
    }
}
