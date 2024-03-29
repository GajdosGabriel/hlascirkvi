<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\Prayers\UnansweredPrayers;

class PrayerFulfilled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prayer:fulfilledOrNotYet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asking if prayer is fulfilled or not yet';

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
        $prayers = new UnansweredPrayers; 
        $prayers->prayersForAsking();
    }
}
