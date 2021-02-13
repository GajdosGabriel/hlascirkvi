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
        $schedule->command('UserSearchByChannelAndPlaylist')->dailyAt('16:24');

        $schedule->command('UserSearchByName')->dailyAt('06:55');


//        $schedule->command('PublisherBufferVideo')->everyMinute();
        $schedule->command('PublisherBufferVideo')->twiceDaily(7, 17);


        $schedule->command('UserSearchByChannelAndPlaylist')->sundays()
            ->hourly()
            ->between('12:00', '16:00');

//        $schedule->command('UserSearchByChannelAndPlaylist')->everyMinute();

//        $schedule->command('Ecav:generate')->everyMinute();
        $schedule->command('ecav:extract')->hourly();
        $schedule->command('tkkbs:extract')->hourly();
        $schedule->command('prayer:extract')->hourly();

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
