<?php

namespace App\Console;

use App\Console\Commands\PostNewslleter;
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
        $schedule->command('MonthlyNewsletter')->dailyAt('10:45');
        // $schedule->command('MonthlyNewsletter')->everyMinute();
        // $schedule->command('MonthlyNewsletter')->monthlyOn(4, '15:00');;


        $schedule->command('UserSearchByChannelAndPlaylist')->dailyAt('16:24');
        $schedule->command('UserSearchByName')->dailyAt('06:55');


//        $schedule->command('PublisherBufferVideo')->everyMinute();
//        $schedule->command('PublisherBufferVideo')->twiceDaily(7, 17);
//        $schedule->command('PublisherBufferVideo')
//            ->twiceDaily(7, 8)
//            ->twiceDaily(10, 13)
//            ->twiceDaily(15, 17)
//        ;

                $schedule->command('PublisherBufferVideo')->twiceDaily(7, 8);
                $schedule->command('PublisherBufferVideo')->twiceDaily(10, 12);
                $schedule->command('PublisherBufferVideo')->twiceDaily(14, 16);
                $schedule->command('PublisherBufferVideo')->twiceDaily(17, 19);
//        $schedule->command('PublisherBufferVideo')->hourly();



        $schedule->command('UserSearchByChannelAndPlaylist')->sundays()
            ->hourly()
            ->between('12:00', '16:00');

//        $schedule->command('UserSearchByChannelAndPlaylist')->everyMinute();

//        $schedule->command('Ecav:generate')->everyMinute();
        $schedule->command('ecav:extract')->hourly();
        $schedule->command('tkkbs:extract')->hourly();
        $schedule->command('prayer:zdruzenieMedaily')->hourly();
        $schedule->command('prayer:mojaKomunita')->hourlyAt(35);

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
