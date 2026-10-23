<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        // Testy píšu do vlastného súboru, aby nezahlcovali laravel.log.
        'testing' => [
            'driver' => 'single',
            'path' => storage_path('logs/testing.log'),
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Denník udalostí (tabuľka system_logs)
    |--------------------------------------------------------------------------
    |
    | Koľko dní sa záznamy držia. Bežných (info) je veľa — hlavne odoslané
    | maily a prihlásenia; varovaní a chýb je málo a pri hľadaní príčiny sú
    | najcennejšie, preto žijú dlhšie. Maže model:prune v noci.
    |
    */

    'system_log' => [
        'days' => (int) env('SYSTEM_LOG_DAYS', 31),
        'error_days' => (int) env('SYSTEM_LOG_ERROR_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hlásenie chýb z laravel.log e-mailom
    |--------------------------------------------------------------------------
    |
    | Príkaz logs:report-errors číta každých 10 minút prírastok logu a chyby
    | (ERROR a vyššie), ktoré sa dajú opraviť v systéme, pošle jedným
    | súhrnným mailom. Bez LOG_REPORT_EMAIL idú superadminom.
    |
    | `ignore` je šum, s ktorým sa nedá nič urobiť — výpadky siete a cudzích
    | služieb, preklepy v artisan príkazoch. Podreťazec sa hľadá v správe aj
    | v triede výnimky (bez ohľadu na veľkosť písmen).
    |
    */

    'report' => [
        'enabled' => (bool) env('LOG_REPORT_ENABLED', true),
        'email' => env('LOG_REPORT_EMAIL'),
        'path' => storage_path('logs/laravel.log'),
        'state' => storage_path('app/log-report.json'),
        'max_bytes' => 5 * 1024 * 1024,
        'ignore' => [
            'Illuminate\Http\Client\ConnectionException',
            'cURL error',
            'Could not connect',
            'timed out',
            'nedostupn',
            'quotaExceeded',
            'Symfony\Component\Console\Exception\\',
            'DebugBar\\',
            // Návrat zo zastaraného OAuth prihlásenia (tlačidlo Späť, dva taby).
            'Laravel\Socialite\Two\InvalidStateException',
        ],
    ],

];
