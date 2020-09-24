<?php

namespace App\Console\Commands;

use App\Jobs\DownloaderYoutube;
use App\Services\VideoUpload;
use Illuminate\Console\Command;


class UserSearchByChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UserSearchByChannelAndPlaylist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search new videos by User channel';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

         (new VideoUpload)->handle();

    }



}
