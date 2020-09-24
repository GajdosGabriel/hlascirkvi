<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManager;

class AvatarController extends Controller
{
    protected $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function store(Request $request) {
        $this->validate($request, [
            'image' => 'required|image|max:3055'

        ]);

        return response(null, 200);
    }
}
