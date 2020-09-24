<?php

namespace App\Console\Commands;


use App\Services\Extractor\ExtractEcav;
use Illuminate\Console\Command;

class EcavEventExtractor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecav:extract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract events from ecav.sk/pozvanky';

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
        (new ExtractEcav())->parseListUrl();
    }
}
