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
        Commands\EcavEventExtractor::class,
        Commands\TkkbsExtractor::class
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


        // $schedule->command('UserSearchByChannelAndPlaylist')->everyMinute();
        $schedule->command('UserSearchByChannelAndPlaylist')->dailyAt('16:24');
        
        // Na každý den iná zostava podľa updater
        $schedule->command('UserSearchByName')->dailyAt('06:55');


            //    $schedule->command('PublisherBufferVideo')->everyMinute();
            //    $schedule->command('PublisherBufferVideo')->twiceDaily(7, 17);
        //        $schedule->command('PublisherBufferVideo')
        //            ->twiceDaily(7, 8)
        //            ->twiceDaily(10, 13)
        //            ->twiceDaily(15, 17)
        //        ;

        // $schedule->command('PublisherBufferVideo')->everyMinute();
        // $schedule->command('PublisherBufferVideo')->twiceDaily(7, 8);
        $schedule->command('PublisherBufferVideo')->twiceDaily(9, 12);
        // $schedule->command('PublisherBufferVideo')->twiceDaily(14, 16);
        $schedule->command('PublisherBufferVideo')->twiceDaily(17, 19);
        //        $schedule->command('PublisherBufferVideo')->hourly();



        $schedule->command('UserSearchByChannelAndPlaylist')->sundays()
            ->hourly()
            ->between('12:00', '16:00');

        //  $schedule->command('UserSearchByChannelAndPlaylist')->everyMinute();

        // Nevyriešenei kodovanie a preto dupľuje záznamy
        $schedule->command('ecav:extract')->hourly();
        $schedule->command('event:tkkbs')->hourly();
        $schedule->command('event:vyveska')->hourly();
        $schedule->command('prayer:zdruzenieMedaily')->hourly();
        $schedule->command('prayer:sluzobniceDuchaSvateho')->hourly();
        // Dočasné vypnuté lebo sa opakuje
        // $schedule->command('prayer:mojaKomunita')->hourlyAt(45);
        
        $schedule->command('youtube:comments')->hourlyAt(17);

        // $schedule->command('prayer:fulfilledOrNotYet')->everyMinute();
        $schedule->command('prayer:fulfilledOrNotYet')->dailyAt('17:20');
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
