<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\VideoUpload;
use App\Http\Controllers\Controller;
use App\Services\Extractor\ExtractEcav;
use App\Services\VideoUploadByUserName;
use App\Services\Extractor\ExtractTkkbs;
use App\Services\Extractor\ExtractVyveska;
use App\Services\Extractor\ExtractMojaKomunita;
use App\Services\Extractor\ExtractYoutubeComment;
use App\Services\Extractor\ExtractZdruzenieMedaily;

class ManualDownloaderController extends Controller
{
    public function videa()
    {
        (new VideoUpload)->handle();
        (new VideoUploadByUserName())->handle();

        return redirect()->route('admin.home');
    }

    public function akcie()
    {
        (new ExtractVyveska())->parseListUrl();
        (new ExtractTkkbs())->parseListUrl();
        (new ExtractEcav())->parseListUrl();

        return redirect()->route('admin.home');
    }

    public function modlitby()
    {
        (new ExtractMojaKomunita())->parseListUrl();
        (new ExtractZdruzenieMedaily())->parseListUrl();
        return redirect()->route('admin.home');
    }

    public function comments()
    {
        (new ExtractYoutubeComment())->handle();
        return redirect()->route('admin.home');
    }
}
