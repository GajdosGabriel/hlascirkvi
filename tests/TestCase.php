<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication {
        createApplication as bootApplication;
    }

    /**
     * Poistka pred zmazaním vývojovej databázy. Pri uloženej konfigurácii
     * (bootstrap/cache/config.php) Laravel ignoruje DB_DATABASE z phpunit.xml
     * a RefreshDatabase by vymazal databázu, nad ktorou beží web. Kontrola je
     * pred setUpTraits, teda skôr, než sa RefreshDatabase dotkne databázy.
     */
    public function createApplication()
    {
        $app = $this->bootApplication();

        $connection = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($database !== ':memory:' && ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Testy by bežali nad databázou „{$database}“, nie nad testovacou (*_test). "
                . 'Spusti `php artisan config:clear` a skús znova.'
            );
        }

        return $app;
    }
}
