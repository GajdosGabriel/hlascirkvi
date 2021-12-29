<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class YoutubeCommentsExtract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract youtube comments from youtube. Check if video is avaible. Check if video can shere another sites';

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
        (new YoutubeCommentsExtract())->getComments();
    }
}
