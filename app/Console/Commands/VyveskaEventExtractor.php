<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Extractor\ExtractVyveska;

class VyveskaEventExtractor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vyveska:extract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract events from vyveska.sk/najnovsie';

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
        (new ExtractVyveska())->parseListUrl();
    }
}
