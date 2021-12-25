<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\VideoUpload;
use App\Http\Controllers\Controller;
use App\Services\Extractor\ExtractTkkbs;
use App\Services\Extractor\ExtractVyveska;

class ManualDownloaderController extends Controller
{
    public function index(){

        (new VideoUpload)->handle();
        (new ExtractVyveska())->parseListUrl();
        (new ExtractTkkbs())->parseListUrl();

        return redirect()->route('admin.home');
    }
}
