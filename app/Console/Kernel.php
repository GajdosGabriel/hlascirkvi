<?php

namespace App\Console;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UserSearchByChannel::class,
        Commands\UserSearchByName::class,
        Commands\BufferPublisher::class,
        Commands\PostNewslleter::class,
        Commands\ImageAudit::class,
        Commands\ImageDimensions::class,
        Commands\ImageRefetch::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('MonthlyNewsletter')->dailyAt('08:20');
        // $schedule->command('MonthlyNewsletter')->everyMinute();

        // $schedule->command('MonthlyNewsletter')->monthlyOn(4, '08:20');


        /*
         * Príkazy siahajúce na cudzie API majú withoutOverlapping(). YouTube
         * klient (App\Services\Youtube\YoutubeApi) má timeout aj retry, beh
         * cez stovky kanálov však trvá dlhšie ako interval niektorých
         * spustení — bez zámku by sa na seba navrstvili.
         */

        // $schedule->command('UserSearchByChannelAndPlaylist')->everyMinute();
        $schedule->command('UserSearchByChannelAndPlaylist')->dailyAt('16:24')->withoutOverlapping();

        // Na každý den iná zostava podľa updater
        $schedule->command('UserSearchByName')->dailyAt('06:55')->withoutOverlapping();


        // Buffer sa vypúšťa po jednom počas celého dňa. Príkaz beží často, ale
        // väčšina behov len skončí — sám si drží denný plán nepravidelných
        // časov (config/buffer.php), aby to nevyzeralo ako dávka o 16:24.
        $schedule->command('PublisherBufferVideo')
            ->everyFiveMinutes()
            ->between('06:50', '21:30')
            ->withoutOverlapping();




        $schedule->command('UserSearchByChannelAndPlaylist')->sundays()
            ->hourly()
            ->between('12:00', '16:00')
            ->withoutOverlapping();

        //  $schedule->command('UserSearchByChannelAndPlaylist')->everyMinute();

        $schedule->command('prayer:zdruzenieMedaily')->hourly()->withoutOverlapping();
        $schedule->command('prayer:sluzobniceDuchaSvateho')->hourly()->withoutOverlapping();
        // Dočasné vypnuté lebo sa opakuje
        // $schedule->command('prayer:mojaKomunita')->hourlyAt(45);

        $schedule->command('youtube:comments')->hourlyAt(17)->withoutOverlapping();

        // Zhrnutia popisov ("V skratke" na detaile). Vypínač, veľkosť dávky
        // a mesačný limit sú v administrácii (/admin/ai); vypnuté = príkaz len skončí.
        $schedule->command('posts:summarize')->hourlyAt(40)->withoutOverlapping();

        // $schedule->command('prayer:fulfilledOrNotYet')->everyMinute();
        $schedule->command('prayer:fulfilledOrNotYet')->dailyAt('17:20');

        // Tabuľka `views` je len pamäť na „tento návštevník tu dnes už bol",
        // trvalý počet drží posts.count_view. Bez preriedenia by rástla donekonečna.
        $schedule->command('app:views-prune')->dailyAt('03:20');

        // Liturgické čítania z KBS na 45 dní dopredu. Chýbajúce dni si síce
        // stránka stiahne aj sama, ale výpadok KBS tak web neucíti.
        $schedule->command('liturgia:stiahnut')->dailyAt('03:35')->withoutOverlapping();

        // Úvod administrácie počíta súhrny cez celé tabuľky; drží sa zahriaty
        // v cache, aby sa /admin/home neotváral sekundu a viac.
        $schedule->call(fn () => app(\App\Services\Dashboard\AdminDashboardStats::class)->warm())
            ->name('admin:dashboard-warm')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
