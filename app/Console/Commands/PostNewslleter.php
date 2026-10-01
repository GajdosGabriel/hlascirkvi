<?php

namespace App\Console\Commands;


use Carbon\Carbon;
use App\Mail\PostNewsletter;
use App\Services\Newsletter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;



class PostNewslleter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MonthlyNewsletter {--force : Rozoslať aj keď sa tento mesiac už rozosielalo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send posts monthly report to all users';

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

        if (! (new Newsletter)->mountlyNewsletter((bool) $this->option('force'))) {
            $this->warn('Newsletter za tento mesiac už bol rozoslaný. Znova ho pošle --force.');
        }
    }
}
