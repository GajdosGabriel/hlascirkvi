<?php

namespace App\Console\Commands;

use App\Services\Extractor\ExtractMojaKomunita;
use Illuminate\Console\Command;

class PrayerMojaKomunita extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prayer:mojaKomunita';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        (new ExtractMojaKomunita())->parseListUrl();
    }
}
