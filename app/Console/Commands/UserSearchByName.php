<?php

namespace App\Console\Commands;

use App\Services\ImageResize;
use App\Jobs\DownloaderYoutube;
use Illuminate\Console\Command;
use App\Services\VideoUploadByUserName;
use App\Repositories\Eloquent\EloquentOrganizationRepository;


class UserSearchByName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UserSearchByName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search video by user name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Hľadá názvy jednotlivých userov podla updater dni v týždni
        (new VideoUploadByUserName())->handle();
    }

}
