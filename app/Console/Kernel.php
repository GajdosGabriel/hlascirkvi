<?php

namespace App\Console;

use App\Models\PendingComment;
use App\Models\PendingFavorite;
use App\Models\PendingPrayer;
use App\Models\PendingRegistration;
use App\Models\SystemLog;
use App\Services\Dashboard\AdminDashboardStats;
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
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
         * Príkazy siahajúce na cudzie API majú withoutOverlapping(). YouTube
         * klient (App\Services\Youtube\YoutubeApi) má timeout aj retry, beh
         * cez stovky kanálov však trvá dlhšie ako interval niektorých
         * spustení — bez zámku by sa na seba navrstvili.
         *
         * Beh cez všetky kanály je len jeden denne: každý ďalší míňa kvótu
         * YouTube API (chyby quotaExceeded).
         */
        $schedule->command('UserSearchByChannelAndPlaylist')->dailyAt('16:24')->withoutOverlapping();

        // Týždenná zostava podľa import_day a zameškané kontroly.
        $schedule->command('UserSearchByName')->dailyAt('06:55')->withoutOverlapping();

        // Buffer sa vypúšťa po jednom počas celého dňa. Príkaz beží často, ale
        // väčšina behov len skončí — sám si drží denný plán nepravidelných
        // časov (config/buffer.php), aby to nevyzeralo ako dávka o 16:24.
        $schedule->command('PublisherBufferVideo')
            ->everyFiveMinutes()
            ->between('06:50', '21:30')
            ->withoutOverlapping();

        // Zdroje modlitbových úmyslov: zoznam je v config/prayer.php.
        foreach (config('prayer.sources', []) as $source) {
            if ($source['enabled'] ?? false) {
                $schedule->command($source['command'])
                    ->hourlyAt($source['minute'] ?? 0)
                    ->withoutOverlapping();
            }
        }

        $schedule->command('youtube:comments')->hourlyAt(17)->withoutOverlapping();
        $schedule->command('comments:moderate')->dailyAt('03:45')->withoutOverlapping();

        // Zhrnutia sa automaticky kontrolujú iba pred vydaním z buffera.
        $schedule->command('canals:enrich')->everyFifteenMinutes()->withoutOverlapping();

        // Hosť z YouTube na webe odpovedať nemôže — po 3 hodinách za neho
        // zareaguje portál (App\Services\GuestReplier).
        $schedule->command('comments:reply-to-guests')->everyFifteenMinutes()->withoutOverlapping();

        $schedule->command('prayer:fulfilledOrNotYet')->dailyAt('17:20');

        // Tabuľka `views` je len pamäť na „tento návštevník tu dnes už bol",
        // trvalý počet drží posts.count_view. Bez preriedenia by rástla donekonečna.
        $schedule->command('app:views-prune')->dailyAt('03:20');

        // Denník udalostí (admin → Denník) je krátka pamäť: info po mesiaci,
        // chyby po troch (config/logging.php → system_log). Maže po dávkach.
        $schedule->command('model:prune', ['--model' => [SystemLog::class]])
            ->dailyAt('03:25')
            ->withoutOverlapping();

        // IP posledného prihlásenia sa drží 90 dní od prihlásenia.
        $schedule->command('users:prune-login-ips')->dailyAt('03:30')->withoutOverlapping();

        // Nepotvrdené registrácie z formulára (App\Models\PendingRegistration)
        // po vypršaní odkazu. Skutočný účet z nich nikdy nevznikol.
        // Rovnako nepotvrdené modlitby a komentáre (PendingPrayer, PendingComment).
        $schedule->command('model:prune', ['--model' => [
            PendingRegistration::class,
            PendingPrayer::class,
            PendingComment::class,
            PendingFavorite::class,
        ]])
            ->dailyAt('03:27')
            ->withoutOverlapping();

        // Po troch dňoch bez potvrdenia jedna pripomienka. Hodinovo, aby
        // prišla zhruba v tú dennú dobu, keď sa človek registroval.
        $schedule->command('registrations:remind')->hourlyAt(12)->withoutOverlapping();
        $schedule->command('pending:remind')->hourlyAt(14)->withoutOverlapping();

        // Liturgické čítania z KBS na 45 dní dopredu. Chýbajúce dni si síce
        // stránka stiahne aj sama, ale výpadok KBS tak web neucíti.
        $schedule->command('liturgia:stiahnut')->dailyAt('03:35')->withoutOverlapping();

        // Úvod administrácie počíta súhrny cez celé tabuľky; drží sa zahriaty
        // v cache, aby sa /admin/home neotváral sekundu a viac.
        $schedule->call(fn () => app(AdminDashboardStats::class)->warm())
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
